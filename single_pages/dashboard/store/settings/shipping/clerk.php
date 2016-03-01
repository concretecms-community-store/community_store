<?php 
defined('C5_EXECUTE') or die(_("Access Denied."));
$addViews = array('add','edit');
$listViews = array('view','success','removed','updated');

if(in_array($controller->getTask(),$listViews)){?>
    
    <div class="ccm-dashboard-header-buttons">
        <a href="<?=URL::to('/dashboard/store/settings/shipping/clerk/add')?>" class="btn btn-primary"> <?= t("Add Box")?></a>
        <a href="<?=URL::to('/dashboard/store/settings/shipping/')?>" class="btn btn-default"><i class="fa fa-gift"></i> <?= t("Shipping Settings")?></a>
        <a href="<?= \URL::to('/dashboard/store/settings')?>" class="btn btn-default"><i class="fa fa-gear"></i> <?= t("General Settings")?></a>
    </div>
    
    <div class="alert alert-info">
        <?= t("The Shipping Clerk allows you to define boxes that you will use in your shipping process. Shipping Method Types that use calculated shipping (e.g. USPS add-on) use this to estimate postage. NOTE: If you have an item that exceeds the boxes defined, the Shipping Clerk will assume it will go in its own box.")?>
    </div>
    
    <?php if(count($packages)>0){?>
        <table class="table table-stripe">
            <thead>
                <tr>
                    <th><?= t("Package Name")?></th>
                    <th><?= t("Outer Dimensions")?></th>
                    <th><?= t("Inner Dimensions")?></th>
                    <th><?= t("Empty Weight")?></th>
                    <th><?= t("Max Weight")?></th>
                    <th><?= t("Actions")?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($packages as $package){?>
                    <tr>
                        <td><?= $package->getReference()?></td>
                        <td><?= $package->getOuterWidth()?> x <?= $package->getOuterLength()?> x <?= $package->getOuterDepth()?>mm</td>
                        <td><?= $package->getInnerWidth()?> x <?= $package->getInnerLength()?> x <?= $package->getInnerDepth()?>mm</td>
                        <td><?= $package->getEmptyWeight()?>g</td>
                        <td><?= $package->getMaxWeight()?>g</td>
                        <td>
                            <a href="<?=URL::to('dashboard/store/settings/shipping/clerk/edit/',$package->getID())?>" class="btn btn-default"><?= t("Edit")?></a>
                            <a href="<?=URL::to('dashboard/store/settings/shipping/clerk/delete/',$package->getID())?>" class="btn btn-danger"><?= t("Delete")?></a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    <?php } ?>
    
<?php } elseif(in_array($controller->getTask(),$addViews)){ ?>
    <form class="form form-vertical" action="<?=URL::to('/dashboard/store/settings/shipping/clerk/save')?>" method="post">
        <input type="hidden" name="id" value="<?= $id?>">
        <div class="row">
            <div class="col-xs-12">
                <div class="form-group">
                    <?= $form->label('reference',t("Package/Box Name"))?>
                    <?= $form->text('reference',$reference)?>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12 col-sm-4">
                <div class="form-group">
                    <?= $form->label('outerLength',t("Outer Length of Box (longest side)"))?>
                    <div class="input-group">
                        <?= $form->text('outerLength',$outerLength)?>
                        <span class="input-group-addon">mm</span>
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-4">
                <div class="form-group">
                <?= $form->label('outerWidth',t("Outer Width of Box"))?>
                    <div class="input-group">
                        <?= $form->text('outerWidth',$outerWidth)?>
                        <span class="input-group-addon">mm</span>
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-4">
                <div class="form-group">
                    <?= $form->label('outerDepth',t("Outer Depth of Box"))?>
                    <div class="input-group">
                        <?= $form->text('outerDepth',$outerDepth)?>
                        <span class="input-group-addon">mm</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12 col-sm-4">
                <div class="form-group">
                    <?= $form->label('innerLength',t("Inner Length of Box (longest side)"))?>
                    <div class="input-group">
                        <?= $form->text('innerLength',$innerLength)?>
                        <span class="input-group-addon">mm</span>
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-4">
                <div class="form-group">
                    <?= $form->label('innerWidth',t("Inner Width of Box"))?>
                    <div class="input-group">
                        <?= $form->text('innerWidth',$innerWidth)?>
                        <span class="input-group-addon">mm</span>
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-4">
                <div class="form-group">
                    <?= $form->label('innerDepth',t("Inner Depth of Box"))?>
                    <div class="input-group">
                        <?= $form->text('innerDepth',$innerDepth)?>
                        <span class="input-group-addon">mm</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12 col-sm-4">
                <div class="form-group">
                    <?= $form->label('emptyWeight',t("Empty Box Weight"))?>
                    <div class="input-group">
                        <?= $form->text('emptyWeight',$emptyWeight)?>
                        <span class="input-group-addon">g</span>
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-4">
                <div class="form-group">
                    <?= $form->label('maxWeight',t("Max Weight"))?>
                    <div class="input-group">
                        <?= $form->text('maxWeight',$maxWeight)?>
                        <span class="input-group-addon">g</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="ccm-dashboard-form-actions-wrapper">
            <div class="ccm-dashboard-form-actions">
                <button class="pull-right btn btn-success" type="submit" ><?= t('%s Box',$task)?></button>
            </div>
        </div>
        
    </form>
<?php } ?>
