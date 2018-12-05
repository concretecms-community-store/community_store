<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Cart;

use Session;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product as StoreProduct;
use Concrete\Package\CommunityStore\Src\CommunityStore\Shipping\Method\ShippingMethod as StoreShippingMethod;
use Concrete\Package\CommunityStore\Src\CommunityStore\Discount\DiscountRule as StoreDiscountRule;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductVariation\ProductVariation as StoreProductVariation;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductOption\ProductOption as StoreProductOption;

class Cart
{
    protected static $cart = null;
    protected static $discounts = null;
    protected static $hasChanged = false;

    // if force set to true, will get cart details fresh, useful if programatically adding things to the cart
    public static function getCart($force = false)
    {
        // this acts as a singleton, in that it wil only fetch the cart from the session and check it for validity once per request
        if (!isset(self::$cart) || $force) {
            $cart = Session::get('communitystore.cart');
            if (!is_array($cart)) {
                Session::set('communitystore.cart', []);
                $cart = [];
            }

            $checkeditems = [];
            $update = false;
            // loop through and check if product hasn't been deleted. Remove from cart session if not found.
            foreach ($cart as $cartitem) {
                $cartitem['product']['qty'] = round($cartitem['product']['qty'], 4);

                $product = StoreProduct::getByID((int) $cartitem['product']['pID']);

                if ($product) {
                    // check that we dont have a non-quantity product in cart with a quantity > 1
                    if (!$product->allowQuantity() && $cartitem['product']['qty'] > 1) {
                        $cartitem['product']['qty'] = 1;
                        $update = true;
                    }

                    $include = true;

                    if ($cartitem['product']['variation']) {
                        if (!StoreProductVariation::getByID($cartitem['product']['variation'])) {
                            $include = false;
                            $update = true;
                        } else {
                            $product->shallowClone = true;
                            $product = clone $product;
                            $product->setVariation($cartitem['product']['variation']);
                        }
                    }

                    // if the cart has greater stock than available
                    if (!$product->isUnlimited() && !$product->allowBackOrders() && $cartitem['product']['qty'] > $product->getQty()) {
                        if ($product->getQty() > 0) {
                            $cartitem['product']['qty'] = $product->getQty(); // set to how many are left
                        } else {
                            $include = false; // otherwise none left, remove from cart
                        }
                        $update = true;
                    }

                    if ($include) {
                        $cartitem['product']['object'] = $product;
                        $checkeditems[] = $cartitem;
                    }
                } else {
                    $update = true;
                }
            }

            if ($update) {
                Session::set('communitystore.cart', $checkeditems);
                self::$hasChanged = true;
            }

            self::$discounts = [];

            $rules = StoreDiscountRule::findAutomaticDiscounts(null, $checkeditems);

            $code = trim(Session::get('communitystore.code'));
            if ($code) {
                $coderules = StoreDiscountRule::findDiscountRuleByCode($code);

                if (count($coderules)) {
                    $rules = array_merge($rules, $coderules);
                } else {
                    Session::set('communitystore.code', '');
                }
            }

            if (count($rules) > 0) {
                foreach ($rules as $rule) {
                    $discountProductGroups = $rule->getProductGroups();
                    $include = true;
                    $matchingprods = [];

                    if (!empty($discountProductGroups)) {
                        $include = false;
                        foreach ($checkeditems as $cartitem) {
                            $groupids = $cartitem['product']['object']->getGroupIDs();

                            if (count(array_intersect($discountProductGroups, $groupids)) > 0) {
                                $include = true;
                                $cartitem['product']['object']->addDiscountRule($rule);
                            }
                        }
                    } else {
                        foreach ($checkeditems as $key => $cartitem) {
                            $cartitem['product']['object']->addDiscountRule($rule);
                        }
                    }

                    if ($include) {
                        self::$discounts[] = $rule;
                    }
                }
            }

            self::$cart = $checkeditems;
        }

        return self::$cart;
    }

    public static function hasChanged()
    {
        return self::$hasChanged;
    }

