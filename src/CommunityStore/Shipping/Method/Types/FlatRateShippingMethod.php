<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Shipping\Method\Types;

use Doctrine\ORM\Mapping as ORM;
use Concrete\Core\Support\Facade\Application;
use Concrete\Core\Support\Facade\DatabaseORM as dbORM;
use Concrete\Package\CommunityStore\Src\CommunityStore\Cart\Cart;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product;
use Concrete\Package\CommunityStore\Src\CommunityStore\Customer\Customer;
use Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Calculator;
use Concrete\Package\CommunityStore\Src\CommunityStore\Shipping\Method\ShippingMethodTypeMethod;
use Concrete\Package\CommunityStore\Src\CommunityStore\Shipping\Method\ShippingMethodOffer;

/**
 * @ORM\Entity
 * @ORM\Table(name="CommunityStoreFlatRateMethods")
 */
class FlatRateShippingMethod extends ShippingMethodTypeMethod
{
    /**
     * @ORM\Column(type="float")
     */
    protected $baseRate;

    /**
     * @ORM\Column(type="string")
     */
    protected $rateType;
    /**
     * @ORM\Column(type="float",nullable=true)
     */
    protected $perItemRate;
    /**
     * @ORM\Column(type="float",nullable=true)
     */
    protected $perWeightRate;
    /**
     * @ORM\Column(type="float")
     */
    protected $minimumAmount;
    /**
     * @ORM\Column(type="float")
     */
    protected $maximumAmount;

    /**
     * @ORM\Column(type="float")
     */
    protected $minimumWeight;
    /**
     * @ORM\Column(type="float")
     */
    protected $maximumWeight;
    /**
     * @ORM\Column(type="string")
     */
    protected $countries;
    /**
     * @ORM\Column(type="text",nullable=true)
     */
    protected $countriesSelected;

    public function setBaseRate($baseRate)
    {
        $this->baseRate = $baseRate > 0 ? $baseRate : 0;
    }

    public function setRateType($rateType)
    {
        $this->rateType = $rateType;
    }

    public function setPerItemRate($perItemRate)
    {
        $this->perItemRate = $perItemRate > 0 ? $perItemRate : null;
    }

    public function setPerWeightRate($perWeightRate)
    {
        $this->perWeightRate = $perWeightRate > 0 ? $perWeightRate : null;
    }

    public function setMinimumAmount($minAmount)
    {
        $this->minimumAmount = $minAmount > 0 ? $minAmount : 0;
    }

    public function setMaximumAmount($maxAmount)
    {
        $this->maximumAmount = $maxAmount > 0 ? $maxAmount : 0;
    }

    public function setMinimumWeight($minWeight)
    {
        $this->minimumWeight = $minWeight > 0 ? $minWeight : 0;
    }

    public function setMaximumWeight($maxWeight)
    {
        $this->maximumWeight = $maxWeight > 0 ? $maxWeight : 0;
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
        if ("update" == $type) {
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
        $countriesSelected = '';
        if (isset($data['countriesSelected'])) {
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
        if ("" == $args['baseRate']) {
            $e->add(t("Please set a Base Rate"));
        }
        if (!is_numeric($args['baseRate'])) {
            $e->add(t("Base Rate should be a number"));
        }
        if ("" == !$args['perItemRate']) {
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
        $subtotal = Calculator::getSubTotal();
        $max = $this->getMaximumAmount();
        if (0 != $max) {
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
        $totalWeight = Cart::getCartWeight();
        $maxWeight = $this->getMaximumWeight();
        if (0 != $maxWeight) {
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
        $customer = new Customer();
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
        $shippableItems = Cart::getShippableItems();

        if (count($shippableItems) > 0) {
            if ('quantity' == $this->getRateType()) {
                $shippingTotal = $this->getQuantityBasedRate($shippableItems);
            } elseif ('weight' == $this->getRateType()) {
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
            $product = $item['product']['object'];

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
            $product = Product::getByID($item['product']['pID']);
            if ($product->isShippable()) {
                $totalQty = $totalQty + $item['product']['qty'];
            }
        }

        $shippingTotal = $baserate + ($totalQty * $peritemrate);

        return $shippingTotal;
    }

    public function getShippingMethodTypeName()
    {
        return t('Flat Rate');
    }

    public function getOffers()
    {
        $offers = [];

        $offer = new ShippingMethodOffer();
        $offer->setRate($this->getRate());

        $offers[] = $offer;

        return $offers;
    }

    public function getOffer($key)
    {
        $this->getOffers()[$key];
    }
}
