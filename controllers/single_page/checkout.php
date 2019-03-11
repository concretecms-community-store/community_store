<?php
namespace Concrete\Package\CommunityStore\Controller\SinglePage;

use Concrete\Core\Page\Controller\PageController;
use Concrete\Core\Support\Facade\Session;
use Concrete\Core\Support\Facade\Config;
use Concrete\Core\User\User;
use Concrete\Core\User\UserInfo;
use Concrete\Core\Attribute\Key\UserKey as UserAttributeKey;
use Concrete\Package\CommunityStore\Src\CommunityStore\Order\Order as StoreOrder;
use Concrete\Package\CommunityStore\Src\CommunityStore\Cart\Cart as StoreCart;
use Concrete\Package\CommunityStore\Src\CommunityStore\Payment\Method as StorePaymentMethod;
use Concrete\Package\CommunityStore\Src\CommunityStore\Customer\Customer as StoreCustomer;
use Concrete\Package\CommunityStore\Src\CommunityStore\Discount\DiscountRule as StoreDiscountRule;
use Concrete\Package\CommunityStore\Src\CommunityStore\Discount\DiscountCode as StoreDiscountCode;
use Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Calculator as StoreCalculator;
use Concrete\Package\CommunityStore\Src\CommunityStore\Shipping\Method\ShippingMethod as StoreShippingMethod;
use Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Price as StorePrice;
use Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Checkout as StoreCheckout;
use Concrete\Package\CommunityStore\Entity\Attribute\Key\StoreOrderKey;
use Concrete\Core\Multilingual\Page\Section\Section;
use Illuminate\Filesystem\Filesystem;
use Concrete\Core\View\View;
use Concrete\Core\Page\Page;
use Concrete\Core\Routing\Redirect;

