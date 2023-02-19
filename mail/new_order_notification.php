<?php
defined('C5_EXECUTE') or die('Access Denied.');

use Concrete\Core\Support\Facade\Url;
use Concrete\Package\CommunityStore\Src\CommunityStore\Customer\Customer;
use Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Price;

$app = \Concrete\Core\Support\Facade\Application::getFacadeApplication();
$dh = $app->make('helper/date');

$subject = t('New Order Notification #%s', $order->getOrderID());
/**
 * HTML BODY START.
 */
ob_start();

?>
    <!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN' 'http://www.w3.org/TR/html4/loose.dtd'>
    <html>
    <head>
      <style>
          body {
              font-family: Arial, 'Helvetica Neue', Helvetica, sans-serif;
          }
      </style>
    </head>
    <body>
    <h2><?= t('An order has been placed') ?></h2>

    <p><strong><?= t('Order') ?>#:</strong> <?= $order->getOrderID() ?></p>
    <p><?= t('Order placed'); ?>: <?= $dh->formatDateTime($order->getOrderDate())?></p>

    <table border="0" width="100%" style="border-collapse: collapse;">
        <tr>
            <td width="50%" valign="top" style="vertical-align: top; padding: 0 10px 0 0;">
                <h3><?= t('Billing Information') ?></h3>
                <p>
                    <?= h($order->getAttribute('billing_first_name')) . ' ' . h($order->getAttribute('billing_last_name')) ?><br>
                    <?php if ($order->getAttribute('billing_company')) { ?>
                        <?= h($order->getAttribute('billing_company')) ?><br>
                    <?php } ?>
                    <?php $address = Customer::formatAddress($order->getAttribute('billing_address')); ?>
                    <?= nl2br($address); ?>
                    <br><br>
                    <strong><?= t('Email') ?></strong>: <a href="mailto:<?= h($order->getAttribute('email')); ?>"><?= h($order->getAttribute('email')); ?></a><br>
                    <strong><?= t('Phone') ?></strong>: <?= h($order->getAttribute('billing_phone')) ?>
                    <?php
                    $vat_number = $order->getAttribute('vat_number');
                    if ($vat_number) { ?>
                    <br /><strong><?= t('VAT Number') ?></strong>: <?= h($vat_number) ?>
                    <?php } ?>
                </p>
            </td>
            <td style="vertical-align: top; padding: 0;">
                <?php if ($order->isShippable()) { ?>
                    <h3><?= t('Shipping Information') ?></h3>
                    <p>
                        <?= h($order->getAttribute('shipping_first_name')) . ' ' . h($order->getAttribute('shipping_last_name')) ?><br />
                        <?php if ($order->getAttribute('shipping_company')) { ?>
                            <?= h($order->getAttribute('shipping_company')) ?><br>
                        <?php } ?>
                        <?php $shippingaddress = $order->getAttribute('shipping_address'); ?>
                        <?php if ($shippingaddress) {
                            $shippingaddress = Customer::formatAddress($shippingaddress);
                            echo nl2br($shippingaddress);
                        }
                        ?>
                    </p>
                <?php } ?>
            </td>
        </tr>

        <?php if (!empty($orderChoicesAttList)) { ?>
            <tr>
                <td colspan="2">
                    <h3><?= t('Other Choices')?></h3>
                    <?php foreach ($orderChoicesAttList as $ak) {
                        $orderOtherAtt = $order->getAttributeValueObject($ak->getAttributeKeyHandle());
                        if ($orderOtherAtt) {
                            $attvalue = trim($orderOtherAtt->getValue('displaySanitized', 'display'));
                            if ($attvalue) { ?>
                                <strong><?= $ak->getAttributeKeyDisplayName() ?></strong>
                                <p><?= str_replace("\r\n", '<br>', $attvalue); ?></p>
                            <?php }
                        }?>
                    <?php } ?>
                </td>
            </tr>
        <?php } ?>

    </table>
    <h3><?= t('Order Details') ?></h3>
    <table border="0" cellpadding="0" cellspacing="0" width="100%">
        <thead>
        <tr>
            <th style="border-bottom: 1px solid #aaa; text-align: left; padding-right: 10px;"><?= t('Product Name') ?></th>
            <th style="border-bottom: 1px solid #aaa; text-align: left; padding-right: 10px;"><?= t('Options') ?></th>
            <th style="border-bottom: 1px solid #aaa; text-align: right; padding-right: 10px;"><?= t('Qty') ?></th>
            <th style="border-bottom: 1px solid #aaa; text-align: right; padding-right: 10px;"><?= t('Price') ?></th>
            <th style="border-bottom: 1px solid #aaa; text-align: right;"><?= t('Subtotal') ?></th>
        </tr>
        </thead>
        <tbody>
        <?php
        $items = $order->getOrderItems();
        if ($items) {
            foreach ($items as $item) {
                ?>
                <tr>
                    <td style="vertical-align: top; padding: 5px 10px 5px 0"><?= $item->getProductName() ?>
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
                                $optionOutput[] = '<strong>' . $option['oioKey'] . ': </strong>' . ($option['oioValue'] ? $option['oioValue'] : '<em>' . t('None') . '</em>');
                            }
                            echo implode('<br>', $optionOutput);
                        }
                        ?>
                    </td>
                    <td style="vertical-align: top; padding: 5px 10px 5px 0; text-align: right"><?= $item->getQuantity() ?> <?= h($item->getQuantityLabel()); ?></td>
                    <td style="vertical-align: top; padding: 5px 10px 5px 0; text-align: right"><?= Price::format($item->getPricePaid()) ?></td>
                    <td style="vertical-align: top; padding: 5px 0 5px 0; text-align: right"><?= Price::format($item->getSubTotal()) ?></td>
                </tr>
                <?php
            }
        }
        ?>
        </tbody>
        <tfoot>
        <tr>
            <td colspan="4" style="text-align: right"><strong><?= t('Items Subtotal')?>:</strong></td>
            <td style="text-align: right"><?= Price::format($order->getSubTotal())?></td>
        </tr>
        </tfoot>
    </table>

    <p>
        <?php if ($order->isShippable()) { ?>
            <strong><?= t('Shipping') ?>:</strong>  <?= Price::format($order->getShippingTotal()) ?><br>
            <strong><?= t('Shipping Method') ?>: </strong><?= $order->getShippingMethodName() ?> <br>

            <?php
            $shippingInstructions = $order->getShippingInstructions();
            if ($shippingInstructions) { ?>
                <strong><?= t('Delivery Instructions') ?>: </strong><?= nl2br(h($shippingInstructions)) ?> <br />
            <?php } ?>
        <?php } ?>

        <?php $applieddiscounts = $order->getAppliedDiscounts();
        if (!empty($applieddiscounts)) { ?>
            <strong><?= (count($applieddiscounts) > 1 ? t('Discounts') : t('Discount')); ?>: </strong>
            <?php
            $discountsApplied = [];
            foreach ($applieddiscounts as $discount) {
                $discountsApplied[] = $discount['odDisplay'];
            }
            echo implode(',', $discountsApplied);
            ?>
            <br/>
        <?php } ?>

        <?php foreach ($order->getTaxes() as $tax) { ?>
            <strong><?= $tax['label'] ?>
                :</strong> <?= Price::format($tax['amount'] ? $tax['amount'] : $tax['amountIncluded']) ?><br>
        <?php } ?>

        <strong class="text-large"><?= t('Total') ?>:</strong> <?= Price::format($order->getTotal()) ?><br><br>

        <?php if ($order->getTotal() > 0) { ?>
            <strong><?= t('Payment Method') ?>: </strong><?= $order->getPaymentMethodName() ?><br />

            <?php
            $paid = $order->getPaid();

            if ($paid) {
                $status = t('Paid') . ' - ' . $dh->formatDateTime($paid);
            } else {
                $status = t('Unpaid');
            }
            ?>
            <strong><?= t('Payment Status') ?>:</strong> <?= $status; ?><br>

            <?php $transactionReference = $order->getTransactionReference();
            if ($transactionReference) { ?>
                <strong><?= t('Transaction Reference') ?>: </strong><?= $transactionReference ?>
            <?php } ?>
        <?php } else { ?>
            <strong><?=  t('Free Order') ?></strong>
        <?php } ?>
    </p>

    <?php
    $notes = $order->getNotes();
    if ($notes) { ?>
      <strong><?= t('Order notes') ?>: </strong><?= nl2br(h($notes)) ?> <br />
    <?php } ?>

    <p><a href="<?= Url::to('/dashboard/store/orders/order/' . $order->getOrderID()); ?>"><?=t('View this order within the Dashboard'); ?></a></p>

    </body>
    </html>

<?php
$bodyHTML = ob_get_clean();
/**
 * HTML BODY END.
 *
 * ======================
 *
 * PLAIN TEXT BODY START
 */
ob_start();

?>

<?= t('Order #:') ?> <?= $order->getOrderID() ?>
<?= t('A new order has been placed on your website') ?>
<?php

$body = ob_get_clean(); ?>
