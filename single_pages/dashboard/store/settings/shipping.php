<?php
defined('C5_EXECUTE') or die("Access Denied.");

use Concrete\Core\Support\Facade\Url;
use Concrete\Package\CommunityStore\Src\CommunityStore\Group\Group;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductGroup\Criteria;
use Concrete\Package\CommunityStore\Src\CommunityStore\Shipping\Method\ShippingMethod;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @var string $actionDescription
 * @var Concrete\Core\Form\Service\Form $form
 * @var Concrete\Package\CommunityStore\Controller\SinglePage\Dashboard\Store\Settings\Shipping $controller
 */

$app = \Concrete\Core\Support\Facade\Application::getFacadeApplication();
$addViews = ['add', 'add_method', 'edit'];
$editViews = ['edit'];

if (in_array($controller->getAction(), $addViews)) {
    // Add Shipping Method View
    /**
     * @var Concrete\Package\CommunityStore\Src\CommunityStore\Shipping\Method\ShippingMethodType $smt
     * @var Concrete\Package\CommunityStore\Src\CommunityStore\Shipping\Method\ShippingMethod|null $sm
     */
    if (!isset($sm) || !is_object($sm)) {
        $sm = null;
    }
    ?>
    <form action="<?= Url::to('/dashboard/store/settings/shipping', 'add_method') ?>" method="post">
        <?= $token->output('community_store'); ?>
        <div class="row">
            <div class="col-md-12 col-md-12">
                <h3><?= $smt->getMethodTypeController()->getShippingMethodTypeName(); ?></h3>
                <?= $form->hidden('shippingMethodTypeID', $smt->getShippingMethodTypeID()); ?>
                <?php if ($sm !== null) { ?>
                    <?= $form->hidden('shippingMethodID', $sm->getID()); ?>
                <?php } ?>
                <div class="row">
                    <div class="col-md-12 col-sm-6">
                        <div class="form-group">
                            <?= $form->label('methodName', t("Method Name")); ?>
                            <?= $form->text('methodName', $sm !== null ? $sm->getName() : ''); ?>
                        </div>
                    </div>
                    <div class="col-md-12 col-sm-3">
                        <div class="form-group">
                            <?= $form->label('methodEnabled', t("Enabled")); ?>
                            <?= $form->select('methodEnabled', [true => t('Yes'), false => t('No')], $sm !== null ? $sm->isEnabled() : ''); ?>
                        </div>
                    </div>
                    <div class="col-md-12 col-sm-3">
                        <div class="form-group">
                            <?= $form->label('methodSortOrder', t("Sort Order")); ?>
                            <?= $form->text('methodSortOrder', $sm !== null ? $sm->getSortOrder() : ''); ?>
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
                                        <option value="<?= $ugkey; ?>" <?= (in_array($ugkey, ($sm !== null ? $sm->getUserGroups() : [])) ? 'selected="selected"' : ''); ?>>  <?= $uglabel; ?></option>
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
                                        <option value="<?= $ugkey; ?>" <?= (in_array($ugkey, ($sm !== null ? $sm->getExcludedUserGroups() : [])) ? 'selected="selected"' : ''); ?>>  <?= $uglabel; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <?= $form->label('methodProductGroupsCriteria', t('Shipping method offered based on product groups')) ?>
                            <?= $form->select(
                                'methodProductGroupsCriteria',
                                [
                                    0 => t("Don't care"),
                                    Criteria::EXCLUDE_ANY_PRODUCT_ANY_GROUP => t("Don't offer this shipping method if any product is in any of the following groups"),
                                    Criteria::EXCLUDE_ALL_PRODUCTS_ANY_GROUP => t("Don't offer this shipping method if all the products are any of the following groups"),
                                    Criteria::ONLYIF_ANY_PRODUCT_ANY_GROUP => t("Only offer this shipping method if any product is in any of the following groups"),
                                    Criteria::ONLYIF_ALL_PRODUCTS_ANY_GROUP => t("Only offer this shipping method if all the products are in any of the following groups"),
                                ],
                                $sm === null ? 0 : (int) $sm->getProductGroupsCriteria(),
                            ) ?>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <?= $form->label('methodProductGroups', t('Product groups')) ?>
                            <div class="ccm-search-field-content ccm-search-field-content-select2">
                                <select multiple="multiple" name="methodProductGroups[]" id="methodProductGroups" class="selectize" style="width: 100%;" placeholder="<?= t('*** Please Select') ?>">
                                    <?php
                                    $selectedProductGroupIDs = $sm === null ? [] : $sm->getProductGroupIDs();
                                    $em = app(EntityManagerInterface::class);
                                    foreach ($em->getRepository(Group::class)->findBy([], ['groupName' => 'ASC']) as $productGroup) {
                                        /** @var Group $productGroup */
                                        $selected = in_array($productGroup->getID(), $selectedProductGroupIDs);
                                        ?>
                                        <option value="<?= $productGroup->getID() ?>"<?= $selected ? ' selected="selected"' : ''?>><?= h($productGroup->getGroupName()) ?></option>
                                        <?php
                                    }
                                    ?>
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
                        $('#methodProductGroupsCriteria')
                            .on('change', function() {
                                $('#methodProductGroups').closest('.row').toggle(parseInt($('#methodProductGroupsCriteria').val()) ? true : false);
                            })
                            .trigger('change')
                        ;
                    });
                </script>

                <div class="row">
                    <div class="col-md-12 col-sm-12">
                        <div class="form-group">
                            <?= $form->label('methodDetails', t("Details")); ?>
                            <?php
                            $editor = $app->make('editor');
                            $editor->getPluginManager()->deselect(array('autogrow'));
                            echo $editor->outputStandardEditor('methodDetails', $sm !== null ? $sm->getDetails() : '');
                            ?>
                        </div>
                    </div>

                </div>
                <hr>
                <?php
                $smt->renderDashboardForm($sm);
                ?>
            </div>
        </div>


        <div class="ccm-dashboard-form-actions-wrapper">
            <div class="ccm-dashboard-form-actions">
                <button class="pull-right btn btn-primary float-end" type="submit"><?= $actionDescription ?></button>
            </div>
        </div>

    </form>

<?php } else { ?>
    <div class="ccm-dashboard-header-buttons">
        <?php
        if (count($methodTypes) > 0) {
            ?>
            <div class="btn-group dropdown">
                <button class="btn btn-primary dropdown-toggle" type="button"  data-bs-toggle="dropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><?= t('Add Shipping Method') ?> <span class="caret"></span></button>
                <ul class="dropdown-menu" role="menu">
                    <?php foreach ($methodTypes as $smt) { ?>
                        <?php if ($smt && !$smt->isHiddenFromAddMenu()) { ?>
                            <li class="dropdown-item"><a class="nav-link" href="<?= Url::to('/dashboard/store/settings/shipping/add', $smt->getShippingMethodTypeID()) ?>"><?= $smt->getMethodTypeController()->getShippingMethodTypeName() ?></a></li>
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
                            <th style="width: 20%"><?= tc("UserGroups", "Available To") ?></th>
                            <th style="width: 20%"><?= tc("UserGroups", "Excluded From") ?></th>
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
