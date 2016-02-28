<?php
defined('C5_EXECUTE') or die(_("Access Denied."));
use \Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Price as StorePrice;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductOption\ProductOption as StoreProductOption;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductOption\ProductOptionItem as StoreProductOptionItem;
?>
<p class="store-cart">

    <h1><?= t("Shopping Cart")?></h1>

    <?php if (isset($actiondata) and !empty($actiondata)) { ?>
        <?php if( $actiondata['action'] =='update') { ?>
            <p class="alert alert-success"><?= t('Your cart has been updated');?></p>
        <?php } ?>

        <?php if($actiondata['action'] == 'clear') { ?>
            <p class="alert alert-warning"><?= t('Your cart has been cleared');?></p>
        <?php } ?>

        <?php if($actiondata['action'] == 'remove') { ?>
            <p class="alert alert-warning"><?= t('Item removed');?></p>
        <?php } ?>

        <?php if($actiondata['quantity'] != $actiondata['added']) { ?>
            <p class="alert alert-warning"><?= t('Due to stock levels your quantity has been limited');?></p>
        <?php } ?>
    <?php } ?>

    <input id='cartURL' type='hidden' data-cart-url='<?=View::url("/cart/")?>'>


<?php
if($cart){
$i=1;


?>
<form method="post" class="form-inline">
<table id="store-cart" class="table table-hover table-condensed" >
    <thead>
    <tr>
        <th><?= t('Product'); ?></th>
        <th><?= t('Price'); ?></th>
        <th><?= t('Quantity'); ?></th>

    </tr>
    </thead>
    <tbody>
    <?php
    foreach ($cart as $k=>$cartItem) {

        $qty = $cartItem['product']['qty'];
        $product = $cartItem['product']['object'];
    if(is_object($product)){
    ?>

    <tr>
        <td data-th="Product">
            <div class="row">
                <div class="col-sm-2 hidden-xs">
                    <a href="<?=URL::page(Page::getByID($product->getPageID()))?>">
                        <?= $product->getImageThumb()?>
                    </a>
                </div>
                <div class="col-sm-10">
                    <h4 class="nomargin">
                        <a href="<?=URL::page(Page::getByID($product->getPageID()))?>">
                            <?= $product->getName()?>
                        </a>
                    </h4>

                    <?php if($cartItem['productAttributes']){?>
                        <div class="store-cart-list-item-attributes">
                            <?php foreach($cartItem['productAttributes'] as $groupID => $valID){
                                $groupID = str_replace("po","",$groupID);
                                $optiongroup = StoreProductOption::getByID($groupID);
                                $optionvalue = StoreProductOptionItem::getByID($valID);

                                ?>
                                <div class="store-cart-list-item-attribute">
                                    <span class="store-cart-list-item-attribute-label"><?= ($optiongroup ? $optiongroup->getName() : '')?>:</span>
                                    <span class="store-cart-list-item-attribute-value"><?= ($optionvalue ? $optionvalue->getName(): '')?></span>
                                </div>
                            <?php }  ?>
                        </div>
                    <?php } ?>

                </div>
            </div>
        </td>
        <td data-th="Price">
            <?php
            $salePrice = $product->getSalePrice();
            if(isset($salePrice) && $salePrice != ""){
                echo '<span class="original-price">'.StorePrice::format($product->getPrice()).'</span>';
                echo '<span class="sale-price">'.StorePrice::format($salePrice).'</span>';
            } else {
                echo StorePrice::format($product->getPrice());
            }
            ?>
        </td>
        <td data-th="Quantity">
            <?php if ($product->allowQuantity()) { ?>

                    <input type="hidden" name="instance[]" value="<?= $k?>" />
                    <span class="cart-item-label"><?= t("Quantity:")?></span>
                    <input type="number" class="form-control" name="pQty[]" min="1" <?=($product->allowBackOrders() || $product->isUnlimited()  ? '' :'max="' . $product->getQty() . '"' );?> value="<?= $qty?>" style="width: 50px;">
            <?php } ?>

            <a name="action"  value="remove" data-instance="<?= $k?>" class="store-btn-cart-list-remove btn-xs btn btn-danger" type="submit"><i class="fa fa-remove"></i><?php //echo t("Remove")?></a>
        </td>


    </tr>
    <?php } }?>

    </tbody>

    <tfoot>
    <tr>
        <td></td>
        <td></td>
        <td> <button name="action" value="update"  class="store-btn-cart-list-update btn btn-default" type="submit"><?= t("Update")?></button>
         <button name="action" value="clear" class="store-btn-cart-list-clear btn btn-default" type="submit"><?= t("Clear Cart")?></button></td>
    </tr>
    </tfoot>
</table>
</form>

    <!--    Hidden form for deleting-->
    <form method="post" id="deleteform">
        <input type="hidden" name="instance" value="" />
        <input type="hidden" name="action" value="remove" value="" />
    </form>

<?php }  ?>

<style>
    .table>tbody>tr>td, .table>tfoot>tr>td{
        vertical-align: middle;
    }
    @media screen and (max-width: 600px) {
        table#cart tbody td .form-control{
            width:20%;
            display: inline !important;
        }


        table#cart thead { display: none; }
        table#cart tbody td { display: block;}
        table#cart tbody td:before {
            content: attr(data-th); font-weight: bold;
            display: inline-block; width: 8rem;
        }

        table#cart tfoot td{display:block; }

    }

</style>



    <?php if ($discountsWithCodesExist && $cart) { ?>
    <h3><?= t('Enter Discount Code');?></h3>
        <form method="post" action="<?= View::url('/cart/');?>" class="form-inline">
            <input type="text" class="form-control" name="code" />
            <input type="hidden" name="action" value="code" />
            <button type="submit" class="btn btn-default btn-cart-discount-apply"><?= t('Apply');?></button>
        </form>
    <?php } ?>

    <?php if ($codesuccess) { ?>
        <p><?= t('Discount has been applied');?></p>
    <?php } ?>

    <?php if ($codeerror) { ?>
        <p><?= t('Invalid code');?></p>
    <?php } ?>


    <?php if ($cart  && !empty($cart)) { ?>
    <p class="store-cart-page-cart-total text-right">
        <strong class="cart-grand-total-label"><?= t("Items Sub Total")?>:</strong>
        <span class="cart-grand-total-value"><?=StorePrice::format($subTotal)?></span>
    </p>

    <?php if ($shippingEnabled) { ?>
        <p class="text-right"><strong><?= t("Shipping") ?>:</strong> <span
                id="store-shipping-total"><?= StorePrice::format($shippingtotal); ?></span></p>
    <?php } ?>

        <?php if(!empty($discounts)) { ?>

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

        <?php }?>

    <p class="store-cart-page-cart-total text-right">
        <strong class="store-cart-grand-total-label"><?= t("Total")?>:</strong>
        <span class="store-cart-grand-total-value"><?=StorePrice::format($total)?></span>
    </p>



    <div class="store-cart-page-cart-links pull-right">

        <a class="store-btn-cart-page-checkout btn btn-primary" href="<?=View::url('/checkout')?>"><?= t('Checkout')?></a>
    </div>
    <?php } else { ?>
    <p class="alert alert-info"><?= t('Your cart is empty');?></p>
    <?php } ?>
    
</div>
