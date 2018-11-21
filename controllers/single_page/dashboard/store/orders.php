<?php
namespace Concrete\Package\CommunityStore\Controller\SinglePage\Dashboard\Store;

use Concrete\Core\Page\Controller\DashboardPageController;
use Config;
use Concrete\Package\CommunityStore\Src\CommunityStore\Order\OrderStatus\OrderStatus as StoreOrderStatus;
use Concrete\Package\CommunityStore\Src\CommunityStore\Order\OrderList as StoreOrderList;
use Concrete\Package\CommunityStore\Src\CommunityStore\Order\Order as StoreOrder;
use Concrete\Package\CommunityStore\Entity\Attribute\Key\StoreOrderKey;
use Concrete\Core\Search\Pagination\PaginationFactory;

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

        if (Config::get('community_store.showUnpaidExternalPaymentOrders')) {
            $orderList->setIncludeExternalPaymentRequested(true);
        }

        $factory = new PaginationFactory(\Request::getInstance());
        $paginator = $factory->createPaginationObject($orderList);

        $pagination = $paginator->renderDefaultView();
        $this->set('orderList', $paginator->getCurrentPageResults());
        $this->set('pagination', $pagination);
        $this->set('paginator', $paginator);
        $this->set('orderStatuses', StoreOrderStatus::getList());
        $this->set('status', $status);
        $this->requireAsset('css', 'communityStoreDashboard');
        $this->requireAsset('javascript', 'communityStoreFunctions');
        $this->set('statuses', StoreOrderStatus::getAll());

        if ('all' == Config::get('community_store.shoppingDisabled')) {
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
                $this->set("orderChoicesAttList", []);
            }
            $this->requireAsset('javascript', 'communityStoreFunctions');
        } else {
            return \Redirect::to('/dashboard/store/orders');
        }

        $this->set('pageTitle', t("Order #") . $order->getOrderID());
    }

    public function updatestatus($oID)
    {
        $data = $this->post();
        if ($this->token->validate('community_store')) {
            StoreOrder::getByID($oID)->updateStatus($data['orderStatus']);
            $this->flash('success', t('Fulfilment Status Updated'));
            return \Redirect::to('/dashboard/store/orders/order', $oID);
        }
    }

    public function markpaid($oID)
    {
        if ($this->token->validate('community_store')) {
            $order = StoreOrder::getByID($oID);
            if ($this->post('transactionReference')) {
                $order->setTransactionReference($this->post('transactionReference'));
            }

            $user = new \User();

            $order->completePayment();
            $order->setPaidByUID($user->getUserID());
            $order->save();

            $this->flash('success', t('Order Marked As Paid'));
            return \Redirect::to('/dashboard/store/orders/order', $oID);
        }
    }

    public function reversepaid($oID)
    {
        if ($this->token->validate('community_store')) {
            $order = StoreOrder::getByID($oID);
            $order->setPaid(null);
            $order->setPaidByUID(null);
            $order->setTransactionReference(null);
            $order->save();

            $this->flash('success', t('Order Payment Reversed'));
            return \Redirect::to('/dashboard/store/orders/order', $oID);
        }
    }

    public function markrefunded($oID)
    {
        if ($this->token->validate('community_store')) {
            $order = StoreOrder::getByID($oID);
            $user = new \User();

            $order->setRefunded(new \DateTime());
            $order->setRefundedByUID($user->getUserID());
            $order->setRefundReason($this->post('oRefundReason'));
            $order->save();

            $this->flash('success', t('Order Marked As Refunded'));
            return \Redirect::to('/dashboard/store/orders/order', $oID);
        }
    }

    public function reverserefund($oID)
    {
        if ($this->token->validate('community_store')) {
            $order = StoreOrder::getByID($oID);
            $order->setRefunded(null);
            $order->setRefundedByUID(null);
            $order->setRefundReason(null);
            $order->save();

            $this->flash('success', t('Order Refund Reversed'));
            return \Redirect::to('/dashboard/store/orders/order', $oID);
        }
    }

    public function markcancelled($oID)
    {
        if ($this->token->validate('community_store')) {
            $order = StoreOrder::getByID($oID);
            $user = new \User();

            $order->setCancelled(new \DateTime());
            $order->setCancelledByUID($user->getUserID());
            $order->save();

            $this->flash('success', t('Order Cancelled'));
            return \Redirect::to('/dashboard/store/orders/order', $oID);
        }
    }

    public function reversecancel($oID)
    {
        if ($this->token->validate('community_store')) {
            $order = StoreOrder::getByID($oID);
            $order->setCancelled(null);
            $order->setCancelledByUID(null);
            $order->save();

            $this->flash('success', t('Order Cancellation Reversed'));
            return \Redirect::to('/dashboard/store/orders/order', $oID);
        }
    }

    public function resendinvoice($oID)
    {
        if ($this->token->validate('community_store')) {
            $order = StoreOrder::getByID($oID);

            if ($order && $this->post('email')) {
                $order->sendOrderReceipt($this->post('email'));
                $this->flash('success', t('Receipt Email Resent to %s', $this->post('email')));
            }

            return \Redirect::to('/dashboard/store/orders/order', $oID);
        }
    }

    public function resendnotification($oID)
    {
        if ($this->token->validate('community_store')) {
            $order = StoreOrder::getByID($oID);
            $emails = $this->post('email');

            if ($order && $emails) {
                $order->sendNotifications($this->post('email'));
                $notificationEmails = explode(",", trim($emails));
                $notificationEmails = array_map('trim', $notificationEmails);
                $this->flash('success', t('Notification Email Resent to %s', implode(', ', $notificationEmails)));
            }

            return \Redirect::to('/dashboard/store/orders/order', $oID);
        }
    }

    public function remove($oID)
    {
        if ($this->token->validate('community_store')) {
            StoreOrder::getByID($oID)->remove();
            $this->flash('success', t('Order Deleted'));
            return \Redirect::to('/dashboard/store/orders');
        }
    }
}
