<?php
defined('C5_EXECUTE') or die("Access Denied.");
$app = \Concrete\Core\Support\Facade\Application::getFacadeApplication();
$dh = $app->make('helper/date');

use \Concrete\Core\Support\Facade\Url;
use \Concrete\Core\User\UserInfoRepository;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Price;

?>

<?php if ($controller->getAction() == 'order') { ?>

    <div class="ccm-dashboard-header-buttons">
        <a href="<?= Url::to('/dashboard/store/orders/printslip/' . $order->getOrderID()) ?>" class="btn btn-primary" target="_blank"><i class="fa fa-print"></i> <?= t("Print Order Slip") ?></a>
    </div>

    <div class="row">
        <div class="col-sm-8">
            <p><strong><?= t('Order placed'); ?>:</strong> <?= $dh->formatDateTime($order->getOrderDate()) ?></p>
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
                    echo '<p class="alert alert-warning text-center"><strong>' . t('Refunded') . ($refundreason ? ' - ' . $refundreason : '') . '</strong></p>';
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
        <legend><?= t("Customer Details") ?></legend>

        <div class="row">
            <div class="col-sm-4">
                <?php $orderemail = $order->getAttribute("email"); ?>

                <h4><?= t("Name") ?></h4>
                <p><?= $order->getAttribute("billing_first_name") . " " . $order->getAttribute("billing_last_name") ?></p>

                <?php if ($orderemail) { ?>
                    <h4><?= t("Email") ?></h4>
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
                $ui = $app->make(UserInfoRepository::class)->getByID($order->getCustomerID());
                if ($ui) { ?>
                    <h4><?= t("User") ?></h4>
                    <p><a href="<?= Url::to('/dashboard/users/search/view/' . $ui->getUserID()); ?>"><?= $ui->getUserName(); ?></a></p>
                <?php } ?>

                <?php if (Config::get('community_store.vat_number')) { ?>
                    <?php $vat_number = $order->getAttribute('vat_number'); ?>
                    <h4><?= t("VAT Number") ?></h4>
                    <p><?= $vat_number ?></p>
                <?php } ?>
            </div>

            <div class="col-sm-4">
                <h4><?= t("Billing Address") ?></h4>
                <p>
                    <?= h($order->getAttribute("billing_first_name")) . " " . h($order->getAttribute("billing_last_name")) ?><br>
                    <?php $billingaddress = $order->getAttributeValueObject('billing_address');
                    if ($billingaddress) {
                        echo $billingaddress->getValue('displaySanitized', 'display');
                    }
                    ?>
                </p>

                <?php $billingcompany = $order->getAttribute("billing_company"); ?>
                <?php if ($billingcompany) { ?>
                    <h4><?= t("Company") ?></h4>
                    <p>
                        <?= h($billingcompany); ?>
                    </p>
                <?php } ?>
            </div>
            <?php if ($order->isShippable()) { ?>
                <div class="col-sm-4">
                    <?php if ($order->getAttribute("shipping_address")->address1) { ?>
                        <h4><?= t("Shipping Address") ?></h4>
                        <p>
                            <?= $order->getAttribute("shipping_first_name") . " " . $order->getAttribute("shipping_last_name") ?><br>
                            <?php $shippingaddress = $order->getAttributeValueObject('shipping_address');
                            if ($shippingaddress) {
                                echo $shippingaddress->getValue('displaySanitized', 'display');
                            }
                            ?>
                        </p>

                        <?php $shippingcompany = $order->getAttribute("shipping_company"); ?>
                        <?php if ($shippingcompany) { ?>
                            <h4><?= t("Company") ?></h4>
                            <p>
                                <?= h($shippingcompany); ?>
                            </p>
                        <?php } ?>
                    <?php } ?>
                </div>
            <?php } ?>
        </div>
        <?php if (!empty($orderChoicesAttList)) { ?>
            <div class="row">
                <div class="col-sm-12">
                    <?php
                    foreach ($orderChoicesAttList as $ak) {
                        $attValue = $order->getAttributeValueObject($ak);
                        if ($attValue) { ?>
                            <h4><?= $ak->getAttributeKeyDisplayName() ?></h4>
                            <p><?= str_replace("\r\n", "<br>", $attValue->getValue('displaySanitized', 'display')); ?></p>
                        <?php } ?>
                    <?php } ?>
                </div>
            </div>
        <?php } ?>
    </fieldset>

    <fieldset>
        <legend><?= t("Order Items") ?></legend>
        <table class="table table-striped">
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
                                foreach ($options as $option) { ?>
                                    <li><strong><?= h($option['oioKey']); ?></strong> <?= h($option['oioValue']) ? h($option['oioValue']) : '<em>' . t('None') . '</em>'; ?></li>
                                <?php }
                                echo "</ul>";
                            }
                            ?>
                        </td>
                        <td class="text-right"><?= Price::format($item->getPricePaid()) ?></td>
                        <td class="text-right"><?= $item->getQuantity() ?> <?= h($item->getQuantityLabel()); ?></td>
                        <td class="text-right"><?= Price::format($item->getSubTotal()) ?></td>
                    </tr>
                    <?php
                }
            }
            ?>
            </tbody>
            <tfoot>
            <tr>
                <td colspan="4" class="text-right"><strong><?= t("Items Subtotal") ?>:</strong></td>
                <td class="text-right"><?= Price::format($order->getSubTotal()) ?></td>
            </tr>
            </tfoot>
        </table>


        <?php $applieddiscounts = $order->getAppliedDiscounts();

        if (!empty($applieddiscounts)) { ?>
            <h4><?= t("Discounts Applied") ?></h4>
            <hr/>
            <table class="table table-striped">
                <thead>
                <tr>
                    <th><strong><?= t("Name") ?></strong></th>
                    <th><?= t("Displayed") ?></th>
                    <th><?= t("Discount") ?></th>
                    <th><?= t("Amount") ?></th>
                    <th><?= t("Triggered") ?></th>
                </tr>

                </thead>
                <tbody>
                <?php foreach ($applieddiscounts as $discount) { ?>
                    <tr>
                        <td><?= h($discount['odName']); ?></td>
                        <td><?= h($discount['odDisplay']); ?></td>
                        <td>
                            <?php
                            $deducttype = $discount['odDeductType'];
                            $deductfrom = $discount['odDeductFrom'];

                            $discountRuleDeduct = $deductfrom;

                            if ($deducttype == 'percentage') {
                                $discountRuleDeduct = t('from products');
                            }

                            if ($deducttype == 'value_all') {
                                $discountRuleDeduct = t('from each product');
                            }

                            if ($deducttype == 'percentage' && $deductfrom == 'shipping') {
                                $discountRuleDeduct = t('from shipping');
                            }

                            if (($deducttype == 'value_all' || $deducttype == 'value') && $deductfrom == 'shipping') {
                                $discountRuleDeduct = t('from shipping');
                            }

                            if ($deducttype == 'fixed') {
                                $discountRuleDeduct = t('set as price');
                            }

                            if ($deducttype == 'fixed' && $deductfrom == 'shipping') {
                                $discountRuleDeduct = t('set as price for shipping');
                            }
                            ?>
                            <?= $discountRuleDeduct; ?>
                        </td>
                        <td><?= ($discount['odValue'] > 0 ? Price::format($discount['odValue']) : \Punic\Number::formatPercent($discount['odPercentage'] / 100) ); ?></td>
                        <td><?= ($discount['odCode'] ? t('by code') . ' <em>' . $discount['odCode'] . '</em>' : t('Automatically')); ?></td>
                    </tr>
                <?php } ?>

                </tbody>
            </table>

        <?php } ?>

        <?php if ($order->isShippable()) { ?>
            <p>
                <strong><?= t("Shipping") ?>: </strong><?= Price::format($order->getShippingTotal()) ?>
            </p>
        <?php } ?>

        <?php $taxes = $order->getTaxes();

        if (!empty($taxes)) { ?>
            <p>
                <?php foreach ($order->getTaxes() as $tax) { ?>
                    <strong><?= h($tax['label']) ?>
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
            <br/><p>
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
                <p><a target="_blank" href="<?= $trackingURL; ?>"><?= t('View shipment tracking'); ?></a></p>
            <?php } ?>

            <?php
            $shippingInstructions = $order->getShippingInstructions();
            if ($shippingInstructions) { ?>
                <p><strong><?= t("Delivery Instructions") ?>: </strong><?= h($shippingInstructions) ?></p>
            <?php } ?>

        <?php } ?>

        <?php $locale = $order->getLocale();
        if ($locale) { ?>
            <br /><p><strong><?= t("Order Locale") ?>: </strong><?= \Punic\Language::getName($locale) ?></p>
        <?php } ?>

        <?php $userAgent = $order->getUserAgent();
        if ($userAgent) { ?>
            <br /><p><strong><?= t("Browser User Agent") ?>: </strong><?= $userAgent ?></p>
        <?php } ?>


    </fieldset>
    <br/>

    <div class="row">
        <div class="col-sm-6">
            <legend><?= t("Fulfilment") ?></legend>
            <table class="table table-striped">
                <thead>
                <tr>
                    <th><?= t("Status") ?></th>
                    <th><?= t("Date") ?></th>
                    <th><?= t("User") ?></th>
                </tr>
                </thead>
                <tbody>
                <?php
                $history = $order->getStatusHistory();
                if ($history) {
                    foreach ($history as $status) {
                        ?>
                        <tr>
                            <td><?= t($status->getOrderStatusName()) ?></td>
                            <td><?= $status->getDate() ?></td>
                            <td><?= $status->getUserName() ?></td>
                        </tr>
                        <?php
                    }
                }
                ?>
                </tbody>
            </table>
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title"><?= t("Update Fulfilment Status") ?></h4>
                </div>
                <div class="panel-body">

                    <form action="<?= Url::to("/dashboard/store/orders/updatestatus", $order->getOrderID()) ?>" method="post">
                        <?= $token->output('community_store'); ?>
                        <div class="form-group">
                            <?= $form->select("orderStatus", $orderStatuses, $order->getStatus()); ?>
                        </div>
                        <input type="submit" class="btn btn-default" value="<?= t("Update") ?>">
                    </form>

                </div>
            </div>

            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title"><?= t("Resend Notification Email") ?></h4>
                </div>
                <div class="panel-body">
                    <form action="<?= Url::to("/dashboard/store/orders/resendnotification", $order->getOrderID()) ?>" method="post">
                        <?= $token->output('community_store'); ?>
                        <div class="form-group">
                            <label for="email"><?= t('Email'); ?></label>
                            <input type="text" class="form-control ccm-input-text" id="email" name="email" value="<?= Config::get('community_store.notificationemails'); ?>"/>
                        </div>
                        <input type="submit" class="btn btn-default" value="<?= t("Resend Notification") ?>">
                    </form>
                </div>
            </div>
        </div>
        <div class="col-sm-6">

            <legend><?= t("Payment Status") ?></legend>

            <?php if ($order->getTotal() == 0) { ?>
                <p><?= t('Free Order'); ?></p>
            <?php } else {
                if (!$paid) { ?>
                    <p class="text-danger"><?= t('Unpaid'); ?></p>
                <?php } ?>

                <?php if ($paid || $refunded) { ?>
                    <table class="table table-striped">
                        <thead>
                        <tr>
                            <th><?= t("Status") ?></th>
                            <th><?= t("Date / Reference") ?></th>
                            <th><?= t("By") ?></th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>

                        <?php if ($paid) { ?>
                            <tr>
                                <td><?= t('Paid') ?>
                                </td>
                                <td><?= $dh->formatDateTime($paid) ?>
                                    <br/>
                                    <?= t('Ref') . ':' ?> <?= $order->getTransactionReference(); ?>
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

                                        <form action="<?= Url::to("/dashboard/store/orders/reversepaid", $order->getOrderID()) ?>" method="post">
                                            <?= $token->output('community_store'); ?>
                                            <input data-confirm-message="<?= h(t('Are you sure you wish to reverse this payment?')); ?>" type="submit" class="confirm-action btn-link" value="<?= t("reverse") ?>">
                                        </form>

                                    <?php } ?></td>

                            </tr>
                        <?php } ?>

                        <?php if ($refunded) { ?>
                            <tr>
                                <td><?= t('Refunded') ?></td>
                                <td><?= $dh->formatDateTime($refunded) ?><br/>
                                    <?= $order->getRefundReason(); ?>
                                </td>
                                <td>
                                    <?php $refundeduser = User::getByUserID($order->getRefundedByUID());
                                    if ($refundeduser) {
                                        echo $refundeduser->getUserName();
                                    }
                                    ?></td>
                                <td>

                                    <form action="<?= Url::to("/dashboard/store/orders/reverserefund", $order->getOrderID()) ?>" method="post">
                                        <?= $token->output('community_store'); ?>
                                        <input data-confirm-message="<?= h(t('Are you sure you wish to reverse this refund?')); ?>" type="submit" class="confirm-action btn-link" value="<?= t("reverse") ?>">
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
                            <h4 class="panel-title"><?= t("Update Payment Status") ?></h4>
                        </div>
                        <div class="panel-body">

                            <?php if (!$paid) { ?>
                                <form action="<?= Url::to("/dashboard/store/orders/markpaid", $order->getOrderID()) ?>" method="post">
                                    <?= $token->output('community_store'); ?>
                                    <div class="form-group">
                                        <label for="transactionReference"><?= t('Transaction Reference'); ?></label>
                                        <input type="text" class="form-control ccm-input-text" id="transactionReference" name="transactionReference"/>
                                    </div>
                                    <input type="submit" class="btn btn-default" value="<?= t("Mark Paid") ?>">
                                </form>
                            <?php } elseif (!$refunded) { ?>
                                <form action="<?= Url::to("/dashboard/store/orders/markrefunded", $order->getOrderID()) ?>" method="post">
                                    <?= $token->output('community_store'); ?>
                                    <div class="form-group">
                                        <label for="oRefundReason"><?= t('Refund Reason'); ?></label>
                                        <input type="text" class="form-control ccm-input-text" id="oRefundReason" name="oRefundReason"/>
                                    </div>
                                    <input type="submit" class="btn btn-default" value="<?= t("Mark Refunded") ?>">
                                </form>
                            <?php } ?>

                        </div>
                    </div>
                <?php } ?>

            <?php } ?>

            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title"><?= t("Resend Invoice Email") ?></h4>
                </div>
                <div class="panel-body">
                    <form action="<?= Url::to("/dashboard/store/orders/resendinvoice", $order->getOrderID()) ?>" method="post">
                        <?= $token->output('community_store'); ?>
                        <div class="form-group">
                            <label for="email"><?= t('Email'); ?></label>
                            <input type="text" class="form-control ccm-input-text" id="email" name="email" value="<?= $order->getAttribute('email'); ?>"/>
                        </div>
                        <input type="submit" class="btn btn-default" value="<?= t("Resend Invoice") ?>">
                    </form>
                </div>
            </div>
        </div>

    </div>


    <?php if (!$order->getCancelled()) { ?>
        <fieldset>
            <legend><?= t("Cancel Order") ?></legend>
            <form action="<?= Url::to("/dashboard/store/orders/markcancelled", $order->getOrderID()) ?>" method="post">
                <?= $token->output('community_store'); ?>
                <input data-confirm-message="<?= h(t('Are you sure you wish to cancel this order?')); ?>" type="submit" class="confirm-action btn btn-danger" value="<?= t("Cancel Order") ?>">
            </form>
        </fieldset>
    <?php } else { ?>
        <form action="<?= Url::to("/dashboard/store/orders/reversecancel", $order->getOrderID()) ?>" method="post">
            <?= $token->output('community_store'); ?>
            <input data-confirm-message="<?= h(t('Are you sure you wish to reverse this cancellation?')); ?>" type="submit" class="confirm-action btn btn-default" value="<?= t("Reverse Cancellation") ?>">
        </form>
        <br/>

        <fieldset>
            <legend><?= t("Delete Order") ?></legend>
            <form action="<?= Url::to("/dashboard/store/orders/remove", $order->getOrderID()) ?>" method="post">
                <?= $token->output('community_store'); ?>
                <input data-confirm-message="<?= h(t('Are you sure you wish to completely delete this order? The order number will be reused.')); ?>" type="submit" class="confirm-action btn btn-danger" value="<?= t("Delete Order") ?>">
            </form>
        </fieldset>
    <?php } ?>


