<?php defined('C5_EXECUTE') or die("Access Denied.");
use \Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Price;

$taxCalc = Config::get('community_store.calculation');
$dh = Core::make('helper/date');

if ($taxCalc == 'extract') {
    $taxValue = 'includedTaxTotal';
    $extraTaxLable =  t('Incl.');
} else {
    $taxValue = 'taxTotal';
    $extraTaxLable = '';
}
?>

<?php if ($shoppingDisabled) { ?>
<p class="alert alert-warning text-center"><?php echo t('Cart and Ordering features are currently disabled. This setting can be changed via the');?> <a href="<?= \URL::to('/dashboard/store/settings#settings-checkout'); ?>"><?= t('settings page.');?></a></p>
<?php } ?>


<div class="row">
    <div class="col-xs-12 col-sm-6">
        <div class="panel-sale panel panel-default">
            <?php $ts = $sr->getTodaysSales(); ?>
            <div class="panel-heading">
                <h2 class="panel-title"><?= t("Today's Sales")?></h2>
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-xs-12 col-sm-6 stat">
                        <strong><?= t('Total')?></strong> <?=Price::format($ts['total'])?>
                    </div>
                    <div class="col-xs-12 col-sm-6 stat">
                        <strong><?= t('Products')?></strong> <?=Price::format($ts['productTotal'])?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-12 col-sm-6 stat">
                        <strong><?= t('Tax')?> <?= $extraTaxLable?></strong> <?=Price::format($ts[$taxValue])?>
                    </div>
                    <div class="col-xs-12 col-sm-6 stat">
                        <strong><?= t('Shipping')?></strong> <?=Price::format($ts['shippingTotal'])?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xs-12 col-sm-6">
        <div class="panel panel-sale panel-default">
            <?php $ytd = $sr->getYearToDate(); ?>
            <div class="panel-heading">
                <h2 class="panel-title"><?= t("Year to Date")?></h2>
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-xs-12 col-sm-6 stat">
                        <strong><?= t('Total')?></strong> <?=Price::format($ytd['total'])?>
                    </div>
                    <div class="col-xs-12 col-sm-6 stat">
                        <strong><?= t('Products')?></strong> <?=Price::format($ytd['productTotal'])?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-12 col-sm-6 stat">
                        <strong><?= t('Tax')?> <?= $extraTaxLable?></strong> <?=Price::format($ytd[$taxValue])?>
                    </div>
                    <div class="col-xs-12 col-sm-6 stat">
                        <strong><?= t('Shipping')?></strong> <?=Price::format($ytd['shippingTotal'])?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-xs-12">
        <hr>
        <h3><?= t("Sales this Week")?></h3>
        <div id="sales-chart"></div>
        <hr>
        <script type="text/javascript">
        $(function(){
            new Chartist.Line('#sales-chart', {
                <?php
                    $months = array(
                        new DateTime(date('Y-M-d', strtotime('-6 days'))),
                        new DateTime(date('Y-M-d', strtotime('-5 days'))),
                        new DateTime(date('Y-M-d', strtotime('-4 days'))),
                        new DateTime(date('Y-M-d', strtotime('-3 days'))),
                        new DateTime(date('Y-M-d', strtotime('-2 days'))),
                        new DateTime(date('Y-M-d', strtotime('-1 day'))),
                        new DateTime(date('Y-M-d'))
                    );
                ?>
                
                labels: [ <?php for($i=0;$i<7;$i++){
                        if($i!=6){
                            echo "'".$months[$i]->format("m/d")."',";
                        } else {
                            echo "'".$months[$i]->format("m/d")."'";
                        }
                    } ?> ],
                // Our series array that contains series objects or in this case series data arrays
                series: [
                    [
                        <?php 
                            for($i=0;$i<7;$i++){
                                $date = $months[$i]->format('Y-m-d');
                                $report = $sr->getTotalsByRange($date,$date);
                                if($i==6){
                                    echo "{meta: '".t('Total')."', value: ".$report['total']."}";
                                } else {
                                    echo "{meta: '".t('Total')."', value: ".$report['total']."},";
                                }
                            }
                        ?>              
                    ]
                ]
            },
            {
                axisY: {
                    offset: 80,
                    labelInterpolationFnc: function(value) {
                      return "$" + value;
                    }
                },
                plugins: [
                    Chartist.plugins.tooltip()
                ],
                lineSmooth: Chartist.Interpolation.none()
            }
            );
            
        
        });
        </script>
    </div>
</div>
<div class="row">
    <div class="col-xs-12">
        <h3><?= t("Recent Orders")?></h3>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th><?= t("Order #")?></th>
                    <th><?= t("Date")?></th>
                    <th><?= t("Products")?></th>
                    <th><?= t("Shipping")?></th>
                    <th><?= t("Tax")?> <?= $extraTaxLable?></th>
                    <th><?= t("Total")?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($orders as $o){?>
                <tr>
                    <td><a href="<?= \URL::to('/dashboard/store/orders/order',$o->getOrderID())?>"><?= $o->getOrderID()?></a></td>
                    <td><?= $dh->formatDateTime($o->getOrderDate())?></td>
                    <td><?=Price::format($o->getSubTotal())?></td>
                    <td><?=Price::format($o->getShippingTotal())?></td>
                    <td>
                        <?php
                        $tax = $o->getTaxTotal();
                        $includedTax = $o->getIncludedTaxTotal();
                        if ($tax) {
                            echo Price::format($tax);
                        } elseif ($includedTax) {
                            echo Price::format($includedTax);
                        }
                        ?>
                    </td>
                    <td><?=Price::format($o->getTotal())?></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>

    </div>
</div>
