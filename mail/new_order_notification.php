<?php
defined('C5_EXECUTE') or die("Access Denied.");
use Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Price as StorePrice;
use Concrete\Package\CommunityStore\Src\Attribute\Key\StoreOrderKey as StoreOrderKey;
use Concrete\Package\CommunityStore\Src\CommunityStore\Customer\Customer as StoreCustomer;

$dh = Core::make('helper/date');

$subject = t("New Order Notification #%s", $order->getOrderID());
/**
 * HTML BODY START
 */
ob_start();

?>
    <!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN' 'http://www.w3.org/TR/html4/loose.dtd'>
    <html>
    <head>
    </head>
    <body>
    <h2><?= t('An order has been placed') ?></h2>

    <p><strong><?= t("Order") ?>#:</strong> <?= $order->getOrderID() ?></p>
    <p><?= t('Order placed');?>: <?= $dh->formatDateTime($order->getOrderDate())?></p>

    <table border="0" width="100%" style="border-collapse: collapse;">
        <tr>
            <td width="50%" valign="top" style="vertical-align: top; padding: 0; padding-right: 10px;">
                <h3><?= t('Billing Information') ?></h3>
                <p>
                    <?= $order->getAttribute("billing_first_name") . " " . $order->getAttribute("billing_last_name") ?><br>
                    <?php $address = StoreCustomer::formatAddress($order->getAttribute("billing_address")); ?>
                    <?= nl2br($address); ?>
                    <br><br>
                    <strong><?= t('Email') ?></strong>: <a href="mailto:<?= $order->getAttribute("email"); ?>"><?= $order->getAttribute("email"); ?></a><br>
                    <strong><?= t('Phone') ?></strong>: <?= $order->getAttribute("billing_phone") ?>
                    <?php
                    $vat_number = $order->getAttribute("vat_number");
                    if ($vat_number) { ?>
                    <br /><strong><?= t('VAT Number') ?></strong>: <?= $vat_number ?>
                    <?php } ?>
                </p>
            </td>
            <td style="vertical-align: top; padding: 0;">
                <?php if ($order->isShippable()) { ?>
                    <h3><?= t('Shipping Information') ?></h3>
                    <p>
                        <?= $order->getAttribute("shipping_first_name") . " " . $order->getAttribute("shipping_last_name") ?>
                        <br />
                        <?php $shippingaddress = $order->getAttribute("shipping_address"); ?>
                        <?php if ($shippingaddress) {
                            $shippingaddress = StoreCustomer::formatAddress($shippingaddress);
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
                    <h3><?= t("Other Choices")?></h3>
                    <?php foreach ($orderChoicesAttList as $ak) {
                        $orderOtherAtt = $order->getAttributeValueObject(StoreOrderKey::getByHandle($ak->getAttributeKeyHandle()));
                        if ($orderOtherAtt) { ?>
                            <strong><?= $ak->getAttributeKeyDisplayName()?></strong>
                            <p><?= str_replace("\r\n", "<br>", $orderOtherAtt->getValue('displaySanitized', 'display')); ?></p>
                        <?php } ?>
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
            <th style="border-bottom: 1px solid #aaa; text-align: left; padding-right: 10px;"><?= t('Qty') ?></th>
            <th style="border-bottom: 1px solid #aaa; text-align: left; padding-right: 10px;"><?= t('Price') ?></th>
            <th style="border-bottom: 1px solid #aaa; text-align: left;"><?= t('Subtotal') ?></th>
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
                            $optionOutput = array();
                            foreach ($options as $option) {
                                $optionOutput[] =  "<strong>" . $option['oioKey'] . ": </strong>" . ($option['oioValue'] ? $option['oioValue'] : '<em>' . t('None') . '</em>');
                            }
                            echo implode('<br>', $optionOutput);
                        }
                        ?>
                    </td>
                    <td style="vertical-align: top; padding: 5px 10px 5px 0;"><?= $item->getQty() ?></td>
                    <td style="vertical-align: top; padding: 5px 10px 5px 0;"><?= StorePrice::format($item->getPricePaid()) ?></td>
                    <td style="vertical-align: top; padding: 5px 0 5px 0;"><?= StorePrice::format($item->getSubTotal()) ?></td>
                </tr>
                <?php
            }
        }
        ?>
        </tbody>
    </table>

    <p>
        <?php if ($order->isShippable()) { ?>
            <strong><?= t("Shipping") ?>:</strong>  <?= StorePrice::format($order->getShippingTotal()) ?><br>
            <strong><?= t("Shipping Method") ?>: </strong><?= $order->getShippingMethodName() ?> <br>

            <?php
            $shippingInstructions = $order->getShippingInstructions();
            if ($shippingInstructions) { ?>
                <strong><?= t("Delivery Instructions") ?>: </strong><?= $shippingInstructions ?> <br />
            <?php } ?>
        <?php } ?>

        <?php $applieddiscounts = $order->getAppliedDiscounts();
        if (!empty($applieddiscounts)) { ?>
            <strong><?= (count($applieddiscounts) > 1 ? t('Discounts') : t('Discount')); ?>: </strong>
            <?php
            $discountsApplied = array();
            foreach ($applieddiscounts as $discount) {
                $discountsApplied[] = $discount['odDisplay'];
            }
            echo implode(',', $discountsApplied);
            ?>
            <br/>
        <?php } ?>

        <?php foreach ($order->getTaxes() as $tax) { ?>
            <strong><?= $tax['label'] ?>
                :</strong> <?= StorePrice::format($tax['amount'] ? $tax['amount'] : $tax['amountIncluded']) ?><br>
        <?php } ?>

        <strong class="text-large"><?= t("Total") ?>:</strong> <?= StorePrice::format($order->getTotal()) ?><br><br>

        <?php if ($order->getTotal() > 0) { ?>
            <strong><?= t("Payment Method") ?>: </strong><?= $order->getPaymentMethodName() ?><br />

            <?php
            $paid = $order->getPaid();

            if ($paid) {
                $status = t('Paid') . ' - ' . $dh->formatDateTime($paid);
            } else {
                $status = t('Unpaid');
            }
            ?>
            <strong><?= t("Payment Status") ?>:</strong> <?= $status; ?><br>

            <?php $transactionReference = $order->getTransactionReference();
            if ($transactionReference) { ?>
                <strong><?= t("Transaction Reference") ?>: </strong><?= $transactionReference ?>
            <?php } ?>
        <?php } else { ?>
            <strong><?=  t('Free Order') ?></strong>
        <?php } ?>
    </p>

    <p><a href="<?= \URL::to('/dashboard/store/orders/order/'. $order->getOrderID());?>"><?=t('View this order within the Dashboard');?></a></p>

    </body>
    </html>

<?php
$bodyHTML = ob_get_clean();
/**
 * HTML BODY END
 *
 * ======================
 *
 * PLAIN TEXT BODY START
 */
ob_start();

?>

<?= t("Order #:") ?> <?= $order->getOrderID() ?>
<?= t("A new order has been placed on your website") ?>
<?php

$body = ob_get_clean(); ?>