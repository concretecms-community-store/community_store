<?php
defined('C5_EXECUTE') or die("Access Denied.");
use User as User;
use Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Price as StorePrice;
use Concrete\Package\CommunityStore\Src\Attribute\Key\StoreOrderKey as StoreOrderKey;

$dh = Core::make('helper/date');

$orderChoicesAttList = StoreOrderKey::getAttributeListBySet('order_choices', new User);
$orderChoicesEnabled = count($orderChoicesAttList)? true : false;


$subject = t("Order Receipt");


/**
 * HTML BODY START
 */
ob_start();

?>
<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN' 'http://www.w3.org/TR/html4/loose.dtd'>
<html>
<body>
<h2><?= t('Your Order') ?></h2>

<p><strong><?= t("Order") ?>#:</strong> <?= $order->getOrderID() ?></p>
<p><?= t('Order placed');?>: <?= $dh->formatDateTime($order->getOrderDate())?></p>

<p><?= t('Below are the details of your order:') ?></p>
<table border="0" width="100%">
    <tr>
        <td width="50%">
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
                <br>
                <?= $order->getAttribute("billing_phone") ?>
            </p>
        </td>
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

    <?php if ($orderChoicesEnabled) { ?>
        <tr>
            <td colspan="3">
                <h4><?= t("Other Choices")?></h4>
                <?php foreach ($orderChoicesAttList as $ak) { ?>
                    <strong><?= $ak->getAttributeKeyDisplayName()?></strong>
                    <p><?= str_replace("\r\n", "<br>", $order->getAttributeValueObject(StoreOrderKey::getByHandle($ak->getAttributeKeyHandle()))->getValue('displaySanitized', 'display')); ?></p>
                <?php } ?>
            </td>
        </tr>
    <?php } ?>

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
                <td><?= $item->getProductName() ?>
                    <?php if ($sku = $item->getSKU()) {
                        echo '(' . $sku . ')';
                    } ?>
                </td>
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
                <td><?= StorePrice::format($item->getSubTotal()) ?></td>
                <td><?= StorePrice::format($item->getPricePaid()) ?></td>
                <td><?= StorePrice::format($item->getSubTotal()) ?></td>
            </tr>
            <?php
        }
    }
    ?>
    </tbody>
</table>


<?php
$downloads = array();
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
    <div style="margin: 30px 0;">
        <p><strong><?= t("Your Downloads") ?></strong></p>
        <ul class="order-downloads">
            <?php
            foreach ($downloads as $name => $file) {
                if (is_object($file)) {
                    echo '<li><a href="' . $file->getForceDownloadURL() . '">' . $name . '</a></li>';
                }
            } ?>
        </ul>
    </div>
<?php } ?>

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
    <strong><?= t("Payment Method") ?>: </strong><?= $order->getPaymentMethodName() ?>
</p>

<?php echo $paymentInstructions; ?>

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

<?= t("Order #%s has been received", $order->getOrderID()) ?>

<?= t("BILLING INFORMATION") ?>
<?= $order->getAttribute("billing_first_name") . " " . $order->getAttribute("billing_last_name") ?>
<?php if ($order->getAttribute("billing_address")->address2) {
    echo $order->getAttribute("billing_address")->address2;
} ?>
<?= $order->getAttribute("billing_address")->city ?>, <?= $order->getAttribute("billing_address")->state_province ?> <?= $order->getAttribute("billing_address")->postal_code ?>
<?= $order->getAttribute("billing_phone") ?>

<?= t("SHIPPING INFORMATION") ?>
<?= $order->getAttribute("shipping_first_name") . " " . $order->getAttribute("shipping_last_name") ?>
<?= $order->getAttribute("shipping_address")->address1 ?>
<?php if ($order->getAttribute("shipping_address")->address2) {
    echo $order->getAttribute("shipping_address")->address2;
} ?>
<?= $order->getAttribute("shipping_address")->city ?>, <?= $order->getAttribute("shipping_address")->state_province ?> <?= $order->getAttribute("shipping_address")->postal_code ?>

<?= t("ORDER ITEMS") ?>
<?php
$items = $order->getOrderItems();
if ($items) {
    foreach ($items as $item) {
        echo "{$item->getQty()}x {$item->getProductName()}";
    }
}
?>

<?= t("Tax") ?>: <?= StorePrice::format($order->getTaxTotal()) ?>
<?= t("Shipping") ?>:  <?= StorePrice::format($order->getShippingTotal()) ?>
<?php $applieddiscounts = $order->getAppliedDiscounts();
if (!empty($applieddiscounts)) { ?>
    <?php
    $discountsApplied = array();
    foreach ($applieddiscounts as $discount) {
        $discountsApplied[] = $discount['odDisplay'];
    }
    echo (count($applieddiscounts) > 1 ? t('Discounts') : t('Discount')) . ' ' . implode(',', $discountsApplied);
    ?>
<?php } ?>
<?= t("Total") ?>: <?= StorePrice::format($order->getTotal()) ?>

<?= t("Payment Method") ?>: </strong><?= $order->getPaymentMethodName() ?>

<?php echo strip_tags($paymentInstructions); ?>

<?php

$body = ob_get_clean(); ?>
