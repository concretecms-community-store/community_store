<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Shipping\Method\Types;

use Doctrine\ORM\Mapping as ORM;
use Concrete\Core\Support\Facade\DatabaseORM as dbORM;
use Concrete\Core\Support\Facade\Application;
use Concrete\Package\CommunityStore\Src\CommunityStore\Shipping\Method\ShippingMethodTypeMethod;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product as StoreProduct;
use Concrete\Package\CommunityStore\Src\CommunityStore\Cart\Cart as StoreCart;
use Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Calculator as StoreCalculator;
use Concrete\Package\CommunityStore\Src\CommunityStore\Customer\Customer as StoreCustomer;
use Concrete\Package\CommunityStore\Src\CommunityStore\Shipping\Method\ShippingMethodOffer as StoreShippingMethodOffer;

/**
 * @ORM\Entity
 * @ORM\Table(name="CommunityStoreElectronicDeliveryFeeMethods")
 */
class ElectronicDeliveryFeeShippingMethod extends ShippingMethodTypeMethod
{
    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $fixedRate;
    /**
     * @ORM\Column(type="float",nullable=true)
     */
    protected $percentageRate;
    /**
     * @ORM\Column(type="string")
     */
    protected $countries;
    /**
     * @ORM\Column(type="text",nullable=true)
     */
    protected $countriesSelected;



    public function setFixedRate($fixedRate)
    {
        $this->fixedRate = $fixedRate > 0 ? $fixedRate : null;
    }

    public function setPercentageRate($percentageRate)
    {
        $this->percentageRate = $percentageRate > 0.00 ? $percentageRate : null;
    }

    public function setCountries($countries)
    {
        $this->countries = $countries;
    }

    public function setCountriesSelected($countriesSelected)
    {
        $this->countriesSelected = $countriesSelected;
    }
    
    

    public function getFixedRate()
    {
        return $this->fixedRate;
    }

    public function getPercentageRate()
    {
        return $this->percentageRate;
    }

    public function getCountries()
    {
        return $this->countries;
    }

    public function getCountriesSelected()
    {
        return $this->countriesSelected;
    }

    public function addMethodTypeMethod($data)
    {
        return $this->addOrUpdate('add', $data);
    }

    public function update($data)
    {
        return $this->addOrUpdate('update', $data);
    }

    private function addOrUpdate($type, $data)
    {
        if ("update" == $type) {
            $sm = $this;
        } else {
            $sm = new self();
        }
        
        $sm->setFixedRate($data['fixedRate']);
        $sm->setPercentageRate($data['percentageRate']);
        $sm->setCountries($data['countries']);
        
        if ($data['countriesSelected']) {
            $countriesSelected = implode(',', $data['countriesSelected']);
        }
        
        $sm->setCountriesSelected($countriesSelected);

        $em = dbORM::entityManager();
        $em->persist($sm);
        $em->flush();

        return $sm;
    }

    public function dashboardForm($shippingMethod = null)
    {
        $app = Application::getFacadeApplication();
        
        $this->set('form', $app->make("helper/form"));
        $this->set('smt', $this);
        $this->set('countryList', $app->make('helper/lists/countries')->getCountries());

        if (is_object($shippingMethod)) {
            $smtm = $shippingMethod->getShippingMethodTypeMethod();
        } else {
            $smtm = new self();
        }
        
        $this->set("smtm", $smtm);
    }

    public function validate($args, $e)
    {
        if ( ("" == $args['fixedRate']) && ("" == $args['percentageRate']) ) {
            $e->add(t("Fixed Rate and Percentage Rate cannot both be unset."));
        }
        
        if ( ("" == !$args['fixedRate']) && (! is_numeric($args['fixedRate'])) ) {
            $e->add(t("Fixed Rate should be a number."));
        }
        
        if ( ("" == !$args['percentageRate']) && (! is_numeric($args['percentageRate'])) ) {
            $e->add(t("Percentage Rate should be a number."));
        }
        
        return $e;
    }

    public function isEligible()
    {
        //three checks - within countries, price range, and weight
        return ( ($this->isWithinSelectedCountries())
        //  &&   ($this->isWithinRange()) // TODO: Should still check to make sure order amount is valid, e.g., more than the fixed rate.
            &&   (StoreCart::getCartWeight() == 0) );
    }

    // TODO: Should check to make sure order amount is valid, e.g., more than the fixed rate.
    // public function isWithinRange()
    // {
    //     $subtotal = StoreCalculator::getSubTotal();
    //     return ($subtotal >= something_or_some_calculation)
    // }

    public function isWithinSelectedCountries()
    {
        $customer = new StoreCustomer();
        $custCountry = $customer->getValue('shipping_address')->country;
        if ('all' != $this->getCountries()) {
            $selectedCountries = explode(',', $this->getCountriesSelected());
            if (in_array($custCountry, $selectedCountries)) {
                return true;
            } else {
                return false;
            }
        } else {
            return true;
        }
    }

    private function getRate()
    {
        // TODO: Add property for electronic deliverability
        // $shippableItems = StoreCart::getShippableItems();
        
        //if (count($shippableItems) == 0) {
        //    return 0;
        //}
        
        $orderSubTotal  = StoreCalculator::getSubTotal();
        $fixedRate      = $this->getFixedRate();
        $percentageRate = $this->getPercentageRate();

        // TODO: Add property for electronic deliverability
        // foreach ($shippableItems as $item) {
        //     //check if item is shippable
        //     $product = StoreProduct::getByID($item['product']['pID']);
        //     if ($product->isShippable()) {
        //         $totalQty = $totalQty + $item['product']['qty'];
        //     }
        // }
        
        $deliveryFeeTotal = ( (($orderSubTotal + $fixedRate) / (1 - $percentageRate)) - $orderSubTotal );

        return $deliveryFeeTotal;
    }

    public function getShippingMethodTypeName()
    {
        return t('Electronic Delivery Fee');
    }

    public function getOffers()
    {
        $offers = [];

        $offer = new StoreShippingMethodOffer();
        $offer->setRate($this->getRate());

        $offers[] = $offer;

        return $offers;
    }

    public function getOffer($key)
    {
        $this->getOffers()[$key];
    }
}
