<?php

namespace Concrete\Package\CommunityStore\Src\CommunityStore\Utilities;

use Concrete\Core\Support\Facade\Config;
use Concrete\Core\Support\Facade\Session;
use Concrete\Package\CommunityStore\Src\CommunityStore\Cart\Cart;
use Concrete\Package\CommunityStore\Src\CommunityStore\Shipping\Method\ShippingMethod;
use Concrete\Package\CommunityStore\Src\CommunityStore\Tax\Tax;

class Calculator
{
    public static function getCartItemPrice($cartItem)
    {
        $product = $cartItem['product']['object'];
        $qty = $cartItem['product']['qty'];
        if (isset($cartItem['product']['customerPrice']) && $cartItem['product']['customerPrice'] > 0) {
            $price = $cartItem['product']['customerPrice'];
        } elseif (isset($cartItem['product']['discountedPrice'])) {
            $price = $cartItem['product']['discountedPrice'];
        } else {
            $price = $product->getActivePrice($qty);
        }

        return $price;
    }

    public static function getSubTotal($cart = false)
    {
        if (!$cart) {
            $cart = Cart::getCart();
        }
        $subtotal = 0;
        if ($cart) {
            foreach ($cart as $cartItem) {
                $qty = $cartItem['product']['qty'];
                $product = $cartItem['product']['object'];

                if (is_object($product)) {
                    $price = self::getCartItemPrice($cartItem);

                    $productSubTotal = $price * $qty;
                    $subtotal = $subtotal + $productSubTotal;
                }
            }
        }

        return round(max($subtotal, 0), 2);
    }

    public static function getShippingTotal($smID = null)
    {
        $cart = Cart::getCart();
        if (empty($cart)) {
            return 0;
        }

        $existingShippingMethodID = Session::get('community_store.smID');
        if ($smID) {
            $shippingMethod = ShippingMethod::getByID($smID);
            Session::set('community_store.smID', $smID);
        } elseif ($existingShippingMethodID) {
            $shippingMethod = ShippingMethod::getByID($existingShippingMethodID);
        }

        if (isset($shippingMethod) && is_object($shippingMethod) && $shippingMethod->getCurrentOffer()) {
            $shippingTotal = $shippingMethod->getCurrentOffer()->getRate();
        } else {
            $shippingTotal = 0;
        }

        return round($shippingTotal, 2);
    }

    public static function getTaxTotals()
    {
        return Tax::getTaxes();
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

        $taxes = Tax::getTaxes();

        $shippingTotal = self::getShippingTotal();
        $discounts = Cart::getDiscounts();

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
        $adjustedShippingTotal = $shippingTotal;
        $discountRatio = 1;
        $discountShippingRatio = 1;

        $formattedtaxes = [];
        if (!empty($discounts)) {
            foreach ($discounts as $discount) {
                if ($discount->getDeductFrom() == 'subtotal') {
                    if ($discount->getDeductType() == 'value') {
                        $adjustedSubtotal -= $discount->getValue();
                    }
                } elseif ($discount->getDeductFrom() == 'shipping') {
                    if ($discount->getDeductType() == 'value' || $discount->getDeductType() == 'value_all') {
                        $adjustedShippingTotal -= $discount->getValue();
                    }

                    if ($discount->getDeductType() == 'percentage') {
                        $adjustedShippingTotal -= ($discount->getPercentage() / 100 * $adjustedShippingTotal);
                    }

                    if ($discount->getDeductType() == 'fixed') {
                        $adjustedShippingTotal = $discount->getValue();
                    }
                }
            }

            if ($subTotal > 0) {
                $discountRatio = $adjustedSubtotal / $subTotal;
            }

            if ($shippingTotal > 0) {
                $discountShippingRatio = $adjustedShippingTotal / $shippingTotal;
            }

            $addedTaxTotal = $discountRatio * $addedTaxTotal;
            $addedShippingTaxTotal = $discountShippingRatio * $addedShippingTaxTotal;

            $includedTaxTotal = $discountRatio * $includedTaxTotal;
            $includedShippingTaxTotal = $discountShippingRatio * $includedShippingTaxTotal;

            if ($discountRatio < 0) {
                $discountRatio = 0;
            }

            foreach ($taxes as $tax) {
                $tax['taxamount'] = round(($discountRatio * $tax['producttaxamount']) + ($discountShippingRatio * $tax['shippingtaxamount']), 2);
                $formattedtaxes[] = $tax;
            }

            $taxes = $formattedtaxes;
        }

        $adjustedSubtotal = max($adjustedSubtotal, 0);
        $adjustedShippingTotal = max($adjustedShippingTotal, 0);

        $addedTaxTotal = max($addedTaxTotal, 0);
        $addedShippingTaxTotal = max($addedShippingTaxTotal, 0);

        $includedTaxTotal = max($includedTaxTotal, 0);
        $includedShippingTaxTotal = max($includedShippingTaxTotal, 0);

        $total = $adjustedSubtotal + $adjustedShippingTotal + $addedTaxTotal + $addedShippingTaxTotal;
        $totalTax = $addedTaxTotal + $addedShippingTaxTotal + $includedTaxTotal + $includedShippingTaxTotal;

        $adjustedSubtotal = round($adjustedSubtotal, 2);
        $adjustedShippingTotal = round($adjustedShippingTotal, 2);
        $addedTaxTotal = round($addedTaxTotal, 2);
        $includedShippingTaxTotal = round($includedShippingTaxTotal, 2);
        $totalTax = round($totalTax, 2);
        $total = round($total, 2);

        return ['discountRatio' => $discountRatio, 'subTotal' => $adjustedSubtotal, 'taxes' => $taxes, 'taxTotal' => $totalTax, 'addedTaxTotal' => $addedTaxTotal + $addedShippingTaxTotal, 'includeTaxTotal' => $includedTaxTotal + $includedShippingTaxTotal, 'shippingTotal' => $adjustedShippingTotal, 'total' => $total];
    }
}
