<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Tax;

use Concrete\Package\CommunityStore\Src\CommunityStore\Event\Event as StoreEvent;

class TaxEvent extends StoreEvent
{

    private $updatedRate = null;
    private $updatedLabel = null;

    public function getUpdatedRate()
    {
        return $this->updatedRate;
    }

    public function setUpdatedRate($rate)
    {
        $this->updatedRate = $rate;
    }

    public function setUpdatedLabel($label)
    {
        $this->updatedLabel = $label;
    }

    public function getUpdatedLabel()
    {
        return($this->updatedLabel);
    }

    public function getTaxRate()
    {
        return $this->subject;
    }

    public function setTaxRate($rate)
    {
        $this->subject = $rate;
    }

}
