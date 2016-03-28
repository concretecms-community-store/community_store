<?php

namespace Concrete\Package\CommunityStore\Controller\SinglePage\Dashboard\Store;

use \Concrete\Core\Page\Controller\DashboardPageController;
use Config;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Order\OrderStatus\OrderStatus as StoreOrderStatus;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Order\OrderList as StoreOrderList;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Order\Order as StoreOrder;
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

        $paginator = $orderList->getPagination();
        $pagination = $paginator->renderDefaultView();
        $this->set('orderList',$paginator->getCurrentPageResults());
        $this->set('pagination',$pagination);
        $this->set('paginator', $paginator);
        $this->set('orderStatuses', StoreOrderStatus::getList());
        $this->requireAsset('css', 'communityStoreDashboard');
        $this->requireAsset('javascript', 'communityStoreFunctions');
        $this->set('statuses', StoreOrderStatus::getAll());

        if (Config::get('community_store.shoppingDisabled') == 'all') {
            $this->set('shoppingDisabled', true);
        }
        $this->set('pageTitle', t('Orders'));
    }
    public function order($oID)
    {
        $order = StoreOrder::getByID($oID);

        if ($order) {
            $this->set("order", $order);
            $this->set('orderStatuses', StoreOrderStatus::getList());
            $orderChoicesAttList = StoreOrderKey::getAttributeListBySet('order_choices');
            $this->set("orderChoicesEnabled", count($orderChoicesAttList)? true : false);
            if (is_array($orderChoicesAttList) && !empty($orderChoicesAttList)) {
                $this->set("orderChoicesAttList", $orderChoicesAttList);
            }
            $this->requireAsset('javascript', 'communityStoreFunctions');
        } else {
            $this->redirect('/dashboard/store/orders');
        }
    }
    public function removed()
    {
        $this->set("success",t("Order Removed"));
        $this->view();
    }
    public function updatestatus($oID)
    {
        $data = $this->post();
        StoreOrder::getByID($oID)->updateStatus($data['orderStatus']);
        $this->redirect('/dashboard/store/orders/order',$oID);
    }
    public function remove($oID)
    {
        StoreOrder::getByID($oID)->remove();
        $this->redirect('/dashboard/store/orders/removed');
    }

}
