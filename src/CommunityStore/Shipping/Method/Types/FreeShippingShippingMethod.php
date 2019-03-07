<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Shipping\Method\Types;

use Doctrine\ORM\Mapping as ORM;
use Concrete\Core\Support\Facade\DatabaseORM as dbORM;
use Concrete\Core\Support\Facade\Application;
use Concrete\Package\CommunityStore\Src\CommunityStore\Shipping\Method\ShippingMethodTypeMethod;
use Concrete\Package\CommunityStore\Src\CommunityStore\Cart\Cart as StoreCart;
use Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Calculator as StoreCalculator;
use Concrete\Package\CommunityStore\Src\CommunityStore\Customer\Customer as StoreCustomer;
use Concrete\Package\CommunityStore\Src\CommunityStore\Shipping\Method\ShippingMethodOffer as StoreShippingMethodOffer;

/**
 * @ORM\Entity
 * @ORM\Table(name="CommunityStoreFreeShippingMethods")
 */
class FreeShippingShippingMethod extends ShippingMethodTypeMethod
{
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
        return $this->addOrUpdate('update', $data);
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
        $sm->setMinimumAmount($data['minimumAmount']);
        $sm->setMaximumAmount($data['maximumAmount']);
        $sm->setMinimumWeight($data['minimumWeight']);
        $sm->setMaximumWeight($data['maximumWeight']);
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
        $totalWeight = StoreCart::getCartWeight();
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

    public function getOffers()
    {
        $offers = [];

        $offer = new StoreShippingMethodOffer();
        $offer->setRate($this->getRate());

        $offers[] = $offer;

        return $offers;
    }

    private function getRate()
    {
        return 0;
    }

    public function getShippingMethodTypeName()
    {
        return t('Free Shipping');
    }
}
