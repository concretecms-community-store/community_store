<?php 
defined('C5_EXECUTE') or die("Access Denied.");
extract($vars); ?>


<div class="row">
    <div class="col-xs-12 col-sm-6">
        <div class="form-group">
            <?= $form->label('fixedRate',t("Fixed Rate")); ?>
            <?= $form->text('fixedRate',$smtm->getFixedRate()); ?>
        </div>
    </div>
    <div class="col-xs-12 col-sm-6">
        <div class="form-group">
            <?= $form->label('percentageRate',t("Percentage Rate")); ?>
            <?= $form->text('percentageRate',$smtm->getPercentageRate()); ?>
        </div>
    </div>
</div>  
<div class="row">
    <div class="col-xs-12 col-sm-6">
        <div class="form-group">
            <?= $form->label('countries',t("Which Countries does this Apply to?")); ?>
            <?= $form->select('countries',array('all'=>t("All Countries"),'selected'=>t("Certain Countries")),$smtm->getCountries()); ?>
        </div>
    </div>
    <div class="col-xs-12 col-sm-6">
        <div class="form-group">
            <?= $form->label('countriesSelected',t("If Certain Countries, which?")); ?>
            <select class="form-control" multiple name="countriesSelected[]">
                <?php $selectedCountries = explode(',',$smtm->getCountriesSelected()); ?>
                <?php foreach($countryList as $code=>$country){?>
                    <option value="<?= $code?>"<?php if(in_array($code,$selectedCountries)){echo " selected";}?>><?= $country?></option>
                <?php } ?>
            </select>
        </div>
    </div>
</div> 
