<?php
defined('C5_EXECUTE') or die(_("Access Denied."));
use \Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Price as StorePrice;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductOption\ProductOption as StoreProductOption;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductOption\ProductOptionItem as StoreProductOptionItem;

?>
<div class="store-cart-page">
<h1><?= t("Shopping Cart") ?></h1>

<?php if (isset($actiondata) and !empty($actiondata)) { ?>
    <?php if ($actiondata['action'] == 'update') { ?>
        <p class="alert alert-success"><?= t('Your cart has been updated'); ?></p>
    <?php } ?>

    <?php if ($actiondata['action'] == 'changed') { ?>
        <p class="alert alert-success"><?= t('Your cart has been updated due to changes in stock levels'); ?></p>
    <?php } ?>

    <?php if ($actiondata['action'] == 'clear') { ?>
        <p class="alert alert-warning"><?= t('Your cart has been cleared'); ?></p>
    <?php } ?>

    <?php if ($actiondata['action'] == 'remove') { ?>
        <p class="alert alert-warning"><?= t('Item removed'); ?></p>
    <?php } ?>

    <?php if ($actiondata['quantity'] != $actiondata['added']) { ?>
        <p class="alert alert-warning"><?= t('Due to stock levels your quantity has been limited'); ?></p>
    <?php } ?>
<?php } ?>

<input id='cartURL' type='hidden' data-cart-url='<?= \URL::to("/cart/") ?>'>

<?php
if ($cart) {
    $i = 1;
    ?>
    <form method="post" class="form-inline" action="<?=  \URL::to("/cart/"); ?>" >
        <table id="store-cart" class="store-cart-table table table-hover table-condensed">
            <thead>
            <tr>
                <th colspan="2"><?= t('Product'); ?></th>
                <th><?= t('Price'); ?></th>
                <th class="text-right"><?= t('Quantity'); ?></th>
            </tr>
            </thead>
            <tbody>
            <?php
            foreach ($cart as $k => $cartItem) {

                $qty = $cartItem['product']['qty'];
                $product = $cartItem['product']['object'];
                if (is_object($product)) {
                    ?>

                    <tr class="store-cart-item">
                        <?php $thumb = $product->getImageThumb(); ?>
                        <?php if ($thumb) { ?>
                        <td class="store-cart-list-thumb">
                            <a href="<?= URL::to(Page::getByID($product->getPageID())) ?>">
                                <?=  $product->getImageThumb() ?>
                            </a>
                        </td>
                        <td class="store-cart-product-name">
                        <?php } else { ?>
                        <td class="store-cart-product-name" colspan="2">
                        <?php } ?>
                        <a href="<?= URL::to(Page::getByID($product->getPageID())) ?>">
                            <?= $product->getName() ?>
                        </a>

                        <?php if ($cartItem['productAttributes']) { ?>
                            <div class="store-cart-list-item-attributes">
                                <?php foreach ($cartItem['productAttributes'] as $groupID => $valID) {

                                    if (substr($groupID, 0, 2) == 'po') {
                                        $groupID = str_replace("po", "", $groupID);
                                        $optionvalue = StoreProductOptionItem::getByID($valID);

                                        if ($optionvalue) {
                                            $optionvalue = $optionvalue->getName();
                                        }
                                    } elseif (substr($groupID, 0, 2) == 'pt')  {
                                        $groupID = str_replace("pt", "", $groupID);
                                        $optionvalue = $valID;
                                    } elseif (substr($groupID, 0, 2) == 'pa')  {
                                        $groupID = str_replace("pa", "", $groupID);
                                        $optionvalue = $valID;
                                    } elseif (substr($groupID, 0, 2) == 'ph')  {
                                        $groupID = str_replace("ph", "", $groupID);
                                        $optionvalue = $valID;
                                    } elseif (substr($groupID, 0, 2) == 'pc')  {
                                        $groupID = str_replace("pc", "", $groupID);
                                        $optionvalue = $valID;
                                    }

                                    $optiongroup = StoreProductOption::getByID($groupID);

                                    ?>
                                    <?php if ($optionvalue) { ?>
                                    <div class="store-cart-list-item-attribute">
                                        <span class="store-cart-list-item-attribute-label"><?= ($optiongroup ? h($optiongroup->getName()) : '') ?>:</span>
                                        <span class="store-cart-list-item-attribute-value"><?= ($optionvalue ? h($optionvalue) : '') ?></span>
                                    </div>
                                    <?php } ?>
                                <?php } ?>
                            </div>
                        <?php } ?>
                        </td>
                        <td class="store-cart-item-price">
                            <?php if (isset($cartItem['product']['customerPrice'])) { ?>
                                <?=StorePrice::format($cartItem['product']['customerPrice'])?>
                            <?php } else {  ?>
                                <?php
                                $salePrice = $product->getSalePrice();
                                if (isset($salePrice) && $salePrice != "") {
                                    echo '<span class="sale-price">' . StorePrice::format($salePrice) . '</span>';
                                } else {
                                    echo StorePrice::format($product->getActivePrice());
                                }
                                ?>
                            <?php } ?>
                        </td>
                        <td class="store-cart-product-qty text-right">
                            <?php if ($product->allowQuantity()) { ?>

                                <input type="hidden" name="instance[]" value="<?= $k ?>"/>
                                <input type="number" class="form-control" name="pQty[]"
                                       min="1" <?= ($product->allowBackOrders() || $product->isUnlimited() ? '' : 'max="' . $product->getQty() . '"'); ?>
                                       value="<?= $qty ?>" style="width: 80px;">
                            <?php } else { ?>
                                1
                            <?php } ?>

                            <?php $quantityLabel = $product->getQtyLabel(); ?>
                            <?php if ($quantityLabel) { ?>
                                 <span class="store-cart-qty-label small"><?= $quantityLabel; ?></span>
                            <?php } ?>

                            <a name="action" data-instance="<?= $k ?>"
                               class="store-btn-cart-list-remove btn-xs btn btn-danger" type="submit"><i
                                    class="fa fa-remove"></i><?php //echo t("Remove")
                                ?></a>
                        </td>
                    </tr>
                <?php }
            } ?>

            </tbody>

            <tfoot>
            <tr>
                <td colspan="4" class="text-right">
                    <button name="action" value="clear" class="store-btn-cart-list-clear btn btn-default"
                            type="submit"><?= t("Clear Cart") ?></button>
                    <button name="action" value="update" class="store-btn-cart-list-update btn btn-default"
                            type="submit"><?= t("Update") ?></button>
                </td>
            </tr>
            </tfoot>
        </table>
    </form>

    <!--    Hidden form for deleting-->
    <form method="post" id="deleteform" action="<?=  \URL::to("/cart/"); ?>">
        <input type="hidden" name="instance" value=""/>
        <input type="hidden" name="action" value="remove" value=""/>
    </form>

<?php } ?>

