<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Report;

use Pagerfanta\Adapter\ArrayAdapter;
use Concrete\Core\Search\Pagination\Pagination;
use Concrete\Core\Search\ItemList\ItemList as AbstractItemList;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product;
use Concrete\Package\CommunityStore\Src\CommunityStore\Order\OrderList;

class ProductReport extends AbstractItemList
{
    private $orderItems;
    private $products;

    public function __construct($from = null, $to = null)
    {
        $this->setOrderItemsByRange($from, $to);
        $this->setProducts();
    }

    public function setOrderItemsByRange($from = null, $to = null)
    {
        if (!isset($from)) {
            $from = OrderList::getDateOfFirstOrder();
        }
        if (!$to) {
            $to = date('Y-m-d');
        }
        $orders = new OrderList();
        $orders->setFromDate($from);
        $orders->setToDate($to);
        $orders->setPaid(true);
        $orders->setCancelled(false);
        $orders->setRefunded(false);
        $this->orderItems = $orders->getOrderItems();
    }

    public function setProductSearch($search = '') {
        if ($search) {

            $newlist = [];

            foreach ($this->products as $product) {

                if (strpos($product['name'], $search) !== false) {
                    $newlist[] = $product;
                }
            }

            $this->products = $newlist;
        }
    }

    public function setProducts()
    {
        $products = [];
        foreach ($this->orderItems as $oi) {
            if (array_key_exists($oi->getProductID(), $products)) {
                $products[$oi->getProductID()]['pricePaid'] = $products[$oi->getProductID()]['pricePaid'] + $oi->getPricePaid();
                $products[$oi->getProductID()]['quantity'] = $products[$oi->getProductID()]['quantity'] + $oi->getQty();
            } else {
                //first figure out what the current product name is.
                //if the product no longer exist, the OI name is fine.

                $pID = $oi->getProductID();

                if ($pID) {
                    $product = Product::getByID($pID);
                    if (is_object($product)) {
                        $name = $product->getName();
                    } else {
                        $name = $oi->getProductName();
                    }

                    $products[$oi->getProductID()] = [
                        'name' => $name,
                        'pID' => $oi->getProductID(),
                        'pricePaid' => $oi->getPricePaid() * $oi->getQty(),
                        'quantity' => $oi->getQty(),
                    ];

                }
            }
        }
        $this->products = $products;
    }

    public function sortByPopularity($direction = 'desc')
    {
        $products = $this->products;

        if ($direction == 'desc') {
            $func = function($a, $b) {
                $a = $a["quantity"];
                $b = $b["quantity"];

                if ($a == $b)
                {
                    return 0;
                }

                return ($a > $b) ? -1 : 1;
            };
        } else {
            $func = function($a, $b) {
                $a = $a["quantity"];
                $b = $b["quantity"];

                if ($a == $b)
                {
                    return 0;
                }

                return ($a < $b) ? -1 : 1;
            };
        }

        usort($products,$func);

        $this->products = $products;
    }

    public function sortByTotal($direction = 'desc')
    {
        $products = $this->products;

        if ($direction == 'desc') {
            $func = function($a, $b) {
                $a = $a["pricePaid"];
                $b = $b["pricePaid"];

                if ($a == $b)
                {
                    return 0;
                }

                return ($a > $b) ? -1 : 1;
            };
        } else {
            $func = function($a, $b) {
                $a = $a["pricePaid"];
                $b = $b["pricePaid"];

                if ($a == $b)
                {
                    return 0;
                }

                return ($a < $b) ? -1 : 1;
            };
        }

        usort($products,$func);

        $this->products = $products;
    }

    public function getOrderItems()
    {
        return $this->orderItems;
    }

    public function getProducts()
    {
        return $this->products;
    }

    protected function executeSortBy($column, $direction = 'asc')
    {
        $this->query->orderBy($column, $direction);
    }

    public function executeGetResults()
    {
        //return $this->deliverQueryObject()->execute()->fetchAll();
    }

    public function debugStart()
    {
    }

    public function debugStop()
    {
    }

    protected function createPaginationObject()
    {
        $pagination = new Pagination($this, new ArrayAdapter($this->getProducts()));

        return $pagination;
    }

    public function getTotalResults()
    {
        return count($this->getProducts());
    }

    public function getResult($queryRow)
    {
        return $queryRow;
    }
}
