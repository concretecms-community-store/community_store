<?php
defined('C5_EXECUTE') or die("Access Denied.");

use \Concrete\Core\Support\Facade\Url;
?>

<?php
$typeViews = ['view','typeadded'];
$typeEdits = ['add','edit'];

if (in_array($controller->getAction(),$typeViews)){ ?>

<div class="ccm-dashboard-header-buttons">
    <a href="<?= Url::to('/dashboard/store/products/types/', 'add')?>" class="btn btn-primary"><?= t("Add Product Type")?></a>
</div>

<?php if($typelist){ ?>
<div class="ccm-dashboard-content-full">
    <table class="ccm-search-results-table">
        <thead>
        <tr>
            <th><a><?= t('Type Name')?></a></th>
            <th><a><?= t('Description')?></a></th>
            <th><a><?= t('Attributes')?></a></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach($typelist as $type){?>
            <tr>
                <td><a href="<?= Url::to('/dashboard/store/products/types/edit/', $type->getTypeID())?>"><?= $type->getName()?></a></td>
                <td>
                    <?= $type->getDescription()?>
                </td>
                <td><a href="<?= Url::to('/dashboard/store/products/types/attributes/', $type->getTypeID())?>"><?= t('Manage Attributes'); ?></a></td>
            </tr>


        <?php } ?>
        </tbody>

    </table>

    <?php } else { ?>

        <div class="alert alert-info"><?= t("You have not added a product type yet")?></div>

    <?php } ?>

    <?php }  ?>

    <?php if (in_array($controller->getAction(),$typeEdits)){ ?>

        <?php if ($controller->getAction() == 'edit') { ?>
            <div class="ccm-dashboard-header-buttons">
                <form method="post" id="deletetype" action="<?= Url::to('/dashboard/store/products/types/delete/')?>">
                    <?= $token->output('community_store'); ?>
                    <input type="hidden" name="ptID" value="<?= $type->getTypeID(); ?>" />
                    <button class="btn btn-danger" ><?= t('Delete'); ?></button>
                </form>
            </div>
        <?php } ?>

        <form method="post" action="<?= $view->action($controller->getAction())?><?= $type->getTypeID() ? '/' .$type->getTypeID()  : '' ;?>">
            <?= $token->output('community_store'); ?>
            <div class="form-group">
                <?= $form->label('ptName',t("Type Name")); ?>
                <?= $form->text('ptName',$type->getName(), array('required'=>'required')); ?>
            </div>


            <div class="form-group">
                <?= $form->label('ptDescription',t("Type Description")); ?>
                <?= $form->textarea('ptDescription',$type->getDescription()); ?>
            </div>


            <script type="text/javascript">

                $(function(){
                    $('#deletetype').submit(function(e){
                        return confirm("<?= t('Are you sure you want to delete this product type?');?>");
                    });

                });

            </script>
            <style>
                .group-product-list:hover {
                    cursor: move
                }
                .fa-arrows-v, .fa-arrows-alt-v {
                    padding-right: 7px;
                    cursor: move !important;
                }

            </style>


            <div class="ccm-dashboard-form-actions-wrapper">
                <div class="ccm-dashboard-form-actions">
                    <a href="<?= Url::to('/dashboard/store/products/types')?>" class="btn btn-default btn-secondary"><?= t('Cancel')?></a>
                    <button class="pull-right btn btn-primary float-end" type="submit"><?= ($type->getTypeID() > 0 ? t('Update') : t('Add'))?></button>
                </div>
            </div>


        </form>


    <?php } ?>
