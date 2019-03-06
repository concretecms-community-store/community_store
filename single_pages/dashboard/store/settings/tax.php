<?php
defined('C5_EXECUTE') or die("Access Denied.");
use \Concrete\Core\Support\Facade\Url;
use \Concrete\Core\Support\Facade\Config;

$listViews = array('view','success','updated','removed','class_deleted','class_updated','class_added');
$addViews = array('add','add_rate','edit');
$addClassViews = array('add_class','edit_class','save_class');

if(in_array($controller->getAction(),$addViews)){
/// Add Tax Method View
?>


<form id="settings-tax" action="<?=Url::to('/dashboard/store/settings/tax','add_rate')?>" method="post" data-states-utility="<?=Url::to('/checkout/getstates')?>">
    <?= $token->output('community_store'); ?>
    <div class="row">
        <div class="col-xs-12 col-md-12">
            <input type="hidden" name="taxRateID" value="<?= $taxRate->getTaxRateID()?>">
            <div class="row">
                        <div class="col-xs-12 col-sm-4">
                            <div class="form-group">
                                <?= $form->label('taxEnabled',t('Enable Tax Rate')); ?>
                                <?= $form->select('taxEnabled',array(false=>t('No'),true=>t('Yes')),$taxRate->isEnabled()); ?>
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-4">
                            <div class="form-group">
                                <?= $form->label('taxLabel',t('Tax Label')); ?>
                                <?= $form->text('taxLabel',$taxRate->getTaxLabel());?>
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-4">
                            <div class="form-group">
                                <?= $form->label('taxRate',t('Tax Rate %')); ?>
                                <div class="input-group">
                                    <?= $form->text('taxRate',$taxRate->getTaxRate()); ?>
                                    <div class="input-group-addon">%</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="taxBased"><?= t("Tax is Based on the")?></label>
                        <?= $form->select('taxBased',array('subtotal'=>t("Product Total"),'grandtotal'=>t("Product Total + Shipping")),$taxRate->getTaxBasedOn()); ?>
                    </div>

                    <h3><?= t("When to Charge Tax")?></h3>


                    <div class="row">

                        <div class="col-sm-5">

                            <div class="form-group">
                                <label for="taxAddress" class="control-label"><?= t("If the Customers...")?></label>
                                <?= $form->select('taxAddress',array('shipping'=>t("Shipping Address"),'billing'=>t("Billing Address")),$taxRate->getTaxAddress()); ?>
                            </div>

                        </div>

                        <div class="col-sm-7">
                        <div class="form-horizontal">
                            <p><strong><?= t("Matches...")?></strong></p>
                            <div class="form-group">
                                <label for="taxCountry" class="col-sm-5 control-label"><?= t("Country")?> <small class="text-muted"><?= t("Required")?></small></label>
                                <div class="col-sm-7">
                                    <?php $country = $taxRate->getTaxCountry(); ?>
                                    <?= $form->select('taxCountry',$countries,$country?$country:'US',array("onchange"=>"updateTaxStates()")); ?>
                                </div>
                            </div>


                            <div class="form-group">
                                <label for="taxState" class="col-sm-5 control-label"><?= t("Region")?> <small class="text-muted"><?= t("Optional")?></small></label>
                                <div class="col-sm-7">
                                    <?php $state = $taxRate->getTaxState(); ?>
                                    <?= $form->select('taxState',$states,$state?$state:""); ?>
                                    <?= $form->hidden("savedTaxState",$state); ?>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="taxState" class="col-sm-5 control-label"><?= t("City")?> <small class="text-muted"><?= t("Optional")?></small></label>
                                <div class="col-sm-7">
                                    <?= $form->text('taxCity',$taxRate->getTaxCity());?>
                                </div>
                            </div>
                        </div>
                        </div>
                    </div>

                <?php if (Config::get('community_store.vat_number')) { ?>
                <h4><?= t("VAT Number Options")?></h4>
                <div class="row">
                    <div class="col-sm-12">
                        <div class="form-group">
                            <div class="checkbox">
                            <label for="taxVatExclude" class="control-label">
                                <?= $form->checkbox('taxVatExclude', 1, $taxRate->getTaxVatExclude()); ?>
                                <strong><?= t("Do not apply tax if a valid VAT number is supplied")?></strong>
                            </label>
                            <p class="help-block"><?= t("If the customer has entered a correctly formatted VAT Number then this tax will not be applied at checkout.")?></p>
                        </div>
                    </div>
                </div>
                <?php } ?>

        </div>
    </div>


    <div class="ccm-dashboard-form-actions-wrapper">
        <div class="ccm-dashboard-form-actions">
            <button class="pull-right btn btn-success" type="submit" ><?= t('%s Tax Rate',$task)?></button>
        </div>
    </div>

</form>

<?php } elseif(in_array($controller->getAction(),$listViews)) { ?>
<div class="ccm-dashboard-header-buttons">
    <a href="<?= Url::to('/dashboard/store/settings/tax','add')?>" class="btn btn-primary"><?= t("Add Tax Rate")?></a>
    <a href="<?= Url::to('/dashboard/store/settings/tax','add_class')?>" class="btn btn-primary"><?= t("Add Tax Class")?></a>
    <a href="<?= Url::to('/dashboard/store/settings')?>" class="btn btn-default"><i class="fa fa-gear"></i> <?= t("General Settings")?></a>
</div>

<div class="dashboard-tax-rates">

	<table class="table table-striped">
        <thead>
            <tr>
                <th><?= t("Tax Class")?></th>
                <th><?= t("Associated Tax Rates")?></th>
                <th class="text-right"><?= t("Actions")?></th>
            </tr>
        </thead>
        <tbody>
            <?php if(count($taxClasses)>0){?>
                <?php foreach($taxClasses as $tc){?>
                    <tr>
                        <td><?= $tc->getTaxClassName()?></td>
                        <td>
                            <?php
                                $taxClassRates = $tc->getTaxClassRates();
                                if($taxClassRates){
                                    foreach($taxClassRates as $taxRate){
                                        echo '<span class="label label-primary">' . $taxRate->getTaxLabel() . '</span> ';
                                    }
                                }
                             ?>
                        </td>
                        <td class="text-right">
                            <a href="<?=Url::to('/dashboard/store/settings/tax/edit_class',$tc->getID())?>" class="btn btn-default"><?= t("Edit")?></a>
                            <?php if(!$tc->isLocked()){?>
                            <a href="<?=Url::to('/dashboard/store/settings/tax/delete_class',$tc->getID())?>" class="btn btn-danger"><?= t("Delete")?></a>
                            <?php } ?>
                        </td>
                    </tr>
                 <?php } ?>
            <?php } ?>
        </tbody>
    </table>

	<table class="table table-striped">
		<thead>
			<tr>
                <th><?= t("Tax Rate")?></th>
                <th><?= t("Rate")?></th>
                <th><?= t("Enabled")?></th>
                <th><?= t('Applies To'); ?></th>
                <th class="text-right"><?= t("Actions")?></th>
            </tr>
		</thead>
		<tbody>
		    <?php if(count($taxRates)>0){?>
		        <?php foreach($taxRates as $tr){?>
        			<tr>
        				<td><?= $tr->getTaxLabel()?></td>
                        <td><?= $tr->getTaxRate()?>%</td>
                        <td><?= ($tr->isEnabled() ? t('Yes') : t('No'))?></td>
                        <td><?= implode(", ", array_filter([$tr->getTaxCity(), $tr->getTaxState(), $tr->getTaxCountry()])); ?></td>
                        <td class="text-right">
        					<a href="<?=Url::to('/dashboard/store/settings/tax/edit',$tr->getTaxRateID())?>" class="btn btn-default"><?= t("Edit")?></a>
        					<a href="<?=Url::to('/dashboard/store/settings/tax/delete',$tr->getTaxRateID())?>" class="btn btn-danger"><?= t("Delete")?></a>
        				</td>
        			</tr>
			     <?php } ?>
			<?php } ?>
		</tbody>
	</table>

</div>

<?php } elseif(in_array($controller->getAction(),$addClassViews)){ ?>

<form id="settings-tax" action="<?=Url::to('/dashboard/store/settings/tax','save_class')?>" method="post" data-states-utility="<?=Url::to('/checkout/getstates')?>">
    <?= $token->output('community_store'); ?>
    <div class="row">
        <div class="col-xs-12 col-md-12">
            <input type="hidden" name="taxClassID" value="<?= $tc->getID()?>">
            <div class="form-group">
                <?= $form->label('taxClassName',t("Tax Class Name")); ?>
                <?= $form->text('taxClassName',$tc->getTaxClassName()); ?>
            </div>
            <?php if(Config::get("communitystore.calculation")=="extract"){?>
                <div class="alert alert-info">
                    <?= t("Since you're prices INCLUDE Tax, you can only specify one tax rate per class. If you need more, you must change this setting in the %stax setting here%s",'<a href="'.Url::to('/dashboard/store/settings').'">','</a>')?>
                </div>
            <?php } ?>
            <div class="form-group">
                <?= $form->label('taxClassRates[]',t("Select Tax Class Rates"));
                $sizeswap = 10;
                ?>
                <div class="ccm-search-field-content ccm-search-field-content-select2">
                <select name="taxClassRates[]" class="taxclassRates select2-select <?= (count($taxRates) < $sizeswap ? '' : 'form-control');?>" multiple="multiple"  style="width: 100%; <?= (count($taxRates) < $sizeswap ? '' : 'height: 200px;');?>">
                    <?php
                        $selectedTaxRates = $tc->getTaxClassRateIDs();
                        if(count($taxRates)){
                            foreach($taxRates as $taxRate){?>
                                <option value="<?= $taxRate->getTaxRateID()?>" <?php if(in_array($taxRate->getTaxRateID(), $selectedTaxRates)){echo "selected";}?>><?= $taxRate->getTaxLabel()?></option>
                    <?php
                            }
                        }
                    ?>
                </select>
                </div>
                <?php if(count($taxRates) < $sizeswap){ ?>
                <script>
                    $(document).ready(function() {
                        $('.taxclassRates').select2();
                    });
                </script>
                <?php } ?>

            </div>
        </div>
    </div>


    <div class="ccm-dashboard-form-actions-wrapper">
        <div class="ccm-dashboard-form-actions">
            <a href="<?= Url::to('/dashboard/store/settings/tax')?>" class="btn btn-default pull-left"><?= t("Cancel / View Taxes")?></a>
            <button class="pull-right btn btn-success" type="submit" ><?= t('%s Tax Rate',$task)?></button>
        </div>
    </div>

</form>

<?php } ?>