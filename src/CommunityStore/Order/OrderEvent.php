<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Order;

use \Symfony\Component\EventDispatcher\GenericEvent;

class OrderEvent extends GenericEvent {

    protected $event;

    public function __construct($currentOrder, $previousOrder = null) {
        $this->currentOrder = $currentOrder;
        $this->previousOrder = $previousOrder;
    }

    public function getCurrentOrder() {
        return $this->currentOrder;
    }

    public function getOrderBeforeChange() {
       return $this->previousOrder;
    }
}
