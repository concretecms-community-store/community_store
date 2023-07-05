<?php defined('C5_EXECUTE') or die("Access Denied."); ?>
<?php if (!$shoppingDisabled && empty($salesSuspended)) {
    ?>
<div class="store-utility-links <?= (0 == $itemCount ? 'store-cart-empty' : ''); ?>">
    <?php if ($showSignIn || $showGreeting) {
        ?>
    <p class="store-utility-links-login">
        <?php if ($showSignIn) {
            $u = new User();
            if (!$u->isLoggedIn()) {
                echo '<a href="' . \Concrete\Core\Support\Facade\Url::to('/login') . '">' . t("Sign In") . '</a>';
            }
        } ?>
        <?php if ($showGreeting) {
            $u = new User();
            if ($u->isLoggedIn()) {
                $msg = '<span class="store-welcome-message">' . t("Welcome back") . '</span>';
                $ui = $app->make(\Concrete\Core\User\UserInfoRepository::class)->getByID($u->getUserID());
                if ($ui && $firstname = $ui->getAttribute('billing_first_name')) {
                    $msg = '<span class="store-welcome-message">' . t("Welcome back, %s", '<span class="first-name">' . $firstname . '</span>') . '</span>';
                }
                echo $msg;
            }
        } ?>
        </p>
    <?php
    } ?>

    <?php if ($showCartItems || $showCartTotal) {
        ?>
        <p class="store-utility-links-totals">
            <?php if ($showCartItems) {
            ?>
                <span class="store-items-in-cart"><?= $itemsLabel; ?> (<span class="store-items-counter"><?= $itemCount; ?></span>)</span>
            <?php
        } ?>

            <?php if ($showCartTotal) {
            ?>
                <span class="store-total-cart-amount"><?= $total; ?></span>
            <?php
        } ?>
        </p>
    <?php
    } ?>

    <?php if (!$inCart) {
        ?>
        <p class="store-utility-links-cart-link">
            <?php if ($popUpCart && !$inCheckout) {
            ?>
                <a href="#" class="store-cart-link store-cart-link-modal"><?= $cartLabel; ?></a>
            <?php
        } else {
            ?>
                <a href="<?= \Concrete\Core\Support\Facade\Url::to($langpath . '/cart'); ?>" class="store-cart-link"><?= $cartLabel; ?></a>
            <?php
        } ?>
        </p>
    <?php
    } ?>

    <?php if (!$inCheckout) {
        ?>
        <?php if ($showCheckout) {
            ?>
        <p  class="store-utility-links-checkout-link">
            <a href="<?= \Concrete\Core\Support\Facade\Url::to($langpath . '/checkout'); ?>" class="store-cart-link"><?= t("Checkout"); ?></a>
        </p>
        <?php
        } ?>
    <?php
    } ?>
</div>
<?php
} ?>