    public static function getDiscounts()
    {
        if (!isset(self::$cart)) {
            self::getCart();
        }

        return self::$discounts;
    }

    public static function add($data)
    {
        $error = false;
        Session::set('community_store.smID', false);
        $product = StoreProduct::getByID((int) $data['pID']);

        $customerPrice = false;

        if ($product->allowCustomerPrice()) {
            $customerPrice = (float) $data['customerPrice'];

            $max = $product->getPriceMaximum();
            $min = $product->getPriceMinimum();

            if (!is_null($min) && $customerPrice < (float) $min) {
                $error = true;
            }

            if (!is_null($max) && $customerPrice > (float) $max) {
                $error = true;
            }
        }

        if (!$product) {
            $error = true;
        }

        if (!$error) {
            if ($product->isExclusive()) {
                self::clear();
            }

            //now, build a nicer "cart item"
            $cartItem = [];

            if (!$product->allowDecimalQuantity()) {
                $data['quantity'] = (int) $data['quantity'];
            }

            if ($customerPrice) {
                $cartItem['product'] = [
                    "pID" => (int) $data['pID'],
                    "qty" => $data['quantity'],
                    "customerPrice" => $customerPrice,
                ];
            } else {
                $cartItem['product'] = [
                    "pID" => (int) $data['pID'],
                    "qty" => $data['quantity'],
                ];
            }

            unset($data['pID']);
            unset($data['quantity']);
            unset($data['customerPrice']);
            unset($data['ccm_token']);

            //since we removed the ID/qty, we're left with just the attributes
            $cartItem['productAttributes'] = $data;

            $removeexistingexclusive = false;

            foreach (self::getCart() as $k => $cart) {
                $cartproduct = StoreProduct::getByID((int) $cart['product']['pID']);

                if ($cartproduct && $cartproduct->isExclusive()) {
                    self::remove($k);
                    $removeexistingexclusive = true;
                }
            }

            $optionItemIds = [];
            $optionsInVariations = [];

            // search for product options, if found, collect the id
            foreach ($cartItem['productAttributes'] as $name => $value) {
                $groupID = false;
                $isOptionList = false;

                if ('po' == substr($name, 0, 2)) {
                    $isOptionList = true;
                    $groupID = str_replace("po", "", $name);

                    if (!$value) {
                        $error = true;  // if we have select option but no value
                    }
                } elseif ('pt' == substr($name, 0, 2)) {
                    $groupID = str_replace("pt", "", $name);
                } elseif ('pa' == substr($name, 0, 2)) {
                    $groupID = str_replace("pa", "", $name);
                } elseif ('ph' == substr($name, 0, 2)) {
                    $groupID = str_replace("ph", "", $name);
                } elseif ('pc' == substr($name, 0, 2)) {
                    $groupID = str_replace("pc", "", $name);
                }

                // if there is a groupID, check to see if it's a required field, reject if no value
                if ($groupID) {
                    $option = StoreProductOption::getByID($groupID);

                    if ($isOptionList && $option->getIncludeVariations()) {
                        $optionsInVariations[] = $value;
                    }

                    if ($option->getRequired() && !$value) {
                        $error = true;
                    }
                }
            }

            if (!empty($optionsInVariations) && $product->hasVariations()) {
                // find the variation via the ids of the options
                $variation = StoreProductVariation::getByOptionItemIDs($optionsInVariations);

                // association the variation with the product
                if ($variation) {
                    $options = $variation->getOptions();
                    if (count($options) == count($optionsInVariations)) {  // check if we've matched to a variation with the correct number of options
                        $product->setVariation($variation);
                        $cartItem['product']['variation'] = $variation->getID();
                    } else {
                        $error = true;
                    }
                } else {
                    $error = true; // variation not matched
                }
            } elseif ($product->hasVariations()) {
                $error = true;  // if we have a product with variations, but no variation data was submitted, it's a broken add-to-cart form
            }
        }

        if (!$error) {
            $cart = self::getCart();

            $exists = self::checkForExistingCartItem($cartItem);

            if (true === $exists['exists'] && !isset($cartItem['product']['customerPrice'])) {
                $existingproductcount = $cart[$exists['cartItemKey']]['product']['qty'];

                //we have a match, update the qty
                if ($product->allowQuantity()) {
                    $newquantity = $cart[$exists['cartItemKey']]['product']['qty'] + $cartItem['product']['qty'];

                    if (!$product->isUnlimited() && !$product->allowBackOrders() && $product->getQty() < max($newquantity, $existingproductcount)) {
                        $newquantity = $product->getQty();
                    }

                    if ($product->getMaxQty() > 0) {
                        if ($newquantity > $product->getMaxQty()) {
                            $newquantity = $product->getMaxQty();
                        }
                    }

                    $added = $newquantity - $existingproductcount;
                } else {
                    $added = 1;
                    $newquantity = 1;
                }

                $cart[$exists['cartItemKey']]['product']['qty'] = $newquantity;
            } else {
                $newquantity = $cartItem['product']['qty'];

                if (!$product->isUnlimited() && !$product->allowBackOrders() && $product->getQty() < $newquantity) {
                    $newquantity = $product->getQty();
                }

                if ($product->getMaxQty() > 0) {
                    if ($newquantity > $product->getMaxQty()) {
                        $newquantity = $product->getMaxQty();
                    }
                }

                $cartItem['product']['qty'] = $newquantity;

                if ($cartItem['product']['qty'] > 0) {
                    if ($product->isExclusive()) {
                        $cart = [$cartItem];
                    } else {
                        $cart[] = $cartItem;
                    }
                }

                $added = $newquantity;
            }

            self::$cart = $cart;
            Session::set('communitystore.cart', $cart);
        }

        return ['added' => $added, 'error' => $error, 'exclusive' => $product->isExclusive(), 'removeexistingexclusive' => $removeexistingexclusive];
    }

