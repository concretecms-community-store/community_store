<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Shipping\Method;

use Concrete\Package\CommunityStore\Src\CommunityStore\Cart\Cart as StoreCart;

class ShippingMethodOffer
{
    private $label;
    private $key;
    private $offerLabel;
    private $offerDetails;
    private $rate;

    /**
     * @return mixed
     */
    public function getMethodLabel()
    {
        return $this->label;
    }

    /**
     * @param mixed $label
     */
    public function setMethodLabel($label)
    {
        $this->label = $label;
    }

    /**
     * @return mixed
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param mixed $key
     */
    public function setKey($key)
    {
        $this->key = $key;
    }


    public function getLabel() {
        if ($this->getOfferLabel()) {
            return $this->getOfferLabel();
        } else {
            return $this->getMethodLabel();
        }
    }

    /**
     * @return mixed
     */
    public function getOfferLabel()
    {
        return $this->offerLabel;
    }

    /**
     * @param mixed $offerLabel
     */
    public function setOfferLabel($offerLabel)
    {
        $this->offerLabel = $offerLabel;
    }

    /**
     * @return mixed
     */
    public function getOfferDetails()
    {
        return $this->offerDetails;
    }

    /**
     * @param mixed $offerDetails
     */
    public function setOfferDetails($offerDetails)
    {
        $this->offerDetails = $offerDetails;
    }

    /**
     * @return mixed
     */
    public function getRate()
    {
        return $this->rate ;
    }


    public function getDiscountedRate()
    {
        $discounts = StoreCart::getDiscounts();
        $deduct = 0;
        $percentage = 1;

        if (!empty($discounts)) {
            foreach ($discounts as $discount) {
                if($discount->getDeductFrom() == 'shipping') {

                    if ($discount->getDeductType() == 'value') {
                        $deduct += $discount->getValue();
                    }

                    if ($discount->getDeductType() == 'percentage') {
                        $percentage -= ($discount->getPercentage() / 100);
                    }
                }
            }
        }

        return max(($this->rate * $percentage) - $deduct, 0);
    }

    /**
     * @param mixed $rate
     */
    public function setRate($rate)
    {
        $this->rate = $rate;
    }



}