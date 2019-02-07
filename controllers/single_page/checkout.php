<?php
namespace Concrete\Package\CommunityStore\Controller\SinglePage;

use PageController;
use Core;
use Session;
use Config;
use User;
use UserAttributeKey;
use Concrete\Package\CommunityStore\Src\CommunityStore\Order\Order as StoreOrder;
use Concrete\Package\CommunityStore\Src\CommunityStore\Cart\Cart as StoreCart;
use Concrete\Package\CommunityStore\Src\CommunityStore\Payment\Method as StorePaymentMethod;
use Concrete\Package\CommunityStore\Src\CommunityStore\Customer\Customer as StoreCustomer;
use Concrete\Package\CommunityStore\Src\CommunityStore\Discount\DiscountRule as StoreDiscountRule;
use Concrete\Package\CommunityStore\Src\CommunityStore\Discount\DiscountCode as StoreDiscountCode;
use Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Calculator as StoreCalculator;
use Concrete\Package\CommunityStore\Src\CommunityStore\Shipping\Method\ShippingMethod as StoreShippingMethod;
use Concrete\Package\CommunityStore\Entity\Attribute\Key\StoreOrderKey;
use Concrete\Core\Multilingual\Page\Section\Section;

class Checkout extends PageController
{
    public function view($guest = false)
    {
        $c = \Page::getCurrentPage();
        $al = Section::getBySectionOfSite($c);
        $langpath = '';
        if ($al !== null) {
            $langpath =  $al->getCollectionHandle();
        }

        if ($this->post()) {
            if ('code' == $this->post('action')) {
                $codeerror = false;
                $codesuccess = false;

                if ($this->post('code')) {
                    $codesuccess = StoreDiscountCode::storeCartCode($this->post('code'));
                    $codeerror = !$codesuccess;
                } else {
                    StoreDiscountCode::clearCartCode();
                }
            }

            $this->set('codeerror', $codeerror);
            $this->set('codesuccess', $codesuccess);
        }

        if ('all' == Config::get('community_store.shoppingDisabled')) {
            return \Redirect::to($langpath . '/');
        }

        $customer = new StoreCustomer();
        $this->set('customer', $customer);
        $guestCheckout = Config::get('community_store.guestCheckout');
        $this->set('guestCheckout', ($guestCheckout ? $guestCheckout : 'off'));
        $this->set('guest', isset($guest) && (bool) $guest);
        $this->set('requiresLogin', StoreCart::requiresLogin());
        $this->set('companyField', Config::get('community_store.companyField'));

        $cart = StoreCart::getCart();

        if (StoreCart::hasChanged()) {
            return \Redirect::to($langpath . '/cart/changed');
        }

        if (0 == StoreCart::getTotalItemsInCart()) {
            return \Redirect::to($langpath . '/cart');
        }
        $this->set('form', Core::make("helper/form"));

        $allcountries = Core::make('helper/lists/countries')->getCountries();

        $db = $this->app->make('database')->connection();

        $ak = UserAttributeKey::getByHandle('billing_address');

        if (version_compare(\Config::get('concrete.version'), '8.0', '>=')) {
            $keysettings = $ak->getController()->getAttributeKeySettings();
            $defaultBillingCountry = $keysettings->getDefaultCountry();
            $hasCustomerBillingCountries = $keysettings->hasCustomCountries();
            $availableBillingCountries = $keysettings->getCustomCountries();
        } else {
            $row = $db->GetRow(
                'select akHasCustomCountries, akDefaultCountry from atAddressSettings where akID = ?',
                [$ak->getAttributeKeyID()]
            );

            $availableBillingCountries = $db->GetCol(
                'select country from atAddressCustomCountries where akID = ?',
                [$ak->getAttributeKeyID()]
            );

            $defaultBillingCountry = $row['akDefaultCountry'];
            $hasCustomerBillingCountries = $row['akHasCustomCountries'];
        }

        if ($hasCustomerBillingCountries) {
            $billingCountries = [];
            foreach ($availableBillingCountries as $countrycode) {
                $billingCountries[$countrycode] = $allcountries[$countrycode];
            }
        } else {
            $billingCountries = $allcountries;
        }

        $ak = UserAttributeKey::getByHandle('shipping_address');

        if (version_compare(\Config::get('concrete.version'), '8.0', '>=')) {
            $keysettings = $ak->getController()->getAttributeKeySettings();
            $defaultShippingCountry = $keysettings->getDefaultCountry();
            $hasCustomerShippingCountries = $keysettings->hasCustomCountries();
            $availableShippingCountries = $keysettings->getCustomCountries();
        } else {
            $row = $db->GetRow(
                'select akHasCustomCountries, akDefaultCountry from atAddressSettings where akID = ?',
                [$ak->getAttributeKeyID()]
            );
            $defaultShippingCountry = $row['akDefaultCountry'];
            $hasCustomerShippingCountries = $row['akHasCustomCountries'];
            $availableShippingCountries = $db->GetCol(
                'select country from atAddressCustomCountries where akID = ?',
                [$ak->getAttributeKeyID()]
            );
        }

        if ($hasCustomerShippingCountries) {
            $shippingCountries = [];
            foreach ($availableShippingCountries as $countrycode) {
                $shippingCountries[$countrycode] = $allcountries[$countrycode];
            }
        } else {
            $shippingCountries = $allcountries;
        }

        $discountsWithCodesExist = StoreDiscountRule::discountsWithCodesExist();

        $this->set("discountsWithCodesExist", $discountsWithCodesExist);
        $this->set('cart', $cart);
        $this->set('discounts', StoreCart::getDiscounts());
        $this->set('hasCode', StoreDiscountCode::hasCartCode());

        $this->set("billingCountries", $billingCountries);
        $this->set("shippingCountries", $shippingCountries);

        $this->set("defaultBillingCountry", $defaultBillingCountry);
        $this->set("defaultShippingCountry", $defaultShippingCountry);

        $statelist = ['' => ''];
        $statelist = array_merge($statelist, Core::make('helper/lists/states_provinces')->getStates());
        $this->set("states", $statelist);

        $orderChoicesAttList = StoreOrderKey::getAttributeListBySet('order_choices', new User());
        $this->set("orderChoicesEnabled", count($orderChoicesAttList) ? true : false);
        if (is_array($orderChoicesAttList) && !empty($orderChoicesAttList)) {
            $this->set("orderChoicesAttList", $orderChoicesAttList);
        }

        $totals = StoreCalculator::getTotals();

        $this->set('subtotal', $totals['subTotal']);
        $this->set('taxes', $totals['taxes']);

        $this->set('taxtotal', $totals['taxTotal']);

        if (\Session::get('community_store.smID')) {
            $this->set('shippingtotal', $totals['shippingTotal']);
        } else {
            $this->set('shippingtotal', false);
        }

        $this->set('total', $totals['total']);
        $this->set('shippingEnabled', StoreCart::isShippable());
        $this->set('shippingInstructions', StoreCart::getShippingInstructions());

        $this->requireAsset('javascript', 'jquery');
        $js = \Concrete\Package\CommunityStore\Controller::returnHeaderJS();
        $this->addFooterItem($js);
        $this->requireAsset('javascript', 'community-store');
        $this->requireAsset('css', 'community-store');
        $this->addFooterItem("
            <script type=\"text/javascript\">
                $(function() {
                    communityStore.loadViaHash();
                });
            </script>
        ");

        $enabledMethods = StorePaymentMethod::getEnabledMethods();

        $availableMethods = [];

        foreach ($enabledMethods as $em) {
            $emmc = $em->getMethodController();

            if ($totals['total'] >= $emmc->getPaymentMinimum() && $totals['total'] <= $emmc->getPaymentMaximum()) {
                $availableMethods[] = $em;
            }
        }

        $this->set("enabledPaymentMethods", $availableMethods);

        $apikey = \Config::get('community_store.placesAPIKey');

        if ($apikey) {
            $this->addFooterItem(
                '<script src="https://maps.googleapis.com/maps/api/js?' . ($apikey ? '&key=' . $apikey : '') . '&libraries=places&callback=initAutocomplete" defer></script>'
            );

            $this->requireAsset('javascript', 'community-store-autocomplete');
            $this->set('addressLookup', true);
        }

        $this->set('token', $this->app->make('token'));
        $this->set('langpath', $langpath);
    }

    public function failed($guest = false)
    {
        $this->set('shippingInstructions', StoreCart::getShippingInstructions());
        $this->set('paymentErrors', Session::get('paymentErrors'));
        $this->set('activeShippingLabel', StoreShippingMethod::getActiveShippingLabel());
        $this->set('shippingTotal', StoreCalculator::getShippingTotal());
        $this->set('lastPaymentMethodHandle', Session::get('paymentMethod'));
        $this->view($guest);
    }

    public function submit($guest = false)
    {
        $token = $this->app->make('token');

        $c = \Page::getCurrentPage();
        $al = Section::getBySectionOfSite($c);
        $langpath = '';
        if ($al !== null) {
            $langpath =  $al->getCollectionHandle();
        }

        if (!$token->validate('community_store'))  {
            return \Redirect::to($langpath . '/checkout');
        }

        $data = $this->post();
        Session::set('paymentMethod', $data['payment-method']);

        //process payment
        $pmHandle = $data['payment-method'];
        $pm = StorePaymentMethod::getByHandle($pmHandle);

        // redirect/fail if we don't have a payment method, or it's shippible and there's no shipping method in the session
        if (false === $pm || (StoreCart::isShippable() && !Session::get('community_store.smID'))) {
            return \Redirect::to($langpath . '/checkout');
        }

        if ($pm->getMethodController()->isExternal()) {
            if (0 != StoreCart::getTotalItemsInCart()) {
                $order = StoreOrder::add($pm, null, 'incomplete');
                Session::set('orderID', $order->getOrderID());
                return \Redirect::to($langpath .'/checkout/external');
            } else {
                return \Redirect::to($langpath . '/cart');
            }
        } else {
            $payment = $pm->submitPayment();
            if (1 == $payment['error']) {
                $errors = $payment['errorMessage'];
                Session::set('paymentErrors', $errors);
                if ($guest) {
                    return \Redirect::to($langpath . '/checkout/failed/1#payment');
                } else {
                    return \Redirect::to($langpath . '/checkout/failed#payment');
                }
            } else {
                $transactionReference = $payment['transactionReference'];
                $order = StoreOrder::add($pm, $transactionReference);

                return \Redirect::to($langpath . '/checkout/complete');
            }
        }
    }

    public function external()
    {
        $this->requireAsset('javascript', 'jquery');
        $pmHandle = Session::get('paymentMethod');
        $pm = false;

        if ($pmHandle) {
            $pm = StorePaymentMethod::getByHandle($pmHandle);
        }

        if (!$pm) {
            $c = \Page::getCurrentPage();
            $al = Section::getBySectionOfSite($c);
            $langpath = '';
            if ($al !== null) {
                $langpath =  $al->getCollectionHandle();
            }

            return \Redirect::to($langpath . '/checkout');
        }

        $this->set('pm', $pm);
        $this->set('action', $pm->getMethodController()->getAction());
    }
}
