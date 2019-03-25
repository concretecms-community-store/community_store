<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Utilities;

use Concrete\Core\Controller\Controller;
use Illuminate\Filesystem\Filesystem;
use Concrete\Core\View\View;
use Concrete\Package\CommunityStore\Src\CommunityStore\Cart\Cart as StoreCart;
use Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Calculator as StoreCalculator;
use Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Price as StorePrice;

class Shipping extends Controller
{
    public function getShippingMethods()
    {
        if (Filesystem::exists(DIR_BASE . "/application/elements/checkout/shipping_methods.php")) {
            View::element("checkout/shipping_methods");
        } else {
            View::element("checkout/shipping_methods", null, "community_store");
        }

        exit();
    }

    public function selectShipping()
    {
        $token = $this->app->make('token');

        if ($this->request->request->all() && $token->validate('community_store')) {
            $smID = $this->request->request->get('smID');
            $sInstructions = $this->request->request->get('sInstructions');

            StoreCart::setShippingInstructions($sInstructions);

            $total = StoreCalculator::getShippingTotal($smID);
            if ($total > 0) {
                echo StorePrice::format($total);
            } else {
                echo 0;
            }
        }
        exit();
    }
}
