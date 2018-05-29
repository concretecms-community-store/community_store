<?php
defined('C5_EXECUTE') or die(_("Access Denied."));
?>

<?php

$groupViews = array('view','groupadded','addgroup');
if (in_array($controller->getTask(),$groupViews)){ ?>

    <?php if($grouplist){ ?>
        <ul class="list-unstyled group-list" data-delete-url="<?= \URL::to('/dashboard/store/products/groups/deletegroup')?>" data-save-url="<?= \URL::to('/dashboard/store/products/groups/editgroup')?>">
            <?php foreach($grouplist as $group){?>
                <li data-group-id="<?= $group->getGroupID()?>">
                    <span class="group-name"><?= $group->getGroupName()?></span>
                    <input class="hideme edit-group-name" type="text" value="<?= $group->getGroupName()?>">
                    <span class="btn btn-default btn-edit-group-name"><i class="fa fa-pencil"></i></span>
                    <span class="hideme btn btn-default btn-cancel-edit"><i class="fa fa-ban"></i></span>
                    <span class="hideme btn btn-warning btn-save-group-name"><i class="fa fa-save"></i></span>
                    <span class="btn btn-danger btn-delete-group"><i class="fa fa-trash"></i></span>
                </li>
            <?php } ?>
        </ul>

    <?php } else { ?>

        <div class="alert alert-info"><?= t("You have not added a group yet")?></div>

    <?php } ?>
    <form method="post" action="<?= $view->action('addgroup')?>">
        <h4><?= t('Add a Group')?></h4>
        <hr>
        <div class="form-group">
            <?= $form->label('groupName',t("Group Name")); ?>
            <?= $form->text('groupName',null,array('style'=>'width:200px')); ?>
        </div>
        <input type="submit" class="btn btn-primary" value="<?= t('Add Group');?>">
    </form>

<?php }  ?>
