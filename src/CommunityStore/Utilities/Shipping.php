<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Utilities;

use Concrete\Core\Controller\Controller;
use Concrete\Core\Support\Facade\Session;
use Concrete\Package\CommunityStore\Src\CommunityStore\Shipping\Method\ShippingMethod;
use Illuminate\Filesystem\Filesystem;
use Concrete\Core\View\View;
use Concrete\Package\CommunityStore\Src\CommunityStore\Cart\Cart;
use Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Price;
use Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Calculator;

class Shipping extends Controller
{
    public function getShippingMethods()
    {
        View::element("checkout/shipping_methods", null, "community_store");

        exit();
    }

    public function selectShipping()
    {
        $token = $this->app->make('token');

        if ($this->request->request->all() && $token->validate('community_store')) {
            $smID = $this->request->request->get('smID');
            $sInstructions = $this->request->request->get('sInstructions');

            // only allow shipping methods that are currently available to the customer
            if (!ShippingMethod::isEligibleMethodID($smID)) {
                Session::set('community_store.smID', false);
                echo 0;
                exit();
            }

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
