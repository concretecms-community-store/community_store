<?php
defined('C5_EXECUTE') or die("Access Denied.");

$app = \Concrete\Core\Support\Facade\Application::getFacadeApplication();

extract($vars);
?>

<div class="form-group">
    <?= $form->label('invoiceMinimum', t('Minimum Order Value'))?>
    <?= $form->number("invoiceMinimum", $invoiceMinimum, ['step'=>'0.01']); ?>
</div>

<div class="form-group">
    <?= $form->label('invoiceMaximum', t('Maximum Order Value'))?>
    <?= $form->number("invoiceMaximum", $invoiceMaximum, ['step'=>'0.01']); ?>
</div>

<div class="form-group">
    <?= $form->label('paymentInstructions', t('Payment Instructions'))?>
    <?php $editor = $app->make('editor');
    echo $editor->outputStandardEditor('paymentInstructions', $paymentInstructions);?>
</div>

<div class="form-group">
    <?= $form->label('markPaid', t('Mark Order As Paid'))?>
    <?= $form->select('markPaid', array('0'=>t('No, orders are left as unpaid'), '1'=>t('Yes, orders are immediately marked as paid')), $markPaid); ?>
</div>
