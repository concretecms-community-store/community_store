<?php
defined('C5_EXECUTE') or die(_("Access Denied."));
use \Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product as StoreProduct;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Price as StorePrice;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductOption\ProductOptionGroup as StoreProductOptionGroup;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductOption\ProductOptionItem as StoreProductOptionItem;
?>

    <?php
    if($cart){
        $i=1; ?>
        <table id="cart" class="table table-hover table-condensed" >
        <?php
        foreach ($cart as $k=>$cartItem){

            $qty = $cartItem['product']['qty'];
            $product =$cartItem['product']['object'];


            if($i%2==0){$classes=" striped"; }else{ $classes=""; }
            if(is_object($product)){
                ?>

                <tr class="checkout-cart-item <?= $classes?>" data-instance-id="<?= $k?>" data-product-id="<?= $pID?>">
                    <td class="cart-list-thumb">
                        <a href="<?=URL::page(Page::getByID($product->getProductPageID()))?>">
                        <?= $product->getProductImageThumb()?>
                        </a>
                    </td>
                    <td class="checkout-cart-product-name">
                        <a href="<?=URL::page(Page::getByID($product->getProductPageID()))?>">
                        <?= $product->getProductName()?>
                        </a>

                        <?php if($cartItem['productAttributes']){?>
                            <div class="checkout-cart-item-attributes">
                                <?php foreach($cartItem['productAttributes'] as $groupID => $valID){
                                    $groupID = str_replace("pog","",$groupID);
                                    $optiongroup = StoreProductOptionGroup::getByID($groupID);
                                    $optionvalue = StoreProductOptionItem::getByID($valID);

                                    ?>
                                    <div class="cart-list-item-attribute">
                                        <span class="cart-list-item-attribute-label"><?= ($optiongroup ? $optiongroup->getName() : '')?>:</span>
                                        <span class="cart-list-item-attribute-value"><?= ($optionvalue ? $optionvalue->getName(): '')?></span>
                                    </div>
                                <?php }  ?>
                            </div>
                        <?php } ?>
                    </td>

                    <td class="checkout-cart-item-price">
                        <?=StorePrice::format($product->getActivePrice())?>
                    </td>

                    <?php if ($product->allowQuantity()) { ?>
                    <td class="checkout-cart-product-qty">
                        <span class="checkout-cart-item-label"><?= t("Qty:")?></span>
                        <?= $qty?>
                    </td>
                    <?php } ?>




                </tr>

                <?php
            }//if is_object
            $i++;
        }//foreach ?>
        </table>
   <?php }//if cart
    ?>
