<?php defined('C5_EXECUTE') or die("Access Denied."); ?>
<?php

$app = \Concrete\Core\Support\Facade\Application::getFacadeApplication();
$captcha = $app->make("captcha");
?>

<h1><?= t('Security Check'); ?></h1>

<form class="store-checkout-form-group " id="store-checkout-form-group-payment" method="post"  action="<?= \Concrete\Core\Support\Facade\Url::to($langpath.'/checkout/'. ($guest ? '/1' : '')) ?>">
    <?= $token->output('community_store'); ?>

    <?php
    $captchaLabel = $captcha->label();
    if (!empty($captchaLabel)) {
        ?>
        <label class="control-label"><?php echo $captchaLabel; ?></label>
    <?php } ?>

    <div><?php $captcha->display(); ?></div>
    <div><?php $captcha->showInput(); ?></div>

    <p><?= t('Select Continue to complete the security check and proceed to the checkout.');?></p>

    <div class="store-checkout-form-group-buttons mb-3 mt-3">
        <input type="submit" class="store-btn-complete-order btn btn-success pull-right float-end" value="<?= t('Continue'); ?>">
    </div>
</form>
