<?php defined('C5_EXECUTE') or die("Access Denied.");

$action = $controller->getAction();
$csm = \Core::make('cshelper/multilingual');

if ($action == 'product') {

    $localecount = count($localePages);

    ?>
    <form method="post" action="<?= $view->action('save')?>">
        <?= $token->output('community_store'); ?>
        <input type="hidden" name="pID" value="<?= $product->getID()?>"/>
    <table class="table table-bordered">
        <tr>
            <th><?= t('Source'); ?></th>
            <th><?= t('Text'); ?></th>
            <th><?= t('Locale') ?></th>
            <th style="width: 50%"><?= t('Translations'); ?></th>
        </tr>

        <?php

        $firstrow = true;
        foreach ($localePages as $lp) { ?>
            <tr>
                <?php if ($firstrow) {
                    $firstrow = false;
                    ?>
                    <td rowspan="<?= $localecount; ?>"><span class="label label-primary"><?= t('Product Name') ?></span>
                    </td>
                    <td rowspan="<?= $localecount; ?>"><?= $product->getName() ?></td>
                <?php } ?>

                <td>
                    <span class="label label-default"><?= $lp->getLocale() ?></span>
                </td>

                <td>
                    <input type="text" class="form-control" name="translation[<?= $lp->getLocale(); ?>][text][productName]" value="<?= $csm->t(null, 'productName', $product->getID(), $lp->getLocale());?>" />
                </td>

            </tr>
        <?php } ?>

        <?php

        $firstrow = true;
        foreach ($localePages as $lp) { ?>
            <tr>
                <?php if ($firstrow) {
                    $firstrow = false;
                    ?>
                    <td rowspan="<?= $localecount; ?>"><span class="label label-primary"><?= t('Short Description') ?></span>
                    </td>
                    <td rowspan="<?= $localecount; ?>"><?= $product->getDescription() ?></td>
                <?php } ?>

                <td >
                    <span class="label label-default"><?= $lp->getLocale() ?></span>
                </td>

                <td>

                    <?php
                    $editor = Core::make('editor');
                    echo $editor->outputStandardEditor('translation[' .$lp->getLocale() .'][longText][productDescription]', $csm->t(null, 'productDescription', $product->getID(), $lp->getLocale()));
                    ?>

                </td>

            </tr>
        <?php } ?>

        <?php

        $firstrow = true;
        foreach ($localePages as $lp) { ?>
            <tr>
                <?php if ($firstrow) {
                    $firstrow = false;
                    ?>
                    <td rowspan="<?= $localecount; ?>"><span class="label label-primary"><?= t('Details') ?></span>
                    </td>
                    <td rowspan="<?= $localecount; ?>"><?= $product->getDetail() ?></td>
                <?php } ?>

                <td>
                    <span class="label label-default"><?= $lp->getLocale() ?></span>
                </td>

                <td>
                    <?php
                    $editor = Core::make('editor');
                    echo $editor->outputStandardEditor('translation[' .$lp->getLocale() .'][longText][productDetails]', $csm->t(null, 'productDetails', $product->getID(), $lp->getLocale()));
                    ?>
                </td>

            </tr>
        <?php } ?>


    </table>

    <div class="ccm-dashboard-form-actions-wrapper">
        <div class="ccm-dashboard-form-actions">
            <a href="<?= \URL::to('/dashboard/multilingual/products/')?>" class="btn btn-default pull-left"><?= t("Cancel")?></a>
            <button class="pull-right btn btn-success"  type="submit" ><?= t('Save Product Translation')?></button>
        </div>
    </div>


<?php } ?>
