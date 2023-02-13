<?php defined('C5_EXECUTE') or die("Access Denied.");

use \Concrete\Package\CommunityStore\Src\CommunityStore\Discount\DiscountRule;
use \Concrete\Core\Support\Facade\Url;
use \Concrete\Core\Support\Facade\Config;

$app = \Concrete\Core\Support\Facade\Application::getFacadeApplication();

$form = $app->make('helper/form');
$date = $app->make('helper/form/date_time');
$dfh = $app->make('helper/date');

$listViews = array('view','updated','removed','success', 'deleted');
$addViews = array('add','edit','save');
$codeViews = array('codes', 'addcodes');

$currencySymbol = Config::get('community_store.symbol');

$version = $app->make('config')->get('concrete.version');
$badgeClass = ' badge ';
if (version_compare($version, '9.0', '<')) {
    $badgeClass = '';
}

?>


<?php if (in_array($controller->getAction(), $listViews)){ ?>
    <div class="ccm-dashboard-header-buttons">
        <a href="<?= Url::to('/dashboard/store/discounts/', 'add')?>" class="btn btn-primary"><?= t("Add Discount Rule")?></a>
	</div>

    <?php
    $version = $app->make('config')->get('concrete.version');
    if (version_compare($version, '9.0', '<')) { ?>
    <form role="form" class="form-inline ccm-search-fields">
        <div class="ccm-search-fields-row ccm-search-fields-submit">
            <div class="form-group">
                <div class="ccm-search-main-lookup-field">
                    <i class="fa fa-search"></i>
                    <?= $form->search('keywords', $searchRequest['keywords'], ['placeholder' => t('Search Discounts'), 'style'=>"min-width: 300px"]) ?>
                </div>

            </div>
            <button type="submit" class="btn btn-default btn-secondary"><?= t('Search') ?></button>
        </div>
    </form>
    <?php } ?>

<?php if(count($discounts)>0) { ?>

        <div class="ccm-dashboard-content-full">
        <table class="ccm-search-results-table">
            <thead>
                <tr>
                    <th><a><?= t('Name')?></a></th>
                    <th><a><?= t('Displayed Text')?></a></th>
                    <th><a><?= t('Discount')?></a></th>
                    <th><a><?= t('Applies')?></a></th>
                    <th><a><?= t('Availability')?></a></th>
                    <th><a><?= t('Actions')?></a></th>
                </tr>
            </thead>
            <tbody>

                <?php
                    foreach ($discounts as $discountRule) {

                        $usergroups = $discountRule->getUserGroups();
                        $productgroups = $discountRule->getProductGroups();
                        $deducttype = $discountRule->getDeductType();
                        $deductfrom = $discountRule->getDeductFrom();

                        $discountRuleDeduct = $discountRule->getDeductFrom();

                        if (empty($productgroups) && $deducttype == 'percentage' ) {
                            $discountRuleDeduct = t('from all products');
                        }

                        if (!empty($productgroups) && $deducttype == 'percentage' ) {
                            $discountRuleDeduct = t('from matching products');
                        }

                        if (!empty($productgroups) && $deducttype == 'percentage' && $deductfrom == 'shipping') {
                            $discountRuleDeduct = t('from shipping when at least one product matches');
                        }

                        if (!empty($productgroups) && $deducttype == 'value_all' ) {
                            $discountRuleDeduct = t('from each matching product');
                        }

                        if (empty($productgroups) && $deducttype == 'value_all' ) {
                            $discountRuleDeduct = t('from each product');
                            $discountRuleDeduct = t('from each product');
                        }

                        if ($deducttype == 'percentage' && $deductfrom == 'shipping' ) {
                            $discountRuleDeduct = t('from shipping');
                        }

                        if (($deducttype == 'value_all' || $deducttype == 'value') && $deductfrom == 'shipping') {
                            $discountRuleDeduct = t('from shipping');
                        }

                        if (!empty($productgroups) && $deducttype == 'fixed' ) {
                            $discountRuleDeduct = t('set as price for all matching products');
                        }

                        if (empty($productgroups) && $deducttype == 'fixed' ) {
                            $discountRuleDeduct = t('set as price for all products');
                        }

                        if ($deducttype == 'fixed' && $deductfrom == 'shipping') {
                            $discountRuleDeduct = t('set as price for shipping');
                        }

                        ?>
                        <tr>
                            <td><strong><a href="<?= Url::to('/dashboard/store/discounts/edit/', $discountRule->getID())?>"><?= h($discountRule->getName()); ?></a></strong>
                            <br />
                                <?php if(!$discountRule->isEnabled()){ ?>
                                    <span class="label label-danger"><?= t('Disabled')?></span>
                                <?php } else { ?>
                                    <span class="label label-success"><?= t('Enabled')?></span>
                                <?php } ?>

                            </td>
                            <td><?= h($discountRule->getDisplay()); ?></td>
                            <td>
                                <?php if ($deducttype == 'percentage') {
                                   echo  h($discountRule->getPercentage()) . '% ' . t($discountRuleDeduct);
                                } else {
                                    echo $currencySymbol .  h($discountRule->getValue()) . ' ' . $discountRuleDeduct;
                                }
                                ?>
                            </td>
                            <td><?php

                                if ($discountRule->getTrigger() == 'auto') {
                                    echo '<span class="label label-warning '.$badgeClass.' bg-warning">' . t('automatically') . '</span><br />';
                                } else {
                                    if ($discountRule->isSingleUse()) {
                                        echo '<span class="label label-primary badge bg-primary">' . t('when single use code entered'). '</span><br />';
                                        echo '<span class="label '. $badgeClass  .  ($discountRule->availableCodes <= 0 ? 'label-danger bg-danger' : 'label-primary bg-primary'). '">' . t2(
                                            /* i18n: %1$s is the number of available codes, %2$s is the total number of codes*/
                                            '%1$s of %2$s code available',
                                            '%1$s of %2$s codes available'
                                        ) . '</span><br />';
                                    } else {
                                        echo '<span class="label label-primary">' . t('when code entered'). '</span><br />';
                                        echo '<span class="label '  . $badgeClass .  ($discountRule->availableCodes <= 0 ? 'label-danger bg-danger' : 'label-primary bg-primary') . '">' . t2(
                                            /* i18n: %s is a number */
                                            '%s code configured',
                                            '%s codes configured'
                                        ) . '</span><br />';
                                    }

                                }

                                if (!empty($productgroups)) {

                                    echo '<span class="label label-primary ' . $badgeClass . ' bg-primary">';

                                    if ($deducttype == 'percentage') {
                                       echo t('to specific product groups');
                                    } else {
                                        echo t('when product groups found in cart');
                                    }

                                    echo '</span><br />';
                                }

                                if (!empty($usergroups)) {
                                    echo '<span class="label label-primary ' . $badgeClass . ' bg-primary">' . t('to specific user groups'). '</span><br />';
                                }

                                if ($discountRule->getQuantity() > 0 || $discountRule->getMaximumQuantity() > 0) {
                                    echo '<span class="label label-primary ' . $badgeClass . ' bg-primary">';

                                    if ($discountRule->getMaximumQuantity() > 0) {
                                        echo  t('when %s to %s products in cart', $discountRule->getQuantity() + 0, $discountRule->getMaximumQuantity() + 0);
                                    } else {
                                        echo  t('when %s or more products in cart', $discountRule->getQuantity() + 0);
                                    }

                                    echo '</span><br />';
                                }


                                ?></td>
                            <td>


                                <?php
                                $restrictions = '';

                                if ($discountRule->getValidFrom() > 0) {
                                    $restrictions .= ' ' . tc(/* i18n: %s is a date/time */'DiscountRule', 'starts %s', $dfh->formatDateTime($discountRule->getValidFrom()));
                                }

                                if ($discountRule->getValidTo() > 0) {
                                    $restrictions .= ' '. tc(/* i18n: %s is a date/time */'DiscountRule', 'expires %s', $dfh->formatDateTime($discountRule->getValidTo()));
                                }

                                if (!$restrictions) {
                                    $restrictions = t('always');
                                }


                                echo trim($restrictions);

                                ?>


                            </td>

                            <td>
                                <p><a class="btn btn-primary btn-sm" href="<?= Url::to('/dashboard/store/discounts/edit/', $discountRule->getID())?>"><?= t('Manage'); ?></a></p>
                                <?php
                                if ($discountRule->getTrigger() == 'code') {
                                    echo '<p>' . '<a class="btn btn-primary btn-sm" href="'. Url::to('/dashboard/store/discounts/codes/', $discountRule->getID()).'"><i class="fa fa-key"></i> '.t('Manage Codes').'</a></p>';
                                } ?>

                            </td>
                        </tr>
                    <?php } ?>
            </tbody>
        </table>
    <?php } else { ?>
        <p class="alert alert-info"><?= t('No Discount Rules have been defined'); ?></p>
    <?php } ?>



        <?php if ($paginator->getTotalPages() > 1) { ?>
        <div class="ccm-search-results-pagination">
            <?= $pagination ?>
        </div>
        <?php } ?>

    </div>

<style>
    @media (max-width: 992px) {
        div#ccm-dashboard-content div.ccm-dashboard-content-full {
            margin-left: -20px !important;
            margin-right: -20px !important;
        }
    }
