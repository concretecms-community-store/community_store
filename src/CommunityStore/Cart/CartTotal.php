<?php 
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Cart;

use \Concrete\Core\Controller\Controller as RouteController;

use \Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Price as StorePrice;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Cart\Cart as StoreCart;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Tax\Tax as StoreTax;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Calculator as StoreCalculator;

class CartTotal extends RouteController
{
        
    public function getSubTotal()
    {
        $totals = StoreCalculator::getTotals();
        $subtotal = $totals['subTotal'];
        echo StorePrice::format($subtotal);
    }
    public function getTotal()
    {
        $totals = StoreCalculator::getTotals();
        $total = $totals['total'];
        echo StorePrice::format($total);
    }
    public function getTaxTotal()
    {
        echo json_encode(StoreTax::getTaxes(true));
    }
    public function getShippingTotal()
    {
        $smID = $_POST['smID'];
        echo StorePrice::format(StoreCalculator::getShippingTotal($smID));
    }
    public function getTotalItems()
    {
        echo StoreCart::getTotalItemsInCart();
    }
    
}
