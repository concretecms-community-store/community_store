<?php
defined('C5_EXECUTE') or die("Access Denied.");

use \Concrete\Core\Support\Facade\Url;
use Concrete\Core\Url\Resolver\Manager\ResolverManagerInterface;
$resolverManager = app(ResolverManagerInterface::class);
?>


<div style="display: none">
    <div id="ccm-page-type-composer-add-set" class="ccm-ui">
        <form method="post" action="<?= $view->action('add_set', $type->getTypeID()) ?>">
            <?php $token->output('add_set') ?>
            <div class="form-group">
                <?= $form->label('ptLayoutSetName', tc('Name of a set', 'Set Name')) ?>
                <?= $form->text('ptLayoutSetName') ?>
            </div>
            <div class="form-group">
                <?= $form->label('ptLayoutSetDescription', tc('Description of a set', 'Set Description')) ?>
                <?= $form->textarea('ptLayoutSetDescription') ?>
            </div>
        </form>
        <div class="dialog-buttons">
            <button class="btn btn-secondary float-start" onclick="jQuery.fn.dialog.closeTop()"><?= t('Cancel') ?></button>
            <button class="btn btn-primary float-end" onclick="$('#ccm-page-type-composer-add-set form').submit()"><?= t('Add Set') ?></button>
        </div>
    </div>
</div>



<div style="display: none">
    <div id="ccm-product-type-set-add-control" class="ccm-ui">
        <form action="<?= $view->action('add_control') ?>" method="post" >
            <input type="hidden" name="ptlsID" id="ptlsID" value="" />
            <input type="hidden" name="akID" id="akID" value="" />
            <?php $token->output('add_control') ?>
        <ul data-list="page-type-composer-control-type" class="item-select-list">
        <?php

        foreach ($controls as $ak) {
            ?>
            <li>
                <a href="#"  data-ak-id="<?= $ak->getAttributeKeyID() ?>">

                    <?php
                    // recusing composer control, as all we want is the icon at this point
                    $ac = new \Concrete\Core\Page\Type\Composer\Control\CollectionAttributeControl();
                    $ac->setAttributeKeyID($ak->getAttributeKeyID());
                    $ac->setPageTypeComposerControlIconFormatter($ak->getController()->getIconFormatter());
                    ?>

                    <?= $ac->getPageTypeComposerControlIcon() ?>
                    <?= $ak->getAttributeKeyName() ?>

                </a>
            </li>
            <?php
        }
        ?>
        </ul>

        </form>
        <div class="dialog-buttons">
            <button class="btn btn-secondary float-start" onclick="jQuery.fn.dialog.closeTop()"><?= t('Cancel') ?></button>
        </div>
    </div>
</div>


<div class="ccm-dashboard-header-buttons">
    <a href="#" data-dialog="add_set" class="btn btn-secondary"><?= t('Add Attribute Set') ?></a>
</div>


<?php

