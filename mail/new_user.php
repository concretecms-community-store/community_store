<?php
defined('C5_EXECUTE') or die("Access Denied.");

$subject = $siteName.' - '.t('account Created');

/**
 * HTML BODY START
 */
ob_start();

?>
    <h2><?= t('Thank you for your order, an account has been created for you at') ?> <?= $siteName ?></h2>

    <p>Your username is: <strong><?= $username; ?></strong></p>
    <p>Your password is: <strong><?= $password; ?></strong></p>

<?php if ($link) { ?>
    <p>You can now access <?= $link; ?></p>
<?php } ?>

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
<?= t('An account has been created for you at') ?> <?= $siteName ?>

    Your username is: <?= $username; ?>
    Your password is: <?= $password; ?>

<?php if ($link) { ?>
    You can now access <?= $link; ?>
<?php } ?>

<?php

$body = ob_get_clean();
ob_end_clean();

// plain text and html emails not currently working, fix coming for 5.7
$body = '';

/**
 * PLAIN TEXT BODY END
 */
