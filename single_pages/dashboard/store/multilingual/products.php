<?php defined('C5_EXECUTE') or die("Access Denied.");
use \Concrete\Core\Support\Facade\Url;

$app = \Concrete\Core\Support\Facade\Application::getFacadeApplication();
$action = $controller->getAction();
$csm = $app->make('cs/helper/multilingual');
$editor = $app->make('editor');

if ($action == 'view') { ?>

<?php if (count($locales) > 0) { ?>

        <div class="ccm-dashboard-header-buttons">

        </div>

        <div class="cccm-dashboard-content-inner">
            <form role="form" class="form-inline">
                <div class="row">
                    <div class="ccm-search-fields-submit col-xs-12 col-md-6">
                        <div class="form-group">
                            <div class="ccm-search-main-lookup-field">
                                <?= $form->search('keywords', $searchRequest['keywords'], ['placeholder' => t('Search by Name or SKU'), 'style'=>"min-width: 220px"]) ?>
                            </div>

                        </div>
                        <button class="btn btn-info" type="submit"><i class="fa fa-search"></i></button>
                    </div>
                    <div class="col-xs-12 col-md-6">
                        <?php if ($grouplist) {
                            $currentFilter = '';
                            ?>
                            <ul id="group-filters" class="nav nav-pills">

                                <li role="presentation" class="dropdown <?= ($gID ? 'active' : ''); ?>">
                                    <?php
                                    if ($gID) {
                                        foreach ($grouplist as $group) {
                                            if ($gID == $group->getGroupID()) {
                                                $currentFilter = $group->getGroupName();
                                            }
                                        }
                                    } ?>

                                    <a class="dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
                                        <?= $currentFilter ? t('Product Group: %s', $currentFilter) : t('Product Group'); ?> <span class="caret"></span>
                                    </a>

                                    <ul class="dropdown-menu">
                                        <li <?= (!$gID ? 'class="active"' : ''); ?>><a href="<?= Url::to('/dashboard/store/multilingual/products/') ?>"><?= t('All Groups') ?></a></li>
                                        <?php foreach ($grouplist as $group) { ?>
                                            <li <?= ($gID == $group->getGroupID() ? 'class="active"' : ''); ?>><a href="<?= Url::to('/dashboard/store/multilingual/products/', $group->getGroupID()) ?>"><?= $group->getGroupName() ?></a></li>
                                        <?php } ?>
                                    </ul>
                                </li>
                            </ul><br />
                        <?php } ?>
                    </div>
                </div>
            </form>
        </div>
        <div class="ccm-dashboard-content-full">
        <table class="ccm-search-results-table">
            <thead>
            <tr>
                <th><a><?= t('Product') ?></a></th>
                <th><a><?= $defaultLocale->getLanguageText($defaultLocale->getLocale()); ?>
                        (<?= $defaultLocale->getLocale() ?>)</a></th>

                <th><a href="<?= $productList->getSortURL('active'); ?>"><?= t('Active') ?></a></th>
                <th><a><?= t('Groups') ?></a></th>

                <?php
                foreach ($locales  as $lp) { ?>
                <th><a><?= $lp->getLanguageText($lp->getLocale()); ?> (<?= $lp->getLocale() ?>)</a>
                <th>
                    <?php } ?>


                <th><a><?= t('Translate') ?></a></th>
            </tr>
            </thead>
            <tbody>

            <?php if (count($products) > 0) {
                foreach ($products as $product) {
                    ?>
                    <tr>
                        <td><?= $product->getImageThumb(); ?></td>
                        <td>
                            <strong><a href="<?= Url::to('/dashboard/store/multilingual/products/translate/', $product->getID()) ?>"><?= $product->getName();
                                    $sku = $product->getSKU();
                                    if ($sku) {
                                        echo ' (' . $sku . ')';
                                    }
                                    ?>
                                </a></strong></td>
                        <td>
                            <?php
                            if ($product->isActive()) {
                                echo "<span class='label label-success'>" . t('Active') . "</span>";
                            } else {
                                echo "<span class='label label-default'>" . t('Inactive') . "</span>";
                            }
                            ?>
                        </td>

                        <td>
                            <?php $productgroups = $product->getGroups();
                            foreach ($productgroups as $pg) { ?>
                                <span class="label label-primary"><?= $pg->getGroup()->getGroupName(); ?></span>
                            <?php } ?>

                            <?php if (empty($productgroups)) { ?>
                                <em><?= t('None'); ?></em>
                            <?php } ?>
                        </td>

                        <?php foreach ($locales

                        as $lp) { ?>
                        <td>
                            <?= $csm->t(null, 'productName', $product->getID(), null, $lp->getLocale()); ?>
                        <td>
                            <?php } ?>
                        <td>
                            <a class="btn btn-sm btn-primary"
                               href="<?= Url::to('/dashboard/store/multilingual/products/translate', $product->getID()) ?>"><?= t('Translate'); ?></i></a>
                        </td>
                    </tr>
                <?php }
            } ?>
            </tbody>
        </table>

        <?php if ($paginator->getTotalPages() > 1) { ?>
            <div class="ccm-search-results-pagination">
                <?= $pagination ?>
            </div>
        <?php } ?>

    </div>

    <?php } else { ?>
    <p class="alert alert-info"><?= t('No additional locales have been defined');?></p>
    <?php } ?>

<?php } ?>

