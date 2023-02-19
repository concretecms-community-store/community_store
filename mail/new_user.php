<?php
defined('C5_EXECUTE') or die('Access Denied.');

$subject = $siteName . ' - ' . t('account created');

/**
 * HTML BODY START.
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
    <h2><?= t(/* i18n: %s is the name of the site*/'Thank you for your order, an account has been created for you at %s', $siteName) ?> </h2>

    <p><?= t('Your username is'); ?>: <strong><?= $username; ?></strong></p>
    <p><?= t('Your password is'); ?>: <strong><?= $password; ?></strong></p>

<?php if ($link) { ?>
    <p><?= t(/* i18n: %s is a link*/'You can now access %s', "<a href=\"{$link}\">{$link}</a>") ?></p>
<?php } ?>
</body>
</html>

<?php
$bodyHTML = ob_get_clean();
/**
 * HTML BODY END.
 *
 * =====================
 *
 * PLAIN TEXT BODY START
 */
ob_start();

?>
<?= t(/* i18n: %s is the name of the site*/'Thank you for your order, an account has been created for you at %s', $siteName) ?>

    <?= t('Your username is'); ?>: <?= $username; ?>
    <?= t('Your password is'); ?>: <?= $password; ?>

<?php if ($link) { ?>
    <?= t(/* i18n: %s is a link*/'You can now access %s', $link); ?>
<?php } ?>

<?php

$body = ob_get_clean();
ob_end_clean();

/**
 * PLAIN TEXT BODY END.
 */
