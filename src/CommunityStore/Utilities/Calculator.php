<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Utilities;

use Concrete\Package\CommunityStore\Src\CommunityStore\Cart\Cart as StoreCart;
use Concrete\Package\CommunityStore\Src\CommunityStore\Tax\Tax as StoreTax;
use Concrete\Package\CommunityStore\Src\CommunityStore\Shipping\Method\ShippingMethod as StoreShippingMethod;
use Config;
use Session;

class Calculator
{
    public static function getSubTotal()
    {
        $cart = StoreCart::getCart();
        $subtotal = 0;
        if ($cart) {
            foreach ($cart as $cartItem) {
                $qty = $cartItem['product']['qty'];
                $product = $cartItem['product']['object'];

                if (is_object($product)) {
                    $productSubTotal = $product->getActivePrice() * $qty;
                    $subtotal = $subtotal + $productSubTotal;
                }
            }
        }

        return max($subtotal, 0);
    }
    public static function getShippingTotal($smID = null)
    {
        $cart = StoreCart::getCart();
        if (empty($cart)) {
            return false;
        }

        $existingShippingMethodID = Session::get('community_store.smID');
        if ($smID) {
            $shippingMethod = StoreShippingMethod::getByID($smID);
            Session::set('community_store.smID', $smID);
        } elseif ($existingShippingMethodID) {
            $shippingMethod = StoreShippingMethod::getByID($existingShippingMethodID);
        }

        if (is_object($shippingMethod) && $shippingMethod->getCurrentOffer()) {
            $shippingTotal = $shippingMethod->getCurrentOffer()->getRate();
        } else {
            $shippingTotal = 0;
        }

        return $shippingTotal;
    }
    public static function getTaxTotals()
    {
        return StoreTax::getTaxes();
    }

    public static function getGrandTotal()
    {
        $totals = self::getTotals();
        return $totals['total'];
    }

        // returns an array of formatted cart totals
    public static function getTotals()
    {
        $subTotal = self::getSubTotal();
        $originalSubTotal = $subTotal;

        $taxes = StoreTax::getTaxes();
        $shippingTotal = self::getShippingTotal();
        $discounts = StoreCart::getDiscounts();

        $addedTaxTotal = 0;
        $includedTaxTotal = 0;
        $addedShippingTaxTotal = 0;
        $includedShippingTaxTotal = 0;

        $taxCalc = Config::get('community_store.calculation');

        if ($taxes) {
            foreach ($taxes as $tax) {
                if ($taxCalc != 'extract') {
                    $addedTaxTotal += $tax['producttaxamount'];
                    $addedShippingTaxTotal += $tax['shippingtaxamount'];
                } else {
                    $includedTaxTotal += $tax['producttaxamount'];
                    $includedShippingTaxTotal += $tax['shippingtaxamount'];
                }
            }
        }

        $adjustedSubtotal = $subTotal;



        if (!empty($discounts)) {
            foreach ($discounts as $discount) {
                if ($discount->getDeductFrom() == 'subtotal') {
                    if ($discount->getDeductType() == 'value') {
                        $adjustedSubtotal -= $discount->getValue();
                    }

                    if ($discount->getDeductType() == 'percentage') {
                        $adjustedSubtotal -= ($discount->getPercentage() / 100 * $adjustedSubtotal);
                    }
                }
            }



            $discountRatio = $adjustedSubtotal / $originalSubTotal;


            $addedTaxTotal  = $discountRatio * $addedTaxTotal;
            $includedTaxTotal  = $discountRatio * $includedTaxTotal;

        }

        $total = ($adjustedSubtotal + $addedTaxTotal  + $addedShippingTaxTotal + $shippingTotal);


//        foreach ($discounts as $discount) {
//            if ($discount->getDeductFrom() == 'total') {
//                if ($discount->getDeductType()  == 'value') {
//                    $total -= $discount->getValue();
//                }
//
//                if ($discount->getDeductType()  == 'percentage') {
//                    $total -= ($discount->getPercentage() / 100 * $total);
//                }
//            }
//        }


        return array('subTotal' => $subTotal, 'taxes' => $taxes, 'taxTotal' => $addedTaxTotal + $includedTaxTotal, 'shippingTotal' => $shippingTotal, 'total' => $total);
    }
}
