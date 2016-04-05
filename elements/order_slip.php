<?php
defined('C5_EXECUTE') or die("Access Denied.");
use \Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Price as Price;
use \Concrete\Package\CommunityStore\Src\Attribute\Key\StoreOrderKey as StoreOrderKey;

?>
<link href="/concrete/css/app.css" rel="stylesheet" type="text/css" media="all">
<div class="ccm-ui">
    <div class="container">
        <h3><?= t("Customer Overview") ?></h3>
        <hr>

        <div class="row">
            <div class="col-xs-12">
                <?php $orderemail = $order->getAttribute("email");

                if ($orderemail) { ?>
                    <h4><?= t("Email") ?></h4>
                    <p><a href="mailto:<?= $order->getAttribute("email"); ?>"><?= $order->getAttribute("email"); ?></a>
                    </p>
                <?php } ?>

                <?php
                $ui = UserInfo::getByID($order->getCustomerID());
                if ($ui) { ?>
                    <h4><?= t("User") ?></h4>
                    <p>
                        <a href="<?= \URL::to('/dashboard/users/search/view/' . $ui->getUserID()); ?>"><?= $ui->getUserName(); ?></a>
                    </p>
                <?php } ?>
            </div>

            <div class="col-xs-6">
                <h4><?= t("Billing Information") ?></h4>

                <p>
                    <?= $order->getAttribute("billing_first_name") . " " . $order->getAttribute("billing_last_name") ?>
                    <br>
                    <?= $order->getAttributeValueObject(StoreOrderKey::getByHandle('billing_address'))->getValue('displaySanitized', 'display'); ?>
                    <br/> <br/><?= t('Phone'); ?>: <?= $order->getAttribute("billing_phone") ?>
                </p>
            </div>
            <div class="col-xs-6">
                <?php if ($order->getAttribute("shipping_address")->address1) { ?>
                    <h4><?= t("Shipping Information") ?></h4>
                    <p>
                        <?= $order->getAttribute("shipping_first_name") . " " . $order->getAttribute("shipping_last_name") ?>
                        <br>
                        <?= $order->getAttributeValueObject(StoreOrderKey::getByHandle('shipping_address'))->getValue('displaySanitized', 'display'); ?>
                    </p>
                <?php } ?>
            </div>

            <?php if ($orderChoicesEnabled) { ?>
                <div class="col-xs-12">
                    <h4><?= t("Other Choices")?></h4>
                    <?php foreach ($orderChoicesAttList as $ak) { ?>
                        <label><?= $ak->getAttributeKeyDisplayName()?></label>
                        <p><?php echo "hej"; ?>
                            <?php echo $order->getAttributeValueObject(StoreOrderKey::getByHandle($ak->getAttributeKeyHandle()))->getValue(); ?>
                            <?php // echo $order->getAttributeValueObject(StoreOrderKey::getByHandle($ak->getAttributeKeyHandle()))->getValue(); ?>
                        </p>
                    <?php } ?>
                </div>
            <?php } ?>

        </div>

        <h3><?= t("Order Info") ?></h3>
        <hr>
        <table class="table table-striped">
            <thead>
            <tr>
                <th><strong><?= t("Product Name") ?></strong></th>
                <th><?= t("Product Options") ?></th>
                <th><?= t("Price") ?></th>
                <th><?= t("Quantity") ?></th>
                <th><?= t("Subtotal") ?></th>
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
                        <td><?= Price::format($item->getPricePaid()) ?></td>
                        <td><?= $item->getQty() ?></td>
                        <td><?= Price::format($item->getSubTotal()) ?></td>
                    </tr>
                    <?php
                }
            }
            ?>
            </tbody>
        </table>

        <p>
            <strong><?= t("Subtotal") ?>: </strong><?= Price::format($order->getSubTotal()) ?><br>
            <?php foreach ($order->getTaxes() as $tax) { ?>
                <strong><?= $tax['label'] ?>:</strong> <?= Price::format($tax['amount'] ? $tax['amount'] : $tax['amountIncluded']) ?><br>
            <?php } ?>
            <strong><?= t("Shipping") ?>: </strong><?= Price::format($order->getShippingTotal()) ?><br>

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

            <strong><?= t("Grand Total") ?>: </strong><?= Price::format($order->getTotal()) ?>
        </p>

        <p>
            <strong><?= t("Payment Method") ?>: </strong><?= t($order->getPaymentMethodName()) ?><br>
            <?php if ($order->isShippable()) { ?>
            <strong><?= t("Shipping Method") ?>: </strong><?= $order->getShippingMethodName() ?>
            <?php } ?>
        </p>

    </div>
</div>
