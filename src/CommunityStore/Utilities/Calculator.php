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
    public static function getShippingTotal($smID = null, $smData = null)
    {
        $cart = StoreCart::getCart();
        if (empty($cart)) {
            return false;
        }

        $existingShippingMethodID = Session::get('smID');
        if ($smID) {
            $shippingMethod = StoreShippingMethod::getByID($smID);
            Session::set('smID', $smID);
            if ($smData) {
                Session::set('smData', $smData);
            }
        } elseif ($existingShippingMethodID) {
            $shippingMethod = StoreShippingMethod::getByID($existingShippingMethodID);
        }

        if (is_object($shippingMethod)) {
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
    public static function getDiscountTotals()
    {
        //should return 3 totals: subtotal, shipping, grand total
    }
    public static function getGrandTotal()
    {
        $subTotal = self::getSubTotal();
        $taxTotal = 0;
        $taxes = self::getTaxTotals();
        $taxCalc = Config::get('community_store.calculation');
        if ($taxes && $taxCalc != 'extract') {
            foreach ($taxes as $tax) {
                $taxTotal += $tax['taxamount'];
            }
        }
        $shippingTotal = self::getShippingTotal();
        $grandTotal = ($subTotal + $taxTotal + $shippingTotal);

        $discounts = StoreCart::getDiscounts();
        foreach ($discounts as $discount) {
            if ($discount->getDeductFrom() == 'total') {
                if ($discount->getDeductType()  == 'value') {
                    $grandTotal -= $discount->getValue();
                }

                if ($discount->getDeductType()  == 'percentage') {
                    $grandTotal -= ($discount->getPercentage() / 100 * $grandTotal);
                }
            }
        }

        return $grandTotal;
    }

    // returns an array of formatted cart totals
    public static function getTotals()
    {
        $subTotal = self::getSubTotal();
        $taxes = StoreTax::getTaxes();
        $addedTaxTotal = 0;
        $includedTaxTotal = 0;
        $taxCalc = Config::get('community_store.calculation');

        if ($taxes) {
            foreach ($taxes as $tax) {
                if ($taxCalc != 'extract') {
                    $addedTaxTotal += $tax['taxamount'];
                } else {
                    $includedTaxTotal += $tax['taxamount'];
                }
            }
        }

        $shippingTotal = self::getShippingTotal();
        $discountedSubtotal = $subTotal;
        $discounts = StoreCart::getDiscounts();
        foreach ($discounts as $discount) {
            if ($discount->getDeductFrom() == 'subtotal') {
                if ($discount->getDeductType()  == 'value') {
                    $discountedSubtotal -= $discount->getValue();
                }

                if ($discount->getDeductType()  == 'percentage') {
                    $discountedSubtotal -= ($discount->getPercentage() / 100 * $discountedSubtotal);
                }
            }
        }

        $total = ($discountedSubtotal + $addedTaxTotal + $shippingTotal);

        foreach ($discounts as $discount) {
            if ($discount->getDeductFrom() == 'total') {
                if ($discount->getDeductType()  == 'value') {
                    $total -= $discount->getValue();
                }

                if ($discount->getDeductType()  == 'percentage') {
                    $total -= ($discount->getPercentage() / 100 * $total);
                }
            }
        }

        return array('subTotal' => $subTotal, 'taxes' => $taxes, 'taxTotal' => $addedTaxTotal + $includedTaxTotal, 'shippingTotal' => $shippingTotal, 'total' => $total);
    }
}
