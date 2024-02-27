<?php
namespace Concrete\Package\CommunityStore\Controller\SinglePage\Checkout;

use Concrete\Core\Page\Page;
use Concrete\Core\Support\Facade\Config;
use Concrete\Core\User\User;
use Concrete\Core\Routing\Redirect;
use Concrete\Core\Support\Facade\Session;
use \Concrete\Core\User\UserInfoRepository;
use Concrete\Core\Page\Controller\PageController;
use Concrete\Package\CommunityStore\Src\CommunityStore\Cart\Cart;
use Concrete\Package\CommunityStore\Src\CommunityStore\Order\Order;
use Concrete\Package\CommunityStore\Entity\Attribute\Key\StoreOrderKey;
use Concrete\Package\CommunityStore\Src\CommunityStore\Customer\Customer;
use Concrete\Package\CommunityStore\Src\CommunityStore\Discount\DiscountCode;

class Complete extends PageController
{
    public function on_start()
    {
        $u = $this->app->make(User::class);
        $u->refreshUserGroups();
    }

    public function view()
    {
        // unset the shipping type, as next order might be unshippable
        Session::set('community_store.smID', '');

        $customer = new Customer();
        $lastorderid = $customer->getLastOrderID();
        $refreshCheck = false;

        $order = false;

        if ($lastorderid) {
            $order = Order::getByID($customer->getLastOrderID());
        }

        $user = $this->app->make(User::class);
        if (is_object($order)) {
            $this->set("order", $order);

            // if order has an associated user, and it's new, but not logged in, log them in now.
            if ($order->getCustomerID() && $order->getMemberCreated()) {
                $ui = $this->app->make(UserInfoRepository::class)->getByID($order->getCustomerID());

                if ($ui) {
                    if (!$user->isRegistered()) {
                        User::loginByUserID($ui->getUserID());
                        $user = $ui->getUserObject();
                    }
                }
            }

            if ($order->getPaid()) {
                $redirectDestination = $order->getOrderCompleteDestination();
                $c = Page::getCurrentPage();

                if ($c->getCollectionPath() != $redirectDestination) {
                    return Redirect::to($redirectDestination);
                }
            } else {
                if ($order->getExternalPaymentRequested()) {
                    // if it's not paid, but external payment was requested e.g. payment, trigger a check/refresh

                    if (Session::get('community_store.refreshCheck') != $order->getOrderID()) {
                        $refreshCheck = true;
                        Session::set('community_store.refreshCheck', $order->getOrderID());
                    }
                }
            }
        } else {
            if (!$user->isSuperUser()) {
                return Redirect::to("/cart");
            } else {
                $this->set('order', new Order());
            }
        }

        Cart::clear();
        DiscountCode::clearCartCode();

        $this->set('refreshCheck', $refreshCheck);

        $this->requireAsset('javascript', 'jquery');
        $js = \Concrete\Package\CommunityStore\Controller::returnHeaderJS();
        $this->addFooterItem($js);
        $this->requireAsset('javascript', 'community-store');
        $this->requireAsset('css', 'community-store');

        $orderChoicesAttList = StoreOrderKey::getAttributeListBySet('order_choices', $user);
        $this->set("orderChoicesEnabled", count($orderChoicesAttList) ? true : false);
        if (is_array($orderChoicesAttList) && !empty($orderChoicesAttList)) {
            $this->set("orderChoicesAttList", $orderChoicesAttList);
        }

        $gtagEnabled = Config::get('community_store.enableGtagPurchase');
        $this->set('gtagEnabled', (bool)$gtagEnabled);
    }
}
