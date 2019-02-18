<?php defined('C5_EXECUTE') or die("Access Denied.");

use \Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Price;

$taxCalc = Config::get('community_store.calculation');
$dh = Core::make('helper/date');

if ($taxCalc == 'extract') {
    $taxValue = 'includedTaxTotal';
    $extraTaxLable = t('Incl.');
} else {
    $taxValue = 'taxTotal';
    $extraTaxLable = '';
}
?>

<?php if ($shoppingDisabled) { ?>
    <p class="alert alert-warning text-center"><?= t('Cart and Ordering features are currently disabled. This setting can be changed via the'); ?>
        <a href="<?= \URL::to('/dashboard/store/settings#settings-checkout'); ?>"><?= t('settings page.'); ?></a></p>
<?php } ?>


<div class="row">
    <div class="col-xs-12 col-sm-6">
        <div class="panel-sale panel panel-default">
            <?php $ts = $sr->getTodaysSales(); ?>
            <div class="panel-heading">
                <h2 class="panel-title"><?= t("Today's Sales") ?></h2>
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-xs-12 col-sm-6 stat">
                        <strong><?= t('Total') ?></strong> <?= Price::format($ts['total']) ?>
                    </div>
                    <div class="col-xs-12 col-sm-6 stat">
                        <strong><?= t('Products') ?></strong> <?= Price::format($ts['productTotal']) ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-12 col-sm-6 stat">
                        <strong><?= t('Tax') ?> <?= $extraTaxLable ?></strong> <?= Price::format($ts[$taxValue]) ?>
                    </div>
                    <div class="col-xs-12 col-sm-6 stat">
                        <strong><?= t('Shipping') ?></strong> <?= Price::format($ts['shippingTotal']) ?>
                    </div>
                </div>
            </div>
        </div>


        <div class="panel-sale panel panel-default">
            <?php $ts = $sr->getTodaysSales(); ?>
            <div class="panel-heading">
                <h2 class="panel-title"><?= t("Sales this Week") ?></h2>
            </div>
            <div class="panel-body">


                <div id="sales-chart"></div>
            </div>
            <div class="panel-footer">
                <a href="<?= \URL::to('/dashboard/store/reports') ?>"><i class="fa fa-line-chart"></i> View Sales Report</a>
            </div>
        </div>

        <script type="text/javascript">
            $(function () {
                new Chartist.Line('#sales-chart', {
                        <?php

                        $symbol = Config::get('community_store.symbol');

                        $months = [
                            new DateTime(date('Y-M-d', strtotime('-6 days'))),
                            new DateTime(date('Y-M-d', strtotime('-5 days'))),
                            new DateTime(date('Y-M-d', strtotime('-4 days'))),
                            new DateTime(date('Y-M-d', strtotime('-3 days'))),
                            new DateTime(date('Y-M-d', strtotime('-2 days'))),
                            new DateTime(date('Y-M-d', strtotime('-1 day'))),
                            new DateTime(date('Y-M-d'))
                        ];
                        ?>

                        labels: [ <?php for ($i = 0; $i < 7; $i++) {
                            if ($i != 6) {
                                echo "'" . $months[$i]->format(t("m/d")) . "',";
                            } else {
                                echo "'" . $months[$i]->format(t("m/d")) . "'";
                            }
                        } ?> ],
                        // Our series array that contains series objects or in this case series data arrays
                        series: [
                            [
                                <?php
                                for ($i = 0; $i < 7; $i++) {
                                    $date = $months[$i]->format('Y-m-d');
                                    $report = $sr->getTotalsByRange($date, $date);
                                    if ($i == 6) {
                                        echo "{meta: '" . t('Total') . "', value: " . $report['total'] . "}";
                                    } else {
                                        echo "{meta: '" . t('Total') . "', value: " . $report['total'] . "},";
                                    }
                                }
                                ?>
                            ]
                        ]
                    },
                    {
                        axisY: {
                            offset: 80,
                            labelInterpolationFnc: function (value) {
                                return "<?= $symbol;?>" + value;
                            }
                        },
                        plugins: [
                            Chartist.plugins.tooltip(
                                {
                                    currency: '$'
                                }
                            )
                        ],
                        lineSmooth: Chartist.Interpolation.none()
                    }
                );


            });
        </script>
    </div>
    <div class="col-xs-12 col-sm-6">
        
        <h4><?= t("Orders") ?></h4>

        <form action="<?= \URL::to('/dashboard/store/orders') ?>">
            <div class="form-group">

                <div class="input-group">
                    <?= $form->search('keywords', $searchRequest['keywords'], ['placeholder' => t('Search Orders')]) ?>
                    <span class="input-group-btn">
                            <button type="submit" class="btn btn-default"><i class="fa fa-search"></i></button>
                        </span>
                </div>
            </div>
        </form>

        <p><a href="<?= \URL::to('/dashboard/store/orders') ?>"><i class="fa fa-list"></i> View All Orders</a></p>

        <hr>

        <h4><?= t("Products") ?></h4>

        <form action="<?= \URL::to('/dashboard/store/products') ?>">
            <div class="form-group">
                <div class="input-group">
                    <?= $form->search('keywords', $searchRequest['keywords'], ['placeholder' => t('Search Products')]) ?>
                    <span class="input-group-btn">
                        <button type="submit" class="btn btn-default"><i class="fa fa-search"></i></button>
                    </span>
                </div>
            </div>
        </form>


        <p class="pull-right"><a href="<?= \URL::to('/dashboard/store/products/add') ?>"><i class="fa fa-plus"></i>
                Add Product</a></p>
        <p><a href="<?= \URL::to('/dashboard/store/products') ?>"><i class="fa fa-gift"></i> View All Products</a></p>

        <hr>


        <h4><?= t("Discounts") ?></h4>
        <p class="pull-right"><a href="<?= \URL::to('/dashboard/store/discounts/add') ?>"><i class="fa fa-plus"></i>
                Add Discount Rule</a></p>
        <p><a href="<?= \URL::to('/dashboard/store/discounts') ?>"><i class="fa fa-scissors"></i> View Discount Rules</a>
        </p>


    </div>
</div>
<div class="row">
    <div class="col-xs-12">
        <hr>

    </div>
</div>
<div class="row">
    <div class="col-xs-12">
        <h4><?= t("Recent Orders") ?></h4>

        <?php
        if (!empty($orders)) { ?>
            <table class="table table-striped">
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
                foreach ($orders as $order) {

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
                            <a href="<?= URL::to('/dashboard/store/orders/order/', $order->getOrderID()) ?>"><?= $order->getOrderID() ?></a><?= $canend; ?>

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
                            <div class="btn-group">
                                <a class="btn btn-primary btn-sm"
                                   href="<?= URL::to('/dashboard/store/orders/order/', $order->getOrderID()) ?>"><?= t("View") ?></a>
                                <button type="button" class="btn btn-primary btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a href="<?=URL::to('/dashboard/store/orders/printslip/' . $order->getOrderID())?>"  target="_blank"><i class="fa fa-print"></i> <?= t("Print Order Slip")?></a></li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <p class="alert alert-info"><?= t('No Orders Found'); ?></p>
        <?php } ?>

    </div>
</div>
