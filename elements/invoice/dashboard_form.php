<?php
defined('C5_EXECUTE') or die("Access Denied.");

$app = \Concrete\Core\Support\Facade\Application::getFacadeApplication();

extract($vars);
?>

<div class="form-group">
    <label><?= t("Minimum Order Value")?></label>
<input type="text" name="invoiceMinimum" value="<?= $invoiceMinimum?>" class="form-control">
</div>

<div class="form-group">
    <label><?= t("Maximum Order Value")?></label>
    <input type="text" name="invoiceMaximum" value="<?= $invoiceMaximum?>" class="form-control">
</div>

<div class="form-group">
    <label><?= t("Payment Instructions")?></label>
    <?php $editor = $app->make('editor');
    echo $editor->outputStandardEditor('paymentInstructions', $paymentInstructions);?>
</div>

<div class="form-group">
    <label><?= t("Mark Order As Paid")?></label>
    <?= $form->select('markPaid', array('0'=>t('No, orders are left as unpaid'), '1'=>t('Yes, orders are immediately marked as paid')), $markPaid); ?>
</div>
