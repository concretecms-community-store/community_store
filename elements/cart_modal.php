<?php
defined('C5_EXECUTE') or die("Access Denied.");
use \Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Price as StorePrice;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductOption\ProductOption as StoreProductOption;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductOption\ProductOptionItem as StoreProductOptionItem;
use \Concrete\Core\Support\Facade\Url;

$app = \Concrete\Core\Support\Facade\Application::getFacadeApplication();
$csm = $app->make('cs/helper/multilingual');
?>
<div class="store-cart-modal clearfix" id="cart-modal">
    <a href="#" class="store-modal-exit">x</a>
    <h3><?= t("Shopping Cart")?></h3>
    <div class="store-cart-page-cart">
        <?php
        if (isset($actiondata) and !empty($actiondata)) { ?>
            <?php if($actiondata['action'] == 'add' && $actiondata['added'] > 0 && !$actiondata['error']) { ?>
                <p class="alert alert-success"><strong><?=  h($csm->t( $actiondata['product']['pName'], 'productName',  $actiondata['product']['pID'])) ; ?></strong> <?= t('has been added to your cart');?></p>
            <?php } ?>

            <?php if( $actiondata['action'] =='update') { ?>
                <p class="alert alert-success"><?= t('Your cart has been updated');?></p>
            <?php } ?>

            <?php if($actiondata['action'] == 'clear') { ?>
                <p class="alert alert-warning"><?= t('Your cart has been cleared');?></p>
            <?php } ?>

            <?php if($actiondata['action'] == 'remove') { ?>
                <p class="alert alert-warning"><?= t('Item removed');?></p>
            <?php } ?>

            <?php if($actiondata['quantity'] != $actiondata['added'] && !$actiondata['error']) { ?>
                <p class="alert alert-warning"><?= t('Due to stock levels your quantity has been limited');?></p>
            <?php } ?>

            <?php if($actiondata['error']) { ?>
                <p class="alert alert-warning"><?= t('An issue has occured adding the product to the cart. You may be missing required information.');?></p>
            <?php } ?>
        <?php } ?>

        <input id='cartURL' type='hidden' data-cart-url='<?=Url::to("/cart/")?>'>
            <?php
            if($cart){ ?>
            <form method="post" action="<?= Url::to('/cart/');?>" id="store-modal-cart">
                <?= $token->output('community_store'); ?>
                <table id="cart" class="table table-hover table-condensed" >
                <thead>
                <tr>
                    <th colspan="2" ><?= t('Product'); ?></th>
                    <th><?= t('Price'); ?></th>
                    <th><?= t('Quantity'); ?></th>
                    <th></th>

                </tr>
                </thead>
                <tbody>

                <?php
                $i=1;
                $allowUpdate = false;
                foreach ($cart as $k=>$cartItem){


                    $qty = $cartItem['product']['qty'];
                    $product = $cartItem['product']['object'];

                    if ($product->allowQuantity()) {
                        $allowUpdate = true;
                    }

                    if($i%2==0){$classes=" striped"; }else{ $classes=""; }
                    if(is_object($product)){
                        $productPage = $product->getProductPage();
                        ?>

                        <tr class="store-cart-page-cart-list-item <?= $classes?>" data-instance-id="<?= $k?>" data-product-id="<?= $product->getID()?>">
                            <?php $thumb = $product->getImageThumb(); ?>
                            <?php if ($thumb) { ?>
                            <td class="cart-list-thumb col-xs-2">
                                <?php if ($productPage) { ?>
                                    <a href="<?= URL::to($productPage) ?>">
                                        <?= $thumb ?>
                                    </a>
                                <?php } else { ?>
                                    <?= $thumb ?>
                                <?php } ?>
                            </td>
                            <td class="checkout-cart-product-name col-xs-4">
                                <?php } else { ?>
                            <td colspan="2" class="checkout-cart-product-name">
                                <?php } ?>
                                <?php if ($productPage) { ?>
                                    <a href="<?= URL::to($productPage) ?>">
                                        <?= $csm->t($product->getName(), 'productName', $product->getID()); ?>
                                    </a>
                                <?php } else { ?>
                                    <?= $csm->t($product->getName(), 'productName', $product->getID()); ?>
                                <?php } ?>

                                <?php if($cartItem['productAttributes']){?>
                                    <div class="store-cart-list-item-attributes">
                                        <?php foreach($cartItem['productAttributes'] as $optionID => $valID){

                                            if (substr($optionID, 0, 2) == 'po') {
                                                $optionID = str_replace("po", "", $optionID);
                                                $optionvalue = StoreProductOptionItem::getByID($valID);

                                                if ($optionvalue) {
                                                    $optionvalue = $optionvalue->getName();
                                                }
                                            } elseif (substr($optionID, 0, 2) == 'pt')  {
                                                $optionID = str_replace("pt", "", $optionID);
                                                $optionvalue = $valID;
                                            } elseif (substr($optionID, 0, 2) == 'pa')  {
                                                $optionID = str_replace("pa", "", $optionID);
                                                $optionvalue = $valID;
                                            } elseif (substr($optionID, 0, 2) == 'ph')  {
                                                $optionID = str_replace("ph", "", $optionID);
                                                $optionvalue = $valID;
                                            } elseif (substr($optionID, 0, 2) == 'pc')  {
                                                $optionID = str_replace("pc", "", $optionID);
                                                $optionvalue = $valID;
                                            }

                                            $optiongroup = StoreProductOption::getByID($optionID);

                                            ?>
                                            <?php if ($optiongroup) { ?>
                                            <div class="store-cart-list-item-attribute">
                                                <span class="store-cart-list-item-attribute-label"><?= ($optiongroup ? h($csm->t($optiongroup->getName(), 'optionName', $product->getID(), $optionID)) : '') ?>:</span>
                                                <span class="store-cart-list-item-attribute-value"><?= ($optionvalue ? h($csm->t($optionvalue, 'optionValue', $product->getID(), $valID)) : '') ?></span>
                                            </div>
                                            <?php } ?>
                                        <?php }  ?>
                                    </div>
                                <?php } ?>
                            </td>

                            <td class="store-cart-list-item-price col-xs-2">
                                <?php if (isset($cartItem['product']['customerPrice'])) { ?>
                                    <?=StorePrice::format($cartItem['product']['customerPrice'])?>
                                <?php } else {  ?>
                                    <?=StorePrice::format($product->getActivePrice($qty))?>
                                <?php } ?>
                            </td>

                            <td class="store-cart-list-product-qty col-xs-3">
                                <?php $quantityLabel = $csm->t($product->getQtyLabel(), 'productQuantityLabel', $product->getID()); ?>
                                <span class="store-qty-container
                            <?php if ($quantityLabel) { ?>input-group
                                <?php } ?>
                                ">
                                <?php if ($product->allowQuantity()) {
                                    $max = $product->getMaxCartQty();
                                    ?>
                                    <?php if ($product->allowDecimalQuantity()) { ?>
                                        <input type="number" name="pQty[]" class="store-product-qty form-control" value="<?= $qty ?>" min="0" step="<?= $product->getQtySteps();?>" <?= ($max ? 'max="' . $max . '"' : '');?>>
                                    <?php } else { ?>
                                        <input type="number" name="pQty[]" class="store-product-qty form-control" value="<?= $qty ?>" min="1" step="1" <?= ($max ? 'max="' . $max . '"' : '');?>>
                                    <?php } ?>

                                    <input type="hidden" name="instance[]" value="<?= $k?>">
                                <?php }  else { ?>
                                1
                                    <?php } ?>
                                <?php if ($quantityLabel) { ?>
                                        <div class="store-cart-qty-label input-group-addon"><?= $quantityLabel; ?></div>
                                <?php } ?>
                                </span>
                            </td>
                            <td class="store-cart-list-remove-button col-xs-1 text-right">
                                <a class="store-btn-cart-list-remove btn btn-danger" data-instance-id="<?= $k?>" data-modal="true"  href="#"><i class="fa fa-remove"></i><?php ///echo t("Remove")?></a>
                            </td>

                        </tr>

                        <?php
                    }//if is_object
                    $i++;
                }//foreach ?>
                </tbody>

                <tfoot>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td colspan="2">
                        <?php if ($allowUpdate) { ?>
                            <p class="text-right"><button type="submit" class="store-btn-cart-modal-update btn btn-primary" data-modal="true" href="#"><?= t("Update")?></button></p>
                        <?php } ?>
                    </td>
                </tr>
                </tfoot>
            </table>
            </form>



            <?php }//if cart
            ?>



        <?php if ($cart  && !empty($cart)) { ?>

        <?php if(!empty($discounts)) { ?>

            <div class="store-cart-page-discounts">
                <p><strong><?= (count($discounts) == 1 ? t('Discount Applied') : t('Discounts Applied'));?></strong></p>
                <ul>
                    <?php foreach($discounts as $discount) { ?>
                        <li><?= h( $csm->t($discount->getDisplay(), 'discountRuleDisplayName', null, $discount->getID())); ?></li>
                    <?php } ?>
                </ul>
            </div>

        <?php }?>

        <p class="store-cart-page-cart-total text-right">
            <strong class="store-cart-grand-total-label"><?= t("Total")?>:</strong>
            <span class="store-cart-grand-total-value"><?=StorePrice::format($total)?></span>
        </p>
        <?php } else { ?>
        <p class="alert alert-info"><?= t('Your cart is empty'); ?></p>
        <?php } ?>


        <div class="store-cart-page-cart-links">
            <p class="pull-left">
                <a class="store-btn-cart-modal-continue btn btn-default" href="#"><?= t("Continue Shopping")?></a>
                <?php if ($cart  && !empty($cart)) { ?>
                <a class="store-btn-cart-modal-clear btn btn-warning" href="#"><?= t('Clear Cart')?></a>
            </p>
            <p class="pull-right"><a class="store-btn-cart-modal-checkout btn  btn-success " href="<?= Url::to($langpath .'/checkout')?>"><?= t('Checkout')?></a></p>
            <?php } ?>
        </div>

    </div>
</div>
