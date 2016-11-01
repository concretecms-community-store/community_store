<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Shipping\Method\Types;

use Package;
use Core;
use Database;
use Concrete\Package\CommunityStore\Src\CommunityStore\Shipping\Method\ShippingMethodTypeMethod;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product as StoreProduct;
use Concrete\Package\CommunityStore\Src\CommunityStore\Cart\Cart as StoreCart;
use Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Calculator as StoreCalculator;
use Concrete\Package\CommunityStore\Src\CommunityStore\Customer\Customer as StoreCustomer;
use Concrete\Package\CommunityStore\Src\CommunityStore\Shipping\Method\ShippingMethodOffer as StoreShippingMethodOffer;

/**
 * @Entity
 * @Table(name="CommunityStoreFlatRateMethods")
 */
class FlatRateShippingMethod extends ShippingMethodTypeMethod
{
    /**
     * @Column(type="float")
     */
    protected $baseRate;

    /**
     * @Column(type="string")
     */
    protected $rateType;
    /**
     * @Column(type="float",nullable=true)
     */
    protected $perItemRate;
    /**
     * @Column(type="float",nullable=true)
     */
    protected $perWeightRate;
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

    public function setBaseRate($baseRate)
    {
        $this->baseRate = $baseRate;
    }
    public function setRateType($rateType)
    {
        $this->rateType = $rateType;
    }
    public function setPerItemRate($perItemRate)
    {
        $this->perItemRate = $perItemRate;
    }
    public function setPerWeightRate($perWeightRate)
    {
        $this->perWeightRate = $perWeightRate;
    }
    public function setMinimumAmount($minAmount)
    {
        $this->minimumAmount = $minAmount;
    }
    public function setMaximumAmount($maxAmount)
    {
        $this->maximumAmount = $maxAmount;
    }
    public function setMinimumWeight($minWeight)
    {
        $this->minimumWeight = $minWeight;
    }
    public function setMaximumWeight($maxWeight)
    {
        $this->maximumWeight = $maxWeight;
    }
    public function setCountries($countries)
    {
        $this->countries = $countries;
    }
    public function setCountriesSelected($countriesSelected)
    {
        $this->countriesSelected = $countriesSelected;
    }

    public function getBaseRate()
    {
        return $this->baseRate;
    }
    public function getRateType()
    {
        return $this->rateType;
    }
    public function getPerItemRate()
    {
        return $this->perItemRate;
    }
    public function getPerWeightRate()
    {
        return $this->perWeightRate;
    }
    public function getMinimumAmount()
    {
        return $this->minimumAmount;
    }
    public function getMaximumAmount()
    {
        return $this->maximumAmount;
    }
    public function getMinimumWeight()
    {
        return $this->minimumWeight;
    }
    public function getMaximumWeight()
    {
        return $this->maximumWeight;
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
        if ($type == "update") {
            $sm = $this;
        } else {
            $sm = new self();
        }
        $sm->setBaseRate($data['baseRate']);
        $sm->setRateType($data['rateType']);
        $sm->setPerItemRate($data['perItemRate']);
        $sm->setPerWeightRate($data['perWeightRate']);
        $sm->setMinimumAmount($data['minimumAmount']);
        $sm->setMaximumAmount($data['maximumAmount']);
        $sm->setMinimumWeight($data['minimumWeight']);
        $sm->setMaximumWeight($data['maximumWeight']);
        $sm->setCountries($data['countries']);
        if ($data['countriesSelected']) {
            $countriesSelected = implode(',', $data['countriesSelected']);
        }
        $sm->setCountriesSelected($countriesSelected);

        $em = \Database::connection()->getEntityManager();
        $em->persist($sm);
        $em->flush();

        return $sm;
    }