<?php if ($discountsWithCodesExist && $cart) { ?>
    <h3><?= t('Enter Discount Code'); ?></h3>
    <form method="post" action="<?= \URL::to('/cart/'); ?>" class="form-inline">
        <div class="form-group">
            <input type="text" class="store-cart-page-discount-field form-control" name="code" placeholder="<?= t('Code'); ?>" />
        </div>
        <input type="hidden" name="action" value="code"/>
        <button type="submit" class="store-cart-page-discount-apply btn btn-default"><?= t('Apply'); ?></button>
    </form>
<?php } ?>

<?php if ($codesuccess) { ?>
    <p><?= t('Discount has been applied'); ?></p>
<?php } ?>

<?php if ($codeerror) { ?>
    <p><?= t('Invalid code'); ?></p>
<?php } ?>


<?php if ($cart && !empty($cart)) { ?>
    <p class="store-cart-page-cart-total text-right">
        <strong class="cart-grand-total-label"><?= t("Items Sub Total") ?>:</strong>
        <span class="cart-grand-total-value"><?= StorePrice::format($subTotal) ?></span>
    </p>

    <?php if ($shippingEnabled) { ?>
        <p class="store-cart-page-shipping text-right"><strong><?= t("Shipping") ?>:</strong>
        <span id="store-shipping-total">
         <?= $shippingtotal !== false ? ($shippingtotal > 0 ? StorePrice::format($shippingtotal) : t('No Charge')) : t('to be determined'); ?>
        </span></p>
    <?php } ?>

    <?php if (!empty($discounts)) { ?>

        <p class="store-cart-page-discounts text-right">
            <strong><?= (count($discounts) == 1 ? t('Discount Applied') : t('Discounts Applied')); ?>
                :</strong>
            <?php
            $discountstrings = array();
            foreach ($discounts as $discount) {
                $discountstrings[] = h($discount->getDisplay());
            }
            echo implode(', ', $discountstrings);
            ?>
        </p>

    <?php } ?>

    <p class="store-cart-page-cart-total text-right">
        <strong class="store-cart-grand-total-label"><?= t("Total") ?>:</strong>
        <span class="store-cart-grand-total-value"><?= StorePrice::format($total) ?></span>
    </p>

    <div class="store-cart-page-cart-links pull-right">
        <a class="store-btn-cart-page-checkout btn btn-primary"
           href="<?= \URL::to('/checkout') ?>"><?= t('Checkout') ?></a>
    </div>
<?php } else { ?>
    <p class="alert alert-info"><?= t('Your cart is empty'); ?></p>
<?php } ?>

</div>
