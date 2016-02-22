<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Utilities;

use Config;

class Price
{
    public static function format($price)
    {
        $price = floatval($price);
        $symbol = Config::get('communitystore.symbol');
        $wholeSep = Config::get('communitystore.whole');
        $thousandSep = Config::get('communitystore.thousand');
        $price = $symbol . number_format($price, 2, $wholeSep, $thousandSep);

        return $price;
    }
    public static function formatFloat($price)
    {
        $price = floatval($price);
        $price = number_format($price, 2, ".", "");

        return $price;
    }
    public function getFloat($price)
    {
        $symbol = Config::get('communitystore.symbol');
        $wholeSep = Config::get('communitystore.whole');
        $thousandSep = Config::get('communitystore.thousand');

        $price = str_replace($symbol, "", $price);
        $price = str_replace($thousandSep, "", $price); //no commas, or spaces or whatevz
        $price = str_replace($wholeSep, ".", $price); // replace whole separator with '.' 

        return $price;
    }
}
