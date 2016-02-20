<?php

namespace Concrete\Package\CommunityStore\Controller\SinglePage\Dashboard\Store\Reports;

use \Concrete\Core\Page\Controller\DashboardPageController;

use \Concrete\Package\CommunityStore\Src\CommunityStore\Order\OrderList as StoreOrderList;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Report\ProductReport as StoreProductReport;
use \Concrete\Core\Search\Pagination\Pagination;

class Products extends DashboardPageController
{

    public function view()
    {
        $dateFrom = $this->post('dateFrom');
        $dateTo = $this->post('dateTo');
        
        if(!$dateFrom){
            $dateFrom = StoreOrderList::getDateOfFirstOrder();
        }
        if(!$dateTo){
            $dateTo = date('Y-m-d');
        }
        $pr = new StoreProductReport($dateFrom,$dateTo);
        $orderBy = $this->post('orderBy');
        if(!$orderBy){
            $orderBy = 'quantity';
        }
        if($orderBy=='quantity'){
            $pr->sortByPopularity();
        } else {
            $pr->sortByTotal();
        }
        
        //$products = $pr->getProducts();

        $this->set('dateFrom',$dateFrom);
        $this->set('dateTo',$dateTo);
        
        $pr->setItemsPerPage(10);

        $paginator = $pr->getPagination();
        $pagination = $paginator->renderDefaultView();
        $this->set('products',$paginator->getCurrentPageResults());
        $this->set('pagination',$pagination);
        $this->set('paginator', $paginator);
    }
    
}
