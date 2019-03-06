<?php
namespace Concrete\Package\CommunityStore\Controller\SinglePage\Checkout;

use Concrete\Core\Page\Controller\PageController;
use Concrete\Core\User\User;
use Concrete\Core\Routing\Redirect;
use Concrete\Core\Support\Facade\Session;
use Concrete\Package\CommunityStore\Src\CommunityStore\Cart\Cart as StoreCart;
use Concrete\Package\CommunityStore\Src\CommunityStore\Order\Order as StoreOrder;
use Concrete\Package\CommunityStore\Src\CommunityStore\Customer\Customer as StoreCustomer;
use Concrete\Package\CommunityStore\Src\CommunityStore\Discount\DiscountCode as StoreDiscountCode;

class Complete extends PageController
{
    public function on_start()
    {
        $u = new User();
        $u->refreshUserGroups();
    }

    public function view()
    {
        $customer = new StoreCustomer();
        $lastorderid = $customer->getLastOrderID();

        if ($lastorderid) {
            $order = StoreOrder::getByID($customer->getLastOrderID());
        }

        if (is_object($order)) {
            $this->set("order", $order);
        } else {
            return Redirect::to("/cart");
        }

        StoreCart::clear();
        StoreDiscountCode::clearCartCode();

        $this->requireAsset('javascript', 'jquery');
        $js = \Concrete\Package\CommunityStore\Controller::returnHeaderJS();
        $this->addFooterItem($js);
        $this->requireAsset('javascript', 'community-store');
        $this->requireAsset('css', 'community-store');

        // unset the shipping type, as next order might be unshippable
        Session::set('community_store.smID', '');
    }
}
