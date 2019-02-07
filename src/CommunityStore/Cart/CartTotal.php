<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Cart;

use Concrete\Core\Controller\Controller as RouteController;
use Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Price as StorePrice;
use Concrete\Package\CommunityStore\Src\CommunityStore\Cart\Cart as StoreCart;
use Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Calculator as StoreCalculator;

class CartTotal extends RouteController
{
    public function getShippingTotal()
    {
        $token = $this->app->make('token');

        if (!empty($_POST) && $token->validate('community_store')) {
            $smID = $_POST['smID'];
            $sInstructions = $_POST['sInstructions'];

            StoreCart::setShippingInstructions($sInstructions);

            $total = StoreCalculator::getShippingTotal($smID);
            if ($total > 0) {
                echo StorePrice::format($total);
            } else {
                echo 0;
            }
        }
    }


}
