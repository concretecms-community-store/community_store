<?php 
defined('C5_EXECUTE') or die("Access Denied.");
use \Concrete\Package\CommunityStore\Src\CommunityStore\Discount\DiscountRule;

$form = Core::make('helper/form');
$date = Core::make('helper/form/date_time');
$dfh = Core::make('helper/date');

$listViews = array('view','updated','removed','success', 'deleted');
$addViews = array('add','edit','save');
$codeViews = array('codes', 'addcodes');

$currencySymbol = Config::get('communitystore.symbol');

?>


<?php if (in_array($controller->getTask(), $listViews)){ ?>
    <div class="ccm-dashboard-header-buttons">
        <a href="<?= View::url('/dashboard/store/discounts/', 'add')?>" class="btn btn-primary"><?= t("Add Discount Rule")?></a>
	</div>


    <div class="ccm-dashboard-content-full">
        <table class="ccm-search-results-table">
            <thead>
                <th><a><?= t('Name')?></a></th>
                <th><a><?= t('Display')?></a></th>
                <th><a><?= t('Discount')?></a></th>
                <th><a><?= t('Applies')?></a></th>
                <th><a><?= t('Availability')?></a></th>
                <th><a><?= t('Enabled')?></a></th>
                <th><a><?= t('Actions')?></a></th>
            </thead>
            <tbody>

                <?php if(count($discounts)>0) {
                    foreach ($discounts as $d) {
                        ?>
                        <tr>
                            <td><strong><a href="<?= View::url('/dashboard/store/discounts/edit/', $d->getID())?>"><?= h($d->getDiscountName()); ?></a></strong></td>
                            <td><?= h($d->getDiscountDisplay()); ?></td>
                            <td>
                                <?php if ($d->getDiscountDeductType() == 'percentage') {
                                   echo  h($d->getDiscountPercentage()) . '% ' . t('from') . ' ' . h($d->getDiscountDeductFrom());
                                } else {
                                    echo $currencySymbol .  h($d->getDiscountValue()) . ' ' . t('from') . ' ' . h($d->getDiscountDeductFrom());
                                }
                                ?>
                            </td>
                            <td><?php

                                if ($d->getDiscountTrigger() == 'auto') {
                                    echo '<span class="label label-warning">' . t('automatically') . '</span><br />';
                                } else {
                                    if ($d->isSingleUse()) {
                                        echo '<span class="label label-primary">' . t('when single use code entered'). '</span><br />';
                                        echo '<span class="label ' .  ($d->availableCodes <= 0 ? 'label-danger' : 'label-primary'). '">' . $d->availableCodes . ' ' . t('of') . ' ' . $d->totalCodes . ' ' . t('codes available') . '</span><br />';
                                    } else {
                                        echo '<span class="label label-primary">' . t('when code entered'). '</span><br />';
                                        echo '<span class="label ' .  ($d->availableCodes <= 0 ? 'label-danger' : 'label-primary') . '">' . $d->availableCodes . ' ' . ($d->availableCodes == 1 ? t('code') : t('codes')) .' '.  t('configured') . '</span><br />';
                                    }

                                }
                                 ?></td>
                            <td>
                                <?php
                                $restrictions = '';

                                if ($d->getDiscountValidFrom() > 0) {
                                    $restrictions .= ' ' . t('starts') . ' ' . $dfh->formatDateTime($d->getDiscountValidFrom());
                                }

                                if ($d->getDiscountValidTo() > 0) {
                                    $restrictions .= ' '. t('expires') . ' ' . $dfh->formatDateTime($d->getDiscountValidTo());
                                }

                                if (!$restrictions) {
                                    $restrictions = t('always');
                                }


                                echo trim($restrictions);

                                ?>


                            </td>
                            <td>
                                <?php if(!$d->isEnabled()){ ?>
                                    <span class="label label-danger"><?= t('Disabled')?></span>
                                <?php } else { ?>
                                    <span class="label label-success"><?= t('Enabled')?></span>
                                <?php } ?>
                            </td>
                            <td>
                                <p><a class="btn btn-default" href="<?= View::url('/dashboard/store/discounts/edit/', $d->getID())?>"><i class="fa fa-pencil"></i></a></p>
                                <?php
                                if ($d->getDiscountTrigger() == 'code') {
                                    echo '<p>' . '<a class="btn btn-default btn-sm" href="'.View::url('/dashboard/store/discounts/codes/', $d->getID()).'">'.t('Manage Codes').'</a></p>';
                                } ?>

                            </td>
                        </tr>
                    <?php }
                }?>
            </tbody>
        </table>


        <?php if ($paginator->getTotalPages() > 1) { ?>
            <?= $pagination ?>
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

<?php if (in_array($controller->getTask(), $addViews)){ ?>

    <?php if ($controller->getTask() == 'edit') { ?>
        <div class="ccm-dashboard-header-buttons">
            <form method="post" id="deleterule" action="<?= View::url('/dashboard/store/discounts/delete/')?>">
                <input type="hidden" name="drID" value="<?= $d->getID(); ?>" />
                <button class="btn btn-danger" ><?= t('Delete'); ?></button>
            </form>
        </div>
    <?php } ?>


    <form method="post" action="<?= $this->action('save')?>" id="discount-add">


    <div class="ccm-pane-body">

        <?php if(!is_object($d)){
            $d = new DiscountRule(); //does nothing other than shutup errors.
            $d->setDiscountTrigger('auto');
            $d->setDiscountDeductType('percentage');
        }
        ?>

        <input type="hidden" name="drID" value="<?= $d->getID()?>"/>

        <div class="form-group">
            <?= $form->label('drName', t('Name'))?>
            <?= $form->text('drName', $d->getDiscountName(), array('class' => '', 'required'=>'required'))?>
        </div>

        <div class="form-group">
            <?= $form->label('drEnabled', t('Enabled'))?>
            <?= $form->select('drEnabled', array('1'=>t('Enabled'), '0'=>t('Disabled')), $d->isEnabled(), array('class' => ''))?>
        </div>

        <div class="form-group">
            <?= $form->label('drDisplay', t('Display Text'))?>
            <?= $form->text('drDisplay', $d->getDiscountDisplay(), array('class' => '', 'required'=>'required'))?>
        </div>



        <div class="form-group">
            <div class="row">
                <div class="col-md-3">
                    <?= $form->label('drDeductType', t('Deduction Type'))?>
                    <div class="radio"><label><?= $form->radio('drDeductType', 'percentage', ($d->getDiscountDeductType() == 'percentage'))?> <?= t('Percentage'); ?></label></div>
                    <div class="radio"><label><?= $form->radio('drDeductType', 'value', ($d->getDiscountDeductType() == 'value'))?> <?= t('Value'); ?></label></div>
                </div>
                <div class="col-md-9 row">
                    <div class="form-group col-md-4"  id="percentageinput"  <?= ($d->getDiscountDeductType() == 'value' ? 'style="display: none;"' : ''); ?>>
                        <?= $form->label('drPercentage', t('Percentage Discount'))?>
                        <div class="input-group">
                            <?= $form->text('drPercentage', $d->getDiscountPercentage(), array('class' => ''))?>
                            <div class="input-group-addon">%</div>
                        </div>
                    </div>

                    <div class="form-group col-md-4" id="valueinput" <?= ($d->getDiscountDeductType() == 'percentage' ? 'style="display: none;"' : ''); ?>>
                        <?= $form->label('drValue', t('Value Discount'))?>
                        <div class="input-group">
                            <div class="input-group-addon"><?= $currencySymbol; ?></div>
                            <?= $form->text('drValue', $d->getDiscountValue(), array('class' => ''))?>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div class="form-group">
            <?= $form->label('drDeductFrom', t('Deduct From'))?>
            <?php
            // commenting out following until product and product group matching is implemented
            //echo $form->select('drDeductFrom', array('total' => t('Total, including shipping'), 'subtotal'=>'Items Sub-total', 'shipping' => t('Shipping'), 'product'=> t('Specific Product'), 'group'=> t('Products in Product Group')), $d->drDeductFrom, array('class' => ''))?>
            <?= $form->select('drDeductFrom', array('total' => t('Total, including shipping'), 'subtotal'=>'Items Sub-total', 'shipping' => t('Shipping')), $d->getDiscountDeductFrom(), array('class' => ''))?>
        </div>

        <div class="form-group">
            <?= $form->label('drTrigger', t('Apply'))?>
            <div class="radio"><label><?= $form->radio('drTrigger', 'auto', ($d->getDiscountTrigger() == 'auto'))?> <?= t('Automatically, (when matching all restrictions)'); ?></label></div>
            <div class="radio"><label><?= $form->radio('drTrigger', 'code', ($d->getDiscountTrigger() == 'code'))?> <?= t('When valid code entered'); ?></label></div>
        </div>

        <div id="codefields" <?= ($d->getDiscountTrigger() == 'auto' ? 'style="display: none;"' : ''); ?>>
            <div class="form-group">
                <label for="drSingleUseCodes"><?= $form->checkbox('drSingleUseCodes', '1',$d->isSingleUse())?> <?= t('Single use codes'); ?></label>
            </div>
            <?php if (!$d->getID()) { ?>
            <p class="alert alert-info"><?= t('Codes can be entered after creating rule');?></p>
            <?php } ?>
        </div>

<!--       <field name="drCurrency" type="C" size="20"></field>-->

        <fieldset><legend><?= t('Restrictions');?></legend>

        <div class="form-group">

            <?= $form->label('drValidFrom', t('Starts'))?>
            <div class="row">
                <div class="col-md-4">
                    <?= $form->select('validFrom', array('0'=>t('Immedately'), '1'=>t('From a specified date')), ($d->getDiscountValidFrom() > 0 ? '1' : '0'), array('class' => 'col-md-4'))?>
                </div>
                <div class="col-md-8" id="fromdate" <?= ($d->getDiscountValidFrom() ? '' : 'style="display: none;"'); ?>>
                    <?= $date->datetime('drValidFrom', $d->getDiscountValidFrom());?>
                </div>
            </div>

        </div>

        <div class="form-group">
            <?= $form->label('drValidTo', t('Ends'))?>
            <div class="row">
                <div class="col-md-4">
                    <?= $form->select('validTo', array('0'=>t('Never'), '1'=>t('At a specified date')),  ($d->getDiscountValidTo() > 0 ? '1' : '0'), array('class' => 'col-md-4'))?>
                </div>
                <div class="col-md-8" id="todate" <?= ($d->getDiscountValidTo() ? '' : 'style="display: none;"'); ?>>
                     <?= $date->datetime('drValidTo', $d->getDiscountValidTo())?>
                </div>
            </div>
        </div>

<!--        <h4>--><?php //echo t('Users / Groups');?><!--</h4>-->
<!--        <p><em>To be implemented</em></p>-->

        </fieldset>

        <div class="form-group">
            <?= $form->label('drDescription', t('Description / Notes'))?>
            <?= $form->textarea('drDescription', $d->getDiscountDescription(), array('class' => 'span5'))?>
        </div>



    </div>

    <div class="ccm-dashboard-form-actions-wrapper">
        <div class="ccm-dashboard-form-actions">
            <a href="<?= URL::to('/dashboard/store/discounts')?>" class="btn btn-default"><?= t('Cancel')?></a>
            <button class="pull-right btn btn-primary" type="submit"><?= ($d->getID() > 0 ? t('Update') : t('Add'))?></button>
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
                }
            });

            $('#drDeductType2').click(function() {
                if($('#drDeductType2').is(':checked')) {
                    $('#percentageinput').hide();
                    $('#valueinput').show();
                }
            });

            $('#drTrigger3').click(function() {
                if($('#drTrigger3').is(':checked')) {
                    $('#codefields').hide();
                }
            });

            $('#drTrigger4').click(function() {
                if($('#drTrigger4').is(':checked')) {
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



<?php if (in_array($controller->getTask(), $codeViews)){ ?>
<div class="ccm-dashboard-header-buttons">
    <a href="<?= View::url('/dashboard/store/discounts/edit', $d->getID())?>" class="btn btn-default"><?= t("Edit Discount Rule")?></a>
</div>


<?php if (isset($successCount)) { ?>
<p class="alert alert-success"><?= $successCount . ' ' . ($successCount == 1 ? t('code added') : t('codes added')); ?></p>
<?php } ?>

<?php if (isset($failedcodes) && count($failedcodes) > 0 ) { ?>
    <p class="alert alert-warning"><?= t('The following codes are were invalid or are already active:')  ?><br />
        <strong><?= implode('<br />', $failedcodes); ?></strong>
    </p>
<?php } ?>



<fieldset><legend><?= t('Current Codes'); ?></legend></fieldset>

<p class="alert alert-info">
    <?php if ($d->isSingleUse()) { ?>
        <?= t('Single Use Codes'); ?></p>
    <?php } else { ?>
        <?= t('Codes can be repeatedly used'); ?>
    <?php } ?>
</p>

<?php if (!empty($codes)) { ?>
        <table class="table table-bordered">
            <tr><th><?= t('Code'); ?></th>

                <?php if ($d->isSingleUse()) { ?>
                <th><?=  t('Used'); ?></th>
                <?php } ?>

                <th></th></tr>

            <?php foreach($codes as $code) { ?>
                    <?php if ($d->isSingleUse()) { ?>

                        <?php if ($code->isUsed()) { ?>
                            <tr>
                                <td><strike><?= $code->getDiscountCodeCode(); ?></strike></td>
                                <td><a class="btn btn-default btn-xs" href="<?= View::url('/dashboard/store/orders/order/', $code['oID']); ?>"><?= t('View Order'); ?></a></td>
                                <td></td>
                            </tr>
                        <?php } else { ?>
                            <tr>
                                <td><?= $code->getDiscountCodeCode(); ?></td>
                                <td><span class="label label-success"><?= t('Available'); ?></span></td>
                            <td>
                                <form method="post" action="<?= View::url('/dashboard/store/discounts/deletecode/')?>">
                                    <input type="hidden" name="dcID" value="<?= $code->getID();?>" />
                                    <button class="btn btn-danger"><i class="fa fa-trash"></i></button>
                                </form>
                                </td>
                                </tr>
                        <?php }  ?>
                    <?php } else { ?>
                        <tr>
                            <td><?= $code['dcCode']; ?></td>
                            <td>
                                <form method="post" action="<?= View::url('/dashboard/store/discounts/deletecode/')?>">
                                    <input type="hidden" name="dcID" value="<?= $code['dcID']?>" />
                                    <button class="btn btn-danger" ><i class="fa fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    <?php } ?>


            <?php } ?>

        </table>

    <?php } else { ?>
    <p><?= t('No codes specified');?></p>

    <?php } ?>
<br />
<form method="post" action="<?= View::url('/dashboard/store/discounts/addcodes', $d->getID())?>" id="codes-add">
<fieldset><legend><?= t('Add Codes'); ?></legend>

    <div class="form-group">
        <?= $form->label('codes', t('Code(s)'))?>
        <?= $form->textarea('codes', '', array('class' => ''))?>
        <span class="help-block"><?= t('Seperate codes via lines or commas. Codes are case-insensitive.'); ?></span>
    </div>

    <div class="form-group">
        <button type="submit" class="btn btn-success"><?= t('Add Codes'); ?></button>
    </div>

</fieldset>

    <div class="ccm-dashboard-form-actions-wrapper">
        <div class="ccm-dashboard-form-actions">
            <a href="<?= URL::to('/dashboard/store/discounts')?>" class="btn btn-default"><?= t('Return to Discount Rules')?></a>
        </div>
    </div>

<?php } ?>


