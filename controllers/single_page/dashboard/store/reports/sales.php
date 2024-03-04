<?php
namespace Concrete\Package\CommunityStore\Controller\SinglePage\Dashboard\Store\Reports;

use Concrete\Core\Http\Request;
use Concrete\Core\Routing\Redirect;
use Concrete\Core\Search\Pagination\PaginationFactory;
use Concrete\Core\Page\Controller\DashboardPageController;
use Concrete\Core\User\User;
use Concrete\Package\CommunityStore\Entity\Attribute\Key\StoreOrderKey;
use Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Price;
use Concrete\Package\CommunityStore\Src\CommunityStore\Order\OrderList;
use Concrete\Package\CommunityStore\Src\CommunityStore\Report\SalesReport;
use Concrete\Package\CommunityStore\Src\CommunityStore\Report\CsvReportExporter;

class Sales extends DashboardPageController
{
    public function view()
    {
        $sr = new SalesReport();
        $this->set('sr', $sr);
        $this->requireAsset('chartist');
        $today = date('Y-m-d');
        $thirtyDaysAgo = date('Y-m-d', strtotime('-30 days'));
        $this->set('defaultFromDate', $thirtyDaysAgo);
        $this->set('defaultToDate', $today);

        $dateFrom = $this->request->get('dateFrom');
        $dateTo = $this->request->get('dateTo');
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
        $orderList->setItemsPerPage(20);
        $orderList->setCancelled(false);

        $factory = new PaginationFactory($this->app->make(Request::class));
        $paginator = $factory->createPaginationObject($orderList);

        $pagination = $paginator->renderDefaultView();
        $this->set('orders', $paginator->getCurrentPageResults());
        $this->set('pagination', $pagination);
        $this->set('paginator', $paginator);

        $this->requireAsset('css', 'communityStoreDashboard');
        $this->set('pageTitle', tc(/* i18n: sale here means the act of selling */ 'Selling', 'Sales Report'));
    }

    public function export()
    {
        $from = $this->request->query->get('fromDate');
        $to = $this->request->query->get('toDate');

        //TODO maybe get all existing orders if needed
        // set to and from
        if ('' == $to || null == $to) {
            $to = date('Y-m-d'); // set to today
        }
        if ('' == $from || null == $from) {
            $from = strtotime('-7 day', $to); // set from a week ago
        }

        // get orders and set the from and to
        $orders = new OrderList();
        $orders->setFromDate($from);
        $orders->setToDate($to);
        $orders->setCancelled(false);

        // exporting
        $export = [];
        // get all order requests
        $orders = $orders->getResults();
        $user = $this->app->make(User::class);

        foreach ($orders as $o) {
            // get tax info for our export data
            $tax = $o->getTaxTotal();
            $includedTax = $o->getIncludedTaxTotal();
            $orderTax = 0;
            if ($tax) {
                $orderTax = Price::formatFloat($tax);
            } elseif ($includedTax) {
                $orderTax = Price::formatFloat($includedTax);
            }
            // getOrderDate returns DateTime need to format it as string
            $date = $o->getOrderDate();
            $paidDate = $o->getPaid();
            $refundedDate = $o->getRefunded();
            // set up our export array

            $last = $o->getAttribute('billing_last_name');
            $first = $o->getAttribute('billing_first_name');

            $fullName = implode(', ', array_filter([$last, $first]));
            if (strlen($fullName) > 0) {
                $customerName = $fullName;
            } else {
                $customerName = '-';
            }

            $row = [
                'Order #' => $o->getOrderID(),
                'Date' => $date->format('Y-m-d H:i:s'),
                'Paid' => $paidDate ? $paidDate->format('Y-m-d H:i:s') : '',
                'Refunded' => $refundedDate ? $refundedDate->format('Y-m-d H:i:s') : '',
                'Products' => $o->getSubTotal(),
                'Shipping' => $o->getShippingTotal(),
                'Tax' => $orderTax,
                'Total' => $o->getTotal(),
                'Payment Method' => $o->getPaymentMethodName(),
                'Transaction Reference' => $o->getTransactionReference(),
                'Customer Name' => $customerName,
                'Customer Email' => $o->getAttribute('email'),
            ];

            $orderChoicesAttList = StoreOrderKey::getAttributeListBySet('order_choices', $user);
            foreach ($orderChoicesAttList as $item) {
                $row[$item->getAttributeKeyDisplayName()] = $o->getAttribute($item->getAttributeKeyHandle());
            }
            $export[] = $row;
        }

        // if we have something to export
        if (count($export) > 0) {
            $filename = 'sale_report_' . date('Y-m-d') . ".csv";

            $this->app->make(
                CsvReportExporter::class,
                [
                    'filename' => $filename,
                    'header' => array_keys(reset($export)),
                    'rows' => $export
                ]
            )->getCsv();
        }
        // redirect if no data to output
        return Redirect::to('/dashboard/store/reports/sales');
    }
}
