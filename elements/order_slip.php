<?php
defined('C5_EXECUTE') or die("Access Denied.");
use \Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Price as StorePrice;
use  \Concrete\Core\Support\Facade\Url;

$app = \Concrete\Core\Support\Facade\Application::getFacadeApplication();
$dh = $app->make('helper/date');

?>
<html>
<head>
    <title><?= t("Order #") . $order->getOrderID() ?></title>
    <link href="<?= str_replace('/index.php/', '/' , Url::to('/concrete/css/app.css')); ?>" rel="stylesheet" type="text/css" media="all">
    <style>
        #store-print-slip td, #store-print-slip th, #store-print-slip p {
            font-size: 0.8em;
        }

        #store-print-slip h1 {
            font-size: 2em;
        }

        #store-print-slip h4 {
            font-size: 1.1em;
        }

        #store-print-slip legend {
            font-size: 1.4em;
            margin-bottom: 0.5em;
        }

        #store-print-slip fieldset {
            margin-bottom: 1em;
            padding-bottom: 1em;
        }

        #store-print-slip .alert {
            margin-top: -3em;
            margin-bottom: 3em;
        }

    </style>
</head>
<body>

<div class="ccm-ui" id="store-print-slip">
    <div class="container">
        <h1><?= t("Order #") . $order->getOrderID() ?></h1>

        <div class="row">
            <div class="col-xs-8">
                <p><strong><?= t('Order placed'); ?>:</strong> <?= $dh->formatDateTime($order->getOrderDate()) ?></p>
            </div>
            <div class="col-xs-4">
                <?php
                $refunded = $order->getRefunded();
                $paid = $order->getPaid();
                $cancelled = $order->getCancelled();

                if ($cancelled) {
                    echo '<p class="alert alert-danger text-center"><strong>' . t('Cancelled') . '</strong></p>';
                } else {
                    if ($refunded) {
                        $refundreason = $order->getRefundReason();
                        echo '<p class="alert alert-warning text-center"><strong>' . t('Refunded') . ' - ' . $dh->formatDateTime($refunded)  . ($refundreason ? ' - ' . $refundreason : '') . '</strong></p>';
                    } elseif ($paid) {
                        echo '<p class="alert alert-success text-center"><strong>' . t('Paid') . ' - ' . $dh->formatDateTime($paid) . '</strong></p>';
                    } elseif ($order->getTotal() > 0) {
                        echo '<p class="alert alert-danger text-center"><strong>' . t('Unpaid') . '</strong></p>';
                    } else {
                        echo '<p class="alert alert-default text-center"><strong>' . t('Free Order') . '</strong></p>';
                    }
                }
                ?>
            </div>
        </div>

        <fieldset>
            <legend><?= t("Customer Details") ?></legend>
            <div class="row">
                <div class="col-xs-4">
                    <h4><?= t("Name")?></h4>
                    <p><?= $order->getAttribute("billing_first_name"). " " . $order->getAttribute("billing_last_name")?></p>

                    <?php $orderemail = $order->getAttribute("email");
                    if ($orderemail) { ?>
                        <h4><?= t("Email") ?></h4>
                        <p><a href="mailto:<?= $order->getAttribute("email"); ?>"><?= $order->getAttribute("email"); ?></a></p>
                    <?php } ?>

                    <?php
                    $phone = $order->getAttribute("billing_phone");
                    if ($phone) {
                    ?>
                        <h4><?= t("Phone") ?></h4>
                        <p><?= $order->getAttribute("billing_phone") ?></p>
                    <?php } ?>

                    <?php
                    $vat_number = $order->getAttribute("vat_number");
                    if (Config::get('community_store.vat_number') && $vat_number) { ?>
                        <h4><?= t("VAT Number")?></h4>
                        <p><?=$vat_number?></p>
                    <?php } ?>

                </div>

                <div class="col-xs-4">
                    <h4><?= t("Billing Address") ?></h4>

                    <p>
                        <?= $order->getAttribute("billing_first_name") . " " . $order->getAttribute("billing_last_name") ?>
                        <br>
                        <?php $billingaddress = $order->getAttributeValueObject('billing_address');
                        if ($billingaddress) {
                            echo $billingaddress->getValue('displaySanitized', 'display');
                        }?>
                    </p>
                </div>
                <div class="col-xs-4">
                    <?php
                    $billingaddress = $order->getAttributeValueObject('shipping_address');
                    if ($billingaddress) { ?>
                        <h4><?= t("Shipping Address") ?></h4>
                        <p>
                            <?= $order->getAttribute("shipping_first_name") . " " . $order->getAttribute("shipping_last_name") ?>
                            <br>
                            <?= $billingaddress->getValue('displaySanitized', 'display'); ?>
                        </p>
                    <?php } ?>
                </div>

                <?php if (!empty($orderChoicesAttList)) { ?>
                    <div class="col-xs-12">
                        <?php foreach ($orderChoicesAttList as $ak) {
                            $attValue = $order->getAttributeValueObject($ak->getAttributeKeyHandle());

                            if ($attValue) {
                                $value = $attValue->getValue('displaySanitized', 'display');

                                if ($value) {
                                ?>
                                <h4><?= $ak->getAttributeKeyDisplayName()?></h4>
                                <p><?= str_replace("\r\n", "<br>", $value); ?></p>
                                <?php } ?>
                            <?php } ?>
                        <?php } ?>
                    </div>
                <?php } ?>

            </div>
        </fieldset>

        <fieldset>
            <legend><?= t("Order Items") ?></legend>

            <table class="table table-striped table-condensed">
                <thead>
                <tr>
                    <th><strong><?= t("Product Name") ?></strong></th>
                    <th><?= t("Product Options") ?></th>
                    <th class="text-right"><?= t("Price") ?></th>
                    <th class="text-right"><?= t("Quantity") ?></th>
                    <th class="text-right"><?= t("Subtotal") ?></th>
                </tr>
                </thead>
                <tbody>
                <?php
                $items = $order->getOrderItems();

                if ($items) {
                    foreach ($items as $item) {
                        ?>
                        <tr>
                            <td><?= h($item->getProductName())?>
                                <?php if ($sku = $item->getSKU()) {
                                    echo '(' .  h($sku) . ')';
                                } ?>
                            </td>
                            <td>
                                <?php
                                $options = $item->getProductOptions();
                                if ($options) {
                                    echo "<ul class='list-unstyled'>";
                                    foreach ($options as $option) {
                                        if ( $option['oioValue']) { ?>
                                            <li><strong><?= h($option['oioKey']); ?></strong> <?= h($option['oioValue']); ?></li>
                                        <?php }
                                    }
                                    echo "</ul>";
                                }
                                ?>
                            </td>
                            <td class="text-right"><?=StorePrice::format($item->getPricePaid()) ?></td>
                            <td class="text-right"><?= $item->getQuantity() ?></td>
                            <td class="text-right"><?=StorePrice::format($item->getSubTotal()) ?></td>
                        </tr>
                        <?php
                    }
                }
                ?>
                </tbody>
                <tfoot>
                <tr>
                    <td colspan="4" class="text-right"><strong><?= t("Items Subtotal")?>:</strong></td>
                    <td class="text-right" ><?= StorePrice::format($order->getSubTotal())?></td>
                </tr>
                </tfoot>
            </table>

            <?php $applieddiscounts = $order->getAppliedDiscounts();

            if (!empty($applieddiscounts)) { ?>
                <h4><?= t("Discounts Applied")?></h4>
                <table class="table table-striped">
                    <thead>
                    <tr>

                        <th><?= t("Discount")?></th>
                        <th class="text-right"><?= t("Amount")?></th>
                    </tr>

                    </thead>
                    <tbody>
                    <?php foreach($applieddiscounts as $discount) { ?>
                        <tr>
                            <td><?= h($discount['odDisplay']); ?></td>
                            <td class="text-right"><?= ($discount['odValue'] > 0 ? StorePrice::format($discount['odValue']) : $discount['odPercentage'] . '%' ); ?></td>
                        </tr>
                    <?php } ?>

                    </tbody>
                </table>

            <?php } ?>
        </fieldset>

        <?php if ($order->isShippable()) { ?>
            <p>
                <strong><?= t("Shipping")?>: </strong><?= StorePrice::format($order->getShippingTotal())?>
            </p>
        <?php } ?>


        <?php $taxes = $order->getTaxes();

        if (!empty($taxes)) { ?>
            <p>
                <?php foreach ($order->getTaxes() as $tax) { ?>
                    <strong><?= h($tax['label']) ?>
                        :</strong> <?= StorePrice::format($tax['amount'] ? $tax['amount'] : $tax['amountIncluded']) ?><br>
                <?php } ?>
            </p>
        <?php } ?>


        <p>
            <strong><?= t("Grand Total") ?>: </strong><?= StorePrice::format($order->getTotal()) ?>
        </p>
        <p>
            <strong><?= t("Payment Method") ?>: </strong><?= t($order->getPaymentMethodName()) ?><br>
            <?php $transactionReference = $order->getTransactionReference();
            if ($transactionReference) { ?>
                <strong><?= t("Transaction Reference") ?>: </strong><?= $transactionReference ?><br>
            <?php } ?>
        </p>


        <?php if ($order->isShippable()) { ?>
            <br/><p>
                <strong><?= t("Shipping Method") ?>: </strong><?= $order->getShippingMethodName() ?>
            </p>

            <?php
            $shippingInstructions = $order->getShippingInstructions();
            if ($shippingInstructions) { ?>
                <p><strong><?= t("Delivery Instructions") ?>: </strong><?= h($shippingInstructions) ?></p>
            <?php } ?>

        <?php } ?>
    </div>
</div>
</body>
</html>
