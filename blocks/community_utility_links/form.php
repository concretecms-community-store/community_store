<?php defined('C5_EXECUTE') or die("Access Denied.");?>
<div class="checkbox">
    <label>
        <?= $form->checkbox('showCartItems', 1, isset($showCartItems) ? $showCartItems : 1);?>
        <?= t('Show Number Of Items In Cart')?>
    </label>
</div>

<div class="checkbox">
    <label>
        <?= $form->checkbox('showCartTotal', 1, isset($showCartTotal) ? $showCartTotal : 1);?>
        <?= t('Show Cart Total')?>
    </label>
</div>

<div class="checkbox">
    <label>
        <?= $form->checkbox('showCheckout', 1, isset($showCheckout) ? $showCheckout : 1);?>
        <?= t('Show Checkout Link')?>
    </label>
</div>

<div class="checkbox">
    <label>
        <?= $form->checkbox('popUpCart', 1, isset($popUpCart) ? $popUpCart : 1);?>
        <?= t('Display Cart In Popup')?>
    </label>
</div>

<div class="checkbox">
    <label>
        <?= $form->checkbox('showGreeting', 1, isset($showSignIn) ? $showSignIn : 1);?>
        <?= t('Show Greeting')?>
    </label>
</div>

<div class="checkbox">
    <label>
        <?= $form->checkbox('showSignIn', 1, isset($showSignIn) ? $showSignIn : 1);?>
        <?= t('Show Sign-In Link')?>
    </label>
</div>


<div class="form-group">
    <?= $form->label('cartLabel', t('Cart Link Label'));?>
    <?= $form->text('cartLabel', $cartLabel ? $cartLabel : t("View Cart"));?>
</div>
<div class="form-group">
    <?= $form->label('itemsLabel', t('Cart Items Label'));?>
    <?= $form->text('itemsLabel', $itemsLabel ? $itemsLabel : t("Items in Cart"));?>
</div>