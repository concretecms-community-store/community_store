<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Order;

use Symfony\Component\EventDispatcher\GenericEvent;

class OrderEvent extends GenericEvent
{
    protected $event;

    public function __construct($currentOrder, $previousStatusHandle = null)
    {
        $this->currentOrder = $currentOrder;
        $this->previousStatusHandle = $previousStatusHandle;
    }

    public function getOrder()
    {
        return $this->currentOrder;
    }

    public function getPreviousStatusHandle()
    {
        return $this->previousStatusHandle;
    }
}
