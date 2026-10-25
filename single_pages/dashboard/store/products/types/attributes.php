<?php
defined('C5_EXECUTE') or die("Access Denied.");

use \Concrete\Core\Support\Facade\Url;
use Concrete\Core\Url\Resolver\Manager\ResolverManagerInterface;
$resolverManager = app(ResolverManagerInterface::class);
$usedKeys = [];
?>


<div style="display: none">
    <div id="ccm-page-type-composer-add-set">
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
            <button class="btn btn-secondary float-start" onclick="communityStoreDashboard.closeDialog()"><?= t('Cancel') ?></button>
            <button class="btn btn-primary float-end pull-right" onclick="document.querySelector('#ccm-page-type-composer-add-set form').submit()"><?= t('Add Set') ?></button>
        </div>
    </div>
</div>





<div class="ccm-dashboard-header-buttons">
    <a href="#" data-dialog="add_set" class="btn btn-primary"><?= t('Add Attribute Set') ?></a>
</div>


<div class="layout-sets">
    <?php

    foreach($type->getLayoutSets() as $set) { ?>
        <div class="ccm-item-set card panel panel-default" data-page-product-type-layout-control-set-id="<?= $set->getLayoutSetID() ?>">
            <div class="card-header panel-heading">
                <ul class="ccm-item-set-controls" style=" float: right;">

                    <li><a href="#" data-dialog="add_control" data-set-id="<?= $set->getLayoutSetID() ?>"><i class="fa fas fa-plus"></i></a></a></li>
                    <li><a href="#" data-command="move_set" style="cursor: move"><i class="fa fas fa-arrows fa-arrows-alt-v"></i></a></li>
                    <li><a href="#" data-edit-set="<?= $set->getLayoutSetID() ?>"><i class="fa fas fa-edit"></i></a></li>
                    <li><a href="#" data-delete-set="<?= $set->getLayoutSetID() ?>"><i class="fa fas fa-trash fa-trash-alt"></i></a></li>
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
                        <button class="btn btn-secondary float-start" onclick="communityStoreDashboard.closeDialog()"><?= t('Cancel') ?></button>
                        <button class="btn btn-danger float-end pull-right" onclick="document.querySelector('form[data-delete-set-form=&quot;<?= $set->getLayoutSetID() ?>&quot;]').submit()"><?=t('Delete Set') ?></button>
                    </div>
                </div>
            </div>

            <div style="display: none;">
                <div data-edit-set-dialog="<?= $set->getLayoutSetID() ?>" >
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
                            <button class="btn btn-secondary float-start" onclick="communityStoreDashboard.closeDialog()"><?= t('Cancel') ?></button>
                            <button class="btn btn-primary float-end pull-right" onclick="document.querySelector('form[data-edit-set-form=&quot;<?= $set->getLayoutSetID() ?>&quot;]').submit()"><?=t('Update Set') ?></button>
                        </div>
                        <?php $token->output('update_set') ?>
                    </form>
                </div>
            </div>

            <table class="table table-hover" style="width: 100%;">
                <tbody class="ccm-item-set-inner">
                <?php $controls = $set->getLayoutSetControls();
                foreach ($controls as $control) {

                    $usedKeys[] = $control->getAttributeKey()->getAttributeKeyID();

                    ?>
                    <tr class="ccm-item-set-control"   data-page-product-type-layout-control-set-control-id="<?=$control->getProductTypeLayoutSetControlID() ?>" >

                        <td style="width: 85%;">
                            <?= h($control->getDisplayLabel(true)) ; ?>
                        </td>
                        <td style="width: 15%;">
                            <span class="badge <?= $control->getHidden() ? 'bg-warning badge-warning' : 'bg-primary badge-primary '?>"><?= $control->getHidden() ? t('Dashboard only') : t('Visible to all') ; ?></span>
                        </td>

                        <td style="text-align: right; white-space: nowrap;">
                            <ul class="ccm-item-set-controls">
                                <li><a href="#" data-command="move-set-control" style="cursor: move"><i class="fa fas fa-arrows fa-arrows-alt-v"></i></a></li>
                                <li><a href="#" data-dialog="edit_control" data-control-id="<?=$control->getProductTypeLayoutSetControlID() ?>" data-control-label="<?= h($control->getCustomLabel()) ?>" data-control-hidden="<?= h($control->getHidden() ? '1' : '0') ?>"><i class="fa fas fa-edit"></i></a></li>
                                <li><a href="#" data-delete-set-control="<?=$control->getProductTypeLayoutSetControlID() ?>"><i class="fa fas fa-trash fa-trash-alt"></i></a></li>
                            </ul>

                            <div style="display: none">
                                <div data-delete-set-control-dialog="<?=$control->getProductTypeLayoutSetControlID() ?>">
                                    <?= app('helper/validation/token')->output('delete_set_control') ?>

                                    <form data-delete-set-control-form="<?= $control->getProductTypeLayoutSetControlID() ?>" action="<?= $view->action('delete_set_control', $control->getProductTypeLayoutSetControlID()) ?>" method="post">
                                        <?php $token->output('delete_set_control') ?>
                                    </form>

                                    <div class="dialog-buttons">
                                        <button class="btn btn-secondary float-start" onclick="communityStoreDashboard.closeDialog()"><?=t('Cancel'); ?></button>
                                        <button class="btn btn-danger float-end pull-right" onclick="document.querySelector('form[data-delete-set-control-form=&quot;<?= $control->getProductTypeLayoutSetControlID() ?>&quot;]').submit()"><?= t('Remove Attribute From Set') ?></button>
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
</div>


<div style="display: none">
    <div id="ccm-product-type-set-edit-control" >
        <form method="post" action="<?= $view->action('edit_control') ?>">
            <input type="hidden" name="ptlscID" id="ptlscID" value="" />


            <?php $token->output('edit_control') ?>
            <div class="form-group">
                <?= $form->label('customLabel', t('Custom Label')) ?>
                <?= $form->text('customLabel') ?>
            </div>

            <div class="form-group">
                <?= $form->label('hidden', t('Visibility')) ?>
                <?= $form->select('hidden', [0=>t('Visible to all'), 1=>t('Dashboard only')]) ?>
            </div>

        </form>
        <div class="dialog-buttons">
            <button class="btn btn-secondary float-start" onclick="communityStoreDashboard.closeDialog()"><?= t('Cancel') ?></button>
            <button class="btn btn-primary float-end pull-right" onclick="document.querySelector('#ccm-product-type-set-edit-control form').submit()"><?= t('Save') ?></button>
        </div>
    </div>
</div>

<div style="display: none">
    <div id="ccm-product-type-set-add-control"  >
        <form action="<?= $view->action('add_control') ?>" method="post" >
            <input type="hidden" name="ptlsID" id="ptlsID" value="" />
            <input type="hidden" name="akID" id="akID" value="" />
            <?php $token->output('add_control') ?>
            <ul data-list="page-type-composer-control-type" class="item-select-list" style="height: 600px; overflow-y: auto">
                <?php

                foreach ($keys as $ak) {
                    if(!in_array($ak->getAttributeKeyID(), $usedKeys)) {
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
                                <?= h($ak->getAttributeKeyName()) ?>

                            </a>
                        </li>
                        <?php
                    }
                }
                ?>
            </ul>

        </form>
        <div class="dialog-buttons">
            <button class="btn btn-secondary float-start " onclick="communityStoreDashboard.closeDialog()"><?= t('Cancel') ?></button>
        </div>
    </div>
</div>




<script type="text/javascript">
    (function () {
        var dashboard = communityStoreDashboard;

        var openDialog = function (element, title, width) {
            window.scrollTo({top: 0, left: 0, behavior: 'instant'});
            dashboard.openDialog(element, {title: title, width: width});
        };

        var onClick = function (selector, handler) {
            document.querySelectorAll(selector).forEach(function (link) {
                link.addEventListener('click', function (e) {
                    e.preventDefault();
                    handler(link);
                });
            });
        };

        onClick('a[data-dialog=add_set]', function () {
            openDialog('#ccm-page-type-composer-add-set', <?= json_encode(t('Add Attribute Set')) ?>, 320);
        });

        onClick('a[data-dialog=add_control]', function (link) {
            document.getElementById('ptlsID').value = link.getAttribute('data-set-id');
            openDialog('#ccm-product-type-set-add-control', <?= json_encode(t('Add Attribute To Set')) ?>, 520);
        });

        onClick('a[data-dialog=edit_control]', function (link) {
            document.getElementById('ptlscID').value = link.getAttribute('data-control-id');
            document.getElementById('customLabel').value = link.getAttribute('data-control-label');
            document.getElementById('hidden').value = link.getAttribute('data-control-hidden');
            openDialog('#ccm-product-type-set-edit-control', <?= json_encode(t('Edit Attribute')) ?>, 520);
        });

        onClick('a[data-delete-set]', function (link) {
            openDialog('div[data-delete-set-dialog="' + link.getAttribute('data-delete-set') + '"]', <?= json_encode(t('Delete Control Set')); ?>, 320);
        });

        onClick('a[data-edit-set]', function (link) {
            openDialog('div[data-edit-set-dialog="' + link.getAttribute('data-edit-set') + '"]', <?= json_encode(t('Update Control Set')); ?>, 320);
        });

        onClick('a[data-delete-set-control]', function (link) {
            openDialog('div[data-delete-set-control-dialog="' + link.getAttribute('data-delete-set-control') + '"]', <?= json_encode(t('Delete Control')); ?>, 420);
        });

        onClick('#ccm-product-type-set-add-control a', function (link) {
            document.getElementById('akID').value = link.getAttribute('data-ak-id');
            document.querySelector('#ccm-product-type-set-add-control form').submit();
        });

        dashboard.sortable('.layout-sets', {
            handle: 'a[data-command=move_set]',
            draggable: '.ccm-item-set',
            onEnd: function () {
                var data = new URLSearchParams();
                data.append('ccm_token', <?= json_encode($token->generate('update_set_display_order')) ?>);
                data.append('ptID', <?= json_encode($type->getTypeID()); ?>);
                document.querySelectorAll('.ccm-item-set').forEach(function (set) {
                    data.append('ptLayoutSetID[]', set.getAttribute('data-page-product-type-layout-control-set-id'));
                });
                dashboard.post(<?= json_encode((string) $view->action('update_set_display_order')) ?>, data);
            }
        });

        document.querySelectorAll('.ccm-item-set-inner').forEach(function (setInner) {
            dashboard.sortable(setInner, {
                handle: 'a[data-command=move-set-control]',
                draggable: '.ccm-item-set-control',
                onEnd: function () {
                    var data = new URLSearchParams();
                    data.append('ccm_token', <?= json_encode($token->generate('update_set_control_display_order')) ?>);
                    data.append('ptLayoutSetID', setInner.closest('.ccm-item-set').getAttribute('data-page-product-type-layout-control-set-id'));
                    setInner.querySelectorAll('.ccm-item-set-control').forEach(function (control) {
                        data.append('ptLayoutSetControlID[]', control.getAttribute('data-page-product-type-layout-control-set-control-id'));
                    });
                    dashboard.post(<?= json_encode((string) $view->action('update_set_control_display_order')) ?>, data);
                }
            });
        });
    })();
</script>
