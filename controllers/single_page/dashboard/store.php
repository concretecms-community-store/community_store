<?php
namespace Concrete\Package\CommunityStore\Controller\SinglePage\Dashboard;

use Concrete\Core\Page\Controller\DashboardPageController;
use Package;
use Core;
use Config;
use Concrete\Package\CommunityStore\Src\CommunityStore\Order\OrderList;
use Concrete\Package\CommunityStore\Src\CommunityStore\Report\SalesReport;
use Concrete\Core\Search\Pagination\PaginationFactory;

class Store extends DashboardPageController
{
    public function view()
    {
        $sr = new SalesReport();
        $this->set('sr', $sr);
        $pkg = Package::getByHandle('community_store');
        $packagePath = $pkg->getRelativePath();
        $this->requireAsset('chartist');
        $today = date('Y-m-d');
        $thirtyDaysAgo = date('Y-m-d', strtotime('-30 days'));
        $this->set('defaultFromDate', $thirtyDaysAgo);
        $this->set('defaultToDate', $today);

        $dateFrom = $this->post('dateFrom');
        $dateTo = $this->post('dateTo');
        if (!$dateFrom) {
            $dateFrom = $thirtyDaysAgo;
        }
        if (!$dateTo) {
            $dateTo = $today;
        }
        $this->set('dateFrom', $dateFrom);
        $this->set('dateTo', $dateTo);

        $ordersTotals = $sr::getTotalsByRange($dateFrom, $dateTo);
        $this->set('ordersTotals', $ordersTotals);

        $orderList = new OrderList();
        $orderList->setFromDate($dateFrom);
        $orderList->setToDate($dateTo);
        $orderList->setItemsPerPage(10);

        $factory = new PaginationFactory(\Request::getInstance());
        $paginator = $factory->createPaginationObject($orderList);

        $pagination = $paginator->renderDefaultView();
        $this->set('orders', $paginator->getCurrentPageResults());
        $this->set('pagination', $pagination);
        $this->set('paginator', $paginator);

        $this->addHeaderItem(Core::make('helper/html')->css($packagePath . '/css/communityStoreDashboard.css'));

        if ('all' == Config::get('community_store.shoppingDisabled')) {
            $this->set('shoppingDisabled', true);
        }
        $this->set('pageTitle', t('Store'));
    }
}
