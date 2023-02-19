<?php
defined('C5_EXECUTE') or die('Access Denied.');
extract($vars); ?>


<div class="row">
    <div class="col-sm-6">
        <div class="form-group">
            <?= $form->label('baseRate', t('Base Price')); ?>
            <?= $form->text('baseRate', $smtm->getBaseRate()); ?>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="row">
            <div class="col-sm-6">
                 <div class="form-group">
                    <?= $form->label('rateType', t('Rate Based On')); ?>
                    <?= $form->select('rateType', ['quantity' => t('Quantity'), 'weight' => t('Weight')], $smtm->getRateType()); ?>
                </div>
            </div>
            <div class="col-sm-6">
                <div id="rate-type-quantity" class="form-group rate-type">
                    <?= $form->label('perItemRate', t('Price per Item')); ?>
                    <?= $form->text('perItemRate', $smtm->getPerItemRate()); ?>
                </div>
                <div id="rate-type-weight" class="form-group rate-type">
                    <?= $form->label('perWeightRate', t('Price per ') . Config::get('community_store.weightUnit')); ?>
                    <?= $form->text('perWeightRate', $smtm->getPerWeightRate()); ?>
                </div>
            </div>

        </div>
    </div>
</div>
<div class="row">
    <div class="col-sm-6">
        <div class="form-group">
            <?= $form->label('countries', t('Which Countries does this Apply to?')); ?>
            <?= $form->select('countries', ['all' => t('All Countries'), 'selected' => t('Certain Countries')], $smtm->getCountries()); ?>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="form-group">
            <?= $form->label('countriesSelected', t('If Certain Countries, which?')); ?>
            <select class="form-control" multiple name="countriesSelected[]">
                <?php $selectedCountries = explode(',', $smtm->getCountriesSelected()); ?>
                <?php foreach($countryList as $code => $country){?>
                    <option value="<?= $code?>"<?php if(in_array($code, $selectedCountries)){echo ' selected'; }?>><?= $country?></option>
                <?php } ?>
            </select>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-sm-6">
        <div class="form-group">
            <?= $form->label('minimumAmount', t('Minimum Purchase Amount for this rate to apply')); ?>
            <?= $form->text('minimumAmount', $smtm->getMinimumAmount() ? $smtm->getMinimumAmount() : '0'); ?>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="form-group">
            <?= $form->label('maximumAmount', t('Maximum Purchase Amount for this rate to apply')); ?>
            <?= $form->text('maximumAmount', $smtm->getMaximumAmount() ? $smtm->getMaximumAmount() : '0'); ?>
            <p class="help-block"><?= t('Leave at 0 for no maximum')?></p>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-sm-6">
        <div class="form-group">
            <?= $form->label('minimumWeight', t('Minimum Weight Amount for this rate to apply')); ?>
            <div class="input-group">
                <?= $form->text('minimumWeight', $smtm->getMinimumWeight() ? $smtm->getMinimumWeight() : '0'); ?>
                <div class="input-group-addon input-group-text"><?=Config::get('community_store.weightUnit')?></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="form-group">
            <?= $form->label('maximumWeight', t('Maximum Weight Amount for this rate to apply')); ?>
            <div class="input-group">
                <?= $form->text('maximumWeight', $smtm->getMaximumWeight() ? $smtm->getMaximumWeight() : '0'); ?>
                <div class="input-group-addon input-group-text"><?=Config::get('community_store.weightUnit')?></div>
            </div>
            <p class="help-block"><?= t('Leave at 0 for no maximum')?></p>
        </div>
    </div>
</div>
<script type="text/javascript">
function showRateType() {
    var selectedOptionValue = $("#rateType option:selected").val();
    if (selectedOptionValue === 'quantity') {
        $(".rate-type").hide();
        $("#rate-type-quantity").show();
    } else if (selectedOptionValue === 'weight') {
        $(".rate-type").hide();
        $("#rate-type-weight").show();
    }
}
showRateType();
$("#rateType").change(function(){
   showRateType();
});

</script>
