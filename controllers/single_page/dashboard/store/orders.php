<?php

namespace Concrete\Package\CommunityStore\Controller\SinglePage\Dashboard\Store;

use Concrete\Core\Page\Controller\DashboardPageController;
use Config;
use User;
use Concrete\Package\CommunityStore\Src\CommunityStore\Order\OrderStatus\OrderStatus as StoreOrderStatus;
use Concrete\Package\CommunityStore\Src\CommunityStore\Order\OrderList as StoreOrderList;
use Concrete\Package\CommunityStore\Src\CommunityStore\Order\Order as StoreOrder;
use Concrete\Package\CommunityStore\Src\Attribute\Key\StoreOrderKey as StoreOrderKey;

class Orders extends DashboardPageController
{

    public function view($status = '')
    {
        $orderList = new StoreOrderList();

        if ($this->get('keywords')) {
            $orderList->setSearch($this->get('keywords'));
        }

        if ($status) {
            $orderList->setStatus($status);
        }

        $orderList->setItemsPerPage(20);

        if (Config::get('community_store.showUnpaidExternalPaymentOrders') ) {
            $orderList->setIncludeExternalPaymentRequested(true);
        }

        $paginator = $orderList->getPagination();
        $pagination = $paginator->renderDefaultView();
        $this->set('orderList',$paginator->getCurrentPageResults());
        $this->set('pagination',$pagination);
        $this->set('paginator', $paginator);
        $this->set('orderStatuses', StoreOrderStatus::getList());
        $this->set('status', $status);
        $this->requireAsset('css', 'communityStoreDashboard');
        $this->requireAsset('javascript', 'communityStoreFunctions');
        $this->set('statuses', StoreOrderStatus::getAll());

        if (Config::get('community_store.shoppingDisabled') == 'all') {
            $this->set('shoppingDisabled', true);
        }
        $this->set('pageTitle', t('Orders'));

        $this->requireAsset('js', 'communityStoreDashboard');
    }
    public function order($oID)
    {
        $order = StoreOrder::getByID($oID);

        if ($order) {
            $this->set("order", $order);
            $this->set('orderStatuses', StoreOrderStatus::getList());
            $orderChoicesAttList = StoreOrderKey::getAttributeListBySet('order_choices');
            if (is_array($orderChoicesAttList) && !empty($orderChoicesAttList)) {
                $this->set("orderChoicesAttList", $orderChoicesAttList);
            } else {
                $this->set("orderChoicesAttList", array());
            }
            $this->requireAsset('javascript', 'communityStoreFunctions');
        } else {
            $this->redirect('/dashboard/store/orders');
        }

        $this->set('pageTitle', t("Order #") . $order->getOrderID());
    }

    public function updatestatus($oID)
    {
        $data = $this->post();
        StoreOrder::getByID($oID)->updateStatus($data['orderStatus']);
        $this->flash('success', t('Fulfilment Status Updated'));
        $this->redirect('/dashboard/store/orders/order',$oID);
    }

    public function markpaid($oID)
    {
        $order = StoreOrder::getByID($oID);

        if ($this->post('transactionReference')) {
            $order->setTransactionReference($this->post('transactionReference'));
        }

        $user = new \User();

        $order->completePayment();
        $order->setPaidByUID($user->getUserID());
        $order->save();

        $this->flash('success', t('Order Marked As Paid'));
        $this->redirect('/dashboard/store/orders/order',$oID);
    }

    public function reversepaid($oID)
    {
        $order = StoreOrder::getByID($oID);
        $order->setPaid(null);
        $order->setPaidByUID(null);
        $order->setTransactionReference(null);
        $order->save();

        $this->flash('success', t('Order Payment Reversed'));
        $this->redirect('/dashboard/store/orders/order',$oID);
    }

    public function markrefunded($oID)
    {
        $order = StoreOrder::getByID($oID);
        $user = new \User();

        $order->setRefunded(new \DateTime());
        $order->setRefundedByUID($user->getUserID());
        $order->setRefundReason($this->post('oRefundReason'));
        $order->save();

        $this->flash('success', t('Order Marked As Refunded'));
        $this->redirect('/dashboard/store/orders/order',$oID);
    }

    public function reverserefund($oID)
    {
        $order = StoreOrder::getByID($oID);
        $order->setRefunded(null);
        $order->setRefundedByUID(null);
        $order->setRefundReason(null);
        $order->save();

        $this->flash('success', t('Order Refund Reversed'));
        $this->redirect('/dashboard/store/orders/order',$oID);
    }

    public function markcancelled($oID)
    {
        $order = StoreOrder::getByID($oID);
        $user = new \User();

        $order->setCancelled(new \DateTime());
        $order->setCancelledByUID($user->getUserID());
        $order->save();

        $this->flash('success', t('Order Cancelled'));
        $this->redirect('/dashboard/store/orders/order',$oID);
    }

    public function reversecancel($oID)
    {
        $order = StoreOrder::getByID($oID);
        $order->setCancelled(null);
        $order->setCancelledByUID(null);
        $order->save();

        $this->flash('success', t('Order Cancellation Reversed'));
        $this->redirect('/dashboard/store/orders/order',$oID);
    }

    public function resendinvoice($oID) {
        $order = StoreOrder::getByID($oID);

        if ($order && $this->post('email')){
           $order->sendOrderReceipt($this->post('email'));
            $this->flash('success', t('Receipt Email Resent to %s', $this->post('email')));
        }

        $this->redirect('/dashboard/store/orders/order',$oID);
    }

    public function resendnotification($oID) {
        $order = StoreOrder::getByID($oID);
        $emails = $this->post('email');

        if ($order && $emails){
            $order->sendNotifications($this->post('email'));
            $notificationEmails = explode(",", trim($emails));
            $notificationEmails = array_map('trim', $notificationEmails);
            $this->flash('success', t('Notification Email Resent to %s', implode(', ', $notificationEmails)));
        }

        $this->redirect('/dashboard/store/orders/order',$oID);
    }

    public function remove($oID)
    {
        StoreOrder::getByID($oID)->remove();
        $this->flash('success', t('Order Deleted'));
        $this->redirect('/dashboard/store/orders');
    }

}
