<?php

namespace Concrete\Package\CommunityStore\Controller\SinglePage\Dashboard\Store\Reports;

use \Concrete\Core\Page\Controller\DashboardPageController;
use Package;

use \Concrete\Package\CommunityStore\Src\CommunityStore\Order\OrderList as StoreOrderList;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Report\SalesReport as StoreSalesReport;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Price;

class Sales extends DashboardPageController
{

    public function view()
    {
        $sr = new StoreSalesReport();
        $this->set('sr',$sr);
        $this->requireAsset('chartist');
        $today = date('Y-m-d');
        $thirtyDaysAgo = date('Y-m-d', strtotime('-30 days'));
        $this->set('defaultFromDate',$thirtyDaysAgo);
        $this->set('defaultToDate',$today);
        
        $dateFrom = $this->post('dateFrom');
        $dateTo = $this->post('dateTo');
        if(!$dateFrom){ $dateFrom = $thirtyDaysAgo; }
        if(!$dateTo){ $dateTo = $today; }
        $this->set('dateFrom',$dateFrom);
        $this->set('dateTo',$dateTo);
        
        $ordersTotals = $sr::getTotalsByRange($dateFrom,$dateTo);
        $this->set('ordersTotals',$ordersTotals);
        
        $orders = new StoreOrderList();
        $orders->setFromDate($dateFrom);
        $orders->setToDate($dateTo);
        $orders->setItemsPerPage(10);
        $orders->setPaid(true);
        $orders->setCancelled(false);

        $paginator = $orders->getPagination();
        $pagination = $paginator->renderDefaultView();
        $this->set('orders',$paginator->getCurrentPageResults());
        $this->set('pagination',$pagination);
        $this->set('paginator', $paginator);

        $this->requireAsset('css', 'communityStoreDashboard');
     
    }

    public function export()
    {
        $from = $this->get('fromDate');
        $to = $this->get('toDate');

        //TODO maybe get all existing orders if needed
        // set to and from 
        if($to == '' || $to == null)
        {
            $to = date('Y-m-d'); // set to today
        }
        if($from == '' || $from == null)
        {
            $from = strtotime('-7 day',$to); // set from a week ago
        }

        // get orders and set the from and to
        $orders = new StoreOrderList();
        $orders->setFromDate($from);
        $orders->setToDate($to);
        //$orders->setItemsPerPage(10);
        $orders->setPaid(true);
        $orders->setCancelled(false);

        // exporting 
        $export = array();
        // get all order requests
        $orders = $orders->getResults();
        
        foreach($orders as $o)
        {
            // get tax info for our export data
            $tax = $o->getTaxTotal();
            $includedTax = $o->getIncludedTaxTotal();
            if ($tax) {
                $orderTax = Price::format($tax);
            } elseif ($includedTax) {
                $orderTax = Price::format($includedTax);
            }
            // getOrderDate returns DateTime need to format it as string
            $date = $o->getOrderDate();
            // set up our export array
            $export[] = array(
                'Order #'=>$o->getOrderID(),
                'Date'=>$date->format('Y-m-d H:i:s'),
                'Products'=>$o->getSubTotal(),
                'Shipping'=>$o->getShippingTotal(),
                'Tax'=>$orderTax,
                'Total'=>$o->getTotal()
            );
        }

        // if we have something to export
        if(count($export) > 0)
        {
            // timings for cache disabling in headers and the file name
            $now = gmdate("D, d M Y H:i:s");
            $expire = gmdate("D, d M Y H:i:s",strtotime("+1 day"));
            $filename = 'sale_report_'.date('Y-m-d').".csv";
            
            // disable caching
            header("Expires: {$expire} GMT");
            header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
            header("Last-Modified: {$now} GMT");

            // force download  
            header("Content-Type: application/force-download");
            header("Content-Type: application/octet-stream");
            header("Content-Type: application/download");

            // disposition / encoding on response body
            header("Content-Disposition: attachment;filename={$filename}");
            header("Content-Transfer-Encoding: binary");

            // start our output buffer and write to output
            ob_start();
            $df = fopen('php://output','w');
            // output our keys
            fputcsv($df, array_keys(reset($export)));
            // output our data
            foreach($export as $row)
            {
                fputcsv($df,$row);
            }
            fclose($df);
            // finally output our data
            echo ob_get_clean();
            die();
        }
        // redirect if no data to output
        $this->redirect('/dashboard/store/reports/sales');
    }
    
}