<?php if ($action == 'translate') {

$localecount = count($locales);

?>
<form method="post" action="<?= $view->action('save') ?>">
    <?= $token->output('community_store'); ?>
    <input type="hidden" name="pID" value="<?= $product->getID() ?>"/>


    <fieldset>
        <legend><a target="_blank" title="<?= t('Manage Product'); ?>" href="<?= Url::to('/dashboard/store/products/edit/', $product->getID()) ?>"><?= h($product->getName()); ?></a></legend>
        <p class="help-block"><?= t('Product specific translations'); ?></p>
    <table class="table table-bordered table-condensed">
        <tr>
            <th><?= t('Context'); ?></th>
            <th><?= t('Text'); ?> - <?= $defaultLocale->getLanguageText($defaultLocale->getLocale()); ?>
                (<?= $defaultLocale->getLocale() ?>)
            </th>
            <th></th>
            <th><?= t('Locale') ?></th>
            <th style="width: 50%"><?= t('Translations'); ?></th>
        </tr>

        <?php

        $firstrow = true;
        foreach ($locales as $lp) { ?>
            <tr>
                <?php if ($firstrow) {
                    $firstrow = false;
                    ?>
                    <td rowspan="<?= $localecount; ?>"><span class="label label-primary"><?= t('Product Name') ?></span>
                    </td>
                    <td rowspan="<?= $localecount; ?>"><?= $product->getName() ?></td>
                <?php } ?>
                <td class="text-center"><i class="copytext fa fa-arrow-right"></i></td>
                <td>
                    <span class="label label-default"><?= $lp->getLanguageText($lp->getLocale()); ?> (<?= $lp->getLocale() ?>)</span>
                </td>

                <td>
                    <input type="text" class="form-control"
                           name="translation[<?= $lp->getLocale(); ?>][text][productName]"
                           value="<?= $csm->t(null, 'productName', $product->getID(), false, $lp->getLocale()); ?>"/>
                </td>

            </tr>
        <?php } ?>

        <?php

        $firstrow = true;
        foreach ($locales as $lp) { ?>
            <tr>
                <?php if ($firstrow) {
                    $firstrow = false;
                    ?>
                    <td rowspan="<?= $localecount; ?>"><span
                                class="label label-primary"><?= t('Short Description') ?></span>
                    </td>
                    <td rowspan="<?= $localecount; ?>"><?= $product->getDescription() ?></td>
                <?php } ?>
                <td class="text-center"><i class="copytext fa fa-arrow-right"></i></td>
                <td>
                    <span class="label label-default"><?= $lp->getLanguageText($lp->getLocale()); ?> (<?= $lp->getLocale() ?>)</span>
                </td>

                <td>

                    <?php
                    echo $editor->outputStandardEditor('translation[' . $lp->getLocale() . '][longText][productDescription]', $csm->t(null, 'productDescription', $product->getID(), false, $lp->getLocale()));
                    ?>

                </td>

            </tr>
        <?php } ?>

        <?php

        $firstrow = true;
        foreach ($locales as $lp) { ?>
            <tr>
                <?php if ($firstrow) {
                    $firstrow = false;
                    ?>
                    <td rowspan="<?= $localecount; ?>"><span class="label label-primary"><?= t('Details') ?></span>
                    </td>
                    <td rowspan="<?= $localecount; ?>"><?= $product->getDetail() ?></td>
                <?php } ?>
                <td class="text-center"><i class="copytext fa fa-arrow-right"></i></td>
                <td>
                    <span class="label label-default"><?= $lp->getLanguageText($lp->getLocale()); ?> (<?= $lp->getLocale() ?>)</span>
                </td>

                <td>
                    <?php
                    echo $editor->outputStandardEditor('translation[' . $lp->getLocale() . '][longText][productDetails]', $csm->t(null, 'productDetails', $product->getID(), false, $lp->getLocale()));
                    ?>
                </td>

            </tr>
        <?php } ?>

        <?php

        $firstrow = true;
        foreach ($locales as $lp) { ?>
            <tr>
                <?php if ($firstrow) {
                    $firstrow = false;
                    ?>
                    <td rowspan="<?= $localecount; ?>"><span
                                class="label label-primary"><?= t('Quantity Label') ?></span>
                    </td>
                    <td rowspan="<?= $localecount; ?>"><?= $product->getQtyLabel() ?></td>
                <?php } ?>
                <td class="text-center"><i class="copytext fa fa-arrow-right"></i></td>
                <td>
                    <span class="label label-default"><?= $lp->getLanguageText($lp->getLocale()); ?> (<?= $lp->getLocale() ?>)</span>
                </td>

                <td>
                    <input type="text" class="form-control"
                           name="translation[<?= $lp->getLocale(); ?>][text][productQuantityLabel]"
                           value="<?= $csm->t(null, 'productQuantityLabel', $product->getID(), false, $lp->getLocale(), false); ?>"
                            placeholder="<?= $csm->t(null, 'productQuantityLabel', false, false, $lp->getLocale()); ?>" />
                </td>

            </tr>
        <?php } ?>
    </table>
    </fieldset>


    <fieldset>
        <legend><?= t('Options and Option Values'); ?></legend>
        <p class="help-block"><?= t('Translations entered below will override common translations for this product only. It is recommended to enter common translations first.'); ?></p>

        <?php $productOptions = $product->getOptions(); ?>
        <?php if (count($productOptions) > 0) { ?>
        <table class="table table-bordered table-condensed">
            <tr>
                <th><?= t('Context'); ?></th>
                <th><?= t('Text'); ?> - <?= $defaultLocale->getLanguageText($defaultLocale->getLocale()); ?>
                    (<?= $defaultLocale->getLocale() ?>)
                </th>
                <th></th>
                <th><?= t('Locale') ?></th>
                <th style="width: 50%"><?= t('Translations'); ?></th>
            </tr>

            <?php foreach ($product->getOptions() as $option) {

            $firstrow = true;
            foreach ($locales as $lp) { ?>
                <?php if ($option->getType() != 'static') { ?>
                <tr>
                    <?php if ($firstrow) {
                        $firstrow = false;
                        ?>

                        <td rowspan="<?= $localecount; ?>"><span
                                    class="label label-primary"><?= t('Option Name'); ?></span>
                        </td>
                        <td rowspan="<?= $localecount; ?>"><?= t($option->getName()); ?></td>
                    <?php } ?>
                    <td class="text-center"><i class="copytext fa fa-arrow-right"></i></td>
                    <td>
                        <span class="label label-default"><?= $lp->getLanguageText($lp->getLocale()); ?> (<?= $lp->getLocale() ?>)</span>
                    </td>
                    <td class="text-center"><i class="copytext fa fa-arrow-right"></i></td>
                    <td>
                        <input type="text" class="form-control"
                               placeholder="<?= $csm->t($option->getName(), 'optionName', false, false, $lp->getLocale()); ?>"
                               name="translation[<?= $lp->getLocale(); ?>][text][optionName][<?= $option->getID(); ?>]"
                               value="<?= $csm->t($option->getName(), 'optionName', $product->getID(), $option->getID(), $lp->getLocale(), false); ?>"/>

                    </td>

                </tr>
                <?php } ?>

                <tr>

                    <td rowspan="<?= $localecount; ?>"><span
                                class="label label-primary">
                    <?php if ($option->getType() != 'static') { ?>
                        <?= t('Option Details'); ?>
                    <?php } else { ?>
                        <?= t('Static HTML'); ?>
                    <?php } ?>

                    </span>
                    </td>
                    <td rowspan="<?= $localecount; ?>"><?= t($option->getDetails()); ?></td>
                    <td class="text-center"><i class="copytext fa fa-arrow-right"></i></td>

                    <td>
                        <span class="label label-default"><?= $lp->getLanguageText($lp->getLocale()); ?> (<?= $lp->getLocale() ?>)</span>
                    </td>

                    <td>
                        <input type="text" class="form-control"
                               placeholder="<?= $csm->t($option->getDetails(), 'optionDetails', false, false, $lp->getLocale()); ?>"
                               name="translation[<?= $lp->getLocale(); ?>][text][optionDetails][<?= $option->getID(); ?>]"
                               value="<?= $csm->t($option->getDetails(), 'optionDetails', $product->getID(), $option->getID(), $lp->getLocale(), false); ?>"/>

                    </td>

                </tr>
            <?php }
            foreach ($option->getOptionItems() as $optionValue) {
                $firstrow = true;
                foreach ($locales as $lp) { ?>
                    <tr>
                        <?php if ($firstrow) {
                            $firstrow = false;
                            ?>

                            <td rowspan="<?= $localecount; ?>">&nbsp;-&nbsp;<span
                                        class="label label-primary"><?= t('Option Value'); ?></span>
                            </td>
                            <td rowspan="<?= $localecount; ?>"><?= t($optionValue->getName()); ?></td>
                        <?php } ?>
                        <td class="text-center"><i class="copytext fa fa-arrow-right"></i></td>
                        <td>
                            <span class="label label-default"><?= $lp->getLanguageText($lp->getLocale()); ?> (<?= $lp->getLocale() ?>)</span>
                        </td>

                        <td>
                            <input type="text" class="form-control"
                                   placeholder="<?= $csm->t($optionValue->getName(), 'optionValue', false, false, $lp->getLocale()); ?>"
                                   name="translation[<?= $lp->getLocale(); ?>][text][optionValue][<?= $optionValue->getID(); ?>]"
                                   value="<?= $csm->t($optionValue->getName(), 'optionValue', $product->getID(), $optionValue->getID(), $lp->getLocale(), false); ?>"/>
                        </td>
                    </tr>
                    <tr>


                        <td rowspan="<?= $localecount; ?>">&nbsp;-&nbsp;<span
                                    class="label label-primary"><?= t('Option Selector Display Label'); ?></span>
                        </td>
                        <td rowspan="<?= $localecount; ?>"><?= t($optionValue->getSelectorName()); ?></td>

                        <td class="text-center"><i class="copytext fa fa-arrow-right"></i></td>
                        <td>
                            <span class="label label-default"><?= $lp->getLanguageText($lp->getLocale()); ?> (<?= $lp->getLocale() ?>)</span>
                        </td>

                        <td>
                            <input type="text" class="form-control"
                                   placeholder="<?= $csm->t($optionValue->getSelectorName(), 'optionSelectorName', false, false, $lp->getLocale()); ?>"
                                   name="translation[<?= $lp->getLocale(); ?>][text][optionSelectorName][<?= $optionValue->getID(); ?>]"
                                   value="<?= $csm->t($optionValue->getSelectorName(), 'optionSelectorName', $product->getID(), $optionValue->getID(), $lp->getLocale(), false); ?>"/>
                        </td>
                    </tr>
                <?php } ?>
            <?php } ?>
        <?php } ?>

    </table>
        <?php } else { ?>
        <p class="alert alert-info"><?= t('No options have been created for this product');?></p>
        <?php } ?>
    </fieldset>


    <fieldset>
        <legend><?= t('Attribute Names and Values'); ?></legend>

        <table class="table table-bordered table-condensed">
            <tr>
                <th><?= t('Context'); ?></th>
                <th><?= t('Text'); ?> - <?= $defaultLocale->getLanguageText($defaultLocale->getLocale()); ?>
                    (<?= $defaultLocale->getLocale() ?>)
                </th>
                <th></th>
                <th><?= t('Locale') ?></th>
                <th style="width: 50%"><?= t('Translations'); ?></th>
            </tr>


            <?php

            foreach ($attrList as $attr) {

                $firstrow = true;
                foreach ($locales as $lp) { ?>
                    <tr>
                        <?php if ($firstrow) {
                            $firstrow = false;
                            ?>

                            <td rowspan="<?= $localecount; ?>"><span
                                        class="label label-primary"><?= t('Attribute Name'); ?></span>
                            </td>
                            <td rowspan="<?= $localecount; ?>"><?= $attr->getAttributeKeyName(); ?></td>
                        <?php } ?>
                        <td class="text-center"><i class="copytext fa fa-arrow-right"></i></td>
                        <td>
                            <span class="label label-default"><?= $lp->getLanguageText($lp->getLocale()); ?> (<?= $lp->getLocale() ?>)</span>
                        </td>

                        <td>
                            <input type="text" class="form-control"
                                   placeholder="<?= $csm->t($attr->getAttributeKeyName(), 'productAttributeName', false, $attr->getAttributeKeyID(), $lp->getLocale()); ?>"
                                   name="translation[<?= $lp->getLocale(); ?>][text][productAttributeName][<?= $attr->getAttributeKeyID(); ?>]"
                                   value="<?= $csm->t(null, 'productAttributeName', $product->getID(), $attr->getAttributeKeyID(), $lp->getLocale(), false); ?>"/>
                        </td>

                    </tr>
                <?php } ?>
            <?php }


            foreach ($attrOptions as $type => $typeAttrOptions) {
                foreach ($typeAttrOptions as $attrOption => $x) {
                    $firstrow = true;
                    foreach ($locales as $lp) { ?>
                        <tr>
                            <?php if ($firstrow) {
                                $firstrow = false;
                                ?>

                                <td rowspan="<?= $localecount; ?>"><span
                                            class="label label-primary"><?= t('Attribute Value'); ?></span>
                                </td>
                                <td rowspan="<?= $localecount; ?>"><?= $attrOption; ?></td>
                            <?php } ?>
                            <td class="text-center"><i class="copytext fa fa-arrow-right"></i></td>
                            <td>
                                <span class="label label-default"><?= $lp->getLanguageText($lp->getLocale()); ?> (<?= $lp->getLocale() ?>)</span>
                            </td>

                            <td>
                                <input type="text" class="form-control"
                                       placeholder="<?= $csm->t($attrOption, 'productAttributeValue', false, false, $lp->getLocale()); ?>"
                                       name="translation[<?= $lp->getLocale(); ?>][<?= $type; ?>][productAttributeValue][<?= h($attrOption); ?>]"
                                       value="<?= $csm->t($attrOption, 'productAttributeValue', $product->getID(), false, $lp->getLocale(), false); ?>"/>
                            </td>

                        </tr>
                    <?php } ?>
                <?php }
            } ?>


        </table>
    </fieldset>


    <div class="ccm-dashboard-form-actions-wrapper">
        <div class="ccm-dashboard-form-actions">
            <a href="<?= Url::to('/dashboard/store/multilingual/products/' . ($groupSearch ? $groupSearch : '') . ($keywordsSearch ? '?keywords='.urlencode($keywordsSearch) : '')) ?>"
               class="btn btn-default pull-left"><?= t("Cancel") ?></a>
            <button class="pull-right btn btn-success" type="submit"><?= t('Save Product Translation') ?></button>
        </div>
    </div>
    <?php } ?>

    <style>
        .copytext {
            cursor: pointer;
        }
    </style>

    <script>
            $(document).ready(function(){
                $('.copytext').click(function(){
                    var content = $(this).parent().prev().html();
                    $(this).parent().next().next().find('input, textarea').val(content);
                });
            });

    </script>
