<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Utilities;

use Concrete\Core\File\File;
use Concrete\Core\Entity\File\File as FileEntity;
use Concrete\Core\Support\Facade\Url;
use Concrete\Core\Support\Facade\Config;
use Concrete\Core\Controller\Controller;
use Concrete\Core\Support\Facade\Application;
use Concrete\Package\CommunityStore\Src\CommunityStore\Order\Order as StoreOrder;

class Download extends Controller
{
    public static function buildDownloadURL($file, $order)
    {
        return Url::to('/store_download/' . $file->getFileID() . '/' . $order->getOrderID() . '/' . md5($order->getOrderDate()->format('Y-m-d H:i:s')));
    }

    public static function downloadFile($fID, $oID, $hash)
    {
        $valid = false;

        $file = File::getByID($fID);
        if ($file instanceof FileEntity && $file->getFileID() > 0) {
            $file->trackDownload(null);
            $fv = $file->getVersion();

            $order = StoreOrder::getByID($oID);

            $expiryhours = Config::get('community_store.download_expiry_hours');
            if (!$expiryhours) {
                $expiryhours = 48;
            }

            $threshhold = new \DateTime();
            $threshhold->sub(new \DateInterval('PT' . $expiryhours . 'H'));
            $orderDate = $order->getOrderDate();

            // check that order exists, and md5 hash of order timestamp matches
            if ($order && md5($orderDate->format('Y-m-d H:i:s')) == $hash && $orderDate > $threshhold) {
                // loop to find whether order contained a product with linked file
                foreach ($order->getOrderItems() as $oi) {
                    $product = $oi->getProductObject();

                    if ($product) {
                        $files = $product->getDownloadFiles();

                        foreach ($files as $f) {
                            if ($f->getFileID() == $fID) {
                                $valid = true;
                                break;
                            }
                        }
                    }

                    if ($valid) {
                        break;
                    }
                }
            }

            if ($valid) {
                return $fv->buildForceDownloadResponse();
            }
        }

        echo t('The download link you have followed has expired or is invalid');

        return false;
    }
}
