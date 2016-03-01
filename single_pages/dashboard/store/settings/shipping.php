<?php 
defined('C5_EXECUTE') or die(_("Access Denied."));
$addViews = array('add','add_method','edit');
$editViews = array('edit');

use \Concrete\Package\CommunityStore\Src\CommunityStore\Shipping\Method\ShippingMethod as StoreShippingMethod;

if(in_array($controller->getTask(),$addViews)){
/// Add Shipping Method View    
?>
    
    
<form action="<?=URL::to('/dashboard/store/settings/shipping','add_method')?>" method="post">

    <div class="row">
        <div class="col-xs-12 col-md-12">
        <?php //echo var_dump($smt); ?>
            <h3><?= $smt->getMethodTypeController()->getShippingMethodTypeName(); ?></h3>
            <?= $form->hidden('shippingMethodTypeID',$smt->getShippingMethodTypeID()); ?>
            <?php if(is_object($sm)){ ?>
            <?= $form->hidden('shippingMethodID',$sm->getID()); ?>
            <?php } ?>
            <div class="row">
                <div class="col-xs-12 col-sm-6">
                    <div class="form-group">
                        <?= $form->label('methodName',t("Method Name")); ?>
                        <?= $form->text('methodName',is_object($sm)?$sm->getName():''); ?>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-6">
                    <div class="form-group">
                        <?= $form->label('methodEnabled',t("Enabled")); ?>
                        <?= $form->select('methodEnabled',array(true=>"Enabled",false=>"Disabled"),is_object($sm)?$sm->isEnabled():''); ?>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-12">
                    <div class="form-group">
                        <?= $form->label('methodDetails',t("Details")); ?>
                        <?php
                        $editor = Core::make('editor');
                        echo $editor->outputStandardEditor('methodDetails', is_object($sm)?$sm->getDetails():'');
                        ?>
                        <style>
                            .redactor-editor {
                                min-height: 80px !important;
                            }
                        </style>
                    </div>
                </div>

            </div>    
            <hr>
            <?php $smt->renderDashboardForm($sm); ?>    
        </div>
    </div>

    
    <div class="ccm-dashboard-form-actions-wrapper">
        <div class="ccm-dashboard-form-actions">
            <button class="pull-right btn btn-success" type="submit" ><?= t('%s Shipping Method',$task)?></button>
        </div>
    </div>
    
</form>
     
<?php } else { ?>
<div class="ccm-dashboard-header-buttons">
    <?php 
    if(count($methodTypes) > 0){?>
    <div class="btn-group">
        <a href="" class="btn btn-primary dropdown-toggle" data-toggle="dropdown"><?= t('Add Method')?> <span class="caret"></span></a>
        <ul class="dropdown-menu" role="menu">
            <?php foreach($methodTypes as $smt){?>
                <?php if(!$smt->isHiddenFromAddMenu()){?>
                    <li><a href="<?=URL::to('/dashboard/store/settings/shipping/add',$smt->getShippingMethodTypeID())?>"><?= $smt->getMethodTypeController()->getShippingMethodTypeName()?></a></li>
                <?php } ?>
            <?php } ?>
        </ul>
    </div>
    <?php } ?>
    <a href="<?=URL::to('/dashboard/store/settings/shipping/clerk')?>" class="btn btn-info"><i class="fa fa-gift"></i> <?= t("Shipping Clerk")?></a>
    <a href="<?= \URL::to('/dashboard/store/settings')?>" class="btn btn-default"><i class="fa fa-gear"></i> <?= t("General Settings")?></a>
</div>

<div class="dashboard-shipping-methods">
	
	<?php if(count($methodTypes) > 0){?>
		<?php foreach($methodTypes as $methodType){?>
			<table class="table table-striped">
				<thead>
					<th><?= t("%s Methods", $methodType->getMethodTypeController()->getShippingMethodTypeName())?></th>
					<th class="text-right"><?= t("Actions")?></th>
				</thead>
				<tbody>
					<?php foreach(StoreShippingMethod::getAvailableMethods($methodType->getShippingMethodTypeID()) as $method){ ?>
					<tr>
						<td><?= $method->getName()?></td>
						<td class="text-right">
							<a href="<?=URL::to('/dashboard/store/settings/shipping/edit',$method->getID())?>" class="btn btn-default"><?= t("Edit")?></a>
							<?php if($method->getShippingMethodTypeMethod()->disableEnabled()){?>
							    <a href="" class="btn btn-default"><?= t("Disable")?></a>
							<?php } else { ?>
							<a href="<?=URL::to('/dashboard/store/settings/shipping/delete',$method->getID())?>" class="btn btn-danger"><?= t("Delete")?></a>
						    <?php } ?>
						</td>
					</tr>
					<?php } ?>
				</tbody>
			</table>
		<?php } ?>
	<?php } ?>
	
</div>

<?php } ?>