<?php defined('C5_EXECUTE') or die("Access Denied."); ?>
<?php if (!$shoppingDisabled) { ?>
<div class="store-utility-links <?= ($itemCount == 0 ? 'store-cart-empty' : ''); ?>">
    <p>
    <?php if ($showSignIn) {
        $u = new User();
        if (!$u->isLoggedIn()) {
            echo '<a href="' . \URL::to('/login') . '">' . t("Sign In") . '</a>';
        }
    } ?>
    <?php if ($showGreeting) {
        $u = new User();
        if ($u->isLoggedIn()) {
            $msg = '<span class="store-welcome-message">' . t("Welcome back") . '</span>';
            $ui = UserInfo::getByID($u->getUserID());
            if ($firstname = $ui->getAttribute('billing_first_name')) {
                $msg = '<span class="store-welcome-message">' . t("Welcome back, ") . '<span class="first-name">' . $firstname . '</span></span>';
            }
            echo $msg;
        }
    } ?>
    </p>

    <p>
        <?php if ($showCartItems) { ?>
            <span class="store-items-in-cart"><?= $itemsLabel ?> (<span class="items-counter"><?= $itemCount ?></span>)</span>
        <?php } ?>

        <?php if ($showCartTotal) { ?>
            <span class="store-total-cart-amount"><?= $total ?></span>
        <?php } ?>
    </p>

    <?php if (!$inCart) { ?>
        <p>
            <?php if ($popUpCart && !$inCheckout) { ?>
                <a href="#" class="store-cart-link store-cart-link-modal"><?= $cartLabel ?></a>
            <?php } else { ?>
                <a href="<?= \URL::to('/cart') ?>" class="store-cart-link"><?= $cartLabel ?></a>
            <?php } ?>
        </p>
    <?php } ?>

    <?php if (!$inCheckout) { ?>
        <p>
            <?php if ($showCheckout) { ?>
                <a href="<?= \URL::to('/checkout') ?>" class="store-cart-link"><?= t("Checkout") ?></a>
            <?php } ?>
        </p>
    <?php } ?>
</div>
<?php } ?>