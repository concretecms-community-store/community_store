<?php

namespace Concrete\Package\CommunityStore\Src\CommunityStore\Shipping\Method;

use Concrete\Package\CommunityStore\Src\CommunityStore\Cart\Cart;

class ShippingMethodOffer
{
    private $label;

    private $key;

    private $offerLabel;

    private $offerDetails;

    private $rate;

    private $shipmentID;

    private $rateID;

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

    public function getLabel()
    {
        if ($this->getOfferLabel()) {
            return $this->getOfferLabel();
        }

            return $this->getMethodLabel();
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
        return $this->rate;
    }

    public function getDiscountedRate()
    {
        $discounts = Cart::getDiscounts();
        $deduct = 0;
        $percentage = 1;

        if (!empty($discounts)) {
            foreach ($discounts as $discount) {
                if ($discount->getDeductFrom() == 'shipping') {
                    if ($discount->getDeductType() == 'value' || $discount->getDeductType() == 'value_all') {
                        $deduct += $discount->getValue();
                    }

                    if ($discount->getDeductType() == 'percentage') {
                        $percentage -= ($discount->getPercentage() / 100);
                    }

                    if ($discount->getDeductType() == 'fixed') {
                        return $discount->getValue();
                    }
                }
            }
        }

        return max(($this->rate * $percentage) - $deduct, 0);
    }

    public function setRate($rate)
    {
        $this->rate = $rate;
    }

    public function getShipmentID()
    {
        return $this->shipmentID;
    }

    public function setShipmentID($shipmentID)
    {
        $this->shipmentID = $shipmentID;
    }

    public function getRateID()
    {
        return $this->rateID;
    }

    public function setRateID($rateID)
    {
        $this->rateID = $rateID;
    }
}
