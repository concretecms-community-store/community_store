<?php 
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Report;

use Concrete\Core\Search\ItemList\ItemList as AbstractItemList;
use Concrete\Core\Search\Pagination\Pagination;
use Pagerfanta\Adapter\ArrayAdapter;

use \Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product as StoreProduct;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Order\OrderList as StoreOrderList;

class ProductReport extends AbstractItemList
{
    private $orderItems;
    private $products;
    
    public function __construct($from=null,$to=null)
    {
        $this->setOrderItemsByRange($from,$to);
        $this->setProducts();
    }
    
    public function setOrderItemsByRange($from=null,$to=null)
    {
        if(!isset($from)){
            $from = StoreOrderList::getDateOfFirstOrder();
        }
        if(!$to){
            $to = date('Y-m-d');
        }
        $orders = new StoreOrderList();
        $orders->setFromDate($from);
        $orders->setToDate($to);
        $this->orderItems = $orders->getOrderItems();
    }
    
    public function setProducts()
    {
        $products = array();
        foreach($this->orderItems as $oi){
                if (array_key_exists($oi->getProductID(), $products)) {
                    $products[$oi->getProductID()]['pricePaid'] = intval($products[$oi->getProductID()]['pricePaid']) + intval($oi->getPricePaid());
                    $products[$oi->getProductID()]['quantity'] = intval($products[$oi->getProductID()]['quantity']) + intval($oi->getQty());
                } else {
                    //first figure out what the current product name is.
                    //if the product no longer exist, the OI name is fine.

                    $pID = $oi->getProductID();

                    if ($pID) {
                        $product = StoreProduct::getByID();
                        if (is_object($product)) {
                            $name = $product->getProductName();
                        } else {
                            $name = $oi->getProductName();
                        }
                        $products[$oi->getProductID()] = array(
                            'name' => $name,
                            'pID' => $oi->getProductID(),
                            'pricePaid' => intval($oi->getPricePaid()) * intval($oi->getQty()),
                            'quantity' => intval($oi->getQty())
                        );
                    }
                }
        }
        $this->products = $products;
        
    }
    public function sortByPopularity($direction = 'desc')
    {
        $products = $this->products;
        usort($products, create_function('$a, $b', '
	        $a = $a["quantity"];
	        $b = $b["quantity"];
	
	        if ($a == $b)
	        {
	            return 0;
	        }
	
	        return ($a ' . ($direction == 'desc' ? '>' : '<') .' $b) ? -1 : 1;
	    '));
        $this->products = $products;
    }
    public function sortByTotal($direction = 'desc')
    {
        $products = $this->products;
        usort($products, create_function('$a, $b', '
	        $a = $a["pricePaid"];
	        $b = $b["pricePaid"];
	
	        if ($a == $b)
	        {
	            return 0;
	        }
	
	        return ($a ' . ($direction == 'desc' ? '>' : '<') .' $b) ? -1 : 1;
	    '));
        $this->products = $products;
    }
    
    public function getOrderItems(){ return $this->orderItems; }
    public function getProducts(){ return $this->products; }
    
    protected function executeSortBy($column, $direction = 'asc')
    {
        $this->query->orderBy($column, $direction);
    }
    public function executeGetResults()
    {
        //return $this->deliverQueryObject()->execute()->fetchAll();
    }
    public function debugStart(){}

    public function debugStop(){}
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
