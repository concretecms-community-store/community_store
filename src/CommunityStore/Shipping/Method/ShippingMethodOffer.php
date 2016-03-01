<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Shipping\Method;

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
        return $this->rate;
    }

    /**
     * @param mixed $rate
     */
    public function setRate($rate)
    {
        $this->rate = $rate;
    }



}