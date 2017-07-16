<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Utilities;

use Config;

class Price
{
    public static function isZeroDecimalCurrency($currency)
    {
        $zeroDecimalCurrencies = [
            'BIF',
            'CLP',
            'DJF',
            'GNF',
            'JPY',
            'KMF',
            'KRW',
            'MGA',
            'PYG',
            'RWF',
            'VND',
            'VUV',
            'XAF',
            'XOF',
            'XPF',
        ];

        return in_array($currency, $zeroDecimalCurrencies);
    }

    public static function getCurrencyMultiplier($currency)
    {
        return self::isZeroDecimalCurrency($currency) ? 1 : 100;
    }

    public static function format($price)
    {
        $price = floatval($price);
        $symbol = Config::get('community_store.symbol');
        $wholeSep = Config::get('community_store.whole');
        $thousandSep = Config::get('community_store.thousand');
        $currency = Config::get('community_store_stripe.currency');
        $decimals = self::isZeroDecimalCurrency($currency) ? 0 : 2;
        $price = $symbol . number_format($price, $decimals, $wholeSep, $thousandSep);

        return $price;
    }

    public static function formatFloat($price)
    {
        $currency = Config::get('community_store_stripe.currency');
        if (!self::isZeroDecimalCurrency($currency)) {
            $price = floatval($price);
            $price = number_format($price, 2, ".", "");
        }

        return $price;
    }

    public static function formatInNumberOfCents($price)
    {
        $currency = Config::get('community_store_stripe.currency');
        if (!self::isZeroDecimalCurrency($currency)) {
            $price = number_format($price * 100, 0, "", "");
        }

        return $price;
    }

    public static function getFloat($price)
    {
        $symbol = Config::get('community_store.symbol');
        $wholeSep = Config::get('community_store.whole');
        $thousandSep = Config::get('community_store.thousand');

        $price = str_replace($symbol, "", $price);
        $price = str_replace($thousandSep, "", $price);
        $price = str_replace($wholeSep, ".", $price);

        return $price;
    }
}