    public function dashboardForm($shippingMethod = null)
    {
        $this->set('form', Core::make("helper/form"));
        $this->set('smt', $this);
        $this->set('countryList', Core::make('helper/lists/countries')->getCountries());

        if (is_object($shippingMethod)) {
            $smtm = $shippingMethod->getShippingMethodTypeMethod();
        } else {
            $smtm = new self();
        }
        $this->set("smtm", $smtm);
    }
    public function validate($args, $e)
    {
        if ($args['baseRate'] == "") {
            $e->add(t("Please set a Base Rate"));
        }
        if (!is_numeric($args['baseRate'])) {
            $e->add(t("Base Rate should be a number"));
        }
        if (!$args['perItemRate'] == "") {
            if (!is_numeric($args['perItemRate'])) {
                $e->add(t("The Price Per Item doesn't have to be set, but it does have to be numeric"));
            }
        }

        return $e;
    }

    public function isEligible()
    {
        //three checks - within countries, price range, and weight
        if ($this->isWithinRange()) {
            if ($this->isWithinSelectedCountries()) {
                if ($this->isWithinWeight()) {
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
        if ($max != 0) {
            if ($subtotal >= $this->getMinimumAmount() && $subtotal <= $this->getMaximumAmount()) {
                return true;
            } else {
                return false;
            }
        } elseif ($subtotal >= $this->getMinimumAmount()) {
            return true;
        } else {
            return false;
        }
    }

    public function isWithinWeight()
    {
        $totalWeight = StoreCart::getCartWeight();
        $maxWeight = $this->getMaximumWeight();
        if ($maxWeight != 0) {
            if ($totalWeight >= $this->getMinimumWeight() && $totalWeight <= $this->getMaximumWeight()) {
                return true;
            } else {
                return false;
            }
        } elseif ($totalWeight >= $this->getMinimumWeight()) {
            return true;
        } else {
            return false;
        }
    }

    public function isWithinSelectedCountries()
    {
        $customer = new StoreCustomer();
        $custCountry = $customer->getValue('shipping_address')->country;
        if ($this->getCountries() != 'all') {
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
        $shippableItems = StoreCart::getShippableItems();
        if (count($shippableItems) > 0) {
            if ($this->getRateType() == 'quantity') {
                $shippingTotal = $this->getQuantityBasedRate($shippableItems);
            } elseif ($this->getRateType() == 'weight') {
                $shippingTotal = $this->getWeightBasedRate($shippableItems);
            }
        } else {
            $shippingTotal = 0;
        }

        return $shippingTotal;
    }

    public function getWeightBasedRate($shippableItems)
    {
        $baserate = $this->getBaseRate();
        $totalWeight = 0;
        foreach ($shippableItems as $item) {
            $product = StoreProduct::getByID($item['product']['pID']);

            if ($item['product']['variation']) {
                $product->setVariation($item['product']['variation']);
            }

            if ($product->isShippable()) {
                $totalProductWeight = $product->getWeight() * $item['product']['qty'];
                $totalWeight = $totalWeight + $totalProductWeight;
            }
        }
        $perWeightRate = $this->getPerWeightRate();
        $totalWeightRate = $perWeightRate * $totalWeight;
        $shippingTotal = $baserate + $totalWeightRate;

        return $shippingTotal;
    }

    public function getQuantityBasedRate($shippableItems)
    {
        $baserate = $this->getBaseRate();
        $peritemrate = $this->getPerItemRate();
        $totalQty = 0;
        //go through items
        foreach ($shippableItems as $item) {
            //check if items are shippable
            $product = StoreProduct::getByID($item['product']['pID']);
            if ($product->isShippable()) {
                $totalQty = $totalQty + $item['product']['qty'];
            }
        }
        if ($totalQty > 1) {
            $shippingTotal = $baserate + (($totalQty - 1) * $peritemrate);
        } elseif ($totalQty == 1) {
            $shippingTotal = $baserate;
        }

        return $shippingTotal;
    }

    public function getShippingMethodTypeName() {
        return t('Flat Rate');
    }

    public function getOffers() {
        $offers = array();

        $offer = new StoreShippingMethodOffer();
        $offer->setRate($this->getRate());

        $offers[] = $offer;
        return $offers;
    }

    public function getOffer($key) {
        $this->getOffers()[$key];
    }
}
