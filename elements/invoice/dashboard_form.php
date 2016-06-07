<?php 
defined('C5_EXECUTE') or die(_("Access Denied."));
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
    <?php $editor = \Core::make('editor');
    echo $editor->outputStandardEditor('paymentInstructions', $paymentInstructions);?>
</div>


