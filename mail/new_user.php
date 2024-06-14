<?php
defined('C5_EXECUTE') or die("Access Denied.");

$subject = $siteName.' - '.t('account created');

$app = \Concrete\Core\Support\Facade\Application::getFacadeApplication();
$csm = $app->make('cs/helper/multilingual');

/**
 * HTML BODY START
 */
ob_start();

?>
    <!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN' 'http://www.w3.org/TR/html4/loose.dtd'>
    <html>
    <head>
        <style>
            body {
                font-family: Arial, 'Helvetica Neue', Helvetica, sans-serif;
            }
        </style>
    </head>
    <body>
    <?php $header = $csm->t(\Concrete\Core\Editor\LinkAbstractor::translateFromEditMode(trim(\Concrete\Core\Support\Facade\Config::get('community_store.receiptHeader'))), 'receiptEmailHeader'); ?>

    <?= $header; ?>

    <h2><?= t(/* i18n: %s is the name of the site*/'An account has been created for you at %s', $siteName) ?> </h2>

    <p><?= t('Your login is');?>: <strong><?= h($username); ?></strong></p>
    <p><?= t('Your password is');?>: <strong><?= h($password); ?></strong></p>

    <?php if ($link) { ?>
        <p><?= t(/* i18n: %s is a link*/'You can now access %s', "<a href=\"{$link}\">{$link}</a>") ?></p>
    <?php } ?>

    <?= $csm->t(trim(\Concrete\Core\Editor\LinkAbstractor::translateFromEditMode(\Concrete\Core\Support\Facade\Config::get('community_store.receiptFooter'))), 'receiptEmailFooter'); ?>

    </body>
    </html>

<?php
$bodyHTML = ob_get_clean();
/**
 * HTML BODY END
 *
 * =====================
 *
 * PLAIN TEXT BODY START
 */
ob_start();

?>
<?= t(/* i18n: %s is the name of the site*/'Thank you for your order, an account has been created for you at %s', $siteName) ?>

<?= t('Your login is');?>: <?= h($username); ?>
<?= t('Your password is');?>: <?= h($password); ?>

<?php if ($link) { ?>
    <?= t(/* i18n: %s is a link*/'You can now access %s', $link); ?>
<?php } ?>

<?php

$body = ob_get_clean();
ob_end_clean();

/**
 * PLAIN TEXT BODY END
 */
