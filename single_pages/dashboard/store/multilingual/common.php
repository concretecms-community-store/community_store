<?php defined('C5_EXECUTE') or die("Access Denied.");

$app = \Concrete\Core\Support\Facade\Application::getFacadeApplication();
$csm = $app->make('cs/helper/multilingual');
$action = $controller->getAction();
$localecount = count($locales);
?>

<?php if (count($locales) > 0) { ?>
<form method="post" action="<?= $view->action('save') ?>">
    <?= $token->output('community_store'); ?>

    <fieldset>
        <legend><?= t('Options and Option Values'); ?></legend>

        <?php if (!empty($optionNames)) { ?>
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

                foreach ($optionNames as $option) {

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
                                    <td rowspan="<?= $localecount; ?>"><?= h($option->getName()); ?></td>
                                <?php } else { ?>
                                    <td class="hidden"><?= h($option->getName()); ?></td>
                                <?php } ?>
                                <td class="text-center"><i class="copytext fa fa-arrow-right"></i></td>
                                <td>
                                    <span class="label label-default"><?= $lp->getLanguageText($lp->getLocale()); ?> (<?= $lp->getLocale() ?>)</span>
                                </td>

                                <td>
                                    <input type="text" class="form-control"
                                           name="translation[options][<?= $lp->getLocale(); ?>][text][optionName][<?= h($option->getName()); ?>]"
                                           value="<?= $csm->t($option->getName(), 'optionName', false, false, $lp->getLocale()); ?>"/>

                                </td>

                            </tr>
                        <?php } ?>
                    <?php } ?>

                <?php } ?>


                <?php

                foreach ($optionDetails as $option) {

                    $firstrow = true;
                    foreach ($locales as $lp) { ?>
                        <tr>
                            <?php if ($firstrow) {
                                $firstrow = false;
                                ?>

                                <td rowspan="<?= $localecount; ?>"><span
                                            class="label label-primary">
                                    <?php if ($option->getType() != 'static') { ?>
                                        <?= t('Option Details'); ?>
                                    <?php } else { ?>
                                        <?= t('Static HTML'); ?>
                                    <?php } ?>
                                    </span>
                                </td>
                                <td rowspan="<?= $localecount; ?>"><?= h($option->getDetails()); ?></td>
                            <?php } else { ?>
                                <td class="hidden"><?= h($option->getDetails()); ?></td>
                            <?php } ?>
                            <td class="text-center"><i class="copytext fa fa-arrow-right"></i></td>
                            <td>
                                <span class="label label-default"><?= $lp->getLanguageText($lp->getLocale()); ?> (<?= $lp->getLocale() ?>)</span>
                            </td>

                            <td>
                                <input type="text" class="form-control"
                                       name="translation[options][<?= $lp->getLocale(); ?>][text][optionDetails][<?= h($option->getDetails()); ?>]"
                                       value="<?= $csm->t($option->getDetails(), 'optionDetails', false, false, $lp->getLocale()); ?>"/>

                            </td>

                        </tr>
                    <?php } ?>

                <?php } ?>

                <?php

                foreach ($optionItems as $option) {

                    $firstrow = true;
                    foreach ($locales as $lp) { ?>
                        <tr>
                            <?php if ($firstrow) {
                                $firstrow = false;
                                ?>

                                <td rowspan="<?= $localecount; ?>"><span
                                            class="label label-primary"><?= t('Option Value'); ?></span>
                                </td>
                                <td rowspan="<?= $localecount; ?>"><?= h($option->getName()); ?></td>
                            <?php } else { ?>
                                <td class="hidden"><?= h($option->getName()); ?></td>
                            <?php } ?>
                            <td class="text-center"><i class="copytext fa fa-arrow-right"></i></td>
                            <td>
                                <span class="label label-default"><?= $lp->getLanguageText($lp->getLocale()); ?> (<?= $lp->getLocale() ?>)</span>
                            </td>

                            <td>
                                <input type="text" class="form-control"
                                       name="translation[options][<?= $lp->getLocale(); ?>][text][optionValue][<?= h($option->getName()); ?>]"
                                       value="<?= $csm->t($option->getName(), 'optionValue', false, false, $lp->getLocale()); ?>"/>
                            </td>

                        </tr>
                    <?php } ?>

                <?php } ?>


                <?php

                foreach ($optionSelectorNames as $option) {

                    $firstrow = true;
                    foreach ($locales as $lp) { ?>
                        <tr>
                            <?php if ($firstrow) {
                                $firstrow = false;
                                ?>

                                <td rowspan="<?= $localecount; ?>"><span
                                            class="label label-primary"><?= t('Option Selector Display Label'); ?></span>
                                </td>
                                <td rowspan="<?= $localecount; ?>"><?= h($option->getSelectorName()); ?></td>
                            <?php } else { ?>
                                <td class="hidden"><?= h($option->getSelectorName()); ?></td>
                            <?php } ?>
                            <td class="text-center"><i class="copytext fa fa-arrow-right"></i></td>
                            <td>
                                <span class="label label-default"><?= $lp->getLanguageText($lp->getLocale()); ?> (<?= $lp->getLocale() ?>)</span>
                            </td>

                            <td>
                                <input type="text" class="form-control"
                                       name="translation[options][<?= $lp->getLocale(); ?>][text][optionSelectorName][<?= h($option->getSelectorName()); ?>]"
                                       value="<?= $csm->t($option->getSelectorName(), 'optionSelectorName', false, false, $lp->getLocale()); ?>"/>
                            </td>

                        </tr>
                    <?php } ?>

                <?php } ?>

            </table>
        <?php } else { ?>
            <p class="alert alert-info"><?= t("No Options or Option Values have been created on products"); ?></p>
        <?php } ?>

    </fieldset>

    <fieldset>
        <legend><?= t('Product Quantity Labels'); ?></legend>

        <?php if (!empty($quantityLabels)) { ?>
            <table class="table table-bordered">
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

                foreach ($quantityLabels as $quantityLabel) {

                    $firstrow = true;
                    foreach ($locales as $lp) { ?>
                        <tr>
                            <?php if ($firstrow) {
                                $firstrow = false;
                                ?>

                                <td rowspan="<?= $localecount; ?>"><span
                                            class="label label-primary"><?= t('Quantity Label'); ?></span>
                                </td>
                                <td rowspan="<?= $localecount; ?>"><?= h($quantityLabel); ?></td>
                            <?php } else { ?>
                                <td class="hidden"><?= h($quantityLabel); ?></td>
                            <?php } ?>
                            <td class="text-center"><i class="copytext fa fa-arrow-right"></i></td>
                            <td>
                                <span class="label label-default"><?= $lp->getLanguageText($lp->getLocale()); ?> (<?= $lp->getLocale() ?>)</span>
                            </td>

                            <td>
                                <input type="text" class="form-control"
                                       name="translation[options][<?= $lp->getLocale(); ?>][text][productQuantityLabel][<?= h($quantityLabel); ?>]"
                                       value="<?= $csm->t($quantityLabel, 'productQuantityLabel', false, false, $lp->getLocale(), false); ?>"/>
                            </td>

                        </tr>
                    <?php } ?>
                <?php }
                ?>

            </table>
        <?php } else { ?>
            <p class="alert alert-info"><?= t("No products have quantity labels entered"); ?></p>
        <?php } ?>

    </fieldset>

    <fieldset>
        <legend><?= t('Product Attribute Names and Values'); ?></legend>

        <?php if (!empty($attrList)) { ?>
            <table class="table table-bordered">
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
                                <td rowspan="<?= $localecount; ?>"><?= h($attr->getAttributeKeyName()); ?></td>
                            <?php } else { ?>
                                <td class="hidden"><?= h($attr->getAttributeKeyName()); ?></td>
                            <?php } ?>
                            <td class="text-center"><i class="copytext fa fa-arrow-right"></i></td>
                            <td>
                                <span class="label label-default"><?= $lp->getLanguageText($lp->getLocale()); ?> (<?= $lp->getLocale() ?>)</span>
                            </td>

                            <td>
                                <input type="text" class="form-control"
                                       name="translation[options][<?= $lp->getLocale(); ?>][text][productAttributeName][<?= $attr->getAttributeKeyID(); ?>]"
                                       value="<?= $csm->t(null, 'productAttributeName', false, $attr->getAttributeKeyID(), $lp->getLocale(), false); ?>"/>
                            </td>

                        </tr>
                    <?php } ?>


                <?php }
                ?>


                <?php
                foreach ($attrOptions as $type => $typeAttrOptions) {

                    foreach ($typeAttrOptions as $attrOption=>$x) {

                        $firstrow = true;
                        foreach ($locales as $lp) { ?>
                            <tr>
                                <?php if ($firstrow) {
                                    $firstrow = false;
                                    ?>

                                    <td rowspan="<?= $localecount; ?>"><span
                                                class="label label-primary"><?= t('Attribute Value'); ?></span>
                                    </td>
                                    <td rowspan="<?= $localecount; ?>"><?= h($attrOption); ?></td>
                                <?php } else { ?>
                                    <td class="hidden"><?= h($attrOption); ?></td>
                                <?php } ?>
                                <td class="text-center"><i class="copytext fa fa-arrow-right"></i></td>
                                <td>
                                    <span class="label label-default"><?= $lp->getLanguageText($lp->getLocale()); ?> (<?= $lp->getLocale() ?>)</span>
                                </td>

                                <td>
                                    <input type="text" class="form-control"
                                           name="translation[options][<?= $lp->getLocale(); ?>][<?= $type; ?>][productAttributeValue][<?= h($attrOption); ?>]"
                                           value="<?= $csm->t($attrOption, 'productAttributeValue', false, false, $lp->getLocale()); ?>"/>
                                </td>

                            </tr>
                        <?php } ?>
                    <?php }
                }?>
            </table>
        <?php } else { ?>
            <p class="alert alert-info"><?= t("No Attribute or Attribute Values have been created on products"); ?></p>
        <?php } ?>

    </fieldset>

    <fieldset>
        <legend><?= t('Product Add To Cart Button Text'); ?></legend>

        <?php if (!empty($cartButtons)) { ?>
            <table class="table table-bordered">
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

                foreach ($cartButtons as $cartButtonLabel) {

                    $firstrow = true;
                    foreach ($locales as $lp) { ?>
                        <tr>
                            <?php if ($firstrow) {
                                $firstrow = false;
                                ?>

                                <td rowspan="<?= $localecount; ?>"><span
                                            class="label label-primary"><?= t(' Add To Cart Button Text'); ?></span>
                                </td>
                                <td rowspan="<?= $localecount; ?>"><?= h($cartButtonLabel); ?></td>
                            <?php } else { ?>
                                <td class="hidden"><?= h($cartButtonLabel); ?></td>
                            <?php } ?>
                            <td class="text-center"><i class="copytext fa fa-arrow-right"></i></td>
                            <td>
                                <span class="label label-default"><?= $lp->getLanguageText($lp->getLocale()); ?> (<?= $lp->getLocale() ?>)</span>
                            </td>

                            <td>
                                <input type="text" class="form-control"
                                       name="translation[options][<?= $lp->getLocale(); ?>][text][productAddToCartText][<?= h($cartButtonLabel); ?>]"
                                       value="<?= $csm->t($cartButtonLabel, 'productAddToCartText', false, false, $lp->getLocale(), false); ?>"/>
                            </td>
                        </tr>
                    <?php } ?>
                <?php }
                ?>

            </table>
        <?php } else { ?>
            <p class="alert alert-info"><?= t("No products have Add To Cart Button Text entered"); ?></p>
        <?php } ?>

    </fieldset>

    <fieldset>
        <legend><?= t('Product Out of Stock Message'); ?></legend>

        <?php if (!empty($outOfStock)) { ?>
            <table class="table table-bordered">
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

                foreach ($outOfStock as $outOfStockMessage) {

                    $firstrow = true;
                    foreach ($locales as $lp) { ?>
                        <tr>
                            <?php if ($firstrow) {
                                $firstrow = false;
                                ?>

                                <td rowspan="<?= $localecount; ?>"><span
                                            class="label label-primary"><?= t('Out of Stock Message'); ?></span>
                                </td>
                                <td rowspan="<?= $localecount; ?>"><?= h($outOfStockMessage); ?></td>
                            <?php } else { ?>
                                <td class="hidden"><?= h($outOfStockMessage); ?></td>
                            <?php } ?>
                            <td class="text-center"><i class="copytext fa fa-arrow-right"></i></td>
                            <td>
                                <span class="label label-default"><?= $lp->getLanguageText($lp->getLocale()); ?> (<?= $lp->getLocale() ?>)</span>
                            </td>

                            <td>
                                <input type="text" class="form-control"
                                       name="translation[options][<?= $lp->getLocale(); ?>][text][productOutOfStockMessage][<?= h($outOfStockMessage); ?>]"
                                       value="<?= $csm->t($outOfStockMessage, 'productOutOfStockMessage', false, false, $lp->getLocale(), false); ?>"/>
                            </td>

                        </tr>
                    <?php } ?>
                <?php }
                ?>

            </table>
        <?php } else { ?>
            <p class="alert alert-info"><?= t("No products have an Out of Stock Message entered"); ?></p>
        <?php } ?>

    </fieldset>

    <div class="ccm-dashboard-form-actions-wrapper">
        <div class="ccm-dashboard-form-actions">

            <button class="pull-right btn btn-success float-end" type="submit"><?= t('Save Translations') ?></button>
        </div>
    </div>

</form>
<?php } else { ?>
    <p class="alert alert-info"><?= t('No additional locales have been defined');?></p>
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
