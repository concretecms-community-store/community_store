<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Tax;

use Doctrine\ORM\Mapping as ORM;
use Concrete\Core\Support\Facade\DatabaseORM as dbORM;
use Concrete\Core\Support\Facade\Config;
use Concrete\Package\CommunityStore\Src\CommunityStore\Cart\Cart as StoreCart;
use Concrete\Package\CommunityStore\Src\CommunityStore\Customer\Customer as StoreCustomer;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product as StoreProduct;
use Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Price as StorePrice;
use Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Calculator as StoreCalculator;
use Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Checkout as StoreCheckout;

/**
 * @ORM\Entity
 * @ORM\Table(name="CommunityStoreTaxRates")
 */
class TaxRate
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $trID;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $taxEnabled;

    /**
     * @ORM\Column(type="string")
     */
    protected $taxLabel;

    /**
     * @ORM\Column(type="float")
     */
    protected $taxRate;

    /**
     * @ORM\Column(type="string")
     */
    protected $taxBasedOn;

    /**
     * @ORM\Column(type="string")
     */
    protected $taxAddress;

    /**
     * @ORM\Column(type="text")
     */
    protected $taxCountry;

    /**
     * @ORM\Column(type="string")
     */
    protected $taxState;

    /**
     * @ORM\Column(type="string")
     */
    protected $taxCity;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $taxVatExclude;

    public function setEnabled($enabled)
    {
        $this->taxEnabled = $enabled;
    }

    public function setTaxLabel($label)
    {
        $this->taxLabel = $label;
    }

    public function setTaxRate($rate)
    {
        $this->taxRate = $rate;
    }

    public function setTaxBasedOn($basedOn)
    {
        $this->taxBasedOn = $basedOn;
    }

    public function setTaxAddress($address)
    {
        $this->taxAddress = $address;
    }

    public function setTaxCountry(array $countries = null)
    {
        if ($countries) {
            $countries = array_map('trim', $countries);
            $countries = implode(',', $countries);
            $this->taxCountry = $countries;
        } else {
            $this->taxCountry = '';
        }
    }

    public function setTaxState($state)
    {
        $this->taxState = $state;
    }

    public function setTaxCity($city)
    {
        $this->taxCity = $city;
    }

    public function setTaxVatExclude($exclude)
    {
        $this->taxVatExclude = $exclude;
    }

    public function getID()
    {
        return $this->trID;
    }

    public function getTaxRateID()
    {
        return $this->trID;
    }

    public function isEnabled()
    {
        return $this->taxEnabled;
    }

    public function getTaxLabel()
    {
        return $this->taxLabel;
    }

    public function getTaxRate()
    {
        return $this->taxRate;
    }

    public function getTaxBasedOn()
    {
        return $this->taxBasedOn;
    }

    public function getTaxAddress()
    {
        return $this->taxAddress;
    }

    public function getTaxCountry()
    {
        return explode(',', $this->taxCountry);
    }

    public function getTaxState()
    {
        return $this->taxState;
    }

    public function getTaxCity()
    {
        return $this->taxCity;
    }

    public function getTaxVatExclude()
    {
        return $this->taxVatExclude;
    }

    public static function getByID($trID)
    {
        $em = dbORM::entityManager();

        return $em->find(get_class(), $trID);
    }

    public function isVatNumberEligible()
    {
        return $this->getTaxVatExclude();
    }

    public function isTaxable()
    {
        $taxAddress = $this->getTaxAddress();
        $taxCountries = $this->getTaxCountry();
        $taxCountries = array_map('strtolower', $taxCountries);
        $taxState = strtolower(trim($this->getTaxState()));
        $taxCity = strtolower(trim($this->getTaxCity()));
        $taxVatExclude = 1 == $this->getTaxVatExclude() ? true : false;
        $taxSettingEnabled = '1' == Config::get('community_store.vat_number') ? true : false;
        $customer = new StoreCustomer();
        $customerIsTaxable = false;

        // If they have a vat_number check if it's valid and if so, don't apply tax
        $vatIsValid = false;
        $vat_number = $customer->getValue("vat_number");
        if (!empty($vat_number) && StoreCheckout::validateVatNumber($vat_number)) {
            $vatIsValid = true;
        }

        switch ($taxAddress) {
            case "billing":
                $userCity = strtolower(trim($customer->getValue("billing_address")->city));
                $userState = strtolower(trim($customer->getValue("billing_address")->state_province));
                $userCountry = strtolower(trim($customer->getValue("billing_address")->country));
                break;
            case "shipping":
                $userCity = strtolower(trim($customer->getValue("shipping_address")->city));
                $userState = strtolower(trim($customer->getValue("shipping_address")->state_province));
                $userCountry = strtolower(trim($customer->getValue("shipping_address")->country));
                break;
        }

        if (in_array($userCountry, $taxCountries)) {
            $customerIsTaxable = true;
            if (!empty($taxState)) {
                if ($userState != $taxState) {
                    $customerIsTaxable = false;
                }
            }
            if (!empty($taxCity)) {
                if ($userCity != $taxCity) {
                    $customerIsTaxable = false;
                }
            }
            if ($taxSettingEnabled && $vatIsValid && $taxVatExclude) {
                $customerIsTaxable = false;
            }
        }

        return $customerIsTaxable;
    }

    public function calculate()
    {
        $cart = StoreCart::getCart();
        $producttaxtotal = 0;
        $shippingtaxtotal = 0;
        if ($cart) {
            foreach ($cart as $cartItem) {
                $pID = $cartItem['product']['pID'];
                $qty = $cartItem['product']['qty'];
                $product = StoreProduct::getByID($pID);

                if ($cartItem['product']['variation']) {
                    $product->shallowClone = true;
                    $product = clone $product;
                    $product->setVariation($cartItem['product']['variation']);
                }

                if (is_object($product)) {
                    if ($product->isTaxable()) {
                        //if this tax rate is in the tax class associated with this product
                        if (is_object($product->getTaxClass())) {
                            if ($product->getTaxClass()->taxClassContainsTaxRate($this)) {
                                $taxCalc = Config::get('community_store.calculation');
                                $productSubTotal = $product->getActivePrice($qty) * $qty;

                                if ('extract' == $taxCalc) {
                                    $taxrate = 1 + ($this->getTaxRate() / 100);
                                    $tax = $productSubTotal - ($productSubTotal / $taxrate);
                                } else {
                                    $taxrate = $this->getTaxRate() / 100;
                                    $tax = $taxrate * $productSubTotal;
                                }

                                $producttaxtotal = $producttaxtotal + $tax;
                            }
                        }//if in products tax class
                    }//if product is taxable
                }//if obj
            }//foreach
        }//if cart

        if ('grandtotal' == $this->getTaxBasedOn()) {
            $shippingTotal = StorePrice::getFloat(StoreCalculator::getShippingTotal());

            if ('extract' == $taxCalc) {
                $taxrate = 1 + ($this->getTaxRate() / 100);
                $shippingtaxtotal = $shippingTotal - ($shippingTotal / $taxrate);
            } else {
                $taxrate = $this->getTaxRate() / 100;
                $shippingtaxtotal = $taxrate * $shippingTotal;
            }
        }

        return ['producttax' => $producttaxtotal, 'shippingtax' => $shippingtaxtotal];
    }

    public function calculateProduct($productObj, $qty)
    {
        $taxtotal = 0;

        if (is_object($productObj)) {
            if ($productObj->isTaxable()) {
                //if this tax rate is in the tax class associated with this product
                if ($productObj->getTaxClass()->taxClassContainsTaxRate($this)) {
                    $taxCalc = $taxCalc = Config::get('community_store.calculation');
                    $productSubTotal = $productObj->getActivePrice($qty) * $qty;

                    if ('extract' == $taxCalc) {
                        $taxrate = 1 + ($this->getTaxRate() / 100);
                        $tax = $productSubTotal - ($productSubTotal / $taxrate);
                    } else {
                        $taxrate = $this->getTaxRate() / 100;
                        $tax = $taxrate * $productSubTotal;
                    }

                    $taxtotal = $taxtotal + $tax;
                }//if in products tax class
            }//if product is taxable
        }//if obj
        return $taxtotal;
    }

    public static function add($data)
    {
        if ($data['taxRateID']) {
            $tr = self::getByID($data['taxRateID']);
        } else {
            $tr = new self();
        }
        $tr->setEnabled($data['taxEnabled']);
        $tr->setTaxLabel($data['taxLabel']);
        $tr->setTaxRate($data['taxRate']);
        $tr->setTaxBasedOn($data['taxBased']);
        $tr->setTaxAddress($data['taxAddress']);
        $tr->setTaxCountry($data['taxCountry']);
        if (is_array($data['taxCountry']) && count($data['taxCountry']) > 1) {
            $data['taxState'] = '';
            $data['taxCity'] = '';
        }
        $tr->setTaxState($data['taxState']);
        $tr->setTaxCity($data['taxCity']);
        $tr->setTaxVatExclude(isset($data['taxVatExclude']) ? $data['taxVatExclude'] : 0);
        $tr->save();

        return $tr;
    }

    public function save()
    {
        $em = dbORM::entityManager();
        $em->persist($this);
        $em->flush();
    }

    public function delete()
    {
        $em = dbORM::entityManager();
        $em->remove($this);
        $em->flush();
    }
}
