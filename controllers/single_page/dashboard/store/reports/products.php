<?php
namespace Concrete\Package\CommunityStore\Controller\SinglePage\Dashboard\Store\Reports;

use Concrete\Core\Http\Request;
use Concrete\Core\Search\Pagination\PaginationFactory;
use Concrete\Core\Page\Controller\DashboardPageController;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product;
use Concrete\Package\CommunityStore\Src\CommunityStore\Order\OrderItem;
use Concrete\Package\CommunityStore\Src\CommunityStore\Order\OrderList;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductList;
use Concrete\Package\CommunityStore\Src\CommunityStore\Report\ProductReport;
use Concrete\Package\CommunityStore\Src\CommunityStore\Report\CsvReportExporter;

class Products extends DashboardPageController
{
    public function view()
    {
        $dateFrom = $this->request->query->get('dateFrom');
        $dateTo = $this->request->query->get('dateTo');

        if (!$dateFrom) {
            $dateFrom = OrderList::getDateOfFirstOrder();
        }
        if (!$dateTo) {
            $dateTo = date('Y-m-d');
        }
        $pr = new ProductReport($dateFrom, $dateTo);

        $productSearch = $this->request->query->get('productSearch');

        if ($productSearch) {
            $pr->setProductSearch($productSearch);
        }

        $orderBy = $this->request->query->get('orderBy');
        if (!$orderBy) {
            $orderBy = 'quantity';
        }
        if ('quantity' == $orderBy) {
            $pr->sortByPopularity();
        } else {
            $pr->sortByTotal();
        }

        $this->set('dateFrom', $dateFrom);
        $this->set('dateTo', $dateTo);
        $this->set('productSearch', $productSearch);

        $pr->setItemsPerPage(20);

        $factory = new PaginationFactory($this->app->make(Request::class));
        $paginator = $factory->createPaginationObject($pr);

        $pagination = $paginator->renderDefaultView();
        $this->set('products', $paginator->getCurrentPageResults());
        $this->set('pagination', $pagination);
        $this->set('paginator', $paginator);
        $this->set('pageTitle', t('Products Report'));
    }

    public function detail($productid = null, $export = false)
    {
        $header = [];

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
            $result = $db->query($sql, [$productid]);

            $orderItems = [];

            while ($row = $result->fetch()) {
                $orderItems[] = OrderItem::getByID($row['oiID']);
            }

            $product = Product::getByID($productid);

            $this->set('orderItems', $orderItems);
            $this->set('product', $product);
            $this->set('pageTitle', t('Orders of %s', $product->getName()));

            if ($export) {
                $outputItems = [];

                foreach ($orderItems as $item) {
                    $order = $item->getOrder();

                    $outputItem = [];
                    $outputItem[] = $order->getOrderID();
                    $outputItem[] = $order->getAttribute("billing_last_name");
                    $outputItem[] = $order->getAttribute("billing_first_name");
                    $outputItem[] = $order->getAttribute("email");
                    $outputItem[] = $order->getAttribute("billing_phone");

                    $productName = $item->getProductName();

                    if ($sku = $item->getSKU()) {
                        $productName .= ' (' . $sku . ')';
                    }

                    $outputItem[] = $productName;
                    $outputItem[] = $item->getQuantity();

                    $options = $item->getProductOptions();
                    $optionStrings = [];
                    if ($options) {
                        foreach ($options as $option) {
                            $optionStrings[] = $option['oioKey'] . ": " . $option['oioValue'];
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
                            $paidstatus = t('Incomplete');
                        }
                    } else {
                        $paidstatus = t('Free Order');
                    }
                    $outputItem[] = $paidstatus;
                    $outputItem[] = $order->getPaymentMethodName();
                    $outputItem[] = $item->getPricePaid() * $item->getQuantity();

                    $outputItems[] = $outputItem;
                }

                $this->app->make(
                    CsvReportExporter::class,
                    [
                        'filename' => t('product_orders') . '_' . $product->getID(),
                        'header' => $header,
                        'rows' => $outputItems
                    ]
                )->getCsv();
            }
        }
    }

    public function export($productid)
    {
        if ($productid) {
            $this->detail($productid, true);
        }
    }

    public function sheet() {
        $productsList = new ProductList();
        $productsList->setItemsPerPage(20);
        $productsList->setActiveOnly(false);
        $productsList->setShowOutOfStock(true);
        $productsList->setSortBy('alpha');
        $productsList->setSortByDirection('asc');

        $allproducts = $productsList->getResults();

        $this->set('products', $allproducts);
        $this->set('pageTitle', 'Product Price/Shipping Sheet');
        $this->render('/dashboard/store/reports/products/sheet');
    }

}
