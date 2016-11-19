<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Tax;

use Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Price as StorePrice;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product as StoreProduct;
use Database;
use Config;

class Tax
{
    public static function getTaxRates()
    {
        $em = \ORM::entityManager();
        $taxRates = $em->createQuery('select tr from \Concrete\Package\CommunityStore\Src\CommunityStore\Tax\TaxRate tr')->getResult();

        return $taxRates;
    }

    public static function getTaxes($format = false)
    {
        $taxRates = self::getTaxRates();
        $taxes = array();
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
                        $taxAmount = StorePrice::format($taxAmount);
                    }
                    $taxes[] = array(
                        'name' => $taxRate->getTaxLabel(),
                        'producttaxamount' => $productTaxAmount,
                        'shippingtaxamount' => $shippingTaxAmount,
                        'taxamount' => $taxAmount,
                        'based' => $taxRate->getTaxBasedOn(),
                        'taxed' => $tax,
                    );
                }
            }
        }

        return $taxes;
    }

    public static function getTaxForProduct($cartItem)
    {
        $product = StoreProduct::getByID($cartItem['product']['pID']);
        $qty = $cartItem['product']['qty'];
        $taxRates = self::getTaxRates();
        $taxes = array();
        if (count($taxRates) > 0) {
            foreach ($taxRates as $taxRate) {
                if ($taxRate->isTaxable()) {
                    $taxAmount = $taxRate->calculateProduct($product, $qty);
                    $taxes[] = array(
                        'name' => $taxRate->getTaxLabel(),
                        'taxamount' => $taxAmount,
                        'based' => $taxRate->getTaxBasedOn(),
                    );
                }
            }
        }

        return $taxes;
    }
    
}
