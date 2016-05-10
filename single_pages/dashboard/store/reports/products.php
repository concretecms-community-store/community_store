<?php
defined('C5_EXECUTE') or die("Access Denied.");
use \Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Price;
?>

<?php
$task = $controller->getTask();
?>

<?php if ($task == 'view') { ?>
<div class="ccm-dashboard-content-full">
	<form action="<?=URL::to('/dashboard/store/reports/products')?>" method="post" class="form form-inline ccm-search-fields">
		<div class="ccm-search-fields-row">
			<div class="form-group form-group-full">
        		<?= $form->label('dateFrom', t('From'))?>
        		<div class="ccm-search-field-content ccm-search-field-content-select2">
					<?= Core::make('helper/form/date_time')->date('dateFrom', $dateFrom); ?>
				</div>
			</div>
		</div>
		<div class="ccm-search-fields-row">
			<div class="form-group form-group-full">
				<?= $form->label('dateFrom', t('To'))?>
				<div class="ccm-search-field-content ccm-search-field-content-select2">
					<?= Core::make('helper/form/date_time')->date('dateTo', $dateTo); ?>
				</div>
			</div>
		</div>
		<div class="ccm-search-fields-row">
			<div class="form-group form-group-full">
				<?= $form->label('orderBy', t('Order By'))?>
				<div class="ccm-search-field-content ccm-search-field-content-select2">
					<?= $form->select('orderBy',array('quantity'=>t('Quantity Sold'),'pricePaid'=>t('Total')),$orderBy); ?>
				</div>
			</div>
		</div>
		<div class="ccm-search-fields-submit">
	        <button type="submit" class="btn btn-primary pull-right"><?= t('Filter Results')?></button>
	    </div>

	</form>

</div>
<table class="table table-striped">
	<thead>
		<tr>
			<th><?= t("Name")?></th>
			<th><?= t("Quantity Sold")?></th>
			<th><?= t("Total")?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach($products as $product){ ?>
		<tr>
			<td><a href="<?php echo URL::to('/dashboard/store/reports/products/detail/ ' . $product['pID']); ?>"><?= $product['name']?></a></td>
			<td><?= $product['quantity']?></td>
			<td><?=Price::format($product['pricePaid'])?></td>
		</tr>
		<?php } ?>
	</tbody>
</table>

<?php if ($paginator->getTotalPages() > 1) { ?>
    <?= $pagination ?>
<?php } ?>
<?php } ?>

<?php if ($task == 'detail') {
	$dh = Core::make('helper/date');
	$totalSold = 0;

	?>

	<div class="ccm-dashboard-header-buttons">
		<a href="<?= \URL::to('/dashboard/store/reports/products/export/' . $product->getID())?>" class="btn btn-primary"><?= t("Export CSV")?></a>
	</div>

	<table class="table table-striped">
		<thead>
		<tr>
			<?php foreach($reportHeader as $header) { ?>
				<th><?= $header; ?></th>
			<?php } ?>

		</tr>
		</thead>
		<tbody>
		<?php foreach($orderItems as $item){
			$order = $item->getOrder();
			?>
			<tr>
				<td><?= $order->getOrderID(); ?></td>
				<td><?= $order->getAttribute("billing_last_name");?></td>
				<td><?= $order->getAttribute("billing_first_name"); ?></td>
				<td><?= $order->getAttribute("email"); ?></td>
				<td><?= $order->getAttribute("billing_phone"); ?></td>
				<td><?= $item->getProductName()?>
					<?php if ($sku = $item->getSKU()) {
						echo '(' .  $sku . ')';
					} ?>
				</td>
				<td><?php
					$qty = $item->getQty();
					$totalSold +=  $qty;
					echo $qty; ?></td>
				<td>
					<?php
					$options = $item->getProductOptions();
					if($options){

						foreach($options as $option){
							echo "<strong>".$option['oioKey'].": </strong>";
							echo $option['oioValue'] . '<br />';
						}
					}
					?>

				</td>
				<td><?= $dh->formatDateTime($order->getOrderDate()); ?></td>
				<td><?= $order->getStatus(); ?></td>

			</tr>
		<?php } ?>
		</tbody>
		<tfoot>
			<tr>
				<td colspan="6" class="text-right"><strong><?= t('Total Quantity Sold'); ?>:</strong></td>
				<td colspan="4"><strong><?= $totalSold; ?></strong></td>
			</tr>
		</tfoot>
	</table>

<?php } ?>
