<?php
defined('C5_EXECUTE') or die("Access Denied.");

use \Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Price as StorePrice;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductOption\ProductOption as StoreProductOption;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductOption\ProductOptionItem as StoreProductOptionItem;

$csm = \Core::make('cs/helper/multilingual');
?>

<?php
if ($cart) {
    $i = 1; ?>
    <table id="cart" class="store-cart-table table table-hover table-condensed">
        <thead>
        <tr>
            <th colspan="2"><?= t('Product');?></th>
            <th><?= t('Price');?></th>
            <th class="text-center"><?= t('Quantity');?></th>
        </tr>
        </thead>
        <tbody>
        <?php
        foreach ($cart as $k => $cartItem) {

            $qty = $cartItem['product']['qty'];
            $product = $cartItem['product']['object'];

            if ($i % 2 == 0) {
                $classes = " striped";
            } else {
                $classes = "";
            }
            if (is_object($product)) {
                $productPage = $product->getProductPage();
                ?>

                <tr class="store-cart-item <?= $classes ?>" data-instance-id="<?= $k ?>"
                    data-product-id="<?= $pID ?>">
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
                    <td colspan="2" class="store-cart-product-name">
                        <?php } ?>
                        <?php if ($productPage) { ?>
                        <a href="<?= URL::to($productPage) ?>">
                            <?= $csm->t($product->getName(), 'productName', $product->getID()); ?>
                        </a>
                        <?php } else { ?>
                            <?= $csm->t($product->getName(), 'productName', $product->getID()); ?>
                        <?php } ?>

                        <?php if ($cartItem['productAttributes']) { ?>
                            <div class="store-cart-item-attributes">
                                <?php foreach ($cartItem['productAttributes'] as $optionID => $valID) {

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
                                    <?php if ($optionvalue) { ?>
                                    <div class="store-cart-list-item-attribute">
                                        <span class="store-cart-list-item-attribute-label"><?= ($optiongroup ? h($csm->t($optiongroup->getName(), 'optionName', $product->getID(), $optionID)) : '') ?>:</span>
                                        <span class="store-cart-list-item-attribute-value"><?= ($optionvalue ? h($csm->t($optionvalue, 'optionValue', $product->getID(), $valID)) : '') ?></span>
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
                            <?=StorePrice::format($product->getActivePrice($qty))?>
                        <?php } ?>
                    </td>

                    <td class="store-cart-product-qty text-center">
                        <?php if ($product->allowQuantity()) { ?>
                            <?= $qty ?>
                        <?php } ?>
                        <?php $quantityLabel = $product->getQtyLabel(); ?>
                        <?php  if ($quantityLabel) { ?>
                            <span class="store-cart-qty-label small"><?= $quantityLabel; ?></span>
                        <?php } ?>
                    </td>

                </tr>

                <?php
            }//if is_object
            $i++;
        }//foreach
        ?>
        </tbody>
    </table>
<?php }//if cart
?>
