<?php
defined('C5_EXECUTE') or die("Access Denied.");

$locale = $order->getLocale();
if ($locale) {
    \Concrete\Core\Localization\Localization::changeLocale($locale);
}

use Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Price;
use Concrete\Package\CommunityStore\Src\CommunityStore\Customer\Customer;
use Concrete\Core\Support\Facade\Config;

$app = \Concrete\Core\Support\Facade\Application::getFacadeApplication();
$dh = $app->make('helper/date');
$csm = $app->make('cs/helper/multilingual');
$subject = t("Order Receipt #%s", $order->getOrderID());

/*
 * HTML BODY START
 */
ob_start();

?>
    <!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN' 'http://www.w3.org/TR/html4/loose.dtd'>
    <html>
    <head>
    </head>
    <body>
    <?php $header = $csm->t(trim(\Concrete\Core\Support\Facade\Config::get('community_store.receiptHeader')), 'receiptEmailHeader'); ?>

    <?php if ($header) {
    echo $header;
} else {
    ?>
        <h2><?= t('Your Order'); ?></h2>
    <?php
} ?>

    <p><strong><?= t("Order"); ?>#:</strong> <?= $order->getOrderID(); ?></p>
    <p><?= t('Order placed'); ?>: <?= $dh->formatDateTime($order->getOrderDate()); ?></p>

    <?php
    $items = $order->getOrderItems();

    $downloads = [];
    foreach ($items as $item) {
        $pObj = $item->getProductObject();

        if (is_object($pObj)) {
            if ($pObj->hasDigitalDownload()) {
                $fileObjs = $pObj->getDownloadFileObjects();
                $downloads[$item->getProductName()] = $fileObjs[0];
            }
        }
    }
    if (count($downloads) > 0) {
        ?>

        <h3><?= t("Your Downloads"); ?></h3>
        <ul class="order-downloads">
            <?php
            foreach ($downloads as $name => $file) {
                if (is_object($file)) {
                    echo '<li><a href="' . \Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Download::buildDownloadURL($file, $order) . '">' . $name . '</a></li>';
                }
            } ?>
        </ul>

    <?php
    } ?>


    <table border="0" width="100%" style="border-collapse: collapse;">
        <tr>
            <td width="50%" style="vertical-align: top; padding: 0 10px 0 0;">
                <h3><?= t('Billing Information'); ?></h3>
                <p>
                    <?= h($order->getAttribute("billing_first_name")) . " " . h($order->getAttribute("billing_last_name")); ?><br>
                    <?php if ($order->getAttribute("billing_company")) {
        ?>
                        <?= h($order->getAttribute("billing_company")); ?><br>
                    <?php
    } ?>
                    <?php $address = Customer::formatAddress($order->getAttribute("billing_address")); ?>
                    <?= nl2br($address); ?>
                    <br><br>
                    <strong><?= t('Phone'); ?></strong>: <?= h($order->getAttribute("billing_phone")); ?><br>
                    <?php
                    $vat_number = $order->getAttribute("vat_number");
                    if ($vat_number) {
                        ?>
                        <strong><?= t('VAT Number'); ?></strong>: <?= h($vat_number); ?><br>
                    <?php
                    } ?>
                </p>
            </td>
            <td style="vertical-align: top; padding: 0;">
                <?php if ($order->isShippable()) {
                        ?>
                    <h3><?= t('Shipping Information'); ?></h3>
                    <p>
                        <?= h($order->getAttribute("shipping_first_name")) . " " . h($order->getAttribute("shipping_last_name")); ?><br>
                        <?php if ($order->getAttribute("shipping_company")) {
                            ?>
                            <?= h($order->getAttribute("shipping_company")); ?><br>
                        <?php
                        } ?>
                        <?php $shippingaddress = $order->getAttribute("shipping_address"); ?>
                        <?php if ($shippingaddress) {
                            $shippingaddress = Customer::formatAddress($shippingaddress);
                            echo nl2br($shippingaddress);
                        } ?>
                    </p>
                <?php
                    } ?>
            </td>
        </tr>

        <?php if (!empty($orderChoicesAttList)) {
                        ?>
            <tr>
                <td colspan="2">
                    <h3><?= t("Other Choices"); ?></h3>
                    <?php foreach ($orderChoicesAttList as $ak) {
                            $orderOtherAtt = $order->getAttributeValueObject($ak->getAttributeKeyHandle());
                            if ($orderOtherAtt) {
                                $attvalue = trim($orderOtherAtt->getValue('displaySanitized', 'display'));
                                if ($attvalue) {
                                    ?>
                                <strong><?= $ak->getAttributeKeyDisplayName(); ?></strong>
                                <p><?= str_replace("\r\n", "<br>", $attvalue); ?></p>
                            <?php
                                }
                            } ?>
                    <?php
                        } ?>
                </td>
            </tr>
        <?php
                    } ?>

    </table>

    <h3><?= t('Order Details'); ?></h3>
    <table border="0" cellpadding="0" cellspacing="0" width="100%">
        <thead>
        <tr>
            <th style="border-bottom: 1px solid #aaa; text-align: left; padding-right: 10px;"><?= t('Product Name'); ?></th>
            <th style="border-bottom: 1px solid #aaa; text-align: left; padding-right: 10px;"><?= t('Options'); ?></th>
            <th style="border-bottom: 1px solid #aaa; text-align: right; padding-right: 10px;"><?= t('Qty'); ?></th>
            <th style="border-bottom: 1px solid #aaa; text-align: right; padding-right: 10px;"><?= t('Price'); ?></th>
            <th style="border-bottom: 1px solid #aaa; text-align: right;"><?= t('Subtotal'); ?></th>
        </tr>
        </thead>
        <tbody>
        <?php

        if ($items) {
            foreach ($items as $item) {
                ?>
                <tr>
                    <td style="vertical-align: top; padding: 5px 10px 5px 0;"><?= $item->getProductName(); ?>
                        <?php if ($sku = $item->getSKU()) {
                    echo '(' . $sku . ')';
                } ?>
                    </td>
                    <td style="vertical-align: top; padding: 5px 10px 5px 0;">
                        <?php
                        $options = $item->getProductOptions();
                if ($options) {
                    $optionOutput = [];
                    foreach ($options as $option) {
                        if ($option['oioValue']) {
                            $optionOutput[] = "<strong>" . $option['oioKey'] . ": </strong>" . $option['oioValue'];
                        }
                    }
                    echo implode('<br>', $optionOutput);
                } ?>
                    </td>
                    <td style="vertical-align: top; padding: 5px 10px 5px 0; text-align: right"><?= $item->getQuantity(); ?> <?= h($item->getQuantityLabel()); ?></td>
                    <td style="vertical-align: top; padding: 5px 10px 5px 0; text-align: right"><?= Price::format($item->getPricePaid()); ?></td>
                    <td style="vertical-align: top; padding: 5px 0 5px 0; text-align: right"><?= Price::format($item->getSubTotal()); ?></td>
                </tr>
                <?php
            }
        }
        ?>
        </tbody>
        <tfoot>
        <tr>
            <td colspan="4" style="text-align: right"><strong><?= t("Items Subtotal")?>:</strong></td>
            <td style="text-align: right"><?= Price::format($order->getSubTotal())?></td>
        </tr>
        </tfoot>
    </table>


    <p>
        <?php if ($order->isShippable()) {
            ?>
            <strong><?= t("Shipping"); ?>:</strong>  <?= Price::format($order->getShippingTotal()); ?><br>
            <strong><?= t("Shipping Method"); ?>: </strong><?= $order->getShippingMethodName(); ?> <br>

            <?php
            $shippingInstructions = $order->getShippingInstructions();
            if ($shippingInstructions) {
                ?>
                <strong><?= t("Delivery Instructions"); ?>: </strong><?= nl2br(h($shippingInstructions)); ?> <br />
            <?php
            } ?>
        <?php
        } ?>

        <?php $applieddiscounts = $order->getAppliedDiscounts();
        if (!empty($applieddiscounts)) {
            ?>
            <strong><?= (count($applieddiscounts) > 1 ? t('Discounts') : t('Discount')); ?>: </strong>
            <?php
            $discountsApplied = [];
            foreach ($applieddiscounts as $discount) {
                $discountsApplied[] = $discount['odDisplay'];
            }
            echo implode(',', $discountsApplied); ?>
            <br/>
        <?php
        } ?>

        <?php foreach ($order->getTaxes() as $tax) {
            ?>
            <strong><?= $tax['label']; ?>
                :</strong> <?= Price::format($tax['amount'] ? $tax['amount'] : $tax['amountIncluded']); ?><br>
        <?php
        } ?>

        <strong class="text-large"><?= t("Total"); ?>:</strong> <?= Price::format($order->getTotal()); ?><br><br>

        <?php if ($order->getTotal() > 0) {
            ?>
            <strong><?= t("Payment Method"); ?>: </strong><?= $order->getPaymentMethodName(); ?><br>
        <?php
        } else {
            ?>
            <strong><?=  t('Free Order'); ?></strong><br>
        <?php
        } ?>

        <?php
        $refunded = $order->getRefunded();
        $paid = $order->getPaid();
        $cancelled = $order->getCancelled();
        $status = '';

        if ($cancelled) {
            echo '<br /><strong>' . t('Cancelled') . '</strong>';
        } else {
            if ($refunded) {
                $status = t('Refunded');
            } elseif ($paid) {
                $status = t('Paid') . ' - ' . $dh->formatDateTime($paid);
            } elseif ($order->getTotal() > 0) {
                $status = t('Unpaid');
            }
        }
        ?>

        <?php if ($status) {
            ?>
            <strong><?= t("Payment Status"); ?>:</strong> <?= $status; ?>
        <?php
        } ?>
    </p>

    <?php
    $notes = $order->getNotes();
    if ($notes) { ?>
      <strong><?= t("Order notes"); ?>: </strong><?= nl2br(h($notes)); ?> <br />
    <?php } ?>

    <?php

    if ($order->getCustomerID()) {
        if ($order->getMemberCreated()) { ?>
            <p><?= t('A new member account has been created with this order. Your username and password have been emailed to you.'); ?></p>
        <?php } else { ?>
            <p><?= t('Your existing member account has been updated with this order. Please use your existing username and password to sign in.');?></p>
        <?php }
    }
    ?>

    <?php if ($link) { ?>
        <p><?= t('You can now access'); ?> <a href="<?= $link; ?>"><?= $link; ?></a></p>
    <?php } ?>

    <?php
    if ($paymentInstructions && $paymentMethodID) {
        $paymentInstructions = $csm->t($paymentInstructions, 'paymentInstructions', false, $paymentMethodID);
    }
    ?>
    <?= $paymentInstructions; ?>
    <?= $csm->t(trim(\Concrete\Core\Support\Facade\Config::get('community_store.receiptFooter')), 'receiptEmailFooter'); ?>

    </body>
    </html>

<?php
$bodyHTML = ob_get_clean();
/*
 * HTML BODY END
 *
 */
?>
