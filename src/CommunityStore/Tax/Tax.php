<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Tax;

use \Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Price as StorePrice;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product as StoreProduct;
use Database;
use Config;

class Tax
{
    public static function getTaxRates()
    {
        $em = Database::connection()->getEntityManager();
        $taxRates = $em->createQuery('select tr from \Concrete\Package\CommunityStore\Src\CommunityStore\Tax\TaxRate tr')->getResult();
        return $taxRates;
    }

    public static function getTaxes($format = false)
    {
        $taxRates = self::getTaxRates();
        $taxes = array();
        if (count($taxRates) > 0) {
            foreach ($taxRates as $taxRate) {
                if($taxRate->isTaxable()){
                    $taxAmount = $taxRate->calculate();
                    if ($taxAmount > 0) {
                        $tax = true;
                    } else { $tax = false; }
                    if ($format == true) {
                        $taxAmount = StorePrice::format($taxAmount);
                    }
                    $taxes[] = array(
                        'name' => $taxRate->getTaxLabel(),
                        'taxamount' => $taxAmount,
                        'based' => $taxRate->getTaxBasedOn(),
                        'taxed' => $tax
                    );
                }
            }
        }
        return $taxes;
    }

    public function getTaxForProduct($cartItem)
    {
        $product = StoreProduct::getByID($cartItem['product']['pID']);
        $qty = $cartItem['product']['qty'];
        $taxRates = self::getTaxRates();
        $taxes = array();
        if (count($taxRates) > 0) {
            foreach ($taxRates as $taxRate) {
                if($taxRate->isTaxable()){
                    $taxAmount = $taxRate->calculateProduct($product,$qty);
                    $taxes[] = array(
                        'name' => $taxRate->getTaxLabel(),
                        'taxamount' => $taxAmount,
                        'based' => $taxRate->getTaxBasedOn()
                    );
                }
            }
        }
        return $taxes;
        
    }
    public static function getConcatenatedTaxStrings()
    {
        $taxes = self::getTaxes();
        $taxCalc = Config::get('communitystore.calculation');

        $taxTotal = array();
        $taxIncludedTotal = array();
        $taxLabels = array();

        foreach($taxes as $tax){
            if ($taxCalc == 'extract') {
                $taxIncludedTotal[] = $tax['taxamount'];
            }  else {
                $taxTotal[] = $tax['taxamount'];
            }
            $taxLabels[] = $tax['name'];
        }

        $taxTotal = implode(',',$taxTotal);
        $taxIncludedTotal = implode(',',$taxIncludedTotal);
        $taxLabels = implode(',',$taxLabels);

        $taxStrings = array(
            'taxTotal' => $taxTotal,
            'taxIncludedTotal' => $taxIncludedTotal,
            'taxLabels' => $taxLabels
        );
        return $taxStrings;
    }
}