class Checkout extends PageController
{
    public function view($guest = false)
    {
        $c = Page::getCurrentPage();
        $al = Section::getBySectionOfSite($c);
        $langpath = '';
        if (null !== $al) {
            $langpath = $al->getCollectionHandle();
        }

        if ($this->request->request->all()) {
            if ('code' == $this->request->request->get('action')) {
                $codeerror = false;
                $codesuccess = false;

                if ($this->request->request->get('code')) {
                    $codesuccess = StoreDiscountCode::storeCartCode($this->request->request->get('code'));
                    $codeerror = !$codesuccess;
                } else {
                    StoreDiscountCode::clearCartCode();
                }
            }

            $this->set('codeerror', $codeerror);
            $this->set('codesuccess', $codesuccess);
        }

        if ('all' == Config::get('community_store.shoppingDisabled')) {
            return Redirect::to($langpath . '/');
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
            return Redirect::to($langpath . '/cart/changed');
        }

        if (0 == StoreCart::getTotalItemsInCart()) {
            return Redirect::to($langpath . '/cart');
        }
        $this->set('form', $this->app->make("helper/form"));

        $allcountries = $this->app->make('helper/lists/countries')->getCountries();

        $ak = UserAttributeKey::getByHandle('billing_address');

        $keysettings = $ak->getController()->getAttributeKeySettings();
        $defaultBillingCountry = $keysettings->getDefaultCountry();
        $hasCustomerBillingCountries = $keysettings->hasCustomCountries();
        $availableBillingCountries = $keysettings->getCustomCountries();

        if ($hasCustomerBillingCountries) {
            $billingCountries = [];
            foreach ($availableBillingCountries as $countrycode) {
                $billingCountries[$countrycode] = $allcountries[$countrycode];
            }
        } else {
            $billingCountries = $allcountries;
        }

        $ak = UserAttributeKey::getByHandle('shipping_address');

        $keysettings = $ak->getController()->getAttributeKeySettings();
        $defaultShippingCountry = $keysettings->getDefaultCountry();
        $hasCustomerShippingCountries = $keysettings->hasCustomCountries();
        $availableShippingCountries = $keysettings->getCustomCountries();

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
        $statelist = array_merge($statelist, $this->app->make('helper/lists/states_provinces')->getStates());
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

        if (Session::get('community_store.smID')) {
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

        $apikey = Config::get('community_store.placesAPIKey');

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

        $c = Page::getCurrentPage();
        $al = Section::getBySectionOfSite($c);
        $langpath = '';
        if (null !== $al) {
            $langpath = $al->getCollectionHandle();
        }

        if (!$token->validate('community_store')) {
            return Redirect::to($langpath . '/checkout');
        }

        $data = $this->request->request->all();
        Session::set('paymentMethod', $data['payment-method']);

        //process payment
        $pmHandle = $data['payment-method'];
        $pm = StorePaymentMethod::getByHandle($pmHandle);

        // redirect/fail if we don't have a payment method, or it's shippible and there's no shipping method in the session
        if (false === $pm || (StoreCart::isShippable() && !Session::get('community_store.smID'))) {
            return Redirect::to($langpath . '/checkout');
        }

        if ($pm->getMethodController()->isExternal()) {
            if (0 != StoreCart::getTotalItemsInCart()) {
                $order = StoreOrder::add($pm, null, 'incomplete');
                Session::set('orderID', $order->getOrderID());

                return Redirect::to($langpath . '/checkout/external');
            } else {
                return Redirect::to($langpath . '/cart');
            }
        } else {
            $payment = $pm->submitPayment();
            if (1 == $payment['error']) {
                $errors = $payment['errorMessage'];
                Session::set('paymentErrors', $errors);
                if ($guest) {
                    return Redirect::to($langpath . '/checkout/failed/1#payment');
                } else {
                    return Redirect::to($langpath . '/checkout/failed#payment');
                }
            } else {
                $transactionReference = $payment['transactionReference'];
                $order = StoreOrder::add($pm, $transactionReference);

                return Redirect::to($langpath . '/checkout/complete');
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
            $c = Page::getCurrentPage();
            $al = Section::getBySectionOfSite($c);
            $langpath = '';
            if (null !== $al) {
                $langpath = $al->getCollectionHandle();
            }

            return Redirect::to($langpath . '/checkout');
        }

        $this->set('pm', $pm);
        $this->set('action', $pm->getMethodController()->getAction());
    }

    public function getShippingMethods()
    {
        if (Filesystem::exists(DIR_BASE . "/application/elements/checkout/shipping_methods.php")) {
            View::element("checkout/shipping_methods");
        } else {
            View::element("checkout/shipping_methods", null, "community_store");
        }

        exit();
    }

    public function getstates()
    {
        $service = $this->app->make('helper/security');
        $countryCode = $service->sanitizeString($this->request->request->get('country'));
        $selectedState = $service->sanitizeString($this->request->request->get('selectedState'));
        $type = $service->sanitizeString($this->request->request->get('type'));
        $class = empty($this->request->request->get('class')) ? 'form-control' : $service->sanitizeString($this->request->request->get('class'));
        $dataList = $this->app->make('helper/json')->decode($this->request->request->get('data'), true);
        $data = '';
        if (is_array($dataList) && count($dataList)) {
            foreach ($dataList as $name => $value) {
                $data .= ' data-' . $name . '="' . $value . '"';
            }
        }

        $requiresstate = ['US', 'AU', 'CA', 'CN', 'MX', 'MY'];

        $required = '';

        if (in_array($countryCode, $requiresstate)) {
            $required = ' required="required" ';
        }

        $list = $this->app->make('helper/lists/states_provinces')->getStateProvinceArray($countryCode);
        if ($list) {
            if ("tax" == $type) {
                echo "<select name='taxState' id='taxState' class='{$class}'{$data}>";
            } else {
                echo "<select $required name='store-checkout-{$type}-state' id='store-checkout-{$type}-state' ccm-passed-value='' class='{$class}'{$data}>";
            }
            echo '<option value=""></option>';

            foreach ($list as $code => $country) {
                if ($code == $selectedState) {
                    echo "<option selected value='{$code}'>{$country}</option>";
                } else {
                    echo "<option value='{$code}'>{$country}</option>";
                }
            }
            echo "<select>";
        } else {
            if ("tax" == $type) {
                echo "<input type='text' name='taxState' id='taxState' class='{$class}'{$data}>";
            } else {
                echo "<input type='text' name='store-checkout-{$type}-state' id='store-checkout-{$type}-state' value='{$selectedState}' class='{$class}'{$data} placeholder='" . t('State / Province') . "'>";
            }
        }

        exit();
    }

    public function setVatNumber()
    {
        $token = $this->app->make('token');

        if ($this->request->request->all() && $token->validate('community_store')) {
            $data = $this->request->request->all();
            // VAT Number validation
            if (Config::get('community_store.vat_number')) {
                $vat_number = str_replace(' ', '', trim($data['vat_number']));
                $e = StoreCheckout::validateVatNumber($vat_number);
                if ($e->has()) {
                    echo $e->outputJSON();

                    exit();
                } else {
                    $this->updateVatNumber($data);
                    echo json_encode([
                        'vat_number' => $vat_number,
                        'error' => false,
                    ]);
                }
            }
        } else {
            echo "An error occured";
        }

        exit();
    }

    public function updater()
    {
        $token = $this->app->make('token');

        if (!$token->validate('community_store')) {
            return false;
        }

        if ($this->request->request->all()) {
            $data = $this->request->request->all();
            $billing = false;
            if ('billing' == $data['adrType']) {
                $billing = true;

                $u = new User();
                $guest = !$u->isLoggedIn();

                $requiresLoginOrDifferentEmail = false;

                if ($guest) {
                    $emailexists = $this->validateAccountEmail($data['email']);
                }

                $orderRequiresLogin = StoreCart::requiresLogin();

                if ($orderRequiresLogin && $emailexists) {
                    $requiresLoginOrDifferentEmail = true;
                }
            }

            $e = $this->validateAddress($data, $billing);

            if ($requiresLoginOrDifferentEmail) {
                $e->add(t('The email address you have entered has already been used to create an account. Please login first or enter a different email address.'));
            }

            if ($e->has()) {
                echo $e->outputJSON();
            } else {
                $customer = new StoreCustomer();
                if ('billing' == $data['adrType']) {
                    $this->updateBilling($data);
                    $address = Session::get('billing_address');
                    $phone = Session::get('billing_phone');
                    $company = Session::get('billing_company');
                    $first_name = Session::get('billing_first_name');
                    $last_name = Session::get('billing_last_name');
                    $email = $customer->getEmail();
                }

                if ('shipping' == $data['adrType']) {
                    $this->updateShipping($data);
                    $address = Session::get('shipping_address');
                    $phone = '';
                    $email = '';
                    $first_name = Session::get('shipping_first_name');
                    $last_name = Session::get('shipping_last_name');
                    $company = Session::get('shipping_company');

                    // VAT Number validation
                    if (Config::get('community_store.vat_number')) {
                        $vat_number = $customer->getValue('vat_number');
                        $e = StoreCheckout::validateVatNumber($vat_number);
                        if ($e->has()) {
                            echo $e->outputJSON();

                            return;
                        }
                    }
                }

                $address = nl2br(StoreCustomer::formatAddressArray($address));

                // Results array
                $results = [
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'phone' => $phone,
                    'company' => $company,
                    'email' => $email,
                    'address' => $address,
                    'error' => false,
                ];

                // If updating shipping method we need vat number
                if ('shipping' == $data['adrType']) {
                    $results['vat_number'] = $vat_number;
                }

                // Return JSON with results
                echo json_encode($results);
            }
        } else {
            echo "An error occured";
        }

        exit();
    }

    public function updateShipping($data)
    {
        //update the users shipping address
        $this->validateAddress($data);
        $customer = new StoreCustomer();

        $guest = $customer->isGuest();

        $noShippingSave = Config::get('community_store.noShippingSave');

        if (!$guest) {
            $noShippingSaveGroups = Config::get('community_store.noShippingSaveGroups');
            $user = new User();
            $usergroups = $user->getUserGroups();

            if (!is_array($usergroups)) {
                $usergroups = [];
            }

            $matchingGroups = array_intersect(explode(',', $noShippingSaveGroups), $usergroups);

            if ($noShippingSaveGroups && empty($matchingGroups)) {
                $noShippingSave = false;
            }
        }

        $address = [
            "address1" => trim($data['addr1']),
            "address2" => trim($data['addr2']),
            "city" => trim($data['city']),
            "state_province" => trim($data['state']),
            "postal_code" => trim($data['postal']),
            "country" => trim($data['count']),
        ];

        if ($guest || !$noShippingSave) {
            $customer->setValue("shipping_first_name", trim($data['fName']));
            $customer->setValue("shipping_address", $address);
            $customer->setValue("vat_number", $data['vat_number']);
            $customer->setValue("shipping_last_name", trim($data['lName']));
            $customer->setValue("shipping_company", trim($data['company']));
        }

        Session::set('shipping_first_name', trim($data['fName']));
        Session::set('shipping_last_name', trim($data['lName']));
        Session::set('shipping_address', $address);
        Session::set('shipping_company', trim($data['company']));
        Session::set('vat_number', $data['vat_number']);
        Session::set('community_store.smID', false);
    }

    private function updatevatnumber($data)
    {
        $token = $this->app->make('token');

        if (!$token->validate('community_store')) {
            return false;
        }

        //update the users vat number
        $customer = new StoreCustomer();
        $customer->setValue("vat_number", trim($data['vat_number']));
        Session::set('vat_number', trim($data['vat_number']));
    }

    public function validateAddress($data, $billing = null)
    {
        $e = $this->app->make('helper/validation/error');
        $vals = $this->app->make('helper/validation/strings');
        $customer = new StoreCustomer();

        if ($billing) {
            if ($customer->isGuest()) {
                if (!$vals->email($data['email'])) {
                    $e->add(t('You must enter a valid email address'));
                }
            }
        }

        if (strlen($data['fName']) < 1) {
            $e->add(t('You must enter a first name'));
        }
        if (strlen($data['fName']) > 255) {
            $e->add(t('Please enter a first name under 255 characters'));
        }
        if (strlen($data['lName']) < 1) {
            $e->add(t('You must enter a Last Name'));
        }
        if (strlen($data['lName']) > 255) {
            $e->add(t('Please enter a last name under 255 characters'));
        }
        if (strlen($data['addr1']) < 3) {
            $e->add(t('You must enter an address'));
        }
        if (strlen($data['addr1']) > 255) {
            $e->add(t('Please enter a street name under 255 characters'));
        }
        if (strlen($data['count']) < 2) {
            $e->add(t('You must enter a Country'));
        }
        if (strlen($data['count']) > 30) {
            $e->add(t('You did not select a Country from the list'));
        }
        if (strlen($data['city']) < 2) {
            $e->add(t('You must enter a City'));
        }
        if (strlen($data['city']) > 30) {
            $e->add(t('You must enter a valid City'));
        }
        if (strlen($data['postal']) > 10) {
            $e->add(t('You must enter a valid Postal Code'));
        }
        if (strlen($data['postal']) < 2) {
            $e->add(t('You must enter a valid Postal Code'));
        }

        return $e;
    }

    public function store_download($fID, $oID, $hash)
    {
        $valid = false;

        $file = File::getByID($fID);
        if ($file instanceof FileEntity && $file->getFileID() > 0) {
            $file->trackDownload(null);
            $fv = $file->getVersion();

            $order = StoreOrder::getByID($oID);

            $expiryhours = Config::get('community_store.download_expiry_hours');
            if (!$expiryhours) {
                $expiryhours = 48;
            }

            $threshhold = new \DateTime();
            $threshhold->sub(new \DateInterval('PT' . $expiryhours . 'H'));
            $orderDate = $order->getOrderDate();

            // check that order exists, and md5 hash of order timestamp matches
            if ($order && md5($orderDate->format('Y-m-d H:i:s')) == $hash && $orderDate > $threshhold) {
                // loop to find whether order contained a product with linked file
                foreach ($order->getOrderItems() as $oi) {
                    $product = $oi->getProductObject();

                    if ($product) {
                        $files = $product->getDownloadFiles();

                        foreach ($files as $f) {
                            if ($f->getFileID() == $fID) {
                                $valid = true;
                                break;
                            }
                        }
                    }

                    if ($valid) {
                        break;
                    }
                }
            }

            if ($valid) {
                return $fv->buildForceDownloadResponse();
            }
        }

        echo t('The download link you have followed has expired or is invalid');

        return false;
    }

    private function validateAccountEmail($email)
    {
        $user = UserInfo::getByEmail($email);

        if ($user) {
            return true;
        } else {
            return false;
        }
    }

    private function updateBilling($data)
    {
        //update the users billing address
        $customer = new StoreCustomer();

        $guest = $customer->isGuest();

        $noBillingSave = Config::get('community_store.noBillingSave');

        if ($guest) {
            $customer->setEmail(trim($data['email']));
        } else {
            $noBillingSaveGroups = Config::get('community_store.noBillingSaveGroups');
            $user = new User();
            $usergroups = $user->getUserGroups();

            if (!is_array($usergroups)) {
                $usergroups = [];
            }

            $matchingGroups = array_intersect(explode(',', $noBillingSaveGroups), $usergroups);
            if ($noBillingSaveGroups && empty($matchingGroups)) {
                $noBillingSave = false;
            }
        }

        $address = [
            "address1" => trim($data['addr1']),
            "address2" => trim($data['addr2']),
            "city" => trim($data['city']),
            "state_province" => trim($data['state']),
            "postal_code" => trim($data['postal']),
            "country" => trim($data['count']),
        ];

        Session::set('billing_first_name', trim($data['fName']));
        Session::set('billing_last_name', trim($data['lName']));
        Session::set('billing_phone', trim($data['phone']));
        Session::set('billing_address', $address);
        Session::set('billing_company', trim($data['company']));

        if ($guest || !$noBillingSave) {
            $customer->setValue("billing_first_name", trim($data['fName']));
            $customer->setValue("billing_last_name", trim($data['lName']));
            $customer->setValue("billing_phone", trim($data['phone']));
            $customer->setValue("billing_address", $address);
            $customer->setValue("billing_company", trim($data['company']));
        }

        Session::set('community_store.smID', false);
    }

    public function selectShipping()
    {
        $token = $this->app->make('token');

        if ($this->request->request->all() && $token->validate('community_store')) {
            $smID = $this->request->request->get('smID');
            $sInstructions = $this->request->request->get('sInstructions');

            StoreCart::setShippingInstructions($sInstructions);

            $total = StoreCalculator::getShippingTotal($smID);
            if ($total > 0) {
                echo StorePrice::format($total);
            } else {
                echo 0;
            }
        }
        exit();
    }
}