<?php } else { ?>

    <div class="ccm-dashboard-header-buttons">
    </div>

    <?php if ($shoppingDisabled) { ?>
        <p class="alert alert-warning text-center"><?= t('Cart and Ordering features are currently disabled. This setting can be changed via the'); ?> <a href="<?= Url::to('/dashboard/store/settings#settings-checkout'); ?>"><?= t('settings page.'); ?></a></p>
    <?php } ?>


    <form role="form" class="form-inline">
        <div class="row">
            <div class="ccm-search-fields-submit col-xs-12 col-md-4">
                <div class="form-group">
                    <div class="ccm-search-main-lookup-field">
                        <?= $form->search('keywords', $searchRequest['keywords'], ['placeholder' => t('Search Orders'), 'style'=>""]) ?>
                    </div>
                </div>
                <button class="btn btn-info" type="submit"><i class="fa fa-search"></i></button>
            </div>
            <div class="col-xs-12 col-md-8">
                <ul id="group-filters" class="nav nav-pills">
                <?php
                $keywordsparam = '';
                if ( $keywords) {
                    $keywordsparam = '?keywords=' . urlencode($keywords);

                }

                if($enabledPaymentMethods){
                    $paymentMethodName = '';
                    foreach ($enabledPaymentMethods as $pm) {
                        if ($paymentMethod == $pm->getID()) {
                            $paymentMethodName = $pm->getName();
                        }
                    } ?>

                    <li role="presentation" class="dropdown <?= ($paymentMethod != 'all' ? 'active' : ''); ?>">
                        <a class="dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
                            <?= $paymentMethod  != 'all' ? t('Payment Method: %s', $paymentMethodName) : t('Payment Method'); ?> <span class="caret"></span>
                        </a>
                        <ul class="dropdown-menu">
                            <li <?= (!$paymentMethod ? 'class="active"' : ''); ?>><a href="<?= \URL::to('/dashboard/store/orders/'  . $status .'/all/' . $paymentStatus . $keywordsparam)?>"><?= t('All Payment Methods')?></a></li>
                            <?php foreach($enabledPaymentMethods as $pm){ ?>
                                <li <?= ($paymentMethod == $pm->getName() ? 'class="active"' : ''); ?>><a href="<?= \URL::to('/dashboard/store/orders/' . $status . '/' . $pm->getID() . '/' . $paymentStatus . $keywordsparam)?>"><?= t($pm->getName());?></a></li>
                            <?php } ?>
                        </ul>
                    </li>
                <?php } ?>
                <li role="presentation" class="dropdown <?= ($paymentStatus != 'all' ? 'active' : ''); ?>">
                    <a class="dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
                        <?= $paymentStatus != 'all' ? t('Payment Status: %s', title_case($paymentStatus)) : t('Payment Status'); ?> <span class="caret"></span>
                    </a>
                    <ul class="dropdown-menu">
                        <li <?= ($paymentStatus == 'all' ? 'class="active"' : '');?>><a href="<?= \URL::to('/dashboard/store/orders/'  . $status .'/' . $paymentMethod . '/all' .$keywordsparam )?>"><?= t('All Payment Statuses')?></a></li>
                            <li <?= ($paymentStatus == 'paid' ? 'class="active"' : '');?>><a href="<?= \URL::to('/dashboard/store/orders/' . $status .'/' . $paymentMethod . '/paid'.$keywordsparam)?>"><?= t('Paid');?></a></li>
                            <li <?= ($paymentStatus == 'unpaid' ? 'class="active"' : '');?>><a href="<?= \URL::to('/dashboard/store/orders/' . $status .'/' . $paymentMethod . '/unpaid'.$keywordsparam)?>"><?= t('Unpaid');?></a></li>
                            <li <?= ($paymentStatus == 'refunded' ? 'class="active"' : '');?>><a href="<?= \URL::to('/dashboard/store/orders/' . $status .'/' . $paymentMethod . '/refunded'.$keywordsparam)?>"><?= t('Refunded');?></a></li>
                            <?php if (Config::get('community_store.showUnpaidExternalPaymentOrders')) { ?>
                            <li <?= ($paymentStatus == 'incomplete' ? 'class="active"' : '');?>><a href="<?= \URL::to('/dashboard/store/orders/' . $status .'/' . $paymentMethod . '/incomplete'.$keywordsparam)?>"><?= t('Incomplete');?></a></li>
                            <?php } ?>
                            <li <?= ($paymentStatus == 'cancelled' ? 'class="active"': '');?>><a href="<?= \URL::to('/dashboard/store/orders/' . $status .'/' . $paymentMethod . '/cancelled'.$keywordsparam)?>"><?= t('Cancelled');?></a></li>
                    </ul>
                </li>

                <?php if($statuses){?>
                    <li role="presentation" class="dropdown <?= ($status != 'all' ? 'active' : ''); ?>">
                        <?php
                        $statusFilter = '';
                        foreach ($statuses as $statusoption) {
                            if ($status == $statusoption->getHandle()) {
                                $statusString = $statusoption->getName();
                            }
                        } ?>

                        <a class="dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
                            <?= $status != 'all' ? t('Fulfilment: %s', $statusString) : t('Fulfilment'); ?> <span class="caret"></span>
                        </a>

                        <ul class="dropdown-menu">
                            <li <?= (!$status ? 'class="active"' : ''); ?>><a href="<?= \URL::to('/dashboard/store/orders/all/' . $paymentMethod . '/' . $paymentStatus . $keywordsparam)?>"><?= t('All Fulfilment Statuses')?></a></li>
                            <?php foreach($statuses as $statusoption){ ?>
                                <li <?= ($status == $statusoption->getHandle() ? 'class="active"' : ''); ?>><a href="<?= \URL::to('/dashboard/store/orders/', $statusoption->getHandle() . '/' . $paymentMethod . '/' . $paymentStatus.$keywordsparam)?>"><?= t($statusoption->getName());?></a></li>
                            <?php } ?>
                        </ul>
                    </li>
                <?php } ?>
            </ul>
            </div>
        </div>
    </form>

    <?php if (!empty($orderList)) { ?>
        <div class="ccm-dashboard-content-full">
        <table class="ccm-search-results-table">
            <thead>
            <tr>
                <th><a><?= t("Order %s", "#") ?></a></th>
                <th><a><?= t("Customer Name") ?></a></th>
                <th><a><?= t("Order Date") ?></a></th>
                <th><a><?= t("Total") ?></a></th>
                <th><a><?= t("Payment") ?></a></th>
                <th><a><?= t("Fulfilment") ?></a></th>
                <th><a><?= t("View") ?></a></th>
            </tr>
            </thead>
            <tbody>
            <?php
            foreach ($orderList as $order) {

                $cancelled = $order->getCancelled();
                $canstart = '';
                $canend = '';
                if ($cancelled) {
                    $canstart = '<del>';
                    $canend = '</del>';
                }
                ?>
                <tr>
                    <td><?= $canstart; ?>
                        <a href="<?= Url::to('/dashboard/store/orders/order/', $order->getOrderID()) ?>"><?= $order->getOrderID() ?></a><?= $canend; ?>

                        <?php if ($cancelled) {
                            echo '<span class="text-danger">' . t('Cancelled') . '</span>';
                        }
                        ?>
                    </td>
                    <td><?= $canstart; ?><?php

                        $last = $order->getAttribute('billing_last_name');
                        $first = $order->getAttribute('billing_first_name');

                        $fullName = implode(', ', array_filter([$last, $first]));
                        if (strlen($fullName) > 0) {
                            echo h($fullName);
                        } else {
                            echo '<em>' . t('Not found') . '</em>';
                        }

                        ?><?= $canend; ?></td>
                    <td><?= $canstart; ?><?= $dh->formatDateTime($order->getOrderDate()) ?><?= $canend; ?></td>
                    <td><?= $canstart; ?><?= Price::format($order->getTotal()) ?><?= $canend; ?></td>
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

                            if ($order->getExternalPaymentRequested()) {
                                echo ' <span class="label label-default">' . t('Incomplete') . '</span>';
                            }
                        } else {
                            echo '<span class="label label-default">' . t('Free Order') . '</span>';
                        }


                        ?>
                    </td>
                    <td><?= t(ucwords($order->getStatus())) ?></td>
                    <td>
                        <div class="btn-group" style="width:100px">
                            <a class="btn btn-primary btn-sm"
                               href="<?= Url::to('/dashboard/store/orders/order/', $order->getOrderID()) ?>"><?= t("View") ?></a>
                            <button type="button" class="btn btn-primary btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a href="<?= Url::to('/dashboard/store/orders/printslip/' . $order->getOrderID()) ?>" target="_blank"><i class="fa fa-print"></i> <?= t("Print Order Slip") ?></a></li>
                            </ul>
                        </div>
                    </td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
        </div>
    <?php } ?>


    <?php if (empty($orderList)) { ?>
        <br/><p class="alert alert-info"><?= t('No Orders Found'); ?></p>
    <?php } ?>

    <?php if ($paginator->getTotalPages() > 1) { ?>
        <div class="ccm-search-results-pagination">
            <?= $pagination ?>
        </div>
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
