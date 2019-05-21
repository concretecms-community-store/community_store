<?php defined('C5_EXECUTE') or die("Access Denied.");
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductVariation\ProductVariation as StoreProductVariation;

$app = \Concrete\Core\Support\Facade\Application::getFacadeApplication();

$communityStoreImageHelper = $app->make('cs/helper/image', ['product_modal']);
$csm = $app->make('cs/helper/multilingual');
$token = $app->make('token');
?>
<form class="store-product-modal" id="store-form-add-to-cart-modal-<?= $product->getID(); ?>">
    <?= $token->output('community_store'); ?>
    <div class="store-product-modal-info-shell">

        <a href="#" class="store-modal-exit">x</a>
        <h4 class="store-product-modal-title"><?= h($csm->t($product->getName(), 'productName', $product->getID())); ?></h4>

        <p class="store-product-modal-thumb">
            <?php
            $imgObj = $product->getImageObj();
            if (!empty($imgObj)) {
                $thumb = $communityStoreImageHelper->getThumbnail($imgObj);
                ?>
                <img src="<?= $thumb->src; ?>">
            <?php } ?>
        </p>

        <p class="store-product-modal-price">
            <?php
            $salePrice = $product->getSalePrice();
            if (isset($salePrice) && "" != $salePrice) {
                $formattedSalePrice = $product->getFormattedSalePrice();
                $formattedOriginalPrice = $product->getFormattedOriginalPrice();
                echo '<span class="store-sale-price">' . $formattedSalePrice . '</span>';
                echo ' ' . t('was') . ' ' . '<span class="store-original-price">' . $formattedOriginalPrice . '</span>';
            } else {
                $formattedPrice = $product->getFormattedPrice();
                echo $formattedPrice;
            }
            ?>
        </p>

        <div class="store-product-modal-details">
            <?= $csm->t($product->getDesc(), 'productDetails', $product->getID()); ?>
        </div>
        <div class="store-product-modal-options">
            <?php if ('all' != Config::get('community_store.shoppingDisabled')) {
                ?>
            <div class="store-product-modal-quantity form-group">
                <?php if ($product->allowQuantity() && $showQuantity) {
                    ?>
                    <div class="store-product-quantity form-group">
                        <label class="store-product-option-group-label"><?= t('Quantity'); ?></label>

                        <?php $qtylabel = $csm->t($product->getQtyLabel(), 'productQuantityLabel', $product->getID()); ?>

                        <?php if ($qtylabel) {
                        ?>
                        <div class="input-group">
                            <?php
                            }
                            $max = $product->getMaxCartQty(); ?>

                            <?php if ($product->allowDecimalQuantity()) {
                                ?>
                                <input type="number" name="quantity" class="store-product-qty form-control" min="<?= $product->getQtySteps() ? $product->getQtySteps() : 0.001; ?>" step="<?= $product->getQtySteps() ? $product->getQtySteps() : 0.001; ?>" <?= ($max ? 'max="' . $max . '"' : ''); ?>>
                                <?php
                            } else {
                                ?>
                                <input type="number" name="quantity" class="store-product-qty form-control" value="1" min="1" step="1" <?= ($max ? 'max="' . $max . '"' : ''); ?>>
                                <?php
                            } ?>

                            <?php if ($qtylabel) {
                            ?>
                            <div class="input-group-addon"><?= $csm->t($product->getQtyLabel(), 'productQtyLabel', $product->getID()) ?></div>
                        </div>
                    <?php
                    } ?>

                    </div>
                    <?php
                } else {
                    ?>
                    <input type="hidden" name="quantity" class="store-product-qty" value="1">
                    <?php
                } ?>
            </div>
            <?php
            } ?>

            <?php

            foreach ($product->getOptions() as $option) {
                $optionItems = $option->getOptionItems();
                $optionType = $option->getType();
                $required = $option->getRequired();
                $displayType = $option->getDisplayType();

                $requiredAttr = '';

                if ($required) {
                    $requiredAttr = ' required="required" placeholder="' . t('Required') . '" ';
                } ?>

                <?php if (!$optionType || 'select' == $optionType) {
                    ?>
                    <div class="store-product-option-group form-group <?= h($option->getHandle()); ?>">
                        <label class="store-product-option-group-label"><?= h($csm->t($option->getName(), 'optionName', $product->getID(), $option->getID())); ?></label>
                        <?php if ('radio' != $displayType) {
                        ?>
                        <select class="store-product-option <?= $option->getIncludeVariations() ? 'store-product-variation' : ''; ?> form-control"
                                name="po<?= $option->getID(); ?>">
                            <?php
                            } ?>
                            <?php
                            $firstAvailableVariation = false;
                            $variation = false;
                            $disabled = false;
                            $outOfStock = false;
                            foreach ($optionItems as $optionItem) {
                                if (!$optionItem->isHidden()) {
                                    $variation = $variationLookup[$optionItem->getID()];
                                    if (!empty($variation)) {
                                        $firstAvailableVariation = (!$firstAvailableVariation && $variation->isSellable()) ? $variation : $firstAvailableVariation;
                                        $disabled = $variation->isSellable() ? '' : 'disabled="disabled" ';
                                        $outOfStock = $variation->isSellable() ? '' : ' (' . t('out of stock') . ')';
                                    }
                                    $selected = '';
                                    if (is_array($availableOptionsids) && in_array($optionItem->getID(), $availableOptionsids)) {
                                        $selected = 'selected="selected"';
                                    } ?>

                                    <?php if ($displayType == 'radio') { ?>
                                        <div class="radio">
                                            <label><input type="radio" required class="store-product-option <?= $option->getIncludeVariations() ? 'store-product-variation' : '' ?> "
                                                    <?= $disabled .  ($selected ? 'checked' : ''); ?> name="po<?= $option->getID();?>" value="<?= $optionItem->getID(); ?>" /><?= h($csm->t($optionItem->getName(), 'optionValue', $product->getID(), $optionItem->getID()));?></label>
                                        </div>
                                    <?php } else { ?>
                                        <option <?= $disabled . ' ' . $selected; ?>value="<?= $optionItem->getID(); ?>"><?= h($csm->t($optionItem->getName(), 'optionValue', $product->getID(), $optionItem->getID())) . $outOfStock; ?></option>
                                    <?php } ?>

                                    <?php
                                }
                            } ?>
                            <?php if ($displayType != 'radio') { ?>
                        </select>
                    <?php } ?>
                    </div>
                    <?php
                } elseif ('text' == $optionType) {
                    ?>
                    <div class="store-product-option-group form-group <?= $option->getHandle(); ?>">
                        <label class="store-product-option-group-label"><?= h($csm->t($option->getName(), 'optionName', $product->getID(), $option->getID())); ?></label>
                        <input class="store-product-option-entry form-control" <?= $requiredAttr; ?>
                               name="pt<?= $option->getID(); ?>"/>
                    </div>
                    <?php
                } elseif ('textarea' == $optionType) {
                    ?>
                    <div class="store-product-option-group form-group <?= $option->getHandle(); ?>">
                        <label class="store-product-option-group-label"><?= h($csm->t($option->getName(), 'optionName', $product->getID(), $option->getID())); ?></label>
                        <textarea class="store-product-option-entry form-control" <?= $requiredAttr; ?>
                                              name="pa<?= $option->getID(); ?>"></textarea>
                    </div>
                    <?php
                } elseif ('checkbox' == $optionType) {
                    ?>
                    <div class="store-product-option-group form-group <?= $option->getHandle(); ?>">
                        <label class="store-product-option-group-label">
                            <input type="hidden" value="<?= t('no'); ?>"
                                   class="store-product-option-checkbox-hidden <?= $option->getHandle(); ?>"
                                   name="pc<?= $option->getID(); ?>"/>
                            <input type="checkbox" value="<?= t('yes'); ?>"
                                   class="store-product-option-checkbox <?= $option->getIncludeVariations() ? 'store-product-variation' : ''; ?> <?= $option->getHandle(); ?>"
                                   name="pc<?= $option->getID(); ?>"/> <?= h($csm->t($option->getName(), 'optionName', $product->getID(), $option->getID())); ?></label>
                    </div>
                    <?php
                } elseif ('hidden' == $optionType) {
                    ?>
                    <input type="hidden" class="store-product-option-hidden <?= $option->getHandle(); ?>"
                           name="ph<?= $option->getID(); ?>"/>
                    <?php
                } ?>
                <?php
            } ?>

        </div>
        <input type="hidden" name="pID" value="<?= $product->getID(); ?>">
        <?php if ('all' != Config::get('community_store.shoppingDisabled')) {
                ?>
        <div class="store-product-modal-buttons">
            <p><button data-add-type="modal" data-product-id="<?= $product->getID(); ?>" class="store-btn-add-to-cart btn btn-primary <?= ($product->isSellable() ? '' : 'hidden'); ?> "><?=  ($btnText ? h($btnText) : t("Add to Cart")); ?></button></p>
            <p class="store-out-of-stock-label alert alert-warning <?= ($product->isSellable() ? 'hidden' : ''); ?>"><?= t("Out of Stock"); ?></p>
        </div>
        <?php
            } ?>
    </div>
