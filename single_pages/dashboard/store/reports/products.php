<?php
defined('C5_EXECUTE') or die("Access Denied.");
use \Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Price;
?>

<?php
$task = $controller->getAction();
?>

<?php if ($task == 'view') { ?>

    <form action="<?= URL::to('/dashboard/store/reports/products') ?>" method="get">

        <div class="row">

            <div class="col-md-3">

                <div class="form-group">
                    <?= $form->label('dateFrom', t('From')) ?>
                    <div class="ccm-search-field-content ccm-search-field-content-select2">
                        <?= Core::make('helper/form/date_time')->date('dateFrom', $dateFrom); ?>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    <?= $form->label('dateFrom', t('To')) ?>
                    <div class="ccm-search-field-content ccm-search-field-content-select2">
                        <?= Core::make('helper/form/date_time')->date('dateTo', $dateTo); ?>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    <?= $form->label('orderBy', t('Order By')) ?>
                    <div class="ccm-search-field-content ccm-search-field-content-select2">
                        <?= $form->select('orderBy', ['quantity' => t('Quantity Sold'), 'pricePaid' => t('Total')], $orderBy); ?>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    <?= $form->label('orderBy', t('Product Search')) ?>
                    <?= $form->text('productSearch', h($productSearch), array('placeholder'=>'All Products') ); ?>
                </div>
            </div>


        </div>

        <div class="row">
            <div class="col-md-12">
                <button type="submit" class="btn btn-default"><?= t('Filter Results') ?></button>
            </div>
        </div>
    </form>

    <hr />


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
	$totalSoldAll = 0;

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
                <td><a href="<?php echo URL::to('/dashboard/store/orders/order/'. $order->getOrderID()); ?>"><?= $order->getOrderID(); ?></a></td>
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
                    $totalSoldAll += $qty;
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

                <td>
                    <?php
                    $paid = $order->getPaid();

                    if ($paid) {
                        $paidstatus = t('Paid');
                        $totalSold +=  $qty;
                    } elseif ($order->getTotal() > 0) {
                        $paidstatus = t('Unpaid');
                        if ($order->getExternalPaymentRequested()) {
                            $paidstatus = t('Incomplete') ;
                        }
                    } else {
                        $paidstatus = t('Free Order');
                        $totalSold +=  $qty;
                    }

                    echo $paidstatus;
                    ?>

                </td>

                <td>
                     <?= $order->getPaymentMethodName(); ?>
                </td>
                <td>
                     <?= Price::format($item->getPricePaid() * $item->getQty()); ?>
                </td>

			</tr>
		<?php } ?>
		</tbody>
		<tfoot>
			<tr>
				<td colspan="6" class="text-right"><strong><?= t('Total Quantity Sold'); ?>:</strong></td>
				<td colspan="7"><strong><?= $totalSold; ?>
                    <?php if ($totalSoldAll != $totalSold) { ?>
                        <?= t(' (%s across all orders)', $totalSoldAll);?>
                    <?php } ?>

                    </strong></td>
			</tr>
		</tfoot>
	</table>

<?php } ?>
