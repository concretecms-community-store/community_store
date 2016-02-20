<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Shipping\Method\Types;

use Package;
use Core;
use Database;

use \Concrete\Package\CommunityStore\Src\CommunityStore\Shipping\Method\ShippingMethodTypeMethod;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Cart\Cart as StoreCart;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Calculator as StoreCalculator;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Customer\Customer as StoreCustomer;

/**
 * @Entity
 * @Table(name="CommunityStoreFreeShippingMethods")
 */
class FreeShippingShippingMethod extends ShippingMethodTypeMethod
{

    /**
     * @Column(type="float")
     */
    protected $minimumAmount;
    /**
     * @Column(type="float")
     */
    protected $maximumAmount;

    /**
     * @Column(type="float")
     */
    protected $minimumWeight;
    /**
     * @Column(type="float")
     */
    protected $maximumWeight;
    /**
     * @Column(type="string")
     */
    protected $countries;
    /**
     * @Column(type="text",nullable=true)
     */
    protected $countriesSelected;

    public function setMinimumAmount($minAmount){ $this->minimumAmount = $minAmount; }
    public function setMaximumAmount($maxAmount){ $this->maximumAmount = $maxAmount; }
    public function setMinimumWeight($minWeight){ $this->minimumWeight = $minWeight; }
    public function setMaximumWeight($maxWeight){ $this->maximumWeight = $maxWeight; }
    public function setCountries($countries){ $this->countries = $countries; }
    public function setCountriesSelected($countriesSelected){ $this->countriesSelected = $countriesSelected; }

    public static function getByID($smtmID)
    {
        $em = Database::connection()->getEntityManager();
        return $em->getRepository('\Concrete\Package\CommunityStore\Src\CommunityStore\Shipping\Method\Types\FreeShippingShippingMethod')
            ->find($smtmID);
    }

    public function getMinimumAmount(){ return $this->minimumAmount; }
    public function getMaximumAmount(){ return $this->maximumAmount; }
    public function getMinimumWeight(){ return $this->minimumWeight; }
    public function getMaximumWeight(){ return $this->maximumWeight; }
    public function getCountries(){ return $this->countries; }
    public function getCountriesSelected(){ return $this->countriesSelected; }

    public function addMethodTypeMethod($data)
    {
        return $this->addOrUpdate('update',$data);
    }
    public function update($data)
    {
        return $this->addOrUpdate('update',$data);
    }

    private function addOrUpdate($type,$data)
    {
        if($type=="update"){
            $sm = $this;
        } else {
            $sm = new self();
        }
        $sm->setMinimumAmount($data['minimumAmount']);
        $sm->setMaximumAmount($data['maximumAmount']);
        $sm->setMinimumWeight($data['minimumWeight']);
        $sm->setMaximumWeight($data['maximumWeight']);
        $sm->setCountries($data['countries']);
        if($data['countriesSelected']){
            $countriesSelected = implode(',',$data['countriesSelected']);
        }
        $sm->setCountriesSelected($countriesSelected);

        $em = Database::connection()->getEntityManager();
        $em->persist($sm);
        $em->flush();

        return $sm;
    }

    public function dashboardForm($shippingMethod = null)
    {
        $this->set('form',Core::make("helper/form"));
        $this->set('smt',$this);
        $pkg = Package::getByHandle("community_store");
        $pkgconfig = $pkg->getConfig();
        $this->set('config',$pkgconfig);
        $this->set('countryList',Core::make('helper/lists/countries')->getCountries());

        if(is_object($shippingMethod)){
            $smtm = $shippingMethod->getShippingMethodTypeMethod();
        } else {
            $smtm = new self();
        }
        $this->set("smtm",$smtm);

    }
    public function validate($args,$e)
    {

        return $e;

    }

    public function isEligible()
    {
        //three checks - within countries, price range, and weight
        if($this->isWithinRange()){
            if($this->isWithinSelectedCountries()){
                if($this->isWithinWeight()){
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function isWithinRange()
    {
        $subtotal = StoreCalculator::getSubTotal();
        $max = $this->getMaximumAmount();
        if($max!=0){
            if($subtotal >= $this->getMinimumAmount() && $subtotal <= $this->getMaximumAmount()){
                return true;
            } else {
                return false;
            }
        } elseif($subtotal >= $this->getMinimumAmount()) {
            return true;
        } else {
            return false;
        }
    }

    public function isWithinWeight()
    {
        $totalWeight = StoreCart::getCartWeight();
        $maxWeight = $this->getMaximumWeight();
        if($maxWeight!=0){
            if($totalWeight >= $this->getMinimumWeight() && $totalWeight <= $this->getMaximumWeight()){
                return true;
            } else {
                return false;
            }
        } elseif($totalWeight >= $this->getMinimumWeight()) {
            return true;
        } else {
            return false;
        }
    }

    public function isWithinSelectedCountries()
    {
        $customer = new StoreCustomer();
        $custCountry = $customer->getValue('shipping_address')->country;
        if($this->getCountries() != 'all'){
            $selectedCountries = explode(',',$this->getCountriesSelected());
            if(in_array($custCountry,$selectedCountries)){
                return true;
            } else {
                return false;
            }
        } else {
            return true;
        }
    }

    public function getRate()
    {
        return 0;
    }

}
