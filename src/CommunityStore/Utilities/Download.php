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

        $file = File::getByID((int) $fID);
        $order = Order::getByID((int) $oID);
        $hash = (string) $hash;

        if ($order && $hash !== '' && $file instanceof FileEntity && $file->getFileID() > 0) {
            $fv = $file->getVersion();

            $expiryhours = Config::get('community_store.download_expiry_hours');
            if (!$expiryhours) {
                $expiryhours = 48;
            }

            $threshhold = new \DateTime();
            $threshhold->sub(new \DateInterval('PT' . $expiryhours . 'H'));
            $orderDate = $order->getOrderDate();

            $matchedOrder = false;

            // check that order exists, and has matches stored security code
            if ($order) {

                $securityCode = $order->getSecurityCode();

                if ($securityCode) {
                    if (hash_equals((string) $securityCode, $hash)) {
                        $matchedOrder = true;
                    }
                } elseif ($orderDate) {
                    // fallback if using old approach where security codes haven't been stored
                    if (hash_equals(md5($orderDate->format('Y-m-d H:i:s')), $hash)) {
                        $matchedOrder = true;
                    }
                }

                // downloads are only available for paid orders, within the expiry period
                if ($matchedOrder && $order->getPaid() && $orderDate > $threshhold) {
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
                $file->trackDownload(null);

                return $fv->buildForceDownloadResponse();
            }
        }

        echo t('The download link you have followed has expired or is invalid');

        return false;
    }
}
