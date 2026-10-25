<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Utilities;

use Concrete\Core\Controller\Controller;
use Concrete\Core\View\View;
use Concrete\Package\CommunityStore\Src\CommunityStore\Payment\Method as PaymentMethod;

class Payment extends Controller
{
    public function getPaymentMethods()
    {
        $totals = Calculator::getTotals();
        $availableMethods = PaymentMethod::getAvailableMethods((float)$totals['subTotal']);

        View::element("checkout/payment_methods",  ['enabledPaymentMethods'=>$availableMethods], "community_store");

        exit();
    }


}