</style>

<?php } ?>

<?php if (in_array($controller->getAction(), $addViews)){ ?>

    <?php if ($controller->getAction() == 'edit') { ?>
        <div class="ccm-dashboard-header-buttons">
            <form method="post" id="deleterule" action="<?= Url::to('/dashboard/store/discounts/delete/')?>">
                <?= $token->output('community_store'); ?>
                <input type="hidden" name="drID" value="<?= $discountRule->getID(); ?>" />
                <button class="btn btn-danger" ><?= t('Delete'); ?></button>
            </form>
        </div>
    <?php } ?>


    <form method="post" action="<?= $this->action('save')?>" id="discount-add">
        <?= $token->output('community_store'); ?>

    <div class="ccm-pane-body">

        <?php if(!is_object($discountRule)){
            $discountRule = new DiscountRule(); //does nothing other than shutup errors.
            $discountRule->setTrigger('auto');
            $discountRule->setDeductType('percentage');
        }
        ?>

        <input type="hidden" name="drID" value="<?= $discountRule->getID()?>"/>

        <div class="form-group">
            <?= $form->label('drName', t('Name'))?>
            <?= $form->text('drName', $discountRule->getName(), array('required'=>'required'))?>
        </div>

        <div class="form-group">
            <?= $form->label('drEnabled', t('Enabled'))?>
            <?= $form->select('drEnabled', array('1'=>t('Yes'), '0'=>t('No')), $discountRule->isEnabled() ? 1: 0)?>
        </div>

        <div class="form-group">
            <?= $form->label('drDisplay', t('Display Text'))?>
            <?= $form->text('drDisplay', $discountRule->getDisplay(), array('required'=>'required'))?>
        </div>

        <div class="form-group">
            <div class="row">
                <div class="col-md-5">
                    <?= $form->label('drDeductType', t('Deduction Type'))?>
                    <div class="radio"><label><?= $form->radio('drDeductType', 'percentage', ($discountRule->getDeductType() == 'percentage'))?> <?= t('Percentage'); ?></label></div>
                    <div class="radio"><label><?= $form->radio('drDeductType', 'value', ($discountRule->getDeductType() == 'value'))?> <?= t('Deduct value once'); ?></label></div>
                    <div class="radio"><label><?= $form->radio('drDeductType', 'value_all', ($discountRule->getDeductType() == 'value_all'))?> <?= t('Deduct value from each matching item'); ?></label></div>
                    <div class="radio"><label><?= $form->radio('drDeductType', 'fixed', ($discountRule->getDeductType() == 'fixed'))?> <?= t('Change to value'); ?></label></div>
                </div>
                <div class="col-md-7 row">
                    <?php
                    $fieldrequired = array('required'=>'required');
                    $visibility = '';
                    if($discountRule->getDeductType() == 'value' || $discountRule->getDeductType() == 'value_all' || $discountRule->getDeductType() == 'fixed') {
                        $fieldrequired = array();
                        $visibility = 'style="display: none;"';
                    } ?>

                    <div class="form-group col-md-8"  id="percentageinput"  <?= $visibility; ?>>
                        <?= $form->label('drPercentage', t('Percentage Discount'))?>
                        <div class="input-group">
                            <?= $form->text('drPercentage', $discountRule->getPercentage(), $fieldrequired)?>
                            <div class="input-group-addon input-group-text">%</div>
                        </div>
                    </div>

                    <?php
                    $fieldrequired = array('required'=>'required');
                    $visibility = '';
                    if($discountRule->getDeductType() == 'percentage') {
                        $fieldrequired = array();
                        $visibility = 'style="display: none;"';
                    } ?>

                    <div class="form-group col-md-8" id="valueinput" <?= $visibility; ?>>
                        <?= $form->label('drValue', t('Value'))?>
                        <div class="input-group">
                            <div class="input-group-addon input-group-text"><?= $currencySymbol; ?></div>
                            <?= $form->text('drValue', $discountRule->getValue(), $fieldrequired)?>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div class="form-group">
            <?= $form->label('drDeductFrom', t('Apply To'))?>
            <?= $form->select('drDeductFrom', array('subtotal'=>t('Items In Cart'), 'shipping' => t('Shipping')), $discountRule->getDeductFrom())?>
        </div>

      <div class="form-group">
          <?= $form->label('drDiscountSalePrices', t('Discount Sale Prices'))?>
          <?= $form->select('drDiscountSalePrices', array('0'=>t('No, sale prices will not be discounted'), '1' => t('Yes, sale prices will have discounts applied')), $discountRule->getDiscountSalePrices())?>
      </div>

        <div class="form-group">
            <?= $form->label('drTrigger', t('Apply'))?>
            <div class="radio"><label><?= $form->radio('drTrigger', 'auto', ($discountRule->getTrigger() == 'auto'))?> <?= t('Automatically, (when matching all restrictions)'); ?></label></div>
            <div class="radio"><label><?= $form->radio('drTrigger', 'code', ($discountRule->getTrigger() == 'code'))?> <?= t('When valid code entered'); ?></label></div>
        </div>


        <div id="codefields" <?= ($discountRule->getTrigger() == 'auto' ? 'style="display: none;"' : ''); ?>>
            <div class="form-group">
                <label for="drSingleUseCodes"><?= $form->checkbox('drSingleUseCodes', '1',$discountRule->isSingleUse())?> <?= t('Single Use Codes'); ?></label>
            </div>
            <?php if (!$discountRule->getID()) { ?>
            <p class="alert alert-info"><?= t('Codes can be entered after creating rule');?></p>
            <?php } ?>
        </div>

        <?php if ($discountRule->getID() && $discountRule->getTrigger() == 'code') { ?>
            <p><a class="btn btn-default btn-secondary btn-sm" href="<?= Url::to('/dashboard/store/discounts/codes/', $discountRule->getID()); ?>"><?= t('Manage Codes'); ?></a></p><br>
       <?php } ?>

<!--       <field name="drCurrency" type="C" size="20"></field>-->

        <fieldset><legend><?= t('Restrictions');?></legend>

            <div class="form-group">

                <?= $form->label('drValidFrom', t('Starts'))?>
                <div class="row">
                    <div class="col-md-4">
                        <?= $form->select('validFrom', array('0'=>t('Immedately'), '1'=>t('From a specified date')), ($discountRule->getValidFrom() > 0 ? '1' : '0') )?>
                    </div>
                    <div class="col-md-8" id="fromdate" <?= ($discountRule->getValidFrom() ? '' : 'style="display: none;"'); ?>>
                        <?= $date->datetime('drValidFrom', $discountRule->getValidFrom());?>
                    </div>
                </div>

            </div>

            <div class="form-group">
                <?= $form->label('drValidTo', t('Ends'))?>
                <div class="row">
                    <div class="col-md-4">
                        <?= $form->select('validTo', array('0'=>t('Never'), '1'=>t('At a specified date')),  ($discountRule->getValidTo() > 0 ? '1' : '0') )?>
                    </div>
                    <div class="col-md-8" id="todate" <?= ($discountRule->getValidTo() ? '' : 'style="display: none;"'); ?>>
                         <?= $date->datetime('drValidTo', $discountRule->getValidTo())?>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <?= $form->label('drProductGroups', t('Product Groups'))?>
                <div class="row">
                    <div class="col-md-12">
                        <div class="ccm-search-field-content ccm-search-field-content-select2">
                            <select multiple="multiple" name="drProductGroups[]" class="selectize" style="width: 100%"
                                    placeholder="<?= (empty($productgroups) ? t('No Product Groups Available') :  t('Select Product Groups')); ?>">
                                <?php
                                if (!empty($productgroups)) {
                                    foreach ($productgroups as $pgkey=>$pglabel) { ?>
                                        <option value="<?= $pgkey;?>" <?= (in_array($pgkey, $selectedproductgroups) ? 'selected="selected"' : ''); ?>>  <?= $pglabel; ?></option>
                                    <?php   }
                                } ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <?= $form->label("usergroups", t("User Groups"));?>
                <div class="ccm-search-field-content ccm-search-field-content-select2">
                    <select multiple="multiple" name="drUserGroups[]" id="groupselect" class="selectize" style="width: 100%;" placeholder="<?= t('Select User Groups');?>">
                        <?php
                        foreach ($usergroups as $ugkey=>$uglabel) { ?>
                            <option value="<?= $ugkey;?>" <?= (in_array($ugkey, $selectedusergroups) ? 'selected="selected"' : ''); ?>>  <?= $uglabel; ?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>


            <script>
                $(document).ready(function() {
                    $('.selectize').selectize({
                        plugins: ['remove_button'],
                        selectOnTab: true
                    });
                    $('.selectize').removeClass('form-control');
                });
            </script>


            <div class="form-group">
                <?= $form->label("drQuantity", t("Minimum Quantity in Cart"));?>
                <?= $form->text('drQuantity', $discountRule->getQuantity())?>
            </div>


            <div class="form-group">
                <?= $form->label("drMaximumQuantity", t("Maximum Quantity in Cart"));?>
                <?= $form->text('drMaximumQuantity', $discountRule->getMaximumQuantity())?>
            </div>

            <div class="form-group">
                <?= $form->label('drDescription', t('Description / Notes'))?>
                <?= $form->textarea('drDescription', $discountRule->getDescription())?>
            </div>

        </fieldset>



        <br /><br /><br /><br />

    </div>

    <div class="ccm-dashboard-form-actions-wrapper">
        <div class="ccm-dashboard-form-actions">
            <a href="<?= Url::to('/dashboard/store/discounts' . ($keywordsSearch ? '?keywords='.urlencode($keywordsSearch) : ''))?>" class="btn btn-default btn-secondary"><?= t('Cancel / View All Discount Rules')?></a>
            <button class="pull-right btn btn-primary float-end" type="submit"><?= ($discountRule->getID() > 0 ? t('Update') : t('Add'))?></button>
        </div>
    </div>

</form>

    <script>
        $(function(){
            $('#deleterule').submit(function(e){
                return confirm("<?= t('Are you sure you want to delete this discount rule?');?>");
            });

            $('#drDeductType1').click(function() {
                if($('#drDeductType1').is(':checked')) {
                    $('#percentageinput').show();
                    $('#valueinput').hide();
                    $('#percentageinput input').prop('required', true);
                    $('#valueinput input').prop('required', false);
                }
            });

            $('#drDeductType2').click(function() {
                if($('#drDeductType2').is(':checked')) {
                    $('#percentageinput').hide();
                    $('#valueinput').show();
                    $('#percentageinput input').prop('required', false);
                    $('#valueinput input').prop('required', true);
                }
            });

            $('#drDeductType3').click(function() {
                if($('#drDeductType3').is(':checked')) {
                    $('#percentageinput').hide();
                    $('#valueinput').show();
                    $('#percentageinput input').prop('required', false);
                    $('#valueinput input').prop('required', true);
                }
            });

            $('#drDeductType4').click(function() {
                if($('#drDeductType4').is(':checked')) {
                    $('#percentageinput').hide();
                    $('#valueinput').show();
                    $('#percentageinput input').prop('required', false);
                    $('#valueinput input').prop('required', true);
                }
            });

            $('#drTrigger5').click(function() {
                if($('#drTrigger5').is(':checked')) {
                    $('#codefields').hide();
                }
            });

            $('#drTrigger6').click(function() {
                if($('#drTrigger6').is(':checked')) {
                    $('#codefields').show();
                }
            });

            $('#validFrom').change(function() {
                if ($(this).val() == '1') {
                    $('#fromdate').show();
                } else {
                    $('#fromdate').hide();
                }
            });

            $('#validTo').change(function() {
                if ($(this).val() == '1') {
                    $('#todate').show();
                } else {
                    $('#todate').hide();
                }
            });
        });
    </script>


<?php } ?>



<?php if (in_array($controller->getAction(), $codeViews)){ ?>
<div class="ccm-dashboard-header-buttons">
    <a href="<?= Url::to('/dashboard/store/discounts/edit', $discountRule->getID())?>" class="btn btn-default btn-secondary"><?= t("Edit Discount Rule")?></a>
</div>

<?php if (isset($failedcodes) && count($failedcodes) > 0 ) { ?>
    <p class="alert alert-warning"><?= t('The following codes are invalid or are already active:')  ?><br />
        <strong><?= implode('<br />', $failedcodes); ?></strong>
    </p>
<?php } ?>



<fieldset><legend><?= t('Current Codes'); ?></legend></fieldset>

<p class="alert alert-info">
    <?php if ($discountRule->isSingleUse()) { ?>
        <?= t('Single Use Codes'); ?>
    <?php } else { ?>
        <?= t('Codes can be repeatedly used'); ?>
    <?php } ?>
</p>


<?php if (count($codes) > 0) { ?>
        <table class="table table-bordered">
            <tr><th><?= t('Code'); ?></th>

                <?php if ($discountRule->isSingleUse()) { ?>
                <th><?=  t('Used'); ?></th>
                <?php } ?>

                <th></th></tr>

            <?php foreach($codes as $code) { ?>
                    <?php if ($discountRule->isSingleUse()) { ?>

                        <?php if ($code->isUsed()) { ?>
                            <tr>
                                <td><del><?= $code->getCode(); ?></del></td>
                                <td><a class="btn btn-default btn-secondary btn-xs" href="<?= Url::to('/dashboard/store/orders/order/', $code->getOID()); ?>"><?= t('View Order'); ?></a></td>
                                <td></td>
                            </tr>
                        <?php } else { ?>
                            <tr>
                                <td><?= $code->getCode(); ?></td>
                                <td><span class="label label-success"><?= t('Available'); ?></span></td>
                            <td>
                                <form method="post" action="<?= Url::to('/dashboard/store/discounts/deletecode/')?>">
                                    <?= $token->output('community_store'); ?>
                                    <input type="hidden" name="dcID" value="<?= $code->getID();?>" />
                                    <button class="btn btn-danger"><i class="fa fa-trash"></i></button>
                                </form>
                                </td>
                                </tr>
                        <?php }  ?>
                    <?php } else { ?>
                        <tr>
                            <td><?= $code->getCode(); ?></td>
                            <td>
                                <form method="post" action="<?= Url::to('/dashboard/store/discounts/deletecode/')?>">
                                    <?= $token->output('community_store'); ?>
                                    <input type="hidden" name="dcID" value="<?= $code->getID();?>" />
                                    <button class="btn btn-danger" ><i class="fa fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    <?php } ?>


            <?php } ?>

        </table>

    <?php } else { ?>
    <p class="alert alert-warning"><?= t('No codes specified');?></p>

    <?php } ?>
<br />
<form method="post" action="<?= Url::to('/dashboard/store/discounts/addcodes', $discountRule->getID())?>" id="codes-add">
    <?= $token->output('community_store'); ?>
    <fieldset><legend><?= t('Add Codes'); ?></legend>

    <div class="form-group">
        <?= $form->label('codes', t('Code(s)'))?>
        <?= $form->textarea('codes', '')?>
        <span class="help-block"><?= t('Seperate codes via lines or commas. Codes are case-insensitive.'); ?></span>
    </div>

    <div class="form-group">
        <button type="submit" class="btn btn-success"><?= t('Add Codes'); ?></button>
    </div>

</fieldset>

    <div class="ccm-dashboard-form-actions-wrapper">
        <div class="ccm-dashboard-form-actions">
            <a href="<?= Url::to('/dashboard/store/discounts' . ($keywordsSearch ? '?keywords='.urlencode($keywordsSearch) : '')) ?>" class="btn btn-default btn-secondary"><?= t('Return to Discount Rules')?></a>
        </div>
    </div>

<?php } ?>
