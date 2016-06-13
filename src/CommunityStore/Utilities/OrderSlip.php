<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Utilities;

use Controller;
use User;
use View;
use Illuminate\Filesystem\Filesystem;
use Concrete\Package\CommunityStore\Src\CommunityStore\Order\Order as StoreOrder;
use Concrete\Package\CommunityStore\Src\Attribute\Key\StoreOrderKey as StoreOrderKey;

class OrderSlip extends Controller
{
    public function renderOrderPrintSlip()
    {
        $o = StoreOrder::getByID($this->post('oID'));
        $orderChoicesAttList = StoreOrderKey::getAttributeListBySet('order_choices', User::getByUserID($order->getCustomerID()));
        $orderChoicesEnabled = count($orderChoicesAttList)? true : false;
        
        if (Filesystem::exists(DIR_BASE."/application/elements/order_slip.php")) {
            View::element("order_slip", array('order' => $o, 'orderChoicesEnabled' => $orderChoicesEnabled, 'orderChoicesAttList' => $orderChoicesAttList));
        } else {
            View::element("order_slip", array('order' => $o, 'orderChoicesEnabled' => $orderChoicesEnabled, 'orderChoicesAttList' => $orderChoicesAttList), "community_store");
        }
    }
}