foreach($type->getLayoutSets() as $set) { ?>
    <div class="ccm-item-set card panel panel-default" data-page-product-type-layout-control-set-id="<?= $set->getLayoutSetID() ?>">
        <div class="card-header panel-heading">
            <ul class="ccm-item-set-controls" style=" float: right;">

                <li><a href="#" data-dialog="add_control" data-set-id="<?= $set->getLayoutSetID() ?>"><i class="fa fas fa-plus"></i></a></a></li>
                <li><a href="#" data-command="move_set" style="cursor: move"><i class="fa fas fa-arrows-alt"></i></a></li>
                <li><a href="#" data-edit-set="<?= $set->getLayoutSetID() ?>"><i class="fa fas fa-edit"></i></a></li>
                <li><a href="#" data-delete-set="<?= $set->getLayoutSetID() ?>"><i class="fa fas fa-trash-alt"></i></a></li>
            </ul>
            <div class="ccm-item-set-name" ><?php
                if ($set->getLayoutSetName()) {
                    echo $set->getLayoutSetName();
                } else {
                    echo t('(No Name)');
                } ?></div>
        </div>

        <div style="display: none">
            <div data-delete-set-dialog="<?= $set->getLayoutSetID() ?>">
                <form data-delete-set-form="<?= $set->getLayoutSetID() ?>" action="<?= $view->action('delete_set', $set->getLayoutSetID()) ?>" method="post">
                    <?= t('Delete this form layout set? This cannot be undone.') ?>
                    <?php $token->output('delete_set') ?>
                </form>
                <div class="dialog-buttons">
                    <button class="btn btn-secondary float-start" onclick="jQuery.fn.dialog.closeTop()"><?= t('Cancel') ?></button>
                    <button class="btn btn-danger float-end" onclick="$('form[data-delete-set-form=<?= $set->getLayoutSetID() ?>]').submit()"><?=t('Delete Set') ?></button>
                </div>
            </div>
        </div>

        <div style="display: none">
            <div data-edit-set-dialog="<?= $set->getLayoutSetID() ?>" class="ccm-ui">
                <form data-edit-set-form="<?= $set->getLayoutSetID() ?>" action="<?= $view->action('update_set', $set->getLayoutSetID()) ?>" method="post">
                    <div class="form-group">
                        <?= $form->label('ptLayoutSetName', tc('Name of a set', 'Set Name')) ?>
                        <?= $form->text('ptLayoutSetName', $set->getLayoutSetName()) ?>
                    </div>
                    <div class="form-group">
                        <?= $form->label('ptLayoutSetDescription', tc('Description of a set', 'Set Description')) ?>
                        <?= $form->textarea('ptLayoutSetDescription', $set->getLayoutSetDescription()) ?>
                    </div>
                    <div class="dialog-buttons">
                        <button class="btn btn-secondary float-start" onclick="jQuery.fn.dialog.closeTop();"><?= t('Cancel') ?></button>
                        <button class="btn btn-primary float-end" onclick="$('form[data-edit-set-form=<?= $set->getLayoutSetID() ?>]').submit();"><?=t('Update Set') ?></button>
                    </div>
                    <?php $token->output('update_set') ?>
                </form>
            </div>
        </div>

        <table class="table table-hover" style="width: 100%;">
            <tbody class="ccm-item-set-inner">
            <?php $controls = $set->getLayoutSetControls();
            foreach ($controls as $control) {


?>
            <tr class="ccm-item-set-control"   data-page-product-type-layout-control-set-control-id="<?=$control->getProductTypeLayoutSetControlID() ?>" >

                <td style="width: 100%;">
                        <?= h($control->getDisplayLabel()) ; ?>
                </td>

                <td style="text-align: right; white-space: nowrap;">
                    <ul class="ccm-item-set-controls">
                        <li><a href="#" data-command="move-set-control" style="cursor: move"><i class="fa fas fa-arrows-alt"></i></a></li>
                        <li><a href="#" data-dialog="edit_control" data-control-id="<?=$control->getProductTypeLayoutSetControlID() ?>" data-control-label="<?= h($control->getCustomLabel()) ?>"><i class="fa fas fa-edit"></i></a></li>
                        <li><a href="#" data-delete-set-control="<?=$control->getProductTypeLayoutSetControlID() ?>"><i class="fa fas fa-trash-alt"></i></a></li>
                    </ul>

                    <div style="display: none">
                        <div data-delete-set-control-dialog="<?=$control->getProductTypeLayoutSetControlID() ?>">
                            <?= app('helper/validation/token')->output('delete_set_control') ?>

                            <form data-delete-set-control-form="<?= $control->getProductTypeLayoutSetControlID() ?>" action="<?= $view->action('delete_set_control', $control->getProductTypeLayoutSetControlID()) ?>" method="post">
                                <?php $token->output('delete_set_control') ?>
                            </form>

                            <div class="dialog-buttons">
                                <button class="btn btn-secondary float-start" onclick="jQuery.fn.dialog.closeTop()"><?=t('Cancel'); ?></button>
                                <button class="btn btn-danger float-end" onclick="$('form[data-delete-set-control-form=<?= $control->getProductTypeLayoutSetControlID() ?>]').submit()"><?= t('Remove Attribute From Set') ?></button>
                            </div>
                        </div>
                    </div>
                </td>
            </tr>


            <?php
            }
            ?>
            </tbody>
        </table>
    </div>
<?php } ?>


<div style="display: none">
    <div id="ccm-product-type-set-edit-control" class="ccm-ui">
        <form method="post" action="<?= $view->action('edit_control') ?>">
            <input type="hidden" name="ptlscID" id="ptlscID" value="" />


            <?php $token->output('edit_control') ?>
            <div class="form-group">
                <?= $form->label('customLabel', t('Custom Label')) ?>
                <?= $form->text('customLabel') ?>
            </div>

        </form>
        <div class="dialog-buttons">
            <button class="btn btn-secondary float-start" onclick="jQuery.fn.dialog.closeTop()"><?= t('Cancel') ?></button>
            <button class="btn btn-primary float-end" onclick="$('#ccm-product-type-set-edit-control form').submit()"><?= t('Save') ?></button>
        </div>
    </div>
</div>



