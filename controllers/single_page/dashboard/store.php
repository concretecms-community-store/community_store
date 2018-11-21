<?php
namespace Concrete\Package\CommunityStore\Controller\SinglePage\Dashboard;

use Concrete\Core\Page\Controller\DashboardPageController;
use Package;
use Core;
use Config;
use Concrete\Package\CommunityStore\Src\CommunityStore\Order\OrderList;
use Concrete\Package\CommunityStore\Src\CommunityStore\Report\SalesReport;

class Store extends DashboardPageController
{
    public function view()
    {
        $sr = new SalesReport();
        $this->set('sr', $sr);
        $this->requireAsset('chartist');
        $this->requireAsset('css', 'communityStoreDashboard');
        $this->requireAsset('javascript', 'communityStoreFunctions');

        $orders = new OrderList();
        $orders->setItemsPerPage(10);

        $paginator = $orders->getPagination();
        $pagination = $paginator->renderDefaultView();
        $this->set('orders', $paginator->getCurrentPageResults());
        $this->set('pagination', $pagination);
        $this->set('paginator', $paginator);

        if ('all' == Config::get('community_store.shoppingDisabled')) {
            $this->set('shoppingDisabled', true);
        }
        $this->set('pageTitle', t('Store'));
    }
}
