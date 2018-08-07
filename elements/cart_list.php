<?php
defined('C5_EXECUTE') or die("Access Denied.");
use \Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product as StoreProduct;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Price as StorePrice;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductOption\ProductOption as StoreProductOption;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductOption\ProductOptionItem as StoreProductOptionItem;

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
                ?>

                <tr class="store-cart-item <?= $classes ?>" data-instance-id="<?= $k ?>"
                    data-product-id="<?= $pID ?>">
                    <?php $thumb = $product->getImageThumb(); ?>
                    <?php if ($thumb) { ?>
                    <td class="store-cart-list-thumb">
                        <a href="<?= URL::to(Page::getByID($product->getPageID())) ?>">
                            <?= $thumb ?>
                        </a>
                    </td>
                    <td class="store-cart-product-name">
                        <?php } else { ?>
                    <td colspan="2" class="store-cart-product-name">
                        <?php } ?>
                        <a href="<?= URL::to(Page::getByID($product->getPageID())) ?>">
                            <?= $product->getName() ?>
                        </a>

                        <?php if ($cartItem['productAttributes']) { ?>
                            <div class="store-cart-item-attributes">
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
                            <?=StorePrice::format($product->getActivePrice($qty))?>
                        <?php } ?>
                    </td>

                    <td class="store-cart-product-qty text-center">
                        <?php if ($product->allowQuantity()) { ?>
                            <?= $qty ?>
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
