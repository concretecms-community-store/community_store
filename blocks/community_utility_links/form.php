<?php defined('C5_EXECUTE') or die("Access Denied.");?>

<div class="checkbox">
    <label>
        <?= $form->checkbox('showCartItems',1,isset($showCartItems)?$showCartItems:1);?>
        <?= t('Show number of items in cart')?>
    </label>
</div>

<div class="checkbox">
    <label>
        <?= $form->checkbox('showCartTotal',1,isset($showCartTotal)?$showCartTotal:1);?>
        <?= t('Show cart subTotal')?>
    </label>
</div>

<div class="checkbox">
    <label>
        <?= $form->checkbox('showSignIn',1,isset($showSignIn)?$showSignIn:1);?>
        <?= t('Show sign-in link')?>
    </label>
</div>

<div class="checkbox">
    <label>
        <?= $form->checkbox('showGreeting',1,isset($showSignIn)?$showSignIn:1);?>
        <?= t('Show greeting')?>
    </label>
</div>

<div class="checkbox">
    <label>
        <?= $form->checkbox('showCheckout',1,isset($showCheckout)?$showCheckout:1);?>
        <?= t('Show checkout link')?>
    </label>
</div>

<div class="checkbox">
    <label>
        <?= $form->checkbox('popUpCart',1,isset($popUpCart)?$popUpCart:1);?>
        <?= t('Display cart in popup')?>
    </label>
</div>

<div class="form-group">
    <?= $form->label('cartLabel',t('Cart Link Label'));?>
    <?= $form->text('cartLabel',$cartLabel?$cartLabel:t("View Cart"));?>
</div>
<div class="form-group">
    <?= $form->label('itemsLabel',t('Cart Items Label'));?>
    <?= $form->text('itemsLabel',$itemsLabel?$itemsLabel:t("Items in Cart"));?>
</div>