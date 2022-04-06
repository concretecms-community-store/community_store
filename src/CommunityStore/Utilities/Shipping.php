<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Utilities;

use Concrete\Core\Controller\Controller;
use Illuminate\Filesystem\Filesystem;
use Concrete\Core\View\View;
use Concrete\Package\CommunityStore\Src\CommunityStore\Cart\Cart;
use Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Price;
use Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Calculator;

class Shipping extends Controller
{
    public function getShippingMethods()
    {
        if (file_exists(DIR_BASE . "/application/elements/checkout/shipping_methods.php")) {
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

            Cart::setShippingInstructions($sInstructions);

            $total = Calculator::getShippingTotal($smID);
            if ($total > 0) {
                echo Price::format($total);
            } else {
                echo 0;
            }
        }
        exit();
    }
}
