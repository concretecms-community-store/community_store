<?php
defined('C5_EXECUTE') or die("Access Denied.");
use \Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Price as StorePrice;

$subject = t("New Order Notification");
/**
 * HTML BODY START
 */
ob_start();

?>
<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN' 'http://www.w3.org/TR/html4/loose.dtd'>
<html>
<body>
<h2><?= t('An order has been placed') ?></h2>

<p><strong><?= t("Order") ?>#:</strong> <?= $order->getOrderID() ?></p>

<table border="0" width="100%">
    <tr>
        <td width="50%" valign="top">
            <strong><?= t('Billing Information') ?></strong>

            <p>
                <?= $order->getAttribute("billing_first_name") . " " . $order->getAttribute("billing_last_name") ?>
                <br>
                <?= $order->getAttribute("billing_address")->address1 ?><br>
                <?php if ($order->getAttribute("billing_address")->address2) {
                    echo $order->getAttribute("billing_address")->address2 . "<br>";
                } ?>
                <?= $order->getAttribute("billing_address")->city ?>
                , <?= $order->getAttribute("billing_address")->state_province ?> <?= $order->getAttribute("billing_address")->postal_code ?>
                <br><br>
                <strong><?= t('Email') ?></strong>: <a
                    href="mailto:<?= $order->getAttribute("email"); ?>"><?= $order->getAttribute("email"); ?></a><br>
                <strong><?= t('Phone') ?></strong>: <?= $order->getAttribute("billing_phone") ?><br>
            </p>
        </td>
        <td valign="top">
        <td>
            <?php if ($order->isShippable()) { ?>
                <strong><?= t('Shipping Information') ?></strong>
                <p>
                    <?= $order->getAttribute("shipping_first_name") . " " . $order->getAttribute("shipping_last_name") ?>
                    <br>
                    <?= $order->getAttribute("shipping_address")->address1 ?><br>
                    <?php if ($order->getAttribute("shipping_address")->address2) {
                        echo $order->getAttribute("shipping_address")->address2 . "<br>";
                    } ?>
                    <?= $order->getAttribute("shipping_address")->city ?>
                    , <?= $order->getAttribute("shipping_address")->state_province ?> <?= $order->getAttribute("shipping_address")->postal_code ?>
                    <br>

                </p>
            <?php } ?>
        </td>
    </tr>
</table>

<h3><?= t('Order Details') ?></h3>
<table border="0" cellpadding="0" cellspacing="0" width="100%">
    <thead>
    <tr>
        <th style="border-bottom: 1px solid #aaa; text-align: left;"><?= t('Product Name') ?></th>
        <th style="border-bottom: 1px solid #aaa; text-align: left;"><?= t('Options') ?></th>
        <th style="border-bottom: 1px solid #aaa; text-align: left;"><?= t('Qty') ?></th>
        <th style="border-bottom: 1px solid #aaa; text-align: left;"><?= t('Price') ?></th>
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
                <td><?= $item->getProductName() ?></td>
                <td>
                    <?php
                    $options = $item->getProductOptions();
                    if ($options) {
                        echo "<ul class='list-unstyled'>";
                        foreach ($options as $option) {
                            echo "<li>";
                            echo "<strong>" . $option['oioKey'] . ": </strong>";
                            echo $option['oioValue'];
                            echo "</li>";
                        }
                        echo "</ul>";
                    }
                    ?>
                </td>
                <td><?= $item->getQty() ?></td>
                <td><?= StorePrice::format($item->getPricePaid()) ?></td>
                <td><?= StorePrice::format($item->getSubTotal()) ?></td>
            </tr>
            <?php
        }
    }
    ?>
    </tbody>
</table>

    <?php if ($order->isShippable()) { ?>
        <strong><?= t("Shipping") ?>:</strong>  <?= StorePrice::format($order->getShippingTotal()) ?><br>
    <?php } ?>

    <?php if ($order->isShippable()) { ?>
        <strong><?= t("Shipping Method") ?>: </strong><?= $order->getShippingMethodName() ?> <br>
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

    <strong><?= t("Payment Method") ?>: </strong><?= $order->getPaymentMethodName() ?><br>
    <?php $transactionReference = $order->getTransactionReference();
    if ($transactionReference) { ?>
        <strong><?= t("Transaction Reference") ?>: </strong><?= $transactionReference ?><br>
    <?php } ?>

</p>


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