<script type="text/javascript">

    var Composer = {

        deleteFromLayoutSetControl: function(ptLayoutSetControlID) {
            jQuery.fn.dialog.showLoader();
            var formData = [{
                'name': 'ccm_token',
                'value': '<?= $token->generate('delete_set_control') ?>'
            }, {
                'name': 'ptLayoutSetControlID',
                'value': ptLayoutSetControlID
            }];

            $.ajax({
                type: 'post',
                data: formData,
                url: '<?= $view->action('delete_set_control') ?>',
                success: function() {
                    jQuery.fn.dialog.hideLoader();
                    jQuery.fn.dialog.closeAll();
                    $('tr[data-page-product-type-layout-control-set-control-id=' + ptLayoutSetControlID + ']').remove();
                }
            });
        }

    }

    $(function() {
        $('a[data-dialog=add_set]').on('click', function() {
            jQuery.fn.dialog.open({
                element: '#ccm-page-type-composer-add-set',
                modal: true,
                width: 320,
                title: '<?= t('Add Attribute Set') ?>',
                height: 'auto'
            });
        });

        $('a[data-dialog=add_control]').on('click', function() {
            $('#ptlsID').val($(this).data('set-id'));

            jQuery.fn.dialog.open({
                element: '#ccm-product-type-set-add-control',
                modal: true,
                width: 520,
                title: '<?= t('Add Attribute To Set') ?>',
                height: 'auto'
            });
        });


        $('a[data-dialog=edit_control]').on('click', function() {
            $('#ptlscID').val($(this).data('control-id'));
            $('#customLabel').val($(this).data('control-label'));

            jQuery.fn.dialog.open({
                element: '#ccm-product-type-set-edit-control',
                modal: true,
                width: 520,
                title: '<?= t('Edit Attribute') ?>',
                height: 'auto'
            });
        });

        $('a[data-delete-set]').on('click', function() {
            var ptLayoutSetID = $(this).attr('data-delete-set');
            jQuery.fn.dialog.open({
                element: 'div[data-delete-set-dialog=' + ptLayoutSetID + ']',
                modal: true,
                width: 320,
                title: '<?=t('Delete Control Set'); ?>',
                height: 'auto'
            });
        });
        $('a[data-edit-set]').on('click', function() {
            var ptLayoutSetID = $(this).attr('data-edit-set');
            jQuery.fn.dialog.open({
                element: 'div[data-edit-set-dialog=' + ptLayoutSetID + ']',
                modal: true,
                width: 320,
                title: '<?=t('Update Control Set'); ?>',
                height: 'auto'
            });
        });

        $('div.ccm-pane-body, #ccm-dashboard-content-regular').sortable({
            handle: 'a[data-command=move_set]',
            items: '.ccm-item-set',
            cursor: 'move',
            axis: 'y',
            stop: function() {
                var formData = [{
                    'name': 'ccm_token',
                    'value': '<?= $token->generate('update_set_display_order') ?>'
                }, {
                    'name': 'ptID',
                    'value': <?= $type->getTypeID(); ?>
                }];
                $('.ccm-item-set').each(function() {
                    formData.push({'name': 'ptLayoutSetID[]', 'value': $(this).attr('data-page-product-type-layout-control-set-id')});
                });
                $.ajax({
                    type: 'post',
                    data: formData,
                    url: '<?= $view->action('update_set_display_order') ?>',
                    success: function() {

                    }
                });
            }
        });
        $('a[data-command=add-form-set-control]').dialog();
        $('a[data-command=edit-form-set-control]').dialog();

        $('.ccm-item-set-inner').sortable({
            handle: 'a[data-command=move-set-control]',
            items: '.ccm-item-set-control',
            cursor: 'move',
            axis: 'y',
            helper: function(e, ui) { // prevent table columns from collapsing
                ui.addClass('active');
                ui.children().each(function () {
                    $(this).width($(this).width());
                });
                return ui;
            },
            stop: function(e, ui) {
                ui.item.removeClass('active');

                var formData = [{
                    'name': 'ccm_token',
                    'value': '<?= $token->generate('update_set_control_display_order') ?>'
                }, {
                    'name': 'ptLayoutSetID',
                    'value': $(this).parent().parent().attr('data-page-product-type-layout-control-set-id')
                }];

                $(this).find('.ccm-item-set-control').each(function() {
                    formData.push({'name': 'ptLayoutSetControlID[]', 'value': $(this).attr('data-page-product-type-layout-control-set-control-id')});
                });

                $.ajax({
                    type: 'post',
                    data: formData,
                    url: '<?= $view->action('update_set_control_display_order') ?>',
                    success: function() {}
                });
            }
        });

        $('.ccm-item-set-inner').on('click', 'a[data-delete-set-control]', function() {
            var ptLayoutSetControlID = $(this).attr('data-delete-set-control');
            jQuery.fn.dialog.open({
                element: 'div[data-delete-set-control-dialog=' + ptLayoutSetControlID + ']',
                modal: true,
                width: 420,
                title: '<?=t('Delete Control'); ?>',
                height: 'auto'
            });
            return false;
        });

        $('#ccm-product-type-set-add-control a').on('click', function() {
            $('#akID').val($(this).data('ak-id'));
            $('#ccm-product-type-set-add-control form').submit();
        });
    });
</script>
