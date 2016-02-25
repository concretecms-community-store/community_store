<?php

namespace Concrete\Package\CommunityStore\Controller\SinglePage\Dashboard\Store;

use \Concrete\Core\Page\Controller\DashboardPageController;
use Package;
use Core;
use Config;

use \Concrete\Package\CommunityStore\Src\CommunityStore\Order\OrderStatus\OrderStatus as StoreOrderStatus;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Tax\TaxClass as StoreTaxClass;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Payment\Method as StorePaymentMethod;

class Settings extends DashboardPageController
{

    public function on_start()
    {
        
    }

    public function view(){
       $this->loadFormAssets();
       $this->set("pageSelector",Core::make('helper/form/page_selector'));
       $this->set("countries",Core::make('helper/lists/countries')->getCountries());
       $this->set("states",Core::make('helper/lists/states_provinces')->getStates());
       $this->set("installedPaymentMethods",StorePaymentMethod::getMethods());
       $this->set("orderStatuses",StoreOrderStatus::getAll());
       $productPublishTarget = Config::get('communitystore.productPublishTarget');
       $this->set('productPublishTarget',$productPublishTarget);
    }
    public function loadFormAssets()
    {
        $pkg = Package::getByHandle('community_store');
        $pkgconfig = $pkg->getConfig();
        $this->set('pkgconfig',$pkgconfig);
        $this->requireAsset('css', 'communityStoreDashboard');
        $this->requireAsset('javascript', 'communityStoreFunctions');
    }
    public function success()
    {
        $this->set('success',t('Settings Saved'));
        $this->view();
    }
    public function failed()
    {
        $this->view();
    }
    public function save()
    {
        $this->view();
        $args = $this->post();
        
        if ($args) {
            $errors = $this->validate($args);
            $this->error = $errors;
            
            if (!$errors->has()) {
                
                Config::save('communitystore.symbol',$args['symbol']);
                Config::save('communitystore.currency',$args['currency']);
                Config::save('communitystore.whole',$args['whole']);
                Config::save('communitystore.thousand',$args['thousand']);
                Config::save('communitystore.taxenabled',$args['taxEnabled']);
                Config::save('communitystore.taxcountry',$args['taxCountry']);
                Config::save('communitystore.taxstate',$args['taxState']);
                Config::save('communitystore.taxcity',trim($args['taxCity']));
                Config::save('communitystore.taxAddress',trim($args['taxAddress']));
                Config::save('communitystore.taxMatch',trim($args['taxMatch']));
                Config::save('communitystore.taxBased',trim($args['taxBased']));
                Config::save('communitystore.taxrate',trim($args['taxRate']));
                Config::save('communitystore.taxName',trim($args['taxName']));
                Config::save('communitystore.calculation',trim($args['calculation']));
                Config::save('communitystore.shippingenabled',$args['shippingEnabled']);
                Config::save('communitystore.shippingbase',$args['shippingBasePrice']);
                Config::save('communitystore.shippingitem',$args['shippingItemPrice']);
                Config::save('communitystore.weightUnit',$args['weightUnit']);
                Config::save('communitystore.sizeUnit',$args['sizeUnit']);
                Config::save('communitystore.notificationemails',$args['notificationEmails']);
                Config::save('communitystore.emailalerts',$args['emailAlert']);
                Config::save('communitystore.emailalertsname',$args['emailAlertName']);
                Config::save('communitystore.productPublishTarget',$args['productPublishTarget']);
                Config::save('communitystore.guestCheckout',$args['guestCheckout']);

                //save payment methods
                if($args['paymentMethodHandle']){

                    // TODO - refactor how this is saved
                    foreach($args['paymentMethodEnabled'] as $pmID=>$value){
                        $pm = StorePaymentMethod::getByID($pmID);
                        $pm->setEnabled($value);
                        $controller = $pm->getMethodController();
                        $controller->save($args);
                    }

                    foreach($args['paymentMethodDisplayName'] as $pmID=>$value){
                        $pm = StorePaymentMethod::getByID($pmID);
                        $pm->setDisplayName($value);
                        $pm->save();
                    }

                    foreach($args['paymentMethodSortOrder'] as $pmID=>$value){
                        $pm = StorePaymentMethod::getByID($pmID);
                        $pm->setSortOrder($value);
                        $pm->save();
                    }
                }

                $this->saveOrderStatuses($args);
                
                $this->redirect('/dashboard/store/settings/success');
                
            }//if no errors 

        }//if post

    }

    private function saveOrderStatuses($data) {
        if (isset($data['osID'])) {
            foreach ($data['osID'] as $key => $id) {
                $orderStatus = StoreOrderStatus::getByID($id);
                $orderStatusSettings = array(
                    'osName' => ((isset($data['osName'][$key]) && $data['osName'][$key]!='') ?
                        $data['osName'][$key] : $orderStatus->getReadableHandle()),
                    'osInformSite' => isset($data['osInformSite'][$key]) ? 1 : 0,
                    'osInformCustomer' => isset($data['osInformCustomer'][$key]) ? 1 : 0,
                    'osSortOrder' => $key
                );
                $orderStatus->update($orderStatusSettings);
            }
            if (isset($data['osIsStartingStatus'])) {
                StoreOrderStatus::setNewStartingStatus(StoreOrderStatus::getByID($data['osIsStartingStatus'])->getHandle());
            } else {
                $orderStatuses = StoreOrderStatus::getAll();
                StoreOrderStatus::setNewStartingStatus($orderStatuses[0]->getHandle());
            }
        }
    }
    public function validate($args)
    {
        $e = Core::make('helper/validation/error');

        if($args['symbol']==""){
            $e->add(t('You must set a currency symbol'));
        }
        if($args['taxEnabled']=='yes'){
            if(!is_numeric(trim($args['taxRate']))){
                $e->add(t('Tax Rate must be set, and a number'));
            }
        }
        if($args['shippingEnabled']=='yes'){
            if(!is_numeric(trim($args['shippingBasePrice']))){
                $e->add(t('Shipping Base Rate must be set, and a number'));
            }
            if(!is_numeric(trim($args['shippingItemPrice']))){
                $e->add(t('Shipping Base Rate must be set, and a number (even if just zero)'));
            }
        }
        $paymentMethodsEnabled = 0;
        foreach($args['paymentMethodEnabled'] as $method){
            if($method==1){
                $paymentMethodsEnabled++;
            }
        }
        if($paymentMethodsEnabled==0){
            $e->add(t('At least one payment method must be enabled'));
        }
        foreach($args['paymentMethodEnabled'] as $pmID=>$value){
            $pm = StorePaymentMethod::getByID($pmID);
            $controller = $pm->getMethodController();
            $e = $controller->validate($args,$e);
        }

        if (!isset($args['osName'])) {
            $e->add(t('You must have at least one Order Status.'));
        }
        
        //before changing tax settings to "Extract", make sure there's only one rate per class
        $taxClasses = StoreTaxClass::getTaxClasses();
        foreach($taxClasses as $taxClass){
            $taxClassRates = $taxClass->getTaxClassRates();
            if(count($taxClassRates)>1){
                $e->add(t("The %s Tax Class can't contain more than 1 Tax Rate if you change how the taxes are calculated",$taxClass->getTaxClassName()));
            }
        }
        
        return $e;
        
    }

}
