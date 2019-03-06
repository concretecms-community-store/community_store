<?php
defined('C5_EXECUTE') or die("Access Denied.");
use \Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Price;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Report\SalesReport;
use \Concrete\Core\Support\Facade\Url;
use \Concrete\Core\Support\Facade\Config;

$app = \Concrete\Core\Support\Facade\Application::getFacadeApplication();
$taxCalc = Config::get('community_store.calculation');
$dh = $app->make('helper/date');

if ($taxCalc == 'extract') {
	$taxValue = 'includedTaxTotal';
	$extraTaxLable =  t('Incl.');
} else {
	$taxValue = 'taxTotal';
	$extraTaxLable = '';
}
?>

<div class="row">
	<div class="col-xs-12 col-md-4">
		<div class="panel-sale panel panel-default">
			<?php $ts = SalesReport::getTodaysSales(); ?>
			<div class="panel-heading">
				<h2 class="panel-title"><?= t("Today's Sales")?></h2>
			</div>
			<div class="panel-body">
				<div class="row">
					<div class="col-xs-12 col-sm-6 stat">
						<strong><?= t('Total')?> </strong> <?=Price::format($ts['total'])?>
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
	<div class="col-xs-12 col-md-4">
		<div class="panel-sale panel panel-default">
			<?php $td = SalesReport::getThirtyDays(); ?>
			<div class="panel-heading">
				<h2 class="panel-title"><?= t('Past 30 Days')?></h2>
			</div>
			<div class="panel-body">
				<div class="row">
					<div class="col-xs-12 col-sm-6 stat">
						<strong><?= t('Total')?></strong> <?=Price::format($td['total'])?>
					</div>
					<div class="col-xs-12 col-sm-6 stat">
						<strong><?= t('Products')?></strong> <?=Price::format($td['productTotal'])?>
					</div>
				</div>
				<div class="row">
					<div class="col-xs-12 col-sm-6 stat">
						<strong><?= t('Tax')?> <?= $extraTaxLable?></strong> <?=Price::format($td[$taxValue])?>
					</div>
					<div class="col-xs-12 col-sm-6 stat">
						<strong><?= t('Shipping')?></strong> <?=Price::format($td['shippingTotal'])?>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="col-xs-12 col-md-4">
		<div class="panel-sale panel panel-default">
			<?php $ytd = SalesReport::getYearToDate(); ?>
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
<hr>
<div id="sales-chart"></div>
<hr>
<script type="text/javascript">
$(function(){
	new Chartist.Line('#sales-chart', {
	    <?php
            $months = array(
                new DateTime(date('Y-M', strtotime('-5 months'))),
                new DateTime(date('Y-M', strtotime('-4 months'))),
                new DateTime(date('Y-M', strtotime('-3 months'))),
                new DateTime(date('Y-M', strtotime('-2 months'))),
                new DateTime(date('Y-M', strtotime('-1 month'))),
                new DateTime(date('Y-M'))
            );
        ?>
	    
	    labels: [ <?php for($i=0;$i<6;$i++){
                if($i!=5){
                    echo "'".$months[$i]->format("M")."',";
                } else {
                    echo "'".$months[$i]->format("M")."'";
                }
            } ?> ],
		// Our series array that contains series objects or in this case series data arrays
	    series: [
	    	[
				<?php 
                    for($i=0;$i<6;$i++){
                        $report = SalesReport::getByMonth($months[$i]->format('Y-M'));
                        if($i==5){
                            echo "{meta: '".t('Total')."', value: ".$report['total']."}";
                        } else {
                            echo "{meta: '".t('Total')."', value: ".$report['total']."},";
                        }
                    }
                ?>				
			],
			[
				<?php 
                    for($i=0;$i<6;$i++){
                        $report = SalesReport::getByMonth($months[$i]->format('Y-M'));
                        if($i==5){
                            echo "{meta: '".t('Products')."', value: ".$report['productTotal']."}";
                        } else {
                            echo "{meta: '".t('Products')."', value: ".$report['productTotal']."},";
                        }
                    }
                ?>				
			],
			[
				<?php 
                    for($i=0;$i<6;$i++){
                        $report = SalesReport::getByMonth($months[$i]->format('Y-M'));
                        if($i==5){
                            echo "{meta: '".t('Shipping')."', value: ".$report['shippingTotal']."}";
                        } else {
                            echo "{meta: '".t('Shipping')."', value: ".$report['shippingTotal']."},";
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
		      return "<?= Config::get('community_store.symbol'); ?>" + value;
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

<div class="well">
	<div class="row">
		<div class="col-xs-12 col-sm-4">
			<h3><?= t("View Orders by Date")?></h3>
		</div>
		<div class="col-xs-12 col-sm-8 text-right">
			<form action="<?=Url::to('/dashboard/store/reports/sales')?>" method="post" class="form form-inline order-report-form">
				<div class="form-group">
					<?= $app->make('helper/form/date_time')->date('dateFrom', $dateFrom); ?>
				</div>
				<div class="form-group">
					<?= $app->make('helper/form/date_time')->date('dateTo', $dateTo); ?>
				</div>
				<input type="submit" class="btn btn-primary">
			</form>
		</div>
	</div>
	<hr>
	<h4><?= t("Summary")?></h4>
	<table class="table table-striped">
		<thead>
			<tr>
				<th><?= t("Products")?></th>
				<th><?= t("Shipping")?></th>
				<th><?= t("Tax")?> <?= $extraTaxLable?></th>
				<th><?= t("Total")?></th>
				<th><?= t("Export")?></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td><?=Price::format($ordersTotals['productTotal'])?></td>
				<td><?=Price::format($ordersTotals['shippingTotal'])?></td>
				<td><?=Price::format($ordersTotals[$taxValue])?></td>
				<td><?=Price::format($ordersTotals['total'])?></td>
				<td><a href="<?=Url::to('/dashboard/store/reports/sales/export?fromDate='.$dateFrom.'&toDate='.$dateTo)?>" class="btn btn-default"><?= t('Export to CSV')?></a></td>
			</tr>
		</tbody>
	</table>
</div>
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
			<td><a href="<?=Url::to('/dashboard/store/orders/order',$o->getOrderID())?>"><?= $o->getOrderID()?></a></td>
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
<?php if ($paginator->getTotalPages() > 1) { ?>
    <?= $pagination ?>
<?php } ?>
