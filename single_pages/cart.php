<?php
defined('C5_EXECUTE') or die("Access Denied.");

use \Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Price;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductOption\ProductOption;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductOption\ProductOptionItem;
use \Concrete\Core\Support\Facade\Url;

$app = \Concrete\Core\Support\Facade\Application::getFacadeApplication();
$csm = $app->make('cs/helper/multilingual');

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

<input id='cartURL' type='hidden' data-cart-url='<?= Url::to($langpath . '/cart/') ?>'>

<?php
if ($cart) {
    $i = 1;
    ?>
    <form method="post" class="form-inline" action="<?=  Url::to($langpath . '/cart/'); ?>" >
        <?= $token->output('community_store'); ?>
        <table id="store-cart" class="store-cart-table table table-hover table-condensed">
            <thead>
            <tr>
                <th colspan="2"><?= t('Product'); ?></th>
                <th class="text-right"><?= t('Price'); ?></th>
                <th class="text-right"><?= t('Quantity'); ?></th>
                <th class="text-right"><?= t('Remove'); ?></th>
            </tr>
            </thead>
            <tbody>
            <?php
            foreach ($cart as $k => $cartItem) {

                $qty = $cartItem['product']['qty'];
                $product = $cartItem['product']['object'];
                if (is_object($product)) {
                    $productPage = $product->getProductPage();
                    ?>

                    <tr class="store-cart-item">
                        <?php $thumb = $product->getImageThumb(); ?>
                        <?php if ($thumb) { ?>
                        <td class="store-cart-list-thumb">
                            <?php if ($productPage) { ?>
                                <a href="<?= URL::to($productPage) ?>">
                                    <?= $thumb ?>
                                </a>
                            <?php } else { ?>
                                <?= $thumb ?>
                            <?php } ?>
                        </td>
                        <td class="store-cart-product-name">
                        <?php } else { ?>
                        <td class="store-cart-product-name" colspan="2">
                        <?php } ?>
                        <?php if ($productPage) { ?>
                            <a href="<?= URL::to($productPage) ?>">
                                <?= $csm->t($product->getName(), 'productName', $cartItem['product']['pID']); ?>
                            </a>
                        <?php } else { ?>
                            <?= $csm->t($product->getName(), 'productName', $cartItem['product']['pID']); ?>
                        <?php } ?>
                        <?php if ($cartItem['productAttributes']) { ?>
                            <div class="store-cart-list-item-attributes">
                                <?php foreach ($cartItem['productAttributes'] as $groupID => $valID) {

                                    if (substr($groupID, 0, 2) == 'po') {
                                        $groupID = str_replace("po", "", $groupID);
                                        $optionvalue = ProductOptionItem::getByID($valID);

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

                                    $optiongroup = ProductOption::getByID($groupID);

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
                        <td class="store-cart-item-price text-right">
                            <?php if (isset($cartItem['product']['customerPrice'])) { ?>
                                <?=Price::format($cartItem['product']['customerPrice'])?>
                            <?php } else {  ?>
                                <?php
                                $salePrice = $product->getSalePrice();
                                if (isset($salePrice) && $salePrice != "") {
                                    echo '<span class="sale-price">' . Price::format($salePrice) . '</span>';
                                } else {
                                    echo Price::format($product->getActivePrice());
                                }
                                ?>
                            <?php } ?>
                        </td>
                        <td class="store-cart-product-qty text-right">
                            <?php $quantityLabel = $csm->t($product->getQtyLabel(), 'productQuantityLabel', $cartItem['product']['pID'] ); ?>

                            <span class="store-qty-container pull-right
                            <?php if ($quantityLabel) { ?>input-group
                                <?php } ?>
                                ">
                            <?php if ($product->allowQuantity()) { ?>
                                <?php if ($product->allowDecimalQuantity()) {
                                    $max = $product->getMaxCartQty();
                                    ?>
                                    <input type="number" name="pQty[]" class="store-product-qty form-control text-right" value="<?= $qty ?>" min="0" step="<?= $product->getQtySteps();?>" <?= ($max ? '' : 'max="' .$max . '"'); ?> >
                                <?php } else { ?>
                                    <input type="number" name="pQty[]" class="store-product-qty form-control text-right" value="<?= $qty ?>" min="1" step="1" <?= ($max ? '' : 'max="' .$max . '"'); ?>>
                                <?php } ?>

                                 <input type="hidden" name="instance[]" value="<?= $k ?>"/>

                            <?php } else { ?>
                                1
                            <?php } ?>

                            <?php if ($quantityLabel) { ?>
                                <div class="store-cart-qty-label input-group-addon"><?= $quantityLabel; ?></div>
                            <?php } ?>
                            </span>

                        </td>
                        <td class="text-right">
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
                <td colspan="5" class="text-right">
                    <button name="action" value="clear" class="store-btn-cart-list-clear btn btn-warning"
                            type="submit"><?= t("Clear Cart") ?></button>
                    <button name="action" value="update" class="store-btn-cart-list-update btn btn-primary"
                            type="submit"><?= t("Update") ?></button>
                </td>
            </tr>
            </tfoot>
        </table>
    </form>

    <!--    Hidden form for deleting-->
    <form method="post" id="deleteform" action="<?=  Url::to($langpath  . '/cart/'); ?>">
        <?= $token->output('community_store'); ?>
        <input type="hidden" name="instance" value=""/>
        <input type="hidden" name="action" value="remove"/>
    </form>

<?php } ?>


<?php if ($cart && !empty($cart)) { ?>
    <?php if ($discountsWithCodesExist && $cart) { ?>
        <h3><?= t('Enter Discount Code'); ?></h3>
        <form method="post" action="<?= Url::to($langpath .'/cart/'); ?>" class="form-inline">
            <?= $token->output('community_store'); ?>
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

    <?php if (!empty($discounts)) { ?>

        <p class="store-cart-page-discounts text-right">
            <strong><?= (count($discounts) == 1 ? t('Discount Applied') : t('Discounts Applied')); ?>:</strong>
            <?php
            $discountstrings = array();
            foreach ($discounts as $discount) {
                $discountstrings[] = h( $csm->t($discount->getDisplay(), 'discountRuleDisplayName', null, $discount->getID()));
            }
            echo implode(', ', $discountstrings);
            ?>
        </p>

    <?php } ?>


    <p class="store-cart-page-cart-total text-right">
        <strong class="cart-grand-total-label"><?= t("Items Sub Total") ?>:</strong>
        <span class="cart-grand-total-value"><?= Price::format($subTotal) ?></span>
    </p>

    <?php if ($shippingEnabled) { ?>
        <p class="store-cart-page-shipping text-right"><strong><?= t("Shipping") ?>:</strong>
        <span id="store-shipping-total">
         <?= $shippingtotal !== false ? ($shippingtotal > 0 ? Price::format($shippingtotal) : t('No Charge')) : t('to be determined'); ?>
        </span></p>
    <?php } ?>

    <?php
    if ($taxtotal > 0) {
        foreach ($taxes as $tax) {
            if ($tax['taxamount'] > 0) { ?>
                <p class="store-cart-page-tax text-right">
                    <strong><?= ($tax['name'] ? $tax['name'] : t("Tax")) ?>:</strong> <span class="tax-amount"><?= Price::format($tax['taxamount']); ?></span>
                </p>
            <?php }
        }
    }
    ?>

    <p class="store-cart-page-cart-total text-right">
        <strong class="store-cart-grand-total-label"><?= t("Total") ?>:</strong>
        <span class="store-cart-grand-total-value"><?= Price::format($total) ?></span>
    </p>

    <div class="store-cart-page-cart-links pull-right">
        <a class="store-btn-cart-page-checkout btn btn-success"
           href="<?= Url::to($langpath . '/checkout') ?>"><?= t('Checkout') ?></a>
    </div>
<?php } else { ?>
    <p class="alert alert-info"><?= t('Your cart is empty'); ?></p>
<?php } ?>

</div>
