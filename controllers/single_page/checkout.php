<?php
namespace Concrete\Package\CommunityStore\Controller\SinglePage;

use PageController;
use Core;
use Session;
use Config;
use Database;
use User;
use UserAttributeKey;
use Concrete\Core\Attribute\Type as AttributeType;

use Concrete\Package\CommunityStore\Src\CommunityStore\Order\Order as StoreOrder;
use Concrete\Package\CommunityStore\Src\CommunityStore\Cart\Cart as StoreCart;
use Concrete\Package\CommunityStore\Src\CommunityStore\Payment\Method as StorePaymentMethod;
use Concrete\Package\CommunityStore\Src\CommunityStore\Customer\Customer as StoreCustomer;
use Concrete\Package\CommunityStore\Src\CommunityStore\Discount\DiscountRule as StoreDiscountRule;
use Concrete\Package\CommunityStore\Src\CommunityStore\Discount\DiscountCode as StoreDiscountCode;
use Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Calculator as StoreCalculator;
use Concrete\Package\CommunityStore\Src\CommunityStore\Shipping\Method\ShippingMethod as StoreShippingMethod;
use Concrete\Package\CommunityStore\Src\Attribute\Key\StoreOrderKey as StoreOrderKey;

class Checkout extends PageController
{
    public function view()
    {
        if ($this->post()) {
            if ($this->post('action') == 'code') {

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

        if (Config::get('community_store.shoppingDisabled') == 'all') {
            $this->redirect("/");
        }

        $customer = new StoreCustomer();
        $this->set('customer', $customer);
        $guestCheckout = Config::get('community_store.guestCheckout');
        $this->set('guestCheckout', ($guestCheckout ? $guestCheckout : 'off'));
        $this->set('requiresLogin', StoreCart::requiresLogin());

        if(StoreCart::getTotalItemsInCart() == 0){
            $this->redirect("/cart/");
        }
        $this->set('form',Core::make("helper/form"));

        $allcountries = Core::make('helper/lists/countries')->getCountries();

        $db = $this->app->make('database')->connection();

        $ak = UserAttributeKey::getByHandle('billing_address');

        if (version_compare(\Config::get('concrete.version'), '8.0', '>=')) {
            $keysettings =  $ak->getController()->getAttributeKeySettings();
            $defaultBillingCountry = $keysettings->getDefaultCountry();
            $hasCustomerBillingCountries = $keysettings->hasCustomCountries();
            $availableBillingCountries = $keysettings->getCustomCountries();
        } else {
            $row = $db->GetRow(
                'select akHasCustomCountries, akDefaultCountry from atAddressSettings where akID = ?',
                array($ak->getAttributeKeyID())
            );

            $availableBillingCountries = $db->GetCol(
                'select country from atAddressCustomCountries where akID = ?',
                array($ak->getAttributeKeyID())
            );

            $defaultBillingCountry = $row['akDefaultCountry'];
            $hasCustomerBillingCountries = $row['akHasCustomCountries'];
        }

        if ($hasCustomerBillingCountries) {
            $billingCountries = array();
            foreach($availableBillingCountries as $countrycode) {
                $billingCountries[$countrycode] = $allcountries[$countrycode];
            }
        } else {
            $billingCountries =  $allcountries;
        }

        $ak = UserAttributeKey::getByHandle('shipping_address');
        
        if (version_compare(\Config::get('concrete.version'), '8.0', '>=')) {
            $keysettings =  $ak->getController()->getAttributeKeySettings();
            $defaultShippingCountry = $keysettings->getDefaultCountry();
            $hasCustomerShippingCountries = $keysettings->hasCustomCountries();
            $availableShippingCountries = $keysettings->getCustomCountries();
        } else {
            $row = $db->GetRow(
                'select akHasCustomCountries, akDefaultCountry from atAddressSettings where akID = ?',
                array($ak->getAttributeKeyID())
            );
            $defaultShippingCountry = $row['akDefaultCountry'];
            $hasCustomerShippingCountries = $row['akHasCustomCountries'];
            $availableShippingCountries = $db->GetCol(
                'select country from atAddressCustomCountries where akID = ?',
                array($ak->getAttributeKeyID())
            );
        }

        if ($hasCustomerShippingCountries) {
            $shippingCountries = array();
            foreach($availableShippingCountries as $countrycode) {
                $shippingCountries[$countrycode] = $allcountries[$countrycode];
            }
        } else {
            $shippingCountries = $allcountries;
        }

        $discountsWithCodesExist = StoreDiscountRule::discountsWithCodesExist();

        $this->set("discountsWithCodesExist",$discountsWithCodesExist);

        $this->set('cart', StoreCart::getCart());
        $this->set('discounts', StoreCart::getDiscounts());
        $this->set('hasCode', StoreDiscountCode::hasCartCode());

        $this->set("billingCountries",$billingCountries);
        $this->set("shippingCountries",$shippingCountries);

        $this->set("defaultBillingCountry",$defaultBillingCountry);
        $this->set("defaultShippingCountry",$defaultShippingCountry);

        $statelist = array(''=>'');
        $statelist = array_merge($statelist, Core::make('helper/lists/states_provinces')->getStates());
        $this->set("states", $statelist);

        $orderChoicesAttList = StoreOrderKey::getAttributeListBySet('order_choices', new User);
        $this->set("orderChoicesEnabled", count($orderChoicesAttList)? true : false);
        if (is_array($orderChoicesAttList) && !empty($orderChoicesAttList)) {
            $this->set("orderChoicesAttList", $orderChoicesAttList);
        }

        $totals = StoreCalculator::getTotals();

        $this->set('subtotal',$totals['subTotal']);
        $this->set('taxes',$totals['taxes']);

        $this->set('taxtotal',$totals['taxTotal']);

        if (\Session::get('community_store.smID')) {
            $this->set('shippingtotal', $totals['shippingTotal']);
        } else {
            $this->set('shippingtotal', false);
        }

        $this->set('total',$totals['total']);
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

        $availableMethods = array();

        foreach($enabledMethods as $em) {
            $emmc = $em->getMethodController();

            if ($totals['total'] >= $emmc->getPaymentMinimum() && $totals['total'] <=  $emmc->getPaymentMaximum()) {
                $availableMethods[] = $em;
            }
        }

        $this->set("enabledPaymentMethods",$availableMethods);
    }

    public function failed()
    {
        $this->set('shippingInstructions', StoreCart::getShippingInstructions());
        $this->set('paymentErrors',Session::get('paymentErrors'));
        $this->set('activeShippingLabel', StoreShippingMethod::getActiveShippingLabel());
        $this->set('shippingTotal', StoreCalculator::getShippingTotal());
        $this->set('lastPaymentMethodHandle',Session::get('paymentMethod'));
        $this->view();
    }
    public function submit()
    {
        $data = $this->post();
        Session::set('paymentMethod',$data['payment-method']);

        //process payment
        $pmHandle = $data['payment-method'];
        $pm = StorePaymentMethod::getByHandle($pmHandle);

        // redirect/fail if we don't have a payment method, or it's shippible and there's no shipping method in the session
        if($pm === false || (StoreCart::isShippable() && !Session::get('community_store.smID'))){
            $this->redirect("/checkout");
            exit();
        }

        if($pm->getMethodController()->isExternal()){
            $order = StoreOrder::add($pm,null,'incomplete');
            Session::set('orderID',$order->getOrderID());
            $this->redirect('/checkout/external');
        } else {
            $payment = $pm->submitPayment();
            if($payment['error']==1){
                $errors = $payment['errorMessage'];
                Session::set('paymentErrors',$errors);
                $this->redirect("/checkout/failed#payment");
            } else {
                $transactionReference = $payment['transactionReference'];
                $order = StoreOrder::add($pm,$transactionReference);
                $this->redirect('/checkout/complete');
            }
        }

    }
    public function external()
    {
        $pmHandle = Session::get('paymentMethod');
        $pm = StorePaymentMethod::getByHandle($pmHandle);

        $this->set('pm',$pm);
        $this->set('action',$pm->getMethodController()->getAction());
    }

}
