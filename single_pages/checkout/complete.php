<?php defined('C5_EXECUTE') or die("Access Denied."); ?>
<?php

use Concrete\Core\Support\Facade\Config;
use Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Price;

$app = \Concrete\Core\Support\Facade\Application::getFacadeApplication();
$dh = $app->make('helper/date');
?>

<div class="store-order-complete-page">

    <h1><?= t("Order #%s has been placed", $order->getOrderID()); ?></h1>
    <p><?= t("Thank you for your order. A receipt will be emailed to you shortly."); ?></p>

    <?php
    $a = new Area('Checkout Complete Header');
    $a->display();
    ?>

    <br>

    <?php
    $downloads = [];
    $orderItems = $order->getOrderItems();
    foreach ($orderItems as $item) {
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
        <fieldset>
            <legend><?= t("Your Downloads"); ?></legend>
            <ul class="order-downloads">
                <?php
                foreach ($downloads as $name => $file) {
                    echo '<li><a href="' . \Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Download::buildDownloadURL($file, $order) . '">' . $name . '</a></li>';
                } ?>
            </ul>
        </fieldset>
        <br>
        <?php
    }

    /*
     *  The Order object is loaded should we wish to place receipt details here.
     *  Example:
     *  echo $order->getTaxTotal()
     *  echo $order->getShippingTotal()
     *  echo $order->getTotal()
     *
     *  $orderItems = $order->getOrderItems();
     *  foreach($orderItems as $item){
     *      echo $item->getProductName();
     *      echo $item->getQuantity();
     *      echo $item->getPricePaid();
     *  }
     *
     */
    ?>

    <fieldset>
        <legend><?= t("Your Details"); ?></legend>

        <div class="row">
            <div class="col-sm-4">
                <?php $orderemail = $order->getAttribute("email"); ?>

                <p><strong><?= t("Name"); ?>:</strong> <?= $order->getAttribute("billing_first_name") . " " . $order->getAttribute("billing_last_name"); ?><br>

                    <?php if ($orderemail) {
                        ?>
                        <strong><?= t("Email"); ?>:</strong> <a href="mailto:<?= $order->getAttribute("email"); ?>"><?= $order->getAttribute("email"); ?></a><br>
                        <?php
                    } ?>

                    <?php
                    $phone = $order->getAttribute("billing_phone");
                    if ($phone) {
                        ?>
                        <strong><?= t('Phone'); ?>:</strong> <?= $phone; ?><br>
                        <?php
                    } ?>

                    <?php if (Config::get('community_store.vat_number')) {
                        ?>
                        <?php $vat_number = $order->getAttribute('vat_number'); ?>
                        <strong><?= t("VAT Number"); ?>:</strong> <?= $vat_number; ?>
                        <?php
                    } ?>
                </p>

                <?php if (!empty($orderChoicesAttList)) {
                    ?>
                    <?php
                    foreach ($orderChoicesAttList as $ak) {
                        $attValue = $order->getAttributeValueObject($ak->getAttributeKeyHandle());

                        if ($attValue) {
                            ?>
                            <h4><?= $ak->getAttributeKeyDisplayName(); ?></h4>
                            <p><?= str_replace("\r\n", "<br>", $attValue->getValue('displaySanitized', 'display')); ?></p>
                            <?php
                        } ?>
                        <?php
                    } ?>
                    <?php
                } ?>
            </div>

            <div class="col-sm-4">
                <h4><?= t("Billing Address"); ?></h4>
                <p>
                    <?= h($order->getAttribute("billing_first_name")) . " " . h($order->getAttribute("billing_last_name")); ?><br>
                    <?php $billingaddress = $order->getAttributeValueObject('billing_address');
                    if ($billingaddress) {
                        echo $billingaddress->getValue('displaySanitized', 'display');
                    }
                    ?>
                </p>

                <?php $billingcompany = $order->getAttribute("billing_company"); ?>
                <?php if ($billingcompany) {
                    ?>
                    <h4><?= t("Company"); ?></h4>
                    <p>
                        <?= h($billingcompany); ?>
                    </p>
                    <?php
                } ?>
            </div>
            <?php if ($order->isShippable()) {
                ?>
                <div class="col-sm-4">
                    <?php if ($order->getAttribute("shipping_address")->address1) {
                        ?>
                        <h4><?= t("Shipping Address"); ?></h4>
                        <p>
                            <?= $order->getAttribute("shipping_first_name") . " " . $order->getAttribute("shipping_last_name"); ?><br>
                            <?php $shippingaddress = $order->getAttributeValueObject('shipping_address');
                            if ($shippingaddress) {
                                echo $shippingaddress->getValue('displaySanitized', 'display');
                            } ?>
                        </p>

                        <?php $shippingcompany = $order->getAttribute("shipping_company"); ?>
                        <?php if ($shippingcompany) {
                            ?>
                            <h4><?= t("Company"); ?></h4>
                            <p>
                                <?= h($shippingcompany); ?>
                            </p>
                            <?php
                        } ?>
                        <?php
                    } ?>
                </div>
                <?php
            } ?>
        </div>

    </fieldset>
    <br>
    <fieldset>
        <legend><?= t("Order Items"); ?></legend>
        <table class="table table-striped">
            <thead>
            <tr>
                <th><strong><?= t("Product Name"); ?></strong></th>
                <th><?= t("Product Options"); ?></th>
                <th class="text-right"><?= t("Price"); ?></th>
                <th class="text-right"><?= t("Quantity"); ?></th>
                <th class="text-right"><?= t("Subtotal"); ?></th>
            </tr>
            </thead>
            <tbody>
            <?php
            $items = $order->getOrderItems();

            if ($items) {
                foreach ($items as $item) {
                    ?>
                    <tr>
                        <td><?= h($item->getProductName()); ?>
                            <?php if ($sku = $item->getSKU()) {
                                echo '(' . h($sku) . ')';
                            } ?>
                        </td>
                        <td>
                            <?php
                            $options = $item->getProductOptions();
                            if ($options) {
                                echo "<ul class='list-unstyled'>";
                                foreach ($options as $option) {
                                    if ($option['oioValue']) {
                                        ?>
                                        <li><strong><?= h($option['oioKey']); ?></strong> <?= h($option['oioValue']); ?></li>
                                        <?php
                                    }
                                }
                                echo "</ul>";
                            } ?>
                        </td>
                        <td class="text-right"><?= Price::format($item->getPricePaid()); ?></td>
                        <td class="text-right"><?= $item->getQuantity(); ?> <?= h($item->getQuantityLabel()); ?></td>
                        <td class="text-right"><?= Price::format($item->getSubTotal()); ?></td>
                    </tr>
                    <?php
                }
            }
            ?>
            </tbody>
            <tfoot>
            <tr>
                <td colspan="4" class="text-right"><strong><?= t("Items Subtotal"); ?>:</strong></td>
                <td class="text-right"><?= Price::format($order->getSubTotal()); ?></td>
            </tr>
            </tfoot>
        </table>


        <?php $applieddiscounts = $order->getAppliedDiscounts();

        if (!empty($applieddiscounts)) {
            ?>
            <h4><?= t("Discounts Applied"); ?></h4>
            <table class="table table-striped">
                <thead>
                <tr>

                    <th><?= t("Discount"); ?></th>
                    <th><?= t("Amount"); ?></th>
                </tr>

                </thead>
                <tbody>
                <?php foreach ($applieddiscounts as $discount) {
                    ?>
                    <tr>
                        <td><?= h($discount['odDisplay']); ?></td>
                        <td><?= ($discount['odValue'] > 0 ? Price::format($discount['odValue']) : \Punic\Number::formatPercent($discount['odPercentage'] / 100)); ?></td>
                    </tr>
                    <?php
                } ?>

                </tbody>
            </table>
            <?php
        } ?>

        <?php if ($order->isShippable()) {
            ?>
            <p>
                <strong><?= t("Shipping"); ?>: </strong><?= Price::format($order->getShippingTotal()); ?>
            </p>
            <?php
        } ?>


        <?php $taxes = $order->getTaxes();

        if (!empty($taxes)) {
            ?>
            <p>
                <?php foreach ($order->getTaxes() as $tax) {
                    ?>
                    <strong><?= h($tax['label']); ?>
                        :</strong> <?= Price::format($tax['amount'] ? $tax['amount'] : $tax['amountIncluded']); ?><br>
                    <?php
                } ?>
            </p>
            <?php
        } ?>


        <p>
            <strong><?= t("Grand Total"); ?>: </strong><?= Price::format($order->getTotal()); ?>
        </p>
        <p>
            <strong><?= t("Payment Method"); ?>: </strong><?= t($order->getPaymentMethodName()); ?><br>
        </p>

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
                $status = t('Pending');
            }
        }
        ?>

        <?php if ($status) {
            ?>
            <p><strong><?= t("Payment Status"); ?></strong>: <?= $status; ?></p>
            <?php
        } ?>


        <?php if ($order->isShippable()) {
            ?>
            <br/><p>
                <strong><?= t("Shipping Method"); ?>: </strong><?= $order->getShippingMethodName(); ?>
            </p>

            <?php
            $shippingInstructions = $order->getShippingInstructions();
            if ($shippingInstructions) {
                ?>
                <p><strong><?= t("Delivery Instructions"); ?>: </strong><?= h($shippingInstructions); ?></p>
                <?php
            } ?>

            <?php
        } ?>

        <?php
        $notes = $order->getNotes();
        if ($notes) { ?>
            <p><strong><?= t("Order notes") ?>: </strong><?= nl2br(h($notes)) ?></p>
        <?php } ?>
    </fieldset>


    <?php
    $a = new Area('Checkout Complete Footer');
    $a->display();
    ?>

</div>

<?php if ($refreshCheck) { ?>
    <script>
        setTimeout(function () {
            window.location.reload(1);
        }, 2000);
    </script>
<?php } ?>

<?php
// uncomment the following to output a gtag purchase event. Ensure you have include a 'global site tag' (gtag.js) before enabling this.
// \Concrete\Core\View\View::element("checkout/gtag", ['order' => $order, 'orderItems' => $orderItems], 'community_store'); ?>

