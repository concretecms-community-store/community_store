<?php
namespace Concrete\Package\CommunityStore\Controller\SinglePage\Dashboard\Store;

use Concrete\Core\Http\Request;
use Concrete\Core\Support\Facade\Config;
use Concrete\Core\Search\Pagination\PaginationFactory;
use Concrete\Core\Page\Controller\DashboardPageController;
use Concrete\Package\CommunityStore\Src\CommunityStore\Order\Order;
use Concrete\Package\CommunityStore\Src\CommunityStore\Order\OrderList;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductType\ProductTypeList;
use Concrete\Package\CommunityStore\Src\CommunityStore\Report\SalesReport;
use Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\SalesSuspension;

class Overview extends DashboardPageController {

    public function view()
    {
        $sr = new SalesReport();
        $this->set('sr', $sr);
        $this->requireAsset('chartist');
        $this->requireAsset('css', 'communityStoreDashboard');
        $this->requireAsset('javascript', 'communityStoreFunctions');

        $orderList = new OrderList();
        $orderList->setItemsPerPage(10);

        $factory = new PaginationFactory($this->app->make(Request::class));
        $paginator = $factory->createPaginationObject($orderList);

        $orders = new OrderList();
        $orders->setItemsPerPage(10);

        $pagination = $paginator->renderDefaultView();
        $this->set('orders', $paginator->getCurrentPageResults());
        $this->set('pagination', $pagination);
        $this->set('paginator', $paginator);

        if ('all' == Config::get('community_store.shoppingDisabled')) {
            $this->set('shoppingDisabled', true);
        }
        $salesSuspension = $this->app->make(SalesSuspension::class);
        $this->set('salesSuspended', $salesSuspension->salesCurrentlySuspended());

        if (!Config::get('community_store.notificationemails')) {
            $this->set('missingNotificationEmails', true);
        }

        if(!Config::get('community_store.emailalerts')) {
            $this->set('missingFromEmail', true);
        }

        $this->set('pageTitle', t('Store'));

        $lastTemporaryOrderClean = Config::get('community_store.lastTemporaryOrderClean');
        $timestamp = time();

        // run every 30 days
        if (!$lastTemporaryOrderClean || ($lastTemporaryOrderClean <  ($timestamp - (60 * 60 * 24 * 30)))) {
            Order::clearTemporaryOrders();
            Config::save('community_store.lastTemporaryOrderClean', $timestamp);
        }
    }

}
