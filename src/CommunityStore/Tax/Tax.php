<?php

namespace Concrete\Package\CommunityStore\Src\CommunityStore\Tax;

use Concrete\Core\Support\Facade\DatabaseORM as dbORM;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product;
use Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Price;
use Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Wholesale;

class Tax
{
    public static function getTaxRates($showall = false)
    {
        if (!$showall) {
            if (Wholesale::isUserWholesale()) {
                return $taxRates = [];
            }
        }

        $em = dbORM::entityManager();

        return $em->createQuery('select tr from \Concrete\Package\CommunityStore\Src\CommunityStore\Tax\TaxRate tr')->getResult();
    }

    public static function getTaxes($format = false)
    {
        $taxRates = self::getTaxRates();
        $taxes = [];
        if (count($taxRates) > 0) {
            foreach ($taxRates as $taxRate) {
                if ($taxRate->isTaxable()) {
                    $taxAmounts = $taxRate->calculate();

                    $productTaxAmount = $taxAmounts['producttax'];
                    $shippingTaxAmount = $taxAmounts['shippingtax'];
                    $taxAmount = $productTaxAmount + $shippingTaxAmount;

                    if ($productTaxAmount > 0 || $shippingTaxAmount > 0) {
                        $tax = true;
                    } else {
                        $tax = false;
                    }
                    if ($format == true) {
                        $taxAmount = Price::format($taxAmount);
                    }
                    $taxes[] = [
                        'name' => $taxRate->getTaxLabel(),
                        'producttaxamount' => $productTaxAmount,
                        'shippingtaxamount' => $shippingTaxAmount,
                        'taxamount' => $taxAmount,
                        'based' => $taxRate->getTaxBasedOn(),
                        'taxed' => $tax,
                        'id' => $taxRate->getID(),
                    ];
                }
            }
        }

        return $taxes;
    }

    public static function getTaxForProduct($cartItem)
    {
        $product = Product::getByID($cartItem['product']['pID']);

        if (isset($cartItem['product']['variation']) && $cartItem['product']['variation']) {
            $product->shallowClone = true;
            $product = clone $product;
            $product->setVariation($cartItem['product']['variation']);
        }

        $qty = $cartItem['product']['qty'];
        $taxRates = self::getTaxRates();
        $taxes = [];
        if (count($taxRates) > 0) {
            foreach ($taxRates as $taxRate) {
                if ($taxRate->isTaxable()) {
                    $taxAmount = $taxRate->calculateProduct($product, $qty);
                    $taxes[] = [
                        'name' => $taxRate->getTaxLabel(),
                        'taxamount' => $taxAmount,
                        'based' => $taxRate->getTaxBasedOn(),
                    ];
                }
            }
        }

        return $taxes;
    }
}
