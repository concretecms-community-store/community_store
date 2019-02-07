<?php defined('C5_EXECUTE') or die("Access Denied.");


$csm = \Core::make('cshelper/multilingual');
$action = $controller->getAction();
$localecount = count($locales);


?>

<form method="post" action="<?= $view->action('save') ?>">

    <fieldset>
        <legend><?= t('Payment Methods'); ?></legend>

        <table class="table table-bordered">
            <tr>
                <th><?= t('Payment Method'); ?></th>
                <th><?= t('Context'); ?></th>
                <th><?= t('Text'); ?> - <?= $defaultLocale->getLanguageText($defaultLocale->getLocale()); ?>
                    (<?= $defaultLocale->getLocale() ?>)
                </th>
                <th><?= t('Locale') ?></th>
                <th style="width: 50%"><?= t('Translations'); ?></th>
            </tr>

            <?php

            foreach ($paymentMethods as $paymentMethod) {

                $firstrow = true;
                foreach ($locales as $lp) { ?>
                    <tr>
                        <?php if ($firstrow) {
                            $firstrow = false;
                            ?>
                            <td rowspan="<?= $localecount * 2; ?>"><?= h($paymentMethod->getName()); ?></td>
                            <td rowspan="<?= $localecount; ?>"><span
                                        class="label label-primary"><?= t('Payment Display Name'); ?></span>
                            </td>
                            <td rowspan="<?= $localecount; ?>"><?= h($paymentMethod->getDisplayName()); ?></td>
                        <?php } ?>

                        <td>
                            <span class="label label-default"><?= $lp->getLanguageText($lp->getLocale()); ?> (<?= $lp->getLocale() ?>)</span>
                        </td>

                        <td>
                            <input type="text" class="form-control"
                                   name="translation[<?= $lp->getLocale(); ?>][text][paymentDisplayName]"
                                   value="<?= $csm->t(null, 'paymentDisplayName'); ?>"/>
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
                                        class="label label-primary"><?= t('Payment Button Label'); ?></span>
                            </td>
                            <td rowspan="<?= $localecount; ?>"><?= h($paymentMethod->getButtonLabel()); ?></td>
                        <?php } ?>

                        <td>
                            <span class="label label-default"><?= $lp->getLanguageText($lp->getLocale()); ?> (<?= $lp->getLocale() ?>)</span>
                        </td>

                        <td>
                            <input type="text" class="form-control"
                                   name="translation[<?= $lp->getLocale(); ?>][text][paymentButtonLabel]"
                                   value="<?= $csm->t(null, 'paymentButtonLabel'); ?>"/>
                        </td>

                    </tr>
                <?php } ?>


            <?php } ?>
        </table>


    </fieldset>


    <fieldset>
        <legend><?= t('Shipping Methods'); ?></legend>

        <table class="table table-bordered">
            <tr>
                <th><?= t('Shipping Method'); ?></th>
                <th><?= t('Context'); ?></th>
                <th><?= t('Text'); ?> - <?= $defaultLocale->getLanguageText($defaultLocale->getLocale()); ?>
                    (<?= $defaultLocale->getLocale() ?>)
                </th>
                <th><?= t('Locale') ?></th>
                <th style="width: 50%"><?= t('Translations'); ?></th>
            </tr>

            <?php

            foreach ($shippingMethods as $shippingMethod) {

                $firstrow = true;
                foreach ($locales as $lp) { ?>
                    <tr>
                        <?php if ($firstrow) {
                            $firstrow = false;
                            ?>
                            <td rowspan="<?= $localecount * 2; ?>"><?= h($shippingMethod->getName()); ?></td>
                            <td rowspan="<?= $localecount; ?>"><span
                                        class="label label-primary"><?= t('Shipping Method Name'); ?></span>
                            </td>
                            <td rowspan="<?= $localecount; ?>"><?= h($shippingMethod->getName()); ?></td>
                        <?php } ?>

                        <td>
                            <span class="label label-default"><?= $lp->getLanguageText($lp->getLocale()); ?> (<?= $lp->getLocale() ?>)</span>
                        </td>

                        <td>
                            <input type="text" class="form-control"
                                   name="translation[<?= $lp->getLocale(); ?>][text][shippingName]"
                                   value="<?= $csm->t(null, 'shippingName'); ?>"/>
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
                                        class="label label-primary"><?= t('Details'); ?></span>
                            </td>
                            <td rowspan="<?= $localecount; ?>"><?= h($paymentMethod->getButtonLabel()); ?></td>
                        <?php } ?>

                        <td>
                            <span class="label label-default"><?= $lp->getLanguageText($lp->getLocale()); ?> (<?= $lp->getLocale() ?>)</span>
                        </td>

                        <td>
                            <input type="text" class="form-control"
                                   name="translation[<?= $lp->getLocale(); ?>][text][paymentButtonLabel]"
                                   value="<?= $csm->t(null, 'paymentButtonLabel'); ?>"/>
                        </td>

                    </tr>
                <?php } ?>


            <?php } ?>
        </table>



    </fieldset>

    <fieldset>
        <legend><?= t('Tax Rates'); ?></legend>
    </fieldset>

    <fieldset>
        <legend><?= t('Discount Rules'); ?></legend>
    </fieldset>


    <div class="ccm-dashboard-form-actions-wrapper">
        <div class="ccm-dashboard-form-actions">

            <button class="pull-right btn btn-success" type="submit"><?= t('Save Translations') ?></button>
        </div>
    </div>

</form>