<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Utilities;

use Concrete\Core\File\File;
use Concrete\Core\Support\Facade\Url;
use Concrete\Core\Support\Facade\Config;
use Concrete\Core\Controller\Controller;
use Concrete\Core\Support\Facade\Application;
use Concrete\Core\Entity\File\File as FileEntity;
use Concrete\Package\CommunityStore\Src\CommunityStore\Order\Order;

class Download extends Controller
{
    public static function buildDownloadURL($file, $order)
    {

        $securityCode = $order->getSecurityCode();
        // fallback if using old approach where security codes haven't been stored
        if (!$securityCode) {
            $securityCode = md5($order->getOrderDate()->format('Y-m-d H:i:s'));
        }

        return Url::to('/store_download/' . $file->getFileID() . '/' . $order->getOrderID() . '/' . $securityCode);
    }

    public static function downloadFile($fID, $oID, $hash)
    {
        $valid = false;

        $file = File::getByID($fID);
        if ($file instanceof FileEntity && $file->getFileID() > 0) {
            $file->trackDownload(null);
            $fv = $file->getVersion();

            $order = Order::getByID($oID);

            $expiryhours = Config::get('community_store.download_expiry_hours');
            if (!$expiryhours) {
                $expiryhours = 48;
            }

            $threshhold = new \DateTime();
            $threshhold->sub(new \DateInterval('PT' . $expiryhours . 'H'));
            $orderDate = $order->getOrderDate();

            if (!$orderDate) {
                $orderDate = $order->getOrderDate();
            }

            $matchedOrder = false;

            // check that order exists, and has matches stored security code
            if ($order) {

                $securityCode = $order->getSecurityCode();

                if ($securityCode) {
                    if ($securityCode == $hash) {
                        $matchedOrder = true;
                    }
                } else {
                    // fallback if using old approach where security codes haven't been stored
                    if (md5($orderDate->format('Y-m-d H:i:s')) == $hash) {
                        $matchedOrder = true;
                    }
                }

                if ($matchedOrder && $orderDate > $threshhold) {
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
            }

            if ($valid) {
                return $fv->buildForceDownloadResponse();
            }
        }

        echo t('The download link you have followed has expired or is invalid');

        return false;
    }
}
