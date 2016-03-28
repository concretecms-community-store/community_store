<?php
defined('C5_EXECUTE') or die("Access Denied.");
$dh = Core::make('helper/date');
use \Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Price as Price;
use \Concrete\Package\CommunityStore\Src\Attribute\Key\StoreOrderKey as StoreOrderKey;
?>

<?php if ($controller->getTask() == 'order'){ ?>

    <div class="ccm-dashboard-header-buttons">
        <form action="<?=URL::to('/dashboard/store/orders/details/slip')?>" method="post" target="_blank">
            <input type="hidden" name="oID" value="<?= $order->getOrderID()?>">
            <button class="btn btn-primary"><?= t("Print Order Slip")?></button>
        </form>
    </div>

    <h3><?= t("Customer Overview")?></h3>
    <hr>
    <div class="row">
        <div class="col-sm-12">
            <?php $orderemail = $order->getAttribute("email");

            if ($orderemail) { ?>
            <h4><?= t("Email")?></h4>
            <p><a href="mailto:<?= $order->getAttribute("email"); ?>"><?= $order->getAttribute("email"); ?></a></p>
            <?php } ?>

            <?php
            $ui = UserInfo::getByID($order->getCustomerID());
            if ($ui) { ?>
            <h4><?= t("User")?></h4>
            <p><a href="<?= \URL::to('/dashboard/users/search/view/' . $ui->getUserID());?>"><?= $ui->getUserName(); ?></a></p>
            <?php } ?>
        </div>

        <div class="col-sm-6">
            <h4><?= t("Billing Information")?></h4>
            <p>
                <?= $order->getAttribute("billing_first_name"). " " . $order->getAttribute("billing_last_name")?><br>
                <?php $billingaddress = $order->getAttributeValueObject(StoreOrderKey::getByHandle('billing_address'));
                if ($billingaddress) {
                    echo $billingaddress->getValue('displaySanitized', 'display');
                }
                ?>
                <br /> <br /><?= t('Phone'); ?>: <?= $order->getAttribute("billing_phone")?>
            </p>
        </div>

        <?php if ($order->isShippable()) { ?>
            <div class="col-sm-6">
                <?php if ($order->getAttribute("shipping_address")->address1) { ?>
                    <h4><?= t("Shipping Information")?></h4>
                    <p>
                        <?= $order->getAttribute("shipping_first_name"). " " . $order->getAttribute("shipping_last_name")?><br>
                        <?php $shippingaddress = $order->getAttributeValueObject(StoreOrderKey::getByHandle('shipping_address'));
                        if ($shippingaddress) {
                            echo $shippingaddress->getValue('displaySanitized', 'display');
                        }
                        ?>
                    </p>
                <?php } ?>
            </div>
        <?php } ?>

    </div>

    <div class="row">
        <?php if ($orderChoicesEnabled) { ?>
            <div class="col-sm-12">
                <h4><?= t("Other Choices")?></h4>
                <?php foreach ($orderChoicesAttList as $ak) { ?>
                    <label><?= $ak->getAttributeKeyDisplayName()?></label>
                    <p><?php echo $order->getAttributeValueObject(StoreOrderKey::getByHandle($ak->getAttributeKeyHandle()))->getValue('displaySanitized', 'display'); ?></p>
                    <?php //echo $ak->getAttributeType()->render('view', $ak); ?>
                <?php } ?>
            </div>
        <?php } ?>
    </div>

    <h3><?= t("Order Info")?></h3>
    <hr>
    <table class="table table-striped">
        <thead>
            <tr>
                <th><strong><?= t("Product Name")?></strong></th>
                <th><?= t("Product Options")?></th>
                <th><?= t("Price")?></th>
                <th><?= t("Quantity")?></th>
                <th><?= t("Subtotal")?></th>
            </tr>
        </thead>
        <tbody>
            <?php
                $items = $order->getOrderItems();

                if($items){
                    foreach($items as $item){
              ?>
                <tr>
                    <td><?= $item->getProductName()?>
                    <?php if ($sku = $item->getSKU()) {
                    echo '(' .  $sku . ')';
                     } ?>
                    </td>
                    <td>
                        <?php
                            $options = $item->getProductOptions();
                            if($options){
                                echo "<ul class='list-unstyled'>";
                                foreach($options as $option){
                                    echo "<li>";
                                    echo "<strong>".$option['oioKey'].": </strong>";
                                    echo $option['oioValue'];
                                    echo "</li>";
                                }
                                echo "</ul>";
                            }
                        ?>
                    </td>
                    <td><?=Price::format($item->getPricePaid())?></td>
                    <td><?= $item->getQty()?></td>
                    <td><?=Price::format($item->getSubTotal())?></td>
                </tr>
              <?php
                    }
                }
            ?>
        </tbody>
        <tfoot>
        <tr>
            <td colspan="4" class="text-right"><strong><?= t("Items Subtotal")?>:</strong></td>
            <td colspan="1" ><?=Price::format($order->getSubTotal())?></td>
        </tr>
        </tfoot>
    </table>


     <?php if ($order->isShippable()) { ?>
     <p>
        <strong><?= t("Shipping")?>: </strong><?=Price::format($order->getShippingTotal())?>
     </p>
        <?php } ?>


    <?php $applieddiscounts = $order->getAppliedDiscounts();

    if (!empty($applieddiscounts)) { ?>
        <h4><?= t("Discounts Applied")?></h4>
        <hr />
        <table class="table table-striped">
            <thead>
            <tr>
                <th><strong><?= t("Name")?></strong></th>
                <th><?= t("Displayed")?></th>
                <th><?= t("Deducted From")?></th>
                <th><?= t("Amount")?></th>
                <th><?= t("Triggered")?></th>
            </tr>

            </thead>
            <tbody>
            <?php foreach($applieddiscounts as $discount) { ?>
                <tr>
                    <td><?= h($discount['odName']); ?></td>
                    <td><?= h($discount['odDisplay']); ?></td>
                    <td><?= h($discount['odDeductFrom']); ?></td>
                    <td><?= ($discount['odValue'] > 0 ? $discount['odValue'] : $discount['odPercentage'] . '%' ); ?></td>
                    <td><?= ($discount['odCode'] ? t('by code'). ' <em>' .$discount['odCode'] .'</em>': t('Automatically') ); ?></td>
                </tr>
            <?php } ?>

            </tbody>
        </table>

    <?php } ?>

    <?php $taxes = $order->getTaxes();

    if (!empty($taxes)) { ?>
        <p>
            <?php foreach ($order->getTaxes() as $tax) { ?>
                <strong><?= $tax['label'] ?>
                    :</strong> <?= Price::format($tax['amount'] ? $tax['amount'] : $tax['amountIncluded']) ?><br>
            <?php } ?>
        </p>
    <?php } ?>

    <p>
        <strong><?= t("Grand Total") ?>: </strong><?= Price::format($order->getTotal()) ?>
    </p>
    <p>
        <strong><?= t("Payment Method") ?>: </strong><?= $order->getPaymentMethodName() ?><br>
        <?php $transactionReference = $order->getTransactionReference();
        if ($transactionReference) { ?>
            <strong><?= t("Transaction Reference") ?>: </strong><?= $transactionReference ?><br>
        <?php } ?>
    </p>

    <?php if ($order->isShippable()) { ?>
        <p>
            <strong><?= t("Shipping Method") ?>: </strong><?= $order->getShippingMethodName() ?>
        </p>
    <?php } ?>

    <br />
    <h3><?= t("Order Status History")?></h3>
    <hr>
    <div class="row">
        <div class="col-sm-4">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title"><?= t("Update Status")?></h4>
                </div>
                <div class="panel-body">

                    <form action="<?=URL::to("/dashboard/store/orders/updatestatus",$order->getOrderID())?>" method="post">
                        <div class="form-group">
                            <?= $form->select("orderStatus",$orderStatuses,$order->getStatus());?>
                        </div>
                        <input type="submit" class="btn btn-default" value="<?= t("Update")?>">
                    </form>

                </div>
            </div>
        </div>
        <div class="col-sm-8">
            <table class="table table-striped">
                <thead>
                <tr>
                    <th><strong><?= t("Status")?></strong></th>
                    <th><?= t("Date")?></th>
                    <th><?= t("User")?></th>
                </tr>
                </thead>
                <tbody>
                <?php
                $history = $order->getStatusHistory();
                if($history){
                    foreach($history as $status){
                        ?>
                        <tr>
                            <td><?= $status->getOrderStatusName()?></td>
                            <td><?= $status->getDate()?></td>
                            <td><?= $status->getUserName()?></td>
                        </tr>
                    <?php
                    }
                }
                ?>
                </tbody>
            </table>
        </div>

    </div>

    <h3><?= t("Manage Order")?></h3>
    <hr>
    <div class="row">
        <div class="col-sm-4">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title"><?= t("Order Options")?></h4>
                </div>
                <div class="panel-body">

                    <a data-confirm-message="<?= h(t('Are you sure you wish to delete this order?')); ?>" id="btn-delete-order" href="<?=URL::to("/dashboard/store/orders/remove", $order->getOrderID())?>" class="btn btn-danger"><?= t("Delete Order")?></a>

                </div>
            </div>
        </div>
    </div>


<?php } else { ?>

    <div class="ccm-dashboard-header-buttons">
    </div>

    <?php if ($shoppingDisabled) { ?>
        <p class="alert alert-warning text-center"><?php echo t('Cart and Ordering features are currently disabled. This setting can be changed via the');?> <a href="<?= \URL::to('/dashboard/store/settings#settings-checkout'); ?>"><?= t('settings page.');?></a></p>
    <?php } ?>

<div class="ccm-dashboard-content-full">
    <form role="form" class="form-inline ccm-search-fields">
        <div class="ccm-search-fields-row">
            <?php if($statuses){?>
                <ul id="group-filters" class="nav nav-pills">
                    <li><a href="<?= \URL::to('/dashboard/store/orders/')?>"><?= t('All Statuses')?></a></li>
                    <?php foreach($statuses as $status){ ?>
                        <li><a href="<?= \URL::to('/dashboard/store/orders/', $status->getHandle())?>"><?= $status->getName();?></a></li>
                    <?php } ?>
                </ul>
            <?php } ?>
        </div>


        <div class="ccm-search-fields-row ccm-search-fields-submit">
            <div class="form-group">
                <div class="ccm-search-main-lookup-field">
                    <i class="fa fa-search"></i>
                    <?= $form->search('keywords', $searchRequest['keywords'], array('placeholder' => t('Search Orders')))?>
                </div>
            </div>
            <button type="submit" class="btn btn-primary pull-right"><?= t('Search')?></button>

        </div>

    </form>

    <table class="ccm-search-results-table">
        <thead>
            <th><a><?= t("Order %s","#")?></a></th>
            <th><a><?= t("Customer Name")?></a></th>
            <th><a><?= t("Order Date")?></a></th>
            <th><a><?= t("Total")?></a></th>
            <th><a><?= t("Status")?></a></th>
            <th><a><?= t("View")?></a></th>
        </thead>
        <tbody>
            <?php
                foreach($orderList as $order){
            ?>
                <tr>
                    <td><a href="<?=URL::to('/dashboard/store/orders/order/',$order->getOrderID())?>"><?= $order->getOrderID()?></a></td>
                    <td><?= $order->getAttribute('billing_last_name').", ".$order->getAttribute('billing_first_name')?></td>
                    <td><?= $dh->formatDateTime($order->getOrderDate())?></td>
                <td><?=Price::format($order->getTotal())?></td>
                    <td><?=ucwords($order->getStatus())?></td>
                    <td><a class="btn btn-primary" href="<?=URL::to('/dashboard/store/orders/order/',$order->getOrderID())?>"><?= t("View")?></a></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<?php if ($paginator->getTotalPages() > 1) { ?>
    <?= $pagination ?>
<?php } ?>

<?php } ?>

<style>
    @media (max-width: 992px) {
        div#ccm-dashboard-content div.ccm-dashboard-content-full {
            margin-left: -20px !important;
            margin-right: -20px !important;
        }
    }
</style>