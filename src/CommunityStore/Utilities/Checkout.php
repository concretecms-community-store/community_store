<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Utilities;

use Controller;
use Core;
use Config;
use Session;
use Illuminate\Filesystem\Filesystem;
use View;
use User;
use UserInfo;
use Concrete\Package\CommunityStore\Src\CommunityStore\Customer\Customer as StoreCustomer;
use Concrete\Package\CommunityStore\Src\CommunityStore\Cart\Cart as StoreCart;
use Concrete\Package\CommunityStore\Src\CommunityStore\Order\Order as StoreOrder;
use Concrete\Core\Entity\File\File as FileEntity;
use Concrete\Core\File\File;

class Checkout extends Controller
{
    public static function validateVatNumber($vat_number)
    {
        $e = Core::make('helper/validation/error');

        // If not VAT number set, return empty errors
        if (empty($vat_number)) {
            return $e;
        }

        // Taken from: https://www.safaribooksonline.com/library/view/regular-expressions-cookbook/9781449327453/ch04s21.html
        $regex = "/^((AT)?U[0-9]{8}|(BE)?0[0-9]{9}|(BG)?[0-9]{9,10}|(CY)?[0-9]{8}L|(CZ)?[0-9]{8,10}|(DE)?[0-9]{9}|(DK)?[0-9]{8}|(EE)?[0-9]{9}|(EL|GR)?[0-9]{9}|(ES)?[0-9A-Z][0-9]{7}[0-9A-Z]|(FI)?[0-9]{8}|(FR)?[0-9A-Z]{2}[0-9]{9}|(GB)?([0-9]{9}([0-9]{3})?|[A-Z]{2}[0-9]{3})|(HU)?[0-9]{8}|(IE)?[0-9]S[0-9]{5}L|(IE)?[0-9]{7}[A-Z]*|(IT)?[0-9]{11}|(LT)?([0-9]{9}|[0-9]{12})|(LU)?[0-9]{8}|(LV)?[0-9]{11}|(MT)?[0-9]{8}|(NL)?[0-9]{9}B[0-9]{2}|(PL)?[0-9]{10}|(PT)?[0-9]{9}|(RO)?[0-9]{2,10}|(SE)?[0-9]{12}|(SI)?[0-9]{8}|(SK)?[0-9]{10})$/i";

        if ('' != $vat_number && !preg_match($regex, $vat_number)) {
            $e->add(t('You must enter a valid VAT Number'));
        }

        return $e;
    }

    public static function buildDownloadURL($file, $order) {
        return \URL::to('/store_download/'. $file->getFileID() .'/' .$order->getOrderID() . '/' . md5($order->getOrderDate()->format('Y-m-d H:i:s')));
    }
}
