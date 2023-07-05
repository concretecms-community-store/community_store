<?php
namespace Concrete\Package\CommunityStore\Controller\SinglePage;

use Concrete\Core\Geolocator\GeolocationResult;
use Concrete\Core\Page\Page;
use Concrete\Core\User\User;
use Concrete\Core\View\View;
use Concrete\Core\Routing\Redirect;
use Illuminate\Filesystem\Filesystem;
use Concrete\Core\Support\Facade\Config;
use Concrete\Core\Support\Facade\Session;
use Concrete\Core\Page\Controller\PageController;
use Concrete\Core\Multilingual\Page\Section\Section;
use Concrete\Core\Attribute\Key\UserKey as UserAttributeKey;
use Concrete\Package\CommunityStore\Src\CommunityStore\Cart\Cart;
use Concrete\Package\CommunityStore\Src\CommunityStore\Order\Order;
use Concrete\Package\CommunityStore\Entity\Attribute\Key\StoreOrderKey;
use Concrete\Package\CommunityStore\Src\CommunityStore\Customer\Customer;
use Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Calculator;
use Concrete\Package\CommunityStore\Src\CommunityStore\Discount\DiscountCode;
use Concrete\Package\CommunityStore\Src\CommunityStore\Discount\DiscountRule;
use Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Tax as TaxHelper;
use Concrete\Package\CommunityStore\Src\CommunityStore\Shipping\Method\ShippingMethod;
use Concrete\Package\CommunityStore\Src\CommunityStore\Payment\Method as PaymentMethod;
use Symfony\Component\HttpFoundation\JsonResponse;
use Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\SalesSuspension;

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

            $codeerror = false;
            $codesuccess = false;

            if ('code' == $this->request->request->get('action')) {
                if ($this->request->request->get('code')) {
                    $codesuccess = DiscountCode::storeCartCode($this->request->request->get('code'));
                    $codeerror = !$codesuccess;
                } else {
                    DiscountCode::clearCartCode();
                }
            }

            $this->set('codeerror', $codeerror);
            $this->set('codesuccess', $codesuccess);

            // if there was a code and it's valid we refresh
            // if the code submitted was empty we also apply since any other existing code is removed
            if (($codesuccess && !$codeerror) || (!$codesuccess && !$codeerror)) {
                // A coupon was added or removed so let's refresh other carts on other open pages
                $this->addFooterItem('<script type="text/javascript">$(function() { communityStore.broadcastCartRefresh({action: \'code\'}); });</script>');
            }

        }

        if ($this->app->make(SalesSuspension::class)->salesCurrentlySuspended()) {
            return Redirect::to($langpath . '/');
        }

        $customer = new Customer();
        $this->set('customer', $customer);
        $guestCheckout = Config::get('community_store.guestCheckout');
        $this->set('guestCheckout', ($guestCheckout ? $guestCheckout : 'off'));
        $this->set('guest', isset($guest) && (bool) $guest);
        $this->set('requiresLogin', Cart::requiresLogin());
        $this->set('companyField', Config::get('community_store.companyField'));

        $cart = Cart::getCart();

        if (Cart::hasChanged()) {
            return Redirect::to($langpath . '/cart/changed');
        }

        if (0 == Cart::getTotalItemsInCart()) {
            return Redirect::to($langpath . '/cart');
        }
        $this->set('form', $this->app->make("helper/form"));


        $useCaptcha = Config::get('community_store.useCaptcha');

        if ($useCaptcha) {
            $captcha = $this->app->make("captcha");
            $token = $this->app->make('token');
            $session = $this->app->make('session');
            if (!$session->get('securityCheck')) {
                if ($token->validate('community_store')) {
                    if ($captcha->check()) {
                        $session->set('securityCheck', true);
                    } else {
                        $this->set('error', t('Incorrect captcha code'));
                    }
                }
            }
        }

        if ($useCaptcha && !$session->get('securityCheck')) {
            $this->render('/checkout/security');
        } else {
            $countryFromIP = null;
            $allcountries = $this->app->make('helper/lists/countries')->getCountries();

            $ak = UserAttributeKey::getByHandle('billing_address');

            $keysettings = $ak->getController()->getAttributeKeySettings();
            $defaultBillingCountry = $keysettings->getDefaultCountry();
            if (method_exists($keysettings, 'geolocateCountry') && $keysettings->geolocateCountry()) {
                if ($countryFromIP === null) {
                    $countryFromIP = $this->geolocateCountry();
                }
                if ($countryFromIP !== '') {
                    $defaultBillingCountry = $countryFromIP;
                }
            }
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
            if (method_exists($keysettings, 'geolocateCountry') && $keysettings->geolocateCountry()) {
                if ($countryFromIP === null) {
                    $countryFromIP = $this->geolocateCountry();
                }
                if ($countryFromIP !== '') {
                    $defaultShippingCountry = $countryFromIP;
                }
            }
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

            $discountsWithCodesExist = DiscountRule::discountsWithCodesExist();

            $this->set("discountsWithCodesExist", $discountsWithCodesExist);
            $this->set('cart', $cart);
            $this->set('discounts', Cart::getDiscounts());
            $this->set('hasCode', DiscountCode::hasCartCode());

            $this->set("billingCountries", $billingCountries);
            $this->set("shippingCountries", $shippingCountries);

            $this->set("defaultBillingCountry", $defaultBillingCountry);
            $this->set("defaultShippingCountry", $defaultShippingCountry);

            $this->set('notes', Session::get('notes'));

            $statelist = ['' => ''];
            $statelist = array_merge($statelist, $this->app->make('helper/lists/states_provinces')->getStates());
            $this->set("states", $statelist);

            $orderChoicesAttList = StoreOrderKey::getAttributeListBySet('order_choices', new User());
            $this->set("orderChoicesEnabled", count($orderChoicesAttList) ? true : false);
            if (is_array($orderChoicesAttList) && !empty($orderChoicesAttList)) {
                $this->set("orderChoicesAttList", $orderChoicesAttList);
            }

            $totals = Calculator::getTotals();
            $availableMethods = PaymentMethod::getAvailableMethods((float)$totals['subTotal']);

            foreach($availableMethods as $pm) {
                $pmController = $pm->getMethodController();

                if (method_exists($pmController, 'headerScripts')) {
                    $pmController->headerScripts($this->view);
                }
            }

            $this->set('subtotal', $totals['subTotal']);
            $this->set('taxes', $totals['taxes']);

            $this->set('taxtotal', $totals['taxTotal']);

            if (Session::get('community_store.smID')) {
                $this->set('shippingtotal', $totals['shippingTotal']);
            } else {
                $this->set('shippingtotal', false);
            }

            $this->set('total', $totals['total']);
            $this->set('shippingEnabled', Cart::isShippable());
            $this->set('activeShippingLabel', ShippingMethod::getActiveShippingLabel());
            $this->set('shippingTotal', Calculator::getShippingTotal());
            $this->set('orderNotesEnabled', Config::get('community_store.orderNotesEnabled'));
            $this->set('shippingInstructions', Cart::getShippingInstructions());

            $this->requireAsset('javascript', 'jquery');
            $js = \Concrete\Package\CommunityStore\Controller::returnHeaderJS();
            $this->addFooterItem($js);

            $this->requireAsset('javascript', 'sysend');
            $this->requireAsset('javascript', 'community-store');

            $this->requireAsset('css', 'community-store');
            $this->addFooterItem("
            <script type=\"text/javascript\">
                $(function() {
                    communityStore.loadViaHash();
                });
            </script>
        ");


            $orderID = Session::get('community_store.tempOrderID');
            $orderTimestamp = Session::get('community_store.tempOrderIDTimeStamp');

            $order = false;
            if ($orderID) {
                $order = Order::getByID($orderID);

                // also check timestamp, in case visitor has returned with still active session after long period and order ID has been reclaimed
                if ($order && $orderTimestamp != $order->getTemporaryRecordCreated()->format(DATE_RFC3339)) {
                    $order = false;
                }
            }

            $this->set('order', $order);

            $apikey = Config::get('community_store.placesAPIKey');

            if ($apikey) {
                $this->addFooterItem(
                    '<script src="https://maps.googleapis.com/maps/api/js?' . ($apikey ? '&key=' . $apikey : '') . '&libraries=places&callback=initAutocomplete" defer></script>'
                );

                $this->requireAsset('javascript', 'community-store-autocomplete');
                $this->set('addressLookup', true);
            }

        }

        $this->set('orderAttributes', Session::get('orderAttributes'));

        $this->set('token', $this->app->make('token'));
        $this->set('langpath', $langpath);
    }

    public function failed($guest = false)
    {
        $this->set('shippingInstructions', Cart::getShippingInstructions());
        $this->set('paymentErrors', Session::get('paymentErrors'));
        $this->set('activeShippingLabel', ShippingMethod::getActiveShippingLabel());
        $this->set('shippingTotal', Calculator::getShippingTotal());
        $this->set('lastPaymentMethodHandle', Session::get('paymentMethod'));
        $this->set('token', $this->app->make('token'));
        return $this->view($guest);
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

        if (Config::get('community_store.useCaptcha')) {
            if (!Session::get('securityCheck')) {
                return Redirect::to($langpath . '/checkout');
            }
        }

        $data = $this->request->request->all();
        Session::set('paymentMethod', $data['payment-method']);

        //process payment
        $pmHandle = $data['payment-method'];
        $pm = PaymentMethod::getByHandle($pmHandle);

        // redirect/fail if we don't have a payment method, or it's shippible and there's no shipping method in the session
        if (false === $pm || (Cart::isShippable() && !Session::get('community_store.smID'))) {
            return Redirect::to($langpath . '/checkout');
        }

        // if no more items in cart, refresh the checkout page
        if (Cart::getTotalItemsInCart() == 0) {
            return Redirect::to($langpath . '/checkout');
        }

        if ($pm->getMethodController()->isExternal()) {
            if (0 != Cart::getTotalItemsInCart()) {
				if (! $this->request->isMethod('POST')) {
					return Redirect::to($langpath . '/cart');
				}

				$nonce = md5(uniqid('cs', true));
				Session::set('checkoutNonce', $nonce);

				$order = Order::add($pm, null, 'incomplete');
                Session::set('orderID', $order->getOrderID());

                // unset the shipping type, as next order might be unshippable
                Session::set('community_store.smID', '');

				return Redirect::to($langpath . '/checkout/external',$nonce);
            } else {
                return Redirect::to($langpath . '/cart');
            }
        } else {
            Session::set('securityCheck', false);

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

                $order = Order::add($pm, $transactionReference);

                // unset the shipping type, as next order might be unshippable
                Session::set('community_store.smID', '');
                Session::set('notes', '');
                return Redirect::to($order->getOrderCompleteDestination());
            }
        }
    }

	public function external($nonce = false)
    {
        $this->requireAsset('javascript', 'jquery');
        $pmHandle = Session::get('paymentMethod');
        $pm = false;

        if ($pmHandle) {
            $pm = PaymentMethod::getByHandle($pmHandle);
        }

		$c = Page::getCurrentPage();
		$al = Section::getBySectionOfSite($c);
		$langpath = '';
		if (null !== $al) {
			$langpath = $al->getCollectionHandle();
		}

        if (!$pm) {
            return Redirect::to($langpath . '/checkout');
        }

		$checkoutNonce = Session::get('checkoutNonce');
		if (!$checkoutNonce) {
			return Redirect::to($langpath . '/checkout');
		}
		Session::remove('checkoutNonce');
		if ($checkoutNonce !== $nonce) {
			return Redirect::to($langpath . '/checkout');
		}

        $ajax = $this->app->make('helper/ajax');
        if ($ajax->isAjaxRequest($this->request)){
            return new JsonResponse(['OK'=>1]);
        }
        $pmController = $pm->getMethodController();
        $action = $pmController->getAction();
        if ($pmController->isExternalActionGET()) {
            return $this->buildRedirect($action);
        }
        $this->set('pm', $pm);
        $this->set('action', $action);
    }

    public function updater()
    {
        $token = $this->app->make('token');

        if (!$token->validate('community_store')) {
            return false;
        }

        $requiresLoginOrDifferentEmail = false;

        if ($this->request->request->all()) {
            $data = $this->request->request->all();
            $billing = false;
            if ('billing' == $data['adrType']) {
                $billing = true;

                $u = new User();
                $guest = !$u->isRegistered();

                $emailexists = false;

                if ($guest) {
                    $emailexists = $this->validateAccountEmail(empty($data['store-email']) ? '' : $data['store-email']);
                }

                $orderRequiresLogin = Cart::requiresLogin();

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
                $customer = new Customer();
                $notes = '';
                if ('billing' == $data['adrType']) {
                    $this->updateBilling($data);
                    if (isset($data['notes'])) {
                        $notes = $data['notes'];
                        Session::set('notes', $notes);
                    }
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
                        $taxHelper = $this->app->make(TaxHelper::class);
                        $e = $taxHelper->validateVatNumber($vat_number);
                        if ($e->has()) {
                            echo $e->outputJSON();
                            return;
                        }
                    }
                }

                $address = nl2br(Customer::formatAddressArray($address));

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

                $results['notes'] = nl2br(h($notes));

                // If updating shipping method we need vat number
                if ('shipping' == $data['adrType'] && isset($vat_number)) {
                    $results['vat_number'] = $vat_number;
                }

                $results['attribute_display'] = Session::get('community_store.attributeDisplay');

                // Return JSON with results
                echo json_encode($results);
            }
        } else {
            echo "An error occurred";
        }

        exit();
    }

    public function attributeupdater()
    {
        $token = $this->app->make('token');

        if (!$token->validate('community_store')) {
            return false;
        }

        if ($this->request->request->all()) {
            $data = $this->request->request->all();
            Session::set('orderAttributes', $data['attrData']);
            echo json_encode(['error'=>false]);
        }

        exit();
    }

    public function updateShipping($data)
    {
        //update the users shipping address
        $this->validateAddress($data);
        $customer = new Customer();

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
            "address1" => trim($data['store-checkout-shipping-address-1']),
            "address2" => trim($data['store-checkout-shipping-address-2']),
            "city" => trim($data['store-checkout-shipping-city']),
            "state_province" => trim($data['store-checkout-shipping-state']),
            "postal_code" => strtoupper(trim($data['store-checkout-shipping-zip'])),
            "country" => trim($data['store-checkout-shipping-country']),
        ];

        if ($guest || !$noShippingSave) {
            $customer->setValue("shipping_first_name", trim($data['store-checkout-shipping-first-name']));
            $customer->setValue("shipping_address", $address);
            $customer->setValue("vat_number", isset($data['vat_number']) ? $data['vat_number'] : '');
            $customer->setValue("shipping_last_name", trim($data['store-checkout-shipping-last-name']));
            $customer->setValue("shipping_company", isset($data['store-checkout-shipping-company']) ? $data['store-checkout-shipping-company'] : '');
        }

        Session::set('shipping_first_name', trim($data['store-checkout-shipping-first-name']));
        Session::set('shipping_last_name', trim($data['store-checkout-shipping-last-name']));
        Session::set('shipping_address', $address);
        Session::set('shipping_company', isset($data['store-checkout-shipping-company']) ? trim($data['store-checkout-shipping-company']) : '');
        Session::set('vat_number', isset($data['vat_number']) ? $data['vat_number'] : '');
        Session::set('community_store.smID', false);
    }

    public function validateAddress($data, $billing = null)
    {
        $e = $this->app->make('helper/validation/error');
        $vals = $this->app->make('helper/validation/strings');
        $customer = new Customer();

        if ($billing) {
            if ($customer->isGuest()) {
                if (!$vals->email(empty($data['store-email']) ? '' : $data['store-email'])) {
                    $e->add(t('You must enter a valid email address'));
                }
            }
        }

        $type = '-shipping-';

        if ($billing) {
            $type = '-billing-';
        }

        if (strlen($data['store-checkout' . $type . 'first-name']) < 1) {
            $e->add(t('You must enter a first name'));
        }
        if (strlen($data['store-checkout' . $type . 'first-name']) > 255) {
            $e->add(t('Please enter a first name under 255 characters'));
        }
        if (strlen($data['store-checkout' . $type . 'last-name']) < 1) {
            $e->add(t('You must enter a Last Name'));
        }
        if (strlen($data['store-checkout' . $type . 'last-name']) > 255) {
            $e->add(t('Please enter a last name under 255 characters'));
        }
        if (strlen($data['store-checkout' . $type . 'address-1']) < 3) {
            $e->add(t('You must enter an address'));
        }
        if (strlen($data['store-checkout' . $type . 'address-1']) > 255) {
            $e->add(t('Please enter a street name under 255 characters'));
        }
        if (strlen($data['store-checkout' . $type . 'country']) < 2) {
            $e->add(t('You must enter a Country'));
        }
        if (strlen($data['store-checkout' . $type . 'country']) > 30) {
            $e->add(t('You did not select a Country from the list'));
        }
        if (strlen($data['store-checkout' . $type . 'city']) < 2) {
            $e->add(t('You must enter a City'));
        }
        if (strlen($data['store-checkout' . $type . 'city']) > 30) {
            $e->add(t('You must enter a valid City'));
        }
        if (strlen($data['store-checkout' . $type . 'zip']) > 10) {
            $e->add(t('You must enter a valid Postal Code'));
        }
        if (strlen($data['store-checkout' . $type . 'zip']) < 2) {
            $e->add(t('You must enter a valid Postal Code'));
        }

        return $e;
    }

    private function validateAccountEmail($email)
    {
        $user = $this->app->make('Concrete\Core\User\UserInfoRepository')->getByEmail($email);

        if ($user) {
            return true;
        } else {
            return false;
        }
    }

    private function updateBilling($data)
    {
        //update the users billing address
        $customer = new Customer();

        $guest = $customer->isGuest();

        $noBillingSave = Config::get('community_store.noBillingSave');

        if ($guest) {
            $customer->setEmail(empty($data['store-email']) ? '' : trim($data['store-email']));
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
            "address1" => trim($data['store-checkout-billing-address-1']),
            "address2" => trim($data['store-checkout-billing-address-2']),
            "city" => trim($data['store-checkout-billing-city']),
            "state_province" => trim($data['store-checkout-billing-state']),
            "postal_code" => strtoupper(trim($data['store-checkout-billing-zip'])),
            "country" => trim($data['store-checkout-billing-country']),
        ];

        Session::set('billing_first_name', trim($data['store-checkout-billing-first-name']));
        Session::set('billing_last_name', trim($data['store-checkout-billing-last-name']));
        Session::set('billing_phone', trim($data['store-checkout-billing-phone']));
        Session::set('billing_address', $address);
        if (isset($data['store-checkout-billing-company'])) {
            Session::set('billing_company', trim($data['store-checkout-billing-company']));
        } else {
            Session::remove('billing_company');
        }

        if ($guest || !$noBillingSave) {
            $customer->setValue("billing_first_name", trim($data['store-checkout-billing-first-name']));
            $customer->setValue("billing_last_name", trim($data['store-checkout-billing-last-name']));
            $customer->setValue("billing_phone", trim($data['store-checkout-billing-phone']));
            $customer->setValue("billing_address", $address);
            if (isset($data['store-checkout-billing-company'])) {
                $customer->setValue("billing_company", trim($data['store-checkout-billing-company']));
            } else {
                $customer->setValue("billing_company", '');
            }
        }

        Session::set('community_store.smID', false);

        $orderID = Session::get('community_store.tempOrderID');
        $orderTimestamp = Session::get('community_store.tempOrderIDTimeStamp');

        $order = false;
        if ($orderID) {
            $order = Order::getByID($orderID);

            // also check timestamp, in case visitor has returned with still active session after long period and order ID has been reclaimed
            if ($order && $orderTimestamp != $order->getTemporaryRecordCreated()->format(DATE_RFC3339)) {
                $order = false;
            }
        }

        if (!$order) {
            $order = new Order();
            $now = new \DateTime();
            $order->setDate($now);
            $order->setTotal(0);
        }

        $order->setTemporaryRecordCreated(true);
        $order->save();

        $order->saveOrderChoices();
        $orderChoicesAttList = StoreOrderKey::getAttributeListBySet('order_choices', new User());

        Session::set('community_store.tempOrderID', $order->getOrderID());
        Session::set('community_store.tempOrderIDTimeStamp', $order->getTemporaryRecordCreated()->format(DATE_RFC3339));

        $attributeDisplay = '';
        if (count($orderChoicesAttList)) {
            ob_start();
            if (file_exists(DIR_BASE . '/application/elements/checkout/order_attributes.php')) {
                View::element('checkout/order_attributes', ['order' => $order, 'orderChoicesAttList' => $orderChoicesAttList]);
            } else {
                View::element('checkout/order_attributes', ['order' => $order, 'orderChoicesAttList' => $orderChoicesAttList], 'community_store');
            }
            $attributeDisplay = ob_get_clean();
        }

        Session::set('community_store.attributeDisplay', $attributeDisplay);

    }

    public function getCartList()
    {
        $cart = Cart::getCart();

        if (file_exists(DIR_BASE . '/application/elements/cart_list.php')) {
            View::element('cart_list', ['cart' => $cart]);
        } else {
            View::element('cart_list', ['cart' => $cart], 'community_store');
        }

        exit();
    }

    /**
     * Try to detect the code of the current visitor's Country.
     * 
     * @return string empty string in case of errors.
     */
    private function geolocateCountry()
    {
        if (!class_exists(GeolocationResult::class)) {
            return '';
        }

        return $this->app->make(GeolocationResult::class)->getCountryCode();
    }
}