    public function checkForExistingCartItem($cartItem)
    {
        foreach (self::getCart() as $k => $cart) {
            //  check if product is the same id first.
            if ($cart['product']['pID'] == $cartItem['product']['pID']) {
                // check if the number of attributes is the same
                if (count($cart['productAttributes']) == count($cartItem['productAttributes'])) {
                    if (empty($cartItem['productAttributes'])) {
                        // if we have no attributes, it's a direct match
                        return ['exists' => true, 'cartItemKey' => $k];
                    } else {
                        // otherwise loop through attributes
                        $attsmatch = true;

                        foreach ($cartItem['productAttributes'] as $key => $value) {
                            if (array_key_exists($key, $cart['productAttributes']) && $cart['productAttributes'][$key] == $value) {
                                // attributes match, keep checking
                            } else {
                                //different attributes means different "product".
                                $attsmatch = false;
                                break;
                            }
                        }

                        if ($attsmatch) {
                            return ['exists' => true, 'cartItemKey' => $k];
                        }
                    }
                }
            }
        }

        return ['exists' => false, 'cartItemKey' => null];
    }

    public static function updateMutiple($data)
    {
        Session::set('community_store.smID', false);
        $count = 0;
        $multipleResult = [];
        foreach ($data['instance'] as $instance) {
            $multipleResult[] = self::update(['instance' => $instance, 'pQty' => $data['pQty'][$count]]);
            ++$count;
        }

        return $multipleResult;
    }

    public static function update($data)
    {
        Session::set('community_store.smID', false);
        $instanceID = $data['instance'];
        $qty = $data['pQty'];

        $cart = self::getCart();

        $product = StoreProduct::getByID((int) $cart[$instanceID]['product']['pID']);

        if ($product && !$product->allowDecimalQuantity()) {
            $qty = (int) $data['pQty'];
        }

        if ($qty > 0 && $product) {
            $newquantity = $qty;

            if ($cart[$instanceID]['product']['variation']) {
                $product->setVariation($cart[$instanceID]['product']['variation']);
            }

            if (!$product->isUnlimited() && !$product->allowBackOrders() && $product->getQty() < $newquantity) {
                $newquantity = $product->getQty();
            }

            if ($product->getMaxQty() > 0) {
                if ($newquantity > $product->getMaxQty()) {
                    $newquantity = $product->getMaxQty();
                }
            }

            $cart[$instanceID]['product']['qty'] = $newquantity;
            $added = $newquantity;
        } else {
            self::remove($instanceID);
        }

        Session::set('communitystore.cart', $cart);
        self::$cart = null;

        return ['added' => $added];
    }