</form>

<?php
if ($product->hasVariations()) {
                $variations = StoreProductVariation::getVariationsForProduct($product);

                $variationLookup = [];

                if (!empty($variations)) {
                    foreach ($variations as $variation) {
                        // returned pre-sorted
                        $ids = $variation->getOptionItemIDs();
                        $variationLookup[implode('_', $ids)] = $variation;
                    }
                }
            }
?>

<?php if ($product->hasVariations() && !empty($variationLookup)) {
    ?>
    <script>
        $(function() {
            <?php
            $varationData = [];
    foreach ($variationLookup as $key => $variation) {
        $product->setVariation($variation);

        $imgObj = $product->getImageObj();

        if ($imgObj) {
            $thumb = $communityStoreImageHelper->getThumbnail($imgObj);
        }

        $varationData[$key] = [
                'price' => $product->getFormattedOriginalPrice(),
                'saleprice' => $product->getFormattedSalePrice(),
                'available' => ($variation->isSellable()),
                'imageThumb' => $thumb ? $thumb->src : '',
                'image' => $imgObj ? $imgObj->getRelativePath() : '', ];
    } ?>


            $('#store-form-add-to-cart-modal-<?= $product->getID(); ?> select').change(function(){

                var variationdata = <?= json_encode($varationData); ?>;
                var ar = [];

                $('#store-form-add-to-cart-modal-<?= $product->getID(); ?> select.store-product-variation, #store-form-add-to-cart-modal-<?= $product->getID(); ?> .store-product-variation:checked').each(function () {
                    ar.push($(this).val());
                });

                ar.sort();

                var pli = $(this).closest('.store-product-modal');

                if (variationdata[ar.join('_')]['saleprice']) {
                    var pricing =  '<span class="store-sale-price">'+ variationdata[ar.join('_')]['saleprice']+'</span>' +
                        ' <?= t('was'); ?> ' + '<span class="store-original-price">' + variationdata[ar.join('_')]['price'] +'</span>';

                    pli.find('.store-product-modal-price').html(pricing);

                } else {
                    pli.find('.store-product-modal-price').html(variationdata[ar.join('_')]['price']);
                }

                if (variationdata[ar.join('_')]['available']) {
                    pli.find('.store-out-of-stock-label').addClass('hidden');
                    pli.find('.store-btn-add-to-cart').removeClass('hidden');
                } else {
                    pli.find('.store-out-of-stock-label').removeClass('hidden');
                    pli.find('.store-btn-add-to-cart').addClass('hidden');
                }

                if (variationdata[ar.join('_')]['imageThumb']) {
                    var image = pli.find('.store-product-modal-thumb img');

                    if (image) {
                        image.attr('src', variationdata[ar.join('_')]['imageThumb']);

                    }
                }

            });
        });
    </script>
<?php
} ?>
