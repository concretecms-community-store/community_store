<?php defined('C5_EXECUTE') or die("Access Denied.");


$csm = \Core::make('cs/helper/multilingual');
$editor = \Core::make('editor');
$action = $controller->getAction();
$localecount = count($locales);
?>


<?if (count($locales) > 0) { ?>
<form method="post" action="<?= $view->action('save') ?>">
    <?= $token->output('community_store'); ?>
    <fieldset>
        <legend><?= t('Payment Methods'); ?></legend>

        <?php if (!empty($paymentMethods)) { ?>
         <table class="table table-bordered table-condensed">
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
                                   name="translation[paymentMethods][<?= $paymentMethod->getID(); ?>][<?= $lp->getLocale(); ?>][text][paymentDisplayName]"
                                   value="<?= $csm->t(null, 'paymentDisplayName', false, $paymentMethod->getID(), $lp->getLocale()); ?>"/>
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
                                   name="translation[paymentMethods][<?= $paymentMethod->getID(); ?>][<?= $lp->getLocale(); ?>][text][paymentButtonLabel]"
                                   value="<?= $csm->t(null, 'paymentButtonLabel', false, $paymentMethod->getID(), $lp->getLocale()); ?>"/>
                        </td>

                    </tr>
                <?php } ?>


            <?php } ?>
        </table>
        <?php } else { ?>
        <p class="alert alert-info"><?= t("No Payment Methods are installed"); ?></p>
        <?php } ?>

    </fieldset>


    <fieldset>
        <legend><?= t('Shipping Methods'); ?></legend>

        <?php if  (!empty($shippingMethods)) { ?>
        <table class="table table-bordered table-condensed">
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
                                   name="translation[shippingMethods][<?= $shippingMethod->getID(); ?>][<?= $lp->getLocale(); ?>][text][shippingName]"
                                   value="<?= $csm->t(null, 'shippingName', false, $shippingMethod->getID(), $lp->getLocale()); ?>"/>
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
                            <td rowspan="<?= $localecount; ?>"><?= $shippingMethod->getDetails(); ?></td>
                        <?php } ?>

                        <td>
                            <span class="label label-default"><?= $lp->getLanguageText($lp->getLocale()); ?> (<?= $lp->getLocale() ?>)</span>
                        </td>

                        <td>
                            <?php
                            echo $editor->outputStandardEditor('translation[shippingMethods]['.  $shippingMethod->getID() . '][' . $lp->getLocale() .'][longText][shippingDetails]', $csm->t(null, 'shippingDetails', false, $shippingMethod->getID(), $lp->getLocale()));
                            ?>
                        </td>

                    </tr>
                <?php } ?>


            <?php } ?>
        </table>
        <?php } else { ?>
            <p class="alert alert-info"><?= t('No Shipping Methods have been defined'); ?></p>
        <?php } ?>


    </fieldset>

    <fieldset>
        <legend><?= t('Tax Rates'); ?></legend>

        <?php if  (!empty($taxRates)) { ?>
            <table class="table table-bordered table-condensed">
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

            foreach ($taxRates as $taxRate) {

                $firstrow = true;
                foreach ($locales as $lp) { ?>
                    <tr>
                        <?php if ($firstrow) {
                            $firstrow = false;
                            ?>
                            <td rowspan="<?= $localecount * 2; ?>"><?= h($taxRate->getTaxLabel()); ?></td>
                            <td rowspan="<?= $localecount; ?>"><span
                                        class="label label-primary"><?= t('Tax Rate Name'); ?></span>
                            </td>
                            <td rowspan="<?= $localecount; ?>"><?= h($taxRate->getTaxLabel()); ?></td>
                        <?php } ?>

                        <td>
                            <span class="label label-default"><?= $lp->getLanguageText($lp->getLocale()); ?> (<?= $lp->getLocale() ?>)</span>
                        </td>

                        <td>
                            <input type="text" class="form-control"
                                   name="translation[taxRates][<?= $taxRate->getID(); ?>][<?= $lp->getLocale(); ?>][text][taxRateName]"
                                   value="<?= $csm->t(null, 'taxRateName', false, $taxRate->getID(), $lp->getLocale()); ?>"/>
                        </td>

                    </tr>
                <?php } ?>


            <?php } ?>
        </table>
        <?php } else { ?>
            <p class="alert alert-info"><?= t('No Tax Rates have been defined'); ?></p>
        <?php } ?>

    </fieldset>

    <fieldset>
        <legend><?= t('Discount Rules'); ?></legend>


        <?php if  (!empty($discountRules)) { ?>
            <table class="table table-bordered table-condensed">
                <tr>
                    <th><?= t('Discount Rule'); ?></th>
                    <th><?= t('Context'); ?></th>
                    <th><?= t('Text'); ?> - <?= $defaultLocale->getLanguageText($defaultLocale->getLocale()); ?>
                        (<?= $defaultLocale->getLocale() ?>)
                    </th>
                    <th><?= t('Locale') ?></th>
                    <th style="width: 50%"><?= t('Translations'); ?></th>
                </tr>

                <?php

                foreach ($discountRules as $discountRule) {

                    $firstrow = true;
                    foreach ($locales as $lp) { ?>
                        <tr>
                            <?php if ($firstrow) {
                                $firstrow = false;
                                ?>
                                <td rowspan="<?= $localecount * 2; ?>"><?= h($discountRule->getName()); ?></td>
                                <td rowspan="<?= $localecount; ?>"><span
                                            class="label label-primary"><?= t('Discount Display Name'); ?></span>
                                </td>
                                <td rowspan="<?= $localecount; ?>"><?= h($discountRule->getDisplay()); ?></td>
                            <?php } ?>

                            <td>
                                <span class="label label-default"><?= $lp->getLanguageText($lp->getLocale()); ?> (<?= $lp->getLocale() ?>)</span>
                            </td>

                            <td>
                                <input type="text" class="form-control"
                                       name="translation[discountRules][<?= $discountRule->getID(); ?>][<?= $lp->getLocale(); ?>][text][discountRuleDisplayName]"
                                       value="<?= $csm->t(null, 'discountRuleDisplayName', false, $discountRule->getID(), $lp->getLocale()); ?>"/>
                            </td>

                        </tr>
                    <?php } ?>


                <?php } ?>
            </table>
        <?php } else { ?>
            <p class="alert alert-info"><?= t('No Discount Rules have been defined'); ?></p>
        <?php } ?>



    </fieldset>

    <fieldset>
        <legend><?= t('Order Attribute Names'); ?></legend>

        <?php if (!empty($orderAttributes)) { ?>
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

                foreach ($orderAttributes as $attr) {

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

                            <td>
                                <span class="label label-default"><?= $lp->getLanguageText($lp->getLocale()); ?> (<?= $lp->getLocale() ?>)</span>
                            </td>

                            <td>
                                <input type="text" class="form-control"
                                       name="translation[orderAttributes][<?= $attr->getAttributeKeyID(); ?>][<?= $lp->getLocale(); ?>][text][orderAttributeName]"
                                       value="<?= $csm->t(null, 'orderAttributeName', false, $attr->getAttributeKeyID(), $lp->getLocale(), false); ?>"/>
                            </td>

                        </tr>
                    <?php } ?>


                <?php }
                ?>
            </table>
        <?php } else { ?>
            <p class="alert alert-info"><?= t("No Attribute or Attribute Values have been created on products"); ?></p>
        <?php } ?>



        <fieldset>
            <legend><?= t('Receipt Email'); ?></legend>

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
                    $firstrow = true;
                    foreach ($locales as $lp) { ?>
                        <tr>
                            <?php if ($firstrow) {
                                $firstrow = false;
                                ?>

                                <td rowspan="<?= $localecount; ?>"><span
                                            class="label label-primary"><?= t('Receipt Email Header'); ?></span>
                                </td>
                                <td rowspan="<?= $localecount; ?>">

                                    <?= $receiptHeader; ?>

                                </td>
                            <?php } ?>

                            <td>
                                <span class="label label-default"><?= $lp->getLanguageText($lp->getLocale()); ?> (<?= $lp->getLocale() ?>)</span>
                            </td>

                            <td>
                                <?php
                                echo $editor->outputStandardEditor('configtranslation[' . $lp->getLocale() .'][longText][receiptEmailHeader]', $csm->t(null, 'receiptEmailHeader', false, false, $lp->getLocale()));
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
                                        class="label label-primary"><?= t('Receipt Email Footer'); ?></span>
                            </td>
                            <td rowspan="<?= $localecount; ?>">

                                <?= $receiptFooter; ?>
                            </td>
                        <?php } ?>

                        <td>
                            <span class="label label-default"><?= $lp->getLanguageText($lp->getLocale()); ?> (<?= $lp->getLocale() ?>)</span>
                        </td>

                        <td>
                            <?php
                            echo $editor->outputStandardEditor('configtranslation[' . $lp->getLocale() .'][longText][receiptEmailFooter]', $csm->t(null, 'receiptEmailFooter', false, false, $lp->getLocale()));
                            ?>
                        </td>

                    </tr>
                    <?php } ?>


                </table>


    </fieldset>


    <div class="ccm-dashboard-form-actions-wrapper">
        <div class="ccm-dashboard-form-actions">

            <button class="pull-right btn btn-success" type="submit"><?= t('Save Translations') ?></button>
        </div>
    </div>

</form>
<?php } else { ?>
    <p class="alert alert-info"><?= t('No additional locales have been defined');?></p>
<?php } ?>