<?php
defined('C5_EXECUTE') or die("Access Denied.");

use \Concrete\Core\Support\Facade\Url;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Shipping\Method\ShippingMethod;

$app = \Concrete\Core\Support\Facade\Application::getFacadeApplication();
$addViews = ['add', 'add_method', 'edit'];
$editViews = ['edit'];

if (in_array($controller->getAction(), $addViews)) {
/// Add Shipping Method View
    ?>


    <form action="<?= Url::to('/dashboard/store/settings/shipping', 'add_method') ?>" method="post">
        <?= $token->output('community_store'); ?>
        <div class="row">
            <div class="col-md-12 col-md-12">
                <?php //echo var_dump($smt);
                ?>
                <h3><?= $smt->getMethodTypeController()->getShippingMethodTypeName(); ?></h3>
                <?= $form->hidden('shippingMethodTypeID', $smt->getShippingMethodTypeID()); ?>
                <?php if (is_object($sm)) { ?>
                    <?= $form->hidden('shippingMethodID', $sm->getID()); ?>
                <?php } ?>
                <div class="row">
                    <div class="col-md-12 col-sm-6">
                        <div class="form-group">
                            <?= $form->label('methodName', t("Method Name")); ?>
                            <?= $form->text('methodName', is_object($sm) ? $sm->getName() : ''); ?>
                        </div>
                    </div>
                    <div class="col-md-12 col-sm-3">
                        <div class="form-group">
                            <?= $form->label('methodEnabled', t("Enabled")); ?>
                            <?= $form->select('methodEnabled', [true => t('Yes'), false => t('No')], is_object($sm) ? $sm->isEnabled() : ''); ?>
                        </div>
                    </div>
                    <div class="col-md-12 col-sm-3">
                        <div class="form-group">
                            <?= $form->label('methodSortOrder', t("Sort Order")); ?>
                            <?= $form->text('methodSortOrder', is_object($sm) ? $sm->getSortOrder() : ''); ?>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <?= $form->label('methodUserGroups[]', t("Available To User Groups")); ?>
                            <div class="ccm-search-field-content ccm-search-field-content-select2">
                                <select multiple="multiple" name="methodUserGroups[]" id="groupselect" class="selectize" style="width: 100%;" placeholder="<?= t('All User Groups'); ?>">
                                    <?php
                                    foreach ($allGroupList as $ugkey => $uglabel) { ?>
                                        <option value="<?= $ugkey; ?>" <?= (in_array($ugkey, (is_object($sm) ? $sm->getUserGroups() : [])) ? 'selected="selected"' : ''); ?>>  <?= $uglabel; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <?= $form->label('methodExcludedUserGroups[]', t("Exclude From User Groups")); ?>
                            <div class="ccm-search-field-content ccm-search-field-content-select2">
                                <select multiple="multiple" name="methodExcludedUserGroups[]" id="groupselect" class="selectize" style="width: 100%;" placeholder="<?= t('None'); ?>">
                                    <?php
                                    foreach ($allGroupList as $ugkey => $uglabel) { ?>
                                        <option value="<?= $ugkey; ?>" <?= (in_array($ugkey, (is_object($sm) ? $sm->getExcludedUserGroups() : [])) ? 'selected="selected"' : ''); ?>>  <?= $uglabel; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
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

                <div class="row">
                    <div class="col-md-12 col-sm-12">
                        <div class="form-group">
                            <?= $form->label('methodDetails', t("Details")); ?>
                            <?php
                            $editor = $app->make('editor');
                            $editor->getPluginManager()->deselect(array('autogrow'));
                            echo $editor->outputStandardEditor('methodDetails', is_object($sm) ? $sm->getDetails() : '');
                            ?>
                        </div>
                    </div>

                </div>
                <hr>
                <?php $smt->renderDashboardForm($sm); ?>
            </div>
        </div>


        <div class="ccm-dashboard-form-actions-wrapper">
            <div class="ccm-dashboard-form-actions">
                <button class="pull-right btn btn-primary float-end" type="submit"><?= t('%s Shipping Method', $task) ?></button>
            </div>
        </div>

    </form>

<?php } else { ?>
    <div class="ccm-dashboard-header-buttons">
        <?php
        if (count($methodTypes) > 0) {
            ?>
            <div class="btn-group">
                <a href="" class="btn btn-primary dropdown-toggle" data-toggle="dropdown"><?= t('Add Shipping Method') ?> <span class="caret"></span></a>
                <ul class="dropdown-menu" role="menu">
                    <?php foreach ($methodTypes as $smt) { ?>
                        <?php if ($smt && !$smt->isHiddenFromAddMenu()) { ?>
                            <li><a class="nav-link" href="<?= Url::to('/dashboard/store/settings/shipping/add', $smt->getShippingMethodTypeID()) ?>"><?= $smt->getMethodTypeController()->getShippingMethodTypeName() ?></a></li>
                        <?php } ?>
                    <?php } ?>
                </ul>
            </div>
        <?php } ?>
        <a href="<?= Url::to('/dashboard/store/settings#settings-shipping') ?>" class="btn btn-default btn-secondary"><i class="fa fa-gear"></i> <?= t("General Settings") ?></a>
    </div>

    <div class="dashboard-shipping-methods">

        <?php
        $shippingmethodcount = 0;
        $shippingmethodenabledcount = 0;

        if (count($methodTypes) > 0) {
            ?>
            <?php foreach ($methodTypes as $methodType) {
                $typemethods = ShippingMethod::getMethods($methodType->getShippingMethodTypeID());
                if (count($typemethods) > 0) {
                    $shippingmethodcount++;
                    ?>
                    <table class="table table-striped">
                        <thead>
                        <tr>
                            <th><?= t("%s Methods", $methodType->getMethodTypeController()->getShippingMethodTypeName()) ?></th>
                            <th style="width: 8%"><?= t("Enabled") ?></th>
                            <th style="width: 20%"><?= t("Available To") ?></th>
                            <th style="width: 20%"><?= t("Excluded From") ?></th>
                            <th style="width: 8%"><?= t("Sort Order") ?></th>
                            <th  style="width: 15%" class="text-right"><?= t("Actions") ?></th>
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
                                    <td><?= $method->isEnabled() ? t('Yes') : t('No') ?></td>
                                    <td>
                                        <?php
                                            $availableTo = $method->getUserGroups();
                                            foreach($availableTo as $gID) {
                                                $group = \Concrete\Core\User\Group\Group::getByID($gID);

                                                if ($group) { ?>
                                                    <span class="label label-default"><?= h($group->getGroupName()); ?></span>
                                                <?php }
                                            }

                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        $excludedFrom = $method->getExcludedUserGroups();
                                        foreach($excludedFrom as $gID) {
                                            $group = \Concrete\Core\User\Group\Group::getByID($gID);

                                            if ($group) { ?>
                                                <span class="label label-default"><?= h($group->getGroupName()); ?></span>
                                            <?php }
                                        }

                                        ?>
                                    </td>
                                    <td><?= $method->getSortOrder() ?></td>
                                    <td class="text-right">
                                        <a href="<?= Url::to('/dashboard/store/settings/shipping/edit', $method->getID()) ?>"
                                           class="btn btn-default btn-secondary"><?= t("Edit") ?></a>
                                        <?php if ($method->getShippingMethodTypeMethod()->disableEnabled()) { ?>
                                            <a href="" class="btn btn-default btn-secondary"><?= t("Disable") ?></a>
                                        <?php } else { ?>
                                            <a href="<?= Url::to('/dashboard/store/settings/shipping/delete', $method->getID()) ?>"
                                               class="btn btn-danger"><?= t("Delete") ?></a>
                                        <?php } ?>
                                    </td>
                                </tr>
                            <?php }
                        } ?>
                        </tbody>
                    </table>
                <?php }
            } ?>
        <?php } ?>

        <?php
        if ($shippingmethodcount == 0) { ?>
            <p class="alert alert-warning"><?= t('No shipping methods are configured'); ?></p>
        <?php } ?>

        <?php
        if ($shippingmethodcount > 0 && $shippingmethodenabledcount == 0) { ?>
            <p class="alert alert-warning"><?= t('No shipping methods are currently enabled'); ?></p>
        <?php } ?>

    </div>

<?php } ?>
