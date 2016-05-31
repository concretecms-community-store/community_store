<?php
namespace Concrete\Package\CommunityStore\Controller\SinglePage\Checkout;

use PageController;
use View;


use \Concrete\Package\CommunityStore\Src\CommunityStore\Order\Order as StoreOrder;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Customer\Customer as StoreCustomer;

defined('C5_EXECUTE') or die(_("Access Denied."));
class Complete extends PageController
{
    public function view()
    {
        $customer = new StoreCustomer();
        $lastorderid = $customer->getLastOrderID();

        if ($lastorderid) {
            $order = StoreOrder::getByID($customer->getLastOrderID());
        }

        if(is_object($order)){
            $this->set("order",$order);
        } else {
            $this->redirect("/cart");
        }
        $this->requireAsset('javascript', 'jquery');
        $js = \Concrete\Package\CommunityStore\Controller::returnHeaderJS();
        $this->addFooterItem($js);
        $this->requireAsset('javascript', 'community-store');
        $this->requireAsset('css', 'community-store');
    }
    

}
