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



<div class="row">
    <div class="col-sm-8">
        <p><strong><?= t('Order placed'); ?>:</strong> <?= $dh->formatDateTime($order->getOrderDate())?></p>
     </div>
    <div class="col-sm-4">
    <?php
    $refunded = $order->getRefunded();
    $paid = $order->getPaid();
    $cancelled = $order->getCancelled();

    if ($cancelled) {
        echo '<p class="alert alert-danger text-center"><strong>' . t('Cancelled') . '</strong></p>';
    } else {
        if ($refunded) {
            $refundreason = $order->getRefundReason();
            echo '<p class="alert alert-warning text-center"><strong>' . t('Refunded') . ($refundreason ? ' - ' .$refundreason : '') . '</strong></p>';
        } elseif ($paid) {
            echo '<p class="alert alert-success text-center"><strong>' . t('Paid') . '</strong></p>';
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
    <legend><?= t("Customer Details")?></legend>

    <div class="row">
        <div class="col-sm-4">
            <?php $orderemail = $order->getAttribute("email"); ?>

            <h4><?= t("Name")?></h4>
            <p><?= $order->getAttribute("billing_first_name"). " " . $order->getAttribute("billing_last_name")?></p>

            <?php if ($orderemail) { ?>
            <h4><?= t("Email")?></h4>
            <p><a href="mailto:<?= $order->getAttribute("email"); ?>"><?= $order->getAttribute("email"); ?></a></p>
            <?php } ?>

            <?php
            $phone = $order->getAttribute("billing_phone");
            if ($phone) {
            ?>
            <h4><?= t('Phone'); ?></h4>
            <p><?= $phone; ?></p>
            <?php } ?>

            <?php
            $ui = UserInfo::getByID($order->getCustomerID());
            if ($ui) { ?>
            <h4><?= t("User")?></h4>
            <p><a href="<?= \URL::to('/dashboard/users/search/view/' . $ui->getUserID());?>"><?= $ui->getUserName(); ?></a></p>
            <?php } ?>
        </div>

        <div class="col-sm-4">
            <h4><?= t("Billing Address")?></h4>
            <p>
                <?= $order->getAttribute("billing_first_name"). " " . $order->getAttribute("billing_last_name")?><br>
                <?php $billingaddress = $order->getAttributeValueObject(StoreOrderKey::getByHandle('billing_address'));
                if ($billingaddress) {
                    echo $billingaddress->getValue('displaySanitized', 'display');
                }
                ?>
            </p>
        </div>
        <?php if ($order->isShippable()) { ?>
            <div class="col-sm-4">
                <?php if ($order->getAttribute("shipping_address")->address1) { ?>
                    <h4><?= t("Shipping Address")?></h4>
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
    </fieldset>

    <fieldset>
    <legend><?= t("Order Items")?></legend>
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
                                    echo ($option['oioValue'] ? $option['oioValue'] : '<em>' .t('None') . '</em>');
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
                    <td><?= t(ucwords($discount['odDeductFrom'])); ?></td>
                    <td><?= ($discount['odValue'] > 0 ? Price::format($discount['odValue']) : $discount['odPercentage'] . '%' ); ?></td>
                    <td><?= ($discount['odCode'] ? t('by code'). ' <em>' .$discount['odCode'] .'</em>': t('Automatically') ); ?></td>
                </tr>
            <?php } ?>

            </tbody>
        </table>

    <?php } ?>

    <?php if ($order->isShippable()) { ?>
    <p>
        <strong><?= t("Shipping")?>: </strong><?=Price::format($order->getShippingTotal())?>
    </p>
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
        <strong><?= t("Payment Method") ?>: </strong><?= t($order->getPaymentMethodName()) ?><br>
        <?php $transactionReference = $order->getTransactionReference();
        if ($transactionReference) { ?>
            <strong><?= t("Transaction Reference") ?>: </strong><?= $transactionReference ?><br>
        <?php } ?>
    </p>

    <?php if ($order->isShippable()) { ?>
        <br /><p>
            <strong><?= t("Shipping Method") ?>: </strong><?= $order->getShippingMethodName() ?>
        </p>



        <?php
        $trackingURL = $order->getTrackingURL();
        $trackingCode = $order->getTrackingCode();
        $carrier = $order->getCarrier();

        if ($carrier) { ?>
            <p><strong><?= t("Carrier") ?>: </strong><?= $carrier ?></p>
        <?php }

        if ($trackingCode) { ?>
            <p><strong><?= t("Tracking Code") ?>: </strong><?= $trackingCode ?> </p>
        <?php }

        if ($trackingURL) { ?>
        <p><a target="_blank" href="<?= $trackingURL; ?>"><?= t('View shipment tracking');?></a></p>
        <?php } ?>

        <?php
        $shippingInstructions = $order->getShippingInstructions();
        if ($shippingInstructions) { ?>
            <p><strong><?= t("Delivery Instructions") ?>: </strong><?= $shippingInstructions ?></p>
        <?php } ?>

    <?php } ?>

     <div class="row">
        <?php if (!empty($orderChoicesAttList)) { ?>
            <div class="col-sm-12">
                <h4><?= t("Other Choices")?></h4>
                <?php foreach ($orderChoicesAttList as $ak) {
                    $attValue = $order->getAttributeValueObject(StoreOrderKey::getByHandle($ak->getAttributeKeyHandle()));
                    if ($attValue) {  ?>
                    <label><?= $ak->getAttributeKeyDisplayName()?></label>
                    <p><?= str_replace("\r\n", "<br>", $attValue->getValue('displaySanitized', 'display')); ?></p>
                    <?php } ?>
                <?php } ?>
            </div>
        <?php } ?>
    </div>


    </fieldset>
    <br />

    <div class="row">
        <div class="col-sm-6">
            <fieldset>
            <legend><?= t("Fulfilment")?></legend>
            <table class="table table-striped">
                <thead>
                <tr>
                    <th><?= t("Status")?></th>
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
                            <td><?= t($status->getOrderStatusName())?></td>
                            <td><?= $status->getDate()?></td>
                            <td><?= $status->getUserName()?></td>
                        </tr>
                        <?php
                    }
                }
                ?>
                </tbody>
            </table>
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title"><?= t("Update Fulfilment Status")?></h4>
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
            </fieldset>
        </div>
        <div class="col-sm-6">
            <fieldset>
            <legend><?= t("Payment Status")?></legend>

            <?php  if($order->getTotal() == 0) { ?>
            <p><?= t('Free Order');?></p>
            <?php } else {
            if (!$paid) { ?>
            <p class="text-danger"><?= t('Unpaid');?></p>
            <?php } ?>

            <?php if ($paid || $refunded) { ?>
            <table class="table table-striped">
                <thead>
                <tr>
                    <th><?= t("Status")?></th>
                    <th><?= t("Date / Reference")?></th>
                    <th><?= t("By")?></th>
                    <th></th>
                </tr>
                </thead>
                <tbody>

                <?php if ($paid) { ?>
                    <tr>
                        <td><?= t('Paid')?>
                        </td>
                        <td><?= $dh->formatDateTime($paid)?>
                            <br />
                            <?= t('Ref') . ':'?> <?= $order->getTransactionReference() ; ?>
                        </td>
                        <td>
                        <?php $paiduser = User::getByUserID($order->getPaidByUID());
                            if ($paiduser) {
                                echo $paiduser->getUserName();
                            } else {
                                echo t('payment');
                            }
                        ?></td>
                        <td><?php if (!$refunded && $paiduser) { ?>

                         <form action="<?=URL::to("/dashboard/store/orders/reversepaid",$order->getOrderID())?>" method="post">
                            <input data-confirm-message="<?= h(t('Are you sure you wish to reverse this payment?')); ?>" type="submit" class="confirm-action btn-link" value="<?= t("reverse")?>">
                         </form>

                        <?php } ?></td>

                    </tr>
                 <?php } ?>

                 <?php if ($refunded) { ?>
                    <tr>
                        <td><?= t('Refunded')?></td>
                        <td><?= $dh->formatDateTime($refunded)?><br />
                        <?= $order->getRefundReason(); ?>
                        </td>
                        <td>
                        <?php $refundeduser = User::getByUserID($order->getRefundedByUID());
                            if ($refundeduser) {
                                echo $refundeduser->getUserName();
                            }
                        ?></td>
                        <td>

                         <form action="<?=URL::to("/dashboard/store/orders/reverserefund",$order->getOrderID())?>" method="post">
                            <input data-confirm-message="<?= h(t('Are you sure you wish to reverse this refund?')); ?>" type="submit" class="confirm-action btn-link" value="<?= t("reverse")?>">
                         </form>

                        </td>

                    </tr>
                 <?php } ?>

                </tbody>
            </table>
             <?php } ?>

           <?php if (!$paid || !$refunded) { ?>
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title"><?= t("Update Payment Status")?></h4>
                </div>
                <div class="panel-body">

                    <?php if (!$paid) { ?>
                    <form action="<?=URL::to("/dashboard/store/orders/markpaid",$order->getOrderID())?>" method="post">
                       <div class="form-group">
                       <label for="transactionReference"><?= t('Transaction Reference'); ?></label>
                       <input type="text" class="form-control ccm-input-text" id="transactionReference" name="transactionReference" />
                       </div>
                        <input type="submit" class="btn btn-default" value="<?= t("Mark Paid")?>">
                    </form>
                    <?php } elseif (!$refunded) {  ?>
                        <form action="<?=URL::to("/dashboard/store/orders/markrefunded",$order->getOrderID())?>" method="post">
                           <div class="form-group">
                           <label for="oRefundReason"><?= t('Refund Reason'); ?></label>
                           <input type="text" class="form-control ccm-input-text" id="oRefundReason" name="oRefundReason" />
                           </div>
                            <input type="submit" class="btn btn-default" value="<?= t("Mark Refunded")?>">
                        </form>
                    <?php } ?>

                </div>
            </div>
            <?php } ?>

             <?php } ?>

                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="panel-title"><?= t("Resend Invoice Email")?></h4>
                    </div>
                    <div class="panel-body">
                        <form action="<?=URL::to("/dashboard/store/orders/resendinvoice",$order->getOrderID())?>" method="post">
                            <div class="form-group">
                                <label for="email"><?= t('Email'); ?></label>
                                <input type="text" class="form-control ccm-input-text" id="email" name="email" value="<?php echo $order->getAttribute('email');?>" />
                            </div>
                            <input type="submit" class="btn btn-default" value="<?= t("Resend Invoice")?>">
                        </form>
                    </div>
                </div>

             </fieldset>
        </div>

    </div>


     <?php if (!$order->getCancelled()) { ?>
        <fieldset>
            <legend><?= t("Cancel Order")?></legend>
            <form action="<?=URL::to("/dashboard/store/orders/markcancelled",$order->getOrderID())?>" method="post">
            <input data-confirm-message="<?= h(t('Are you sure you wish to cancel this order?')); ?>" type="submit" class="confirm-action btn btn-danger" value="<?= t("Cancel Order")?>">
            </form>
        </fieldset>
    <?php } else { ?>
     <form action="<?=URL::to("/dashboard/store/orders/reversecancel",$order->getOrderID())?>" method="post">
        <input data-confirm-message="<?= h(t('Are you sure you wish to reverse this cancellation?')); ?>" type="submit" class="confirm-action btn btn-default" value="<?= t("Reverse Cancellation")?>">
     </form>
     <br />

    <fieldset>
    <legend><?= t("Delete Order")?></legend>
        <a data-confirm-message="<?= h(t('Are you sure you wish to completely delete this order? The order number will be reused.')); ?>" id="btn-delete-order" href="<?=URL::to("/dashboard/store/orders/remove", $order->getOrderID())?>" class="btn btn-danger"><?= t("Delete Order")?></a>
    <?php } ?>
    </fieldset>


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
                    <li <?= (!$status ? 'class="active"' : ''); ?>><a href="<?= \URL::to('/dashboard/store/orders/')?>"><?= t('All Statuses')?></a></li>

                    <?php foreach($statuses as $statusoption){ ?>
                        <li <?= ($status == $statusoption->getHandle() ? 'class="active"' : ''); ?>><a href="<?= \URL::to('/dashboard/store/orders/', $statusoption->getHandle())?>"><?= t($statusoption->getName());?></a></li>
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
            <button type="submit" class="btn btn-default"><?= t('Search')?></button>

        </div>

    </form>

    <?php if (!empty($orderList)) { ?>
    <table class="ccm-search-results-table">
        <thead>
            <tr>
                <th><a><?= t("Order %s","#")?></a></th>
                <th><a><?= t("Customer Name")?></a></th>
                <th><a><?= t("Order Date")?></a></th>
                <th><a><?= t("Total")?></a></th>
                <th><a><?= t("Payment")?></a></th>
                <th><a><?= t("Fulfilment")?></a></th>
                <th><a><?= t("View")?></a></th>
            </tr>
        </thead>
        <tbody>
            <?php
                foreach($orderList as $order){

                $cancelled = $order->getCancelled();
                $canstart = '';
                $canend = '';
                if ($cancelled) {
                    $canstart = '<del>';
                    $canend = '</del>';
                }
            ?>
                <tr class="danger">
                    <td><?= $canstart; ?>
                    <a href="<?=URL::to('/dashboard/store/orders/order/',$order->getOrderID())?>"><?= $order->getOrderID()?></a><?= $canend; ?>

                    <?php if ($cancelled) {
                        echo '<span class="text-danger">' . t('Cancelled') .'</span>';
                    }
                    ?>
                    </td>
                    <td><?= $canstart; ?><?php

                   $last = $order->getAttribute('billing_last_name');
                   $first = $order->getAttribute('billing_first_name');

                   if ($last || $first ) {
                    echo $last.", ".$first;
                   } else {
                    echo '<em>' .t('Not found') . '</em>';
                   }

                    ?><?= $canend; ?></td>
                    <td><?= $canstart; ?><?= $dh->formatDateTime($order->getOrderDate())?><?= $canend; ?></td>
                    <td><?= $canstart; ?><?=Price::format($order->getTotal())?><?= $canend; ?></td>
                    <td>
                        <?php
                        $refunded = $order->getRefunded();
                        $paid = $order->getPaid();

                        if ($refunded) {
                            echo '<span class="label label-warning">' . t('Refunded') . '</span>';
                        } elseif ($paid) {
                            echo '<span class="label label-success">' . t('Paid') . '</span>';
                        } elseif ($order->getTotal() > 0) {
                            echo '<span class="label label-danger">' . t('Unpaid') . '</span>';
                        } else {
                            echo '<span class="label label-default">' . t('Free Order') . '</span>';
                        }
                        ?>
                    </td>
                    <td><?=t(ucwords($order->getStatus()))?></td>
                    <td><a class="btn btn-primary" href="<?=URL::to('/dashboard/store/orders/order/',$order->getOrderID())?>"><?= t("View")?></a></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
    <?php } ?>
</div>

<?php if (empty($orderList)) { ?>
<br /><p class="alert alert-info"><?= t('No Orders Found');?></p>
<?php } ?>

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
