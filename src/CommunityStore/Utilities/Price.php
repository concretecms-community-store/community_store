<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Utilities;

use Config;

class Price
{
    public static function format($price)
    {
        $price = floatval($price);
        $symbol = Config::get('community_store.symbol');
        $wholeSep = Config::get('community_store.whole');
        $thousandSep = Config::get('community_store.thousand');
        $price = $symbol . number_format($price, 2, $wholeSep, $thousandSep);

        return $price;
    }
    public static function formatFloat($price)
    {
        $price = floatval($price);
        $price = number_format($price, 2, ".", "");

        return $price;
    }

    public static function formatInNumberOfCents($price)
    {
        $price = number_format($price * 100, 0, "", "");
        return $price;
    }

    public function getFloat($price)
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
