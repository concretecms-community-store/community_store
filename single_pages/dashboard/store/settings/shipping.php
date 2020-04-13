<?php
defined('C5_EXECUTE') or die("Access Denied.");

use \Concrete\Core\Support\Facade\Url;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Shipping\Method\ShippingMethod;

$app = \Concrete\Core\Support\Facade\Application::getFacadeApplication();
$addViews = array('add','add_method','edit');
$editViews = array('edit');

if(in_array($controller->getAction(),$addViews)){
/// Add Shipping Method View
?>


<form action="<?=Url::to('/dashboard/store/settings/shipping','add_method')?>" method="post">
    <?= $token->output('community_store'); ?>
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
                <div class="col-xs-12 col-sm-3">
                    <div class="form-group">
                        <?= $form->label('methodEnabled',t("Enabled")); ?>
                        <?= $form->select('methodEnabled',array(true=>t('Yes'),false=>t('No')),is_object($sm)?$sm->isEnabled():''); ?>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-3">
                    <div class="form-group">
                        <?= $form->label('methodSortOrder',t("Sort Order")); ?>
                        <?= $form->text('methodSortOrder',is_object($sm)?$sm->getSortOrder():''); ?>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-12">
                    <div class="form-group">
                        <?= $form->label('methodDetails',t("Details")); ?>
                        <?php
                        $editor = $app->make('editor');
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
            <button class="pull-right btn btn-primary" type="submit" ><?= t('%s Shipping Method',$task)?></button>
        </div>
    </div>

</form>

<?php } else { ?>
<div class="ccm-dashboard-header-buttons">
    <?php
    if(count($methodTypes) > 0){?>
    <div class="btn-group">
        <a href="" class="btn btn-primary dropdown-toggle" data-toggle="dropdown"><?= t('Add Shipping Method')?> <span class="caret"></span></a>
        <ul class="dropdown-menu" role="menu">
            <?php foreach($methodTypes as $smt){?>
                <?php if($smt && !$smt->isHiddenFromAddMenu()){?>
                    <li><a href="<?=Url::to('/dashboard/store/settings/shipping/add',$smt->getShippingMethodTypeID())?>"><?= $smt->getMethodTypeController()->getShippingMethodTypeName()?></a></li>
                <?php } ?>
            <?php } ?>
        </ul>
    </div>
    <?php } ?>
    <a href="<?= Url::to('/dashboard/store/settings#settings-shipping')?>" class="btn btn-default"><i class="fa fa-gear"></i> <?= t("General Settings")?></a>
</div>

<div class="dashboard-shipping-methods">

	<?php
    $shippingmethodcount = 0;
    $shippingmethodenabledcount = 0;

    if(count($methodTypes) > 0){?>
		<?php foreach($methodTypes as $methodType) {
            $typemethods = ShippingMethod::getMethods($methodType->getShippingMethodTypeID());
            if (count($typemethods) > 0) {
                $shippingmethodcount++;
                ?>
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th><?= t("%s Methods", $methodType->getMethodTypeController()->getShippingMethodTypeName()) ?></th>
                        <th style="width: 20%;"><?= t("Enabled") ?></th>
                        <th style="width: 20%;"><?= t("Sort Order") ?></th>
                        <th class="text-right" style="width: 20%;"><?= t("Actions") ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($typemethods as $method) {
                        if ($method) {
                            if ($method->isEnabled()) {
                                $shippingmethodenabledcount++;
                            }
                            ?>
                            <tr>
                                <td><?= $method->getName() ?></td>
                                <td style="width: 20%;"><?= $method->isEnabled() ? t('Yes') : t('No') ?></td>
                                <td style="width: 20%;"><?= $method->getSortOrder() ?></td>
                                <td class="text-right" style="width: 20%;">
                                    <a href="<?= Url::to('/dashboard/store/settings/shipping/edit', $method->getID()) ?>"
                                       class="btn btn-default"><?= t("Edit") ?></a>
                                    <?php if ($method->getShippingMethodTypeMethod()->disableEnabled()) { ?>
                                        <a href="" class="btn btn-default"><?= t("Disable") ?></a>
                                    <?php } else { ?>
                                        <a href="<?= Url::to('/dashboard/store/settings/shipping/delete', $method->getID()) ?>"
                                           class="btn btn-danger"><?= t("Delete") ?></a>
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php }
                    }?>
                    </tbody>
                </table>
            <?php }
        }?>
	<?php } ?>

    <?php
    if ($shippingmethodcount == 0) { ?>
    <p class="alert alert-warning"><?= t('No shipping methods are configured');?></p>
    <?php } ?>

    <?php
    if ($shippingmethodcount > 0 && $shippingmethodenabledcount == 0) { ?>
        <p class="alert alert-warning"><?= t('No shipping methods are currently enabled');?></p>
    <?php } ?>

</div>

<?php } ?>
