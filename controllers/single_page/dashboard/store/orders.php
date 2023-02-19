<?php

namespace Concrete\Package\CommunityStore\Controller\SinglePage\Dashboard\Store;

use Concrete\Core\Filesystem\ElementManager;
use Concrete\Core\Http\Request;
use Concrete\Core\Page\Controller\DashboardPageController;
use Concrete\Core\Routing\Redirect;
use Concrete\Core\Search\Pagination\PaginationFactory;
use Concrete\Core\Support\Facade\Config;
use Concrete\Core\User\User;
use Concrete\Core\View\View;
use Concrete\Package\CommunityStore\Entity\Attribute\Key\StoreOrderKey;
use Concrete\Package\CommunityStore\Src\CommunityStore\Order\Order;
use Concrete\Package\CommunityStore\Src\CommunityStore\Order\OrderEvent;
use Concrete\Package\CommunityStore\Src\CommunityStore\Order\OrderList;
use Concrete\Package\CommunityStore\Src\CommunityStore\Order\OrderStatus\OrderStatus;
use Concrete\Package\CommunityStore\Src\CommunityStore\Payment\Method as PaymentMethod;

class Orders extends DashboardPageController
{
    public function view($status = 'all', $paymentMethod = 'all', $paymentStatus = 'all')
    {
        $statusFilter = $status;
        $paymentMethodFilter = $paymentMethod;
        $paymentStatusFilter = $paymentStatus;

        if ($status == 'all') {
            $statusFilter = '';
        }

        if ($paymentMethod == 'all') {
            $paymentMethodFilter = '';
        }

        if ($paymentStatus == 'all') {
            $paymentStatusFilter = '';
        }

        $orderList = new OrderList();

        if ($this->request->query->get('keywords')) {
            $orderList->setSearch($this->request->query->get('keywords'));
            $this->set('keywords', $this->request->query->get('keywords'));
        }

        if ($statusFilter) {
            $orderList->setStatus($statusFilter);
        }

        if ($paymentMethodFilter) {
            $orderList->setPaymentMethods($paymentMethodFilter);
        }

        if ($paymentStatusFilter) {
            $orderList->setPaymentStatus($paymentStatusFilter);
        }

        if (Config::get('community_store.numberOfOrders')) {
            $orderList->setItemsPerPage(Config::get('community_store.numberOfOrders'));
        } else {
            $orderList->setItemsPerPage(20);
        }

        if (Config::get('community_store.showUnpaidExternalPaymentOrders')) {
            $orderList->setIncludeExternalPaymentRequested(true);
        }

        $factory = new PaginationFactory($this->app->make(Request::class));
        $paginator = $factory->createPaginationObject($orderList);
        $paymentMethods = PaymentMethod::getEnabledMethods();

        $pagination = $paginator->renderDefaultView();
        $this->set('orderList', $paginator->getCurrentPageResults());
        $this->set('pagination', $pagination);
        $this->set('paginator', $paginator);
        $statuses = OrderStatus::getList();
        $this->set('orderStatuses', $statuses);
        $this->set('status', $status);
        $this->requireAsset('css', 'communityStoreDashboard');
        $this->requireAsset('javascript', 'communityStoreFunctions');
        $fulfilmentStatuses = OrderStatus::getAll();
        $this->set('statuses', $fulfilmentStatuses);
        $this->set('paymentMethod', $paymentMethod);
        $this->set('enabledPaymentMethods', $paymentMethods);
        $this->set('paymentStatus', $paymentStatus);

        if (Config::get('community_store.shoppingDisabled') == 'all') {
            $this->set('shoppingDisabled', true);
        }
        $this->set('pageTitle', t('Orders'));

        $paymentStatuses = [];

        $paymentStatuses['paid'] = t('Paid');
        $paymentStatuses['unpaid'] = t('Unpaid');
        $paymentStatuses['refunded'] = t('Refunded');
        if (Config::get('community_store.showUnpaidExternalPaymentOrders')) {
            $paymentStatuses['incomplete'] = t('Incomplete');
        }

        $paymentStatuses['cancelled'] = t('Cancelled');

        $headerSearch = $this->getHeaderSearch($paymentMethods, $paymentMethod, $paymentStatuses, $paymentStatus, $fulfilmentStatuses, $status);
        $this->set('headerSearch', $headerSearch);
    }

    public function order($oID)
    {
        $order = Order::getByID($oID);

        if ($order) {
            $this->set('order', $order);
            $this->set('orderStatuses', OrderStatus::getList());
            $orderChoicesAttList = StoreOrderKey::getAttributeListBySet('order_choices');
            if (is_array($orderChoicesAttList) && !empty($orderChoicesAttList)) {
                $this->set('orderChoicesAttList', $orderChoicesAttList);
            } else {
                $this->set('orderChoicesAttList', []);
            }
            $this->requireAsset('javascript', 'communityStoreFunctions');
        } else {
            return Redirect::to('/dashboard/store/orders');
        }

        $this->set('showFiles', class_exists('Concrete\Package\CommunityStoreFileUploads\Src\CommunityStore\Order\OrderItemFile'));

        $this->set('pageTitle', t('Order #') . $order->getOrderID());
    }

    public function updatestatus($oID)
    {
        $data = $this->request->request->all();
        if ($this->token->validate('community_store')) {
            $comment = isset($data['comment']) ? $data['comment'] : null;
            Order::getByID($oID)->updateStatus($data['orderStatus'], $comment);
            $this->flash('success', t('Fulfilment Status Updated'));

            return Redirect::to('/dashboard/store/orders/order', $oID);
        }
    }

    public function markpaid($oID)
    {
        if ($this->token->validate('community_store')) {
            $order = Order::getByID($oID);
            if ($this->request->request->get('transactionReference')) {
                $order->setTransactionReference($this->request->request->get('transactionReference'));
            }

            $user = new User();

            $order->completePayment();
            $order->setExternalPaymentRequested(null);
            $order->setPaidByUID($user->getUserID());
            $order->save();

            $this->flash('success', t('Order Marked As Paid'));

            return Redirect::to('/dashboard/store/orders/order', $oID);
        }
    }

    public function reversepaid($oID)
    {
        if ($this->token->validate('community_store')) {
            $order = Order::getByID($oID);
            $order->setPaid(null);
            $order->setPaidByUID(null);
            $order->setTransactionReference(null);
            $order->save();

            $this->flash('success', t('Order Payment Reversed'));

            return Redirect::to('/dashboard/store/orders/order', $oID);
        }
    }

    public function markrefunded($oID)
    {
        if ($this->token->validate('community_store')) {
            $order = Order::getByID($oID);
            $user = new User();

            $order->setRefunded(new \DateTime());
            $order->setRefundedByUID($user->getUserID());
            $order->setRefundReason($this->request->request->get('oRefundReason'));
            $order->save();

            $this->flash('success', t('Order Marked As Refunded'));

            return Redirect::to('/dashboard/store/orders/order', $oID);
        }
    }

    public function reverserefund($oID)
    {
        if ($this->token->validate('community_store')) {
            $order = Order::getByID($oID);
            $order->setRefunded(null);
            $order->setRefundedByUID(null);
            $order->setRefundReason(null);
            $order->save();

            $this->flash('success', t('Order Refund Reversed'));

            return Redirect::to('/dashboard/store/orders/order', $oID);
        }
    }

    public function markcancelled($oID)
    {
        if ($this->token->validate('community_store')) {
            $order = Order::getByID($oID);
            $user = new User();

            $order->setCancelled(new \DateTime());
            $order->setCancelledByUID($user->getUserID());
            $order->save();
            $event = new OrderEvent($order);
            $event = \Events::dispatch(OrderEvent::ORDER_CANCELLED, $event);

            $this->flash('success', t('Order Cancelled'));

            return Redirect::to('/dashboard/store/orders/order', $oID);
        }
    }

    public function reversecancel($oID)
    {
        if ($this->token->validate('community_store')) {
            $order = Order::getByID($oID);
            $order->setCancelled(null);
            $order->setCancelledByUID(null);
            $order->save();

            $this->flash('success', t('Order Cancellation Reversed'));

            return Redirect::to('/dashboard/store/orders/order', $oID);
        }
    }

    public function resendinvoice($oID)
    {
        if ($this->token->validate('community_store')) {
            $order = Order::getByID($oID);

            if ($order && $this->request->request->get('email')) {
                $order->sendOrderReceipt($this->request->request->get('email'));
                $this->flash('success', t('Receipt Email resent to %s', $this->request->request->get('email')));
            }

            return Redirect::to('/dashboard/store/orders/order', $oID);
        }
    }

    public function resendnotification($oID)
    {
        if ($this->token->validate('community_store')) {
            $order = Order::getByID($oID);
            $emails = $this->request->request->get('email');

            if ($order && $emails) {
                $order->sendNotifications($this->request->request->get('email'));
                $notificationEmails = explode(',', trim($emails));
                $notificationEmails = array_map('trim', $notificationEmails);
                $this->flash('success', t('Notification Email resent to %s', implode(', ', $notificationEmails)));
            }

            return Redirect::to('/dashboard/store/orders/order', $oID);
        }
    }

    public function remove($oID)
    {
        if ($this->token->validate('community_store')) {
            Order::getByID($oID)->remove();
            $this->flash('success', t('Order Deleted'));
        }

        return Redirect::to('/dashboard/store/orders');
    }

    public function printslip($oID)
    {
        $o = Order::getByID($oID);
        $orderChoicesAttList = StoreOrderKey::getAttributeListBySet('order_choices', User::getByUserID($o->getCustomerID()));

        if (file_exists(DIR_BASE . '/application/elements/order_slip.php')) {
            View::element('order_slip', ['order' => $o, 'orderChoicesAttList' => $orderChoicesAttList]);
        } else {
            View::element('order_slip', ['order' => $o, 'orderChoicesAttList' => $orderChoicesAttList], 'community_store');
        }

        exit();
    }

    protected function getHeaderSearch($paymentMethods, $paymentMethod, $paymentStatuses, $paymentStatus, $fulfilmentStatuses, $status)
    {
        if (!isset($this->headerSearch)) {
            $this->headerSearch = $this->app->make(ElementManager::class)->get('orders/search', 'community_store', ['paymentMethods' => $paymentMethods, 'paymentMethod' => $paymentMethod, 'paymentStatuses' => $paymentStatuses, 'paymentStatus' => $paymentStatus, 'fulfilmentStatuses' => $fulfilmentStatuses, 'status' => $status]);
        }

        return $this->headerSearch;
    }
}
