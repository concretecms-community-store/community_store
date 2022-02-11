<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Order;

use Concrete\Package\CommunityStore\Src\CommunityStore\Event\Event as StoreEvent;

class OrderEvent extends StoreEvent
{
    const ORDER_CREATED = 'on_community_store_order_created';
    const ORDER_PLACED = 'on_community_store_order';
    const ORDER_STATUS_UPDATE = 'on_community_store_order_status_update';
    const ORDER_PAYMENT_COMPLETE = 'on_community_store_payment_complete';
    const ORDER_BEFORE_PAYMENT_COMPLETE = 'on_community_store_before_payment_complete';
    const ORDER_BEFORE_USER_ADD = 'on_community_store_before_user_add';
    const ORDER_CANCELLED = 'on_community_store_order_cancelled';

    protected $event;

    protected $userData;

    protected $userDataUpdated = false;

    protected $notificationEmails;

    public function __construct($currentOrder, $previousStatusHandle = null)
    {
        $this->currentOrder = $currentOrder;
        $this->previousStatusHandle = $previousStatusHandle;
    }

    public function getOrder()
    {
        return $this->currentOrder;
    }

    public function setNotificationEmails($notificationEmails)
    {
        $this->notificationEmails = $notificationEmails;
    }

    public function getNotificationEmails()
    {
        return $this->notificationEmails;
    }

    public function getPreviousStatusHandle()
    {
        return $this->previousStatusHandle;
    }

	/**
	 * @param $data array
	 */
    public function updateUserData($data){
    	$this->userData = $data;
    	$this->userDataUpdated = true;

	}

	/**
	 * @return array | null
	 */
	public function getUserData(){
		return $this->userData;
	}

	public function userDataUpdated() {
    	return $this->userDataUpdated;
	}

}
