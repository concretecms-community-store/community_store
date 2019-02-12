<?php defined('C5_EXECUTE') or die("Access Denied.");


$csm = \Core::make('cshelper/multilingual');
$action = $controller->getAction();
$localecount = count($locales);


?>

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
                    <th><?= t('Locale') ?></th>
                    <th style="width: 50%"><?= t('Translations'); ?></th>
                </tr>

                <?php

                foreach ($optionNames as $option) {

                    $firstrow = true;
                    foreach ($locales as $lp) { ?>
                        <tr>
                            <?php if ($firstrow) {
                                $firstrow = false;
                                ?>

                                <td rowspan="<?= $localecount; ?>"><span
                                        class="label label-primary"><?= t('Option Name'); ?></span>
                                </td>
                                <td rowspan="<?= $localecount; ?>"><?= t($option->getName()); ?></td>
                            <?php } ?>

                            <td>
                                <span class="label label-default"><?= $lp->getLanguageText($lp->getLocale()); ?> (<?= $lp->getLocale() ?>)</span>
                            </td>

                            <td>
                                <input type="text" class="form-control"
                                       name="translation[options][<?= $lp->getLocale(); ?>][text][optionName][<?= h($option->getName()); ?>]"
                                       value="<?= $csm->t($option->getName(), 'optionName', false, $lp->getLocale()); ?>"/>

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
                                <td rowspan="<?= $localecount; ?>"><?= t($option->getName()); ?></td>
                            <?php } ?>

                            <td>
                                <span class="label label-default"><?= $lp->getLanguageText($lp->getLocale()); ?> (<?= $lp->getLocale() ?>)</span>
                            </td>

                            <td>
                                <input type="text" class="form-control"
                                       name="translation[options][<?= $lp->getLocale(); ?>][text][optionValue][<?= h($option->getName()); ?>]"
                                       value="<?= $csm->t($option->getName(), 'optionValue', false, $lp->getLocale()); ?>"/>
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
        <legend><?= t('Attribute Names and Values'); ?></legend>

        <?php if (!empty($optionStrings)) { ?>
            <table class="table table-bordered">
                <tr>
                    <th><?= t('Context'); ?></th>
                    <th><?= t('Text'); ?> - <?= $defaultLocale->getLanguageText($defaultLocale->getLocale()); ?>
                        (<?= $defaultLocale->getLocale() ?>)
                    </th>
                    <th><?= t('Locale') ?></th>
                    <th style="width: 50%"><?= t('Translations'); ?></th>
                </tr>

                <?php

                foreach ($optionStrings as $paymentMethod) {

                    $firstrow = true;
                    foreach ($locales as $lp) { ?>
                        <tr>
                            <?php if ($firstrow) {
                                $firstrow = false;
                                ?>

                                <td rowspan="<?= $localecount; ?>"><span
                                        class="label label-primary"><?= t('Payment Display Name'); ?></span>
                                </td>
                                <td rowspan="<?= $localecount; ?>">XXX</td>
                            <?php } ?>

                            <td>
                                <span class="label label-default"><?= $lp->getLanguageText($lp->getLocale()); ?> (<?= $lp->getLocale() ?>)</span>
                            </td>

                            <td>
                                <input type="text" class="form-control"
                                       name="translation[options][<?= $lp->getLocale(); ?>][text][optionName]"
                                       value="<?= $csm->t(null, 'optionName', false, $lp->getLocale()); ?>"/>
                            </td>

                        </tr>
                    <?php } ?>

                <?php } ?>
            </table>
        <?php } else { ?>
            <p class="alert alert-info"><?= t("No Options or Option Values have been created on products"); ?></p>
        <?php } ?>

    </fieldset>



    <div class="ccm-dashboard-form-actions-wrapper">
        <div class="ccm-dashboard-form-actions">

            <button class="pull-right btn btn-success" type="submit"><?= t('Save Translations') ?></button>
        </div>
    </div>

</form>