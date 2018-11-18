<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Cart;

use Concrete\Core\Controller\Controller as RouteController;
use View;
use Illuminate\Filesystem\Filesystem;
use Concrete\Package\CommunityStore\Src\CommunityStore\Cart\Cart as StoreCart;
use Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Calculator as StoreCalculator;

class CartModal extends RouteController
{
    public function getCartModal()
    {
        $cart = StoreCart::getCart();
        $discounts = StoreCart::getDiscounts();
        $totals = StoreCalculator::getTotals();

        $total = $totals['subTotal'];

        $app = \Concrete\Core\Support\Facade\Application::getFacadeApplication();
        $token =  $app->make('token');

        if (Filesystem::exists(DIR_BASE . '/application/elements/cart_modal.php')) {
            View::element('cart_modal', ['cart' => $cart, 'total' => $total, 'discounts' => $discounts, 'actiondata' => $this->post(), 'token'=>$token]);
        } else {
            View::element('cart_modal', ['cart' => $cart, 'total' => $total, 'discounts' => $discounts, 'actiondata' => $this->post(), 'token'=>$token], 'community_store');
        }
    }
}
