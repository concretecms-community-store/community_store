<?php
namespace Concrete\Package\CommunityStore\Controller\SinglePage;

use PageController;
use Core;
use View;
use Session;
use Config;
use Database;
use UserAttributeKey;

use \Concrete\Package\CommunityStore\Src\CommunityStore\Order\Order as StoreOrder;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Cart\Cart as StoreCart;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Payment\Method as StorePaymentMethod;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Customer\Customer as StoreCustomer;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Discount\DiscountRule as StoreDiscountRule;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Discount\DiscountCode as StoreDiscountCode;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Calculator as StoreCalculator;

class Checkout extends PageController
{
    public function view()
    {
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

        $db = Database::connection();

        $ak = UserAttributeKey::getByHandle('billing_address');
        $row = $db->GetRow(
            'select akHasCustomCountries, akDefaultCountry from atAddressSettings where akID = ?',
            array($ak->getAttributeKeyID())
        );

        $defaultBillingCountry = $row['akDefaultCountry'];

        if ($row['akHasCustomCountries'] == 1) {
            $availableBillingCountries = $db->GetCol(
                'select country from atAddressCustomCountries where akID = ?',
                array($ak->getAttributeKeyID())
            );

            $billingCountries = array();
            foreach($availableBillingCountries as $countrycode) {
                $billingCountries[$countrycode] = $allcountries[$countrycode];
            }
        } else {
            $billingCountries =  $allcountries;
        }

        $ak = UserAttributeKey::getByHandle('shipping_address');
        $row = $db->GetRow(
            'select akHasCustomCountries, akDefaultCountry from atAddressSettings where akID = ?',
            array($ak->getAttributeKeyID())
        );

        $defaultShippingCountry = $row['akDefaultCountry'];

        if ($row['akHasCustomCountries'] == 1) {
            $availableShippingCountries = $db->GetCol(
                'select country from atAddressCustomCountries where akID = ?',
                array($ak->getAttributeKeyID())
            );

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

        $this->set("states",Core::make('helper/lists/states_provinces')->getStates());

        $totals = StoreCalculator::getTotals();

        $this->set('subtotal',$totals['subTotal']);
        $this->set('taxes',$totals['taxes']);

        $this->set('taxtotal',$totals['taxTotal']);
        $this->set('shippingtotal',$totals['shippingTotal']);
        $this->set('total',$totals['total']);
        $this->set('shippingEnabled', StoreCart::isShippable());

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
        $this->set('paymentErrors',Session::get('paymentErrors'));
        $this->view();
    }
    public function submit()
    {
        $data = $this->post();
        
        //process payment
        $pmHandle = $data['payment-method'];
        $pm = StorePaymentMethod::getByHandle($pmHandle);
        if($pm === false){
            //There was no payment method enabled somehow.
            //so we'll force invoice.
            $pm = StorePaymentMethod::getByHandle('invoice');
        }

        if($pm->getMethodController()->external == true){
            $pmsess = Session::get('paymentMethod');
            $pmsess[$pm->getID()] = $data['payment-method'];
            Session::set('paymentMethod',$pmsess);
            $order = StoreOrder::add($data,$pm,null,'incomplete');
            Session::set('orderID',$order->getOrderID());
            $this->redirect('/checkout/external');
        } else {
            $payment = $pm->submitPayment();
            if($payment['error']==1){
                $pmsess = Session::get('paymentMethod');
                $pmsess[$pm->getID()] = $data['payment-method'];
                Session::set('paymentMethod',$pmsess);
                $errors = $payment['errorMessage'];
                Session::set('paymentErrors',$errors);
                $this->redirect("/checkout/failed#payment");
            } else {
                $transactionReference = $payment['transactionReference'];
                StoreOrder::add($data,$pm,$transactionReference);
                $this->redirect('/checkout/complete');
            }
        }

    }
    public function external()
    {
        $pm = Session::get('paymentMethod');
        /*print_r($pm);
        exit();die();
        */
        foreach($pm as $pmID=>$handle){
            $pm = StorePaymentMethod::getByID($pmID);
        }
        //$pm = PaymentMethod::getByHandle($pm[3]);
        $this->set('pm',$pm);
        $this->set('action',$pm->getMethodController()->getAction());

    }
    public function validate()
    {
        
    }

}
