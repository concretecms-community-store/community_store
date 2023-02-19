<?php

namespace Concrete\Package\CommunityStore\Src\CommunityStore\Utilities;

use Concrete\Core\Support\Facade\Config;

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
        $currency = Config::get('community_store.currency');
        $symbol = Config::get('community_store.symbol');

        if ($currency) {
            return \Punic\Number::formatCurrency($price, $currency);
        }
        $price = (float) $price;
        $wholeSep = Config::get('community_store.whole');
        $thousandSep = Config::get('community_store.thousand');
        $decimals = self::isZeroDecimalCurrency($currency) ? 0 : 2;

        return $symbol . number_format($price, $decimals, $wholeSep, $thousandSep);
    }

    public static function formatFloat($price)
    {
        $currency = Config::get('community_store_stripe.currency');
        if (!self::isZeroDecimalCurrency($currency)) {
            $price = (float) $price;
            $price = number_format($price, 2, '.', '');
        }

        return $price;
    }

    public static function formatInNumberOfCents($price)
    {
        $currency = Config::get('community_store_stripe.currency');
        if (!self::isZeroDecimalCurrency($currency)) {
            $price = number_format($price * 100, 0, '', '');
        }

        return $price;
    }
}
