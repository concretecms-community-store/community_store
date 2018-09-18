<?php

namespace Concrete\Package\CommunityStore\Controller\SinglePage\Dashboard\Store\Reports;

use \Concrete\Core\Page\Controller\DashboardPageController;

use \Concrete\Package\CommunityStore\Src\CommunityStore\Order\OrderList as StoreOrderList;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Order\OrderItem as StoreOrderItem;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product as StoreProduct;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Report\ProductReport as StoreProductReport;
use \Concrete\Core\Search\Pagination\Pagination;

class Products extends DashboardPageController
{

    public function view()
    {
        $dateFrom = $this->get('dateFrom');
        $dateTo = $this->get('dateTo');
        
        if(!$dateFrom){
            $dateFrom = StoreOrderList::getDateOfFirstOrder();
        }
        if(!$dateTo){
            $dateTo = date('Y-m-d');
        }
        $pr = new StoreProductReport($dateFrom,$dateTo);
        $orderBy = $this->get('orderBy');
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
        
        $pr->setItemsPerPage(20);

        $paginator = $pr->getPagination();
        $pagination = $paginator->renderDefaultView();
        $this->set('products',$paginator->getCurrentPageResults());
        $this->set('pagination',$pagination);
        $this->set('paginator', $paginator);
    }

    public function detail($productid = null, $export = false) {

        $header = array();

        $header[] = t("Order #");
        $header[] = t("Last Name");
        $header[] = t("First Name");
        $header[] = t("Email");
        $header[] = t("Phone");
        $header[] = t("Product");
        $header[] = t("Quantity");
        $header[] = t("Options");
        $header[] = t("Order Date");
        $header[] = t("Order Status");
        $header[] = t("Payment");
        $header[] = t("Method");
        $header[] = t("Amount");

        $this->set('reportHeader', $header);


        if ($productid) {
            $db = $this->app->make('database')->connection();

            $sql = 'SELECT csoi.oiID from CommunityStoreOrderItems csoi, CommunityStoreOrders cso
                    WHERE cso.oID = csoi.oID AND csoi.pID = ? AND cso.externalPaymentRequested is null AND cso.oCancelled IS NULL
                    AND cso.oRefunded IS NULL
                    ORDER BY cso.oDate DESC';
            $result = $db->query($sql, array($productid));

            $orderItems = array();

            while($row = $result->fetchRow()) {
                $orderItems[] = StoreOrderItem::getByID($row['oiID']);
            }

            $product = StoreProduct::getByID($productid);

            $this->set('orderItems', $orderItems);
            $this->set('product', $product);
            $this->set('pageTitle',t('Orders of %s', $product->getName()) );

            if ($export) {
                header('Content-type: text/csv');
                header('Content-Disposition: attachment; filename="' . t(/*i18n file name for product customer exports*/'product_orders') . '_' . $product->getID() . '.csv"');

                $fp = fopen('php://output', 'w');
                fputcsv($fp, $header);

                foreach($orderItems as $item) {

                    $order = $item->getOrder();

                    $outputItem = array();
                    $outputItem[] = $order->getOrderID();
                    $outputItem[] = $order->getAttribute("billing_last_name");
                    $outputItem[] = $order->getAttribute("billing_first_name");
                    $outputItem[] = $order->getAttribute("email");
                    $outputItem[] = $order->getAttribute("billing_phone");

                    $productName = $item->getProductName();

                    if ($sku = $item->getSKU()) {
                        $productName .=  ' (' .  $sku . ')';
                    }

                    $outputItem[] = $productName;
                    $outputItem[] = $item->getQty();

                    $options = $item->getProductOptions();
                    $optionStrings = array();
                    if($options){
                        foreach($options as $option){
                            $optionStrings[] =  $option['oioKey'].": " . $option['oioValue'];
                        }
                    }
                    $outputItem[] = implode(', ', $optionStrings);
                    $outputItem[] = $order->getOrderDate()->format('c');
                    $outputItem[] = $order->getStatus();

                    $paidstatus = '';

                    $paid = $order->getPaid();

                    if ($paid) {
                        $paidstatus = t('Paid');
                    } elseif ($order->getTotal() > 0) {
                        $paidstatus = t('Unpaid');

                        if ($order->getExternalPaymentRequested()) {
                            $paidstatus = t('Incomplete') ;
                        }
                    } else {
                        $paidstatus = t('Free Order');
                    }
                    $outputItem[] = $paidstatus;
                    $outputItem[] = $order->getPaymentMethodName();
                    $outputItem[] = $item->getPricePaid() * $item->getQty();

                    fputcsv($fp, $outputItem);
                }

                fclose($fp);
                exit();
            }
        }

    }

    public function export($productid) {
        if ($productid) {
            $this->detail($productid, true);
        }
    }

    
}
