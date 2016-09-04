<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Cart;

use Concrete\Core\Controller\Controller as RouteController;
use Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Price as StorePrice;
use Concrete\Package\CommunityStore\Src\CommunityStore\Cart\Cart as StoreCart;
use Concrete\Package\CommunityStore\Src\CommunityStore\Tax\Tax as StoreTax;
use Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Calculator as StoreCalculator;

class CartTotal extends RouteController
{
    public function getShippingTotal()
    {
        $smID = $_POST['smID'];
        $sInstructions = $_POST['sInstructions'];

        StoreCart::setShippingInstructions($sInstructions);

        $total = StoreCalculator::getShippingTotal($smID);
        if ($total>0) {
            echo StorePrice::format($total);
        } else {
            echo 0;
        }
    }
    public function getCartSummary() {
        $totals = StoreCalculator::getTotals();
        $itemCount = StoreCart::getTotalItemsInCart();
        $total = $totals['total'];
        $subTotal = $totals['subTotal'];
        $shippingTotal = $totals['shippingTotal'];

        $taxes = $totals['taxes'];
        $formattedtaxes = array();

        foreach($taxes as $tax) {
            $tax['taxamount'] = StorePrice::format($tax['taxamount']);
            $formattedtaxes[] = $tax;
        }

        if (!\Session::get('community_store.smID')) {
            $shippingTotalRaw = false;
        } else {
            $shippingTotalRaw = $shippingTotal;
        }

        $data = array('subTotal'=> StorePrice::format($subTotal), 'total'=>StorePrice::format($total), 'itemCount'=>$itemCount, 'totalCents'=> $total * 100, 'taxes'=>$formattedtaxes, 'shippingTotalRaw'=> $shippingTotalRaw,'shippingTotal'=>StorePrice::format($shippingTotal));
        echo json_encode($data);
    }
}
