<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Tax;

use Database;
use Config;
use Concrete\Package\CommunityStore\Src\CommunityStore\Cart\Cart as StoreCart;
use Concrete\Package\CommunityStore\Src\CommunityStore\Customer\Customer as StoreCustomer;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product as StoreProduct;
use Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Price as StorePrice;
use Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Calculator as StoreCalculator;

defined('C5_EXECUTE') or die(_("Access Denied."));

/**
 * @Entity
 * @Table(name="CommunityStoreTaxRates")
 */
class TaxRate
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="AUTO")
     */
    protected $trID;

    /**
     * @Column(type="boolean")
     */
    protected $taxEnabled;

    /**
     * @Column(type="string")
     */
    protected $taxLabel;

    /**
     * @Column(type="float")
     */
    protected $taxRate;

    /**
     * @Column(type="string")
     */
    protected $taxBasedOn;

    /**
     * @Column(type="string")
     */
    protected $taxAddress;

    /**
     * @Column(type="string")
     */
    protected $taxCountry;

    /**
     * @Column(type="string")
     */
    protected $taxState;

    /**
     * @Column(type="string")
     */
    protected $taxCity;

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
    public function setTaxCountry($country)
    {
        $this->taxCountry = $country;
    }
    public function setTaxState($state)
    {
        $this->taxState = $state;
    }
    public function setTaxCity($city)
    {
        $this->taxCity = $city;
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
        return $this->taxCountry;
    }
    public function getTaxState()
    {
        return $this->taxState;
    }
    public function getTaxCity()
    {
        return $this->taxCity;
    }

    public static function getByID($trID)
    {
        $db = \Database::connection();
        $em = $db->getEntityManager();

        return $em->find(get_class(), $trID);
    }

    public function isTaxable()
    {
        $taxAddress = $this->getTaxAddress();
        $taxCountry = strtolower($this->getTaxCountry());
        $taxState = strtolower(trim($this->getTaxState()));
        $taxCity = strtolower(trim($this->getTaxCity()));

        $customer = new StoreCustomer();
        $customerIsTaxable = false;

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

        if ($userCountry == $taxCountry) {
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
        }

        return $customerIsTaxable;
    }

    public function calculate()
    {
        $cart = StoreCart::getCart();
        $taxtotal = 0;
        if ($cart) {
            foreach ($cart as $cartItem) {
                $pID = $cartItem['product']['pID'];
                $qty = $cartItem['product']['qty'];
                $product = StoreProduct::getByID($pID);
                if (is_object($product)) {
                    if ($product->isTaxable()) {
                        //if this tax rate is in the tax class associated with this product
                        if (is_object($product->getTaxClass())) {
                            if ($product->getTaxClass()->taxClassContainsTaxRate($this)) {
                                $taxCalc = Config::get('community_store.calculation');

                                if ($taxCalc == 'extract') {
                                    $taxrate =   1 + ($this->getTaxRate() / 100) ;
                                } else {
                                    $taxrate = $this->getTaxRate() / 100;
                                }

                                switch ($this->getTaxBasedOn()) {
                                    case "subtotal":
                                        $productSubTotal = $product->getActivePrice() * $qty;

                                        if ($taxCalc == 'extract') {
                                            $tax = $productSubTotal - ($productSubTotal / $taxrate);
                                        } else {
                                            $tax = $taxrate * $productSubTotal;
                                        }

                                        $taxtotal = $taxtotal + $tax;
                                        break;
                                    case "grandtotal":
                                        $productSubTotal = $product->getActivePrice() * $qty;
                                        $shippingTotal = StorePrice::getFloat(StoreCalculator::getShippingTotal());
                                        $taxableTotal = $productSubTotal + $shippingTotal;

                                        if ($taxCalc == 'extract') {
                                            $tax = $taxableTotal - ($taxableTotal / $taxrate);
                                        } else {
                                            $tax = $taxrate * $taxableTotal;
                                        }

                                        $taxtotal = $taxtotal + $tax;
                                        break;
                                }
                            }
                        }//if in products tax class
                    }//if product is taxable
                }//if obj
            }//foreach
        }//if cart

        return $taxtotal;
    }
    public function calculateProduct($productObj, $qty)
    {
        $taxtotal = 0;

        if (is_object($productObj)) {
            if ($productObj->isTaxable()) {
                //if this tax rate is in the tax class associated with this product
                if ($productObj->getTaxClass()->taxClassContainsTaxRate($this)) {
                    $taxCalc = $taxCalc = Config::get('community_store.calculation');

                    if ($taxCalc == 'extract') {
                        $taxrate = 1 + ($this->getTaxRate() / 100);
                    } else {
                        $taxrate = $this->getTaxRate() / 100;
                    }

                    switch ($this->getTaxBasedOn()) {
                        case "subtotal":
                            $productSubTotal = $productObj->getActivePrice() * $qty;

                            if ($taxCalc == 'extract') {
                                $tax = $productSubTotal - ($productSubTotal / $taxrate);
                            } else {
                                $tax = $taxrate * $productSubTotal;
                            }

                            $taxtotal = $taxtotal + $tax;
                            break;
                        case "grandtotal":
                            $productSubTotal = $productObj->getActivePrice() * $qty;
                            $shippingTotal = StorePrice::getFloat(StoreCalculator::getShippingTotal());
                            $taxableTotal = $productSubTotal + $shippingTotal;

                            if ($taxCalc == 'extract') {
                                $tax = $taxableTotal - ($taxableTotal / $taxrate);
                            } else {
                                $tax = $taxrate * $taxableTotal;
                            }

                            $taxtotal = $taxtotal + $tax;
                            break;
                    }
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
        $tr->setTaxState($data['taxState']);
        $tr->setTaxCity($data['taxCity']);
        $tr->save();

        return $tr;
    }
    public function save()
    {
        $em = \Database::connection()->getEntityManager();
        $em->persist($this);
        $em->flush();
    }

    public function delete()
    {
        $em = \Database::connection()->getEntityManager();
        $em->remove($this);
        $em->flush();
    }
}