    public static function remove($instanceID)
    {
        Session::set('community_store.smID', false);
        $cart = self::getCart();
        unset($cart[$instanceID]);
        Session::set('communitystore.cart', $cart);
        self::$cart = null;
    }

    public static function clear()
    {
        Session::set('community_store.smID', false);
        $cart = self::getCart();
        unset($cart);
        Session::set('communitystore.cart', null);
        self::$cart = null;
    }

    public static function getTotalItemsInCart()
    {
        $total = 0;
        if (self::getCart()) {
            foreach (self::getCart() as $item) {
                $subtotal = min($item['product']['qty'], 1);
                $total = $total + $subtotal;
            }
        }

        return $total;
    }

    public static function isShippable()
    {
        $shippableItems = self::getShippableItems();
        $shippingMethods = StoreShippingMethod::getAvailableMethods();
        if (count($shippingMethods) > 0) {
            if (count($shippableItems) > 0) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public static function getShippableItems()
    {
        $shippableItems = [];
        //go through items
        if (self::getCart()) {
            foreach (self::getCart() as $item) {
                //check if items are shippable
                $product = StoreProduct::getByID($item['product']['pID']);
                if ($product->isShippable()) {
                    $shippableItems[] = $item;
                }
            }
        }

        return $shippableItems;
    }

    public static function getCartWeight($unit = '')
    {
        $totalWeight = 0;
        if (self::getCart()) {
            foreach (self::getCart() as $item) {
                $product = StoreProduct::getByID($item['product']['pID']);

                if ($item['product']['variation']) {
                    $product->setVariation($item['product']['variation']);
                }

                if ($product->isShippable()) {
                    $totalProductWeight = $product->getWeight() * $item['product']['qty'];
                    $totalWeight = $totalWeight + $totalProductWeight;
                }
            }
        }

        if ($unit) {
            $storeweightunit = \Config::get('community_store.weightUnit');

            if ($storeweightunit != $unit) {
                // convert to grams first
                if ('kg' == $storeweightunit) {
                    $totalWeight *= 1000;
                }

                if ('oz' == $storeweightunit) {
                    $totalWeight *= 28.3495;
                }

                if ('lb' == $storeweightunit) {
                    $totalWeight *= 453.592;
                }
                // end convert to grams

                if ('kg' == $unit) {
                    $totalWeight *= 0.001;
                }

                if ('oz' == $unit) {
                    $totalWeight *= 0.035274;
                }

                if ('lb' == $unit) {
                    $totalWeight *= 0.00220462;
                }
            }
        }

        //only returns weight of shippable items.
        return $totalWeight;
    }

    // determines if a cart requires a customer to be logged in
    public static function requiresLogin()
    {
        if (self::getCart()) {
            foreach (self::getCart() as $item) {
                $product = StoreProduct::getByID($item['product']['pID']);
                if ($product) {
                    if ($product->hasUserGroups() && !$product->createsLogin()) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    // determines if the cart contains a product that will auto-create a user account
    public function createsAccount()
    {
        if (self::getCart()) {
            foreach (self::getCart() as $item) {
                $product = StoreProduct::getByID($item['product']['pID']);
                if ($product) {
                    if ($product->createsLogin()) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    public static function setShippingInstructions($sInstructions)
    {
        \Session::set('communitystore.sInstructions', $sInstructions);
    }

    public static function getShippingInstructions()
    {
        return  \Session::get('communitystore.sInstructions');
    }
}
