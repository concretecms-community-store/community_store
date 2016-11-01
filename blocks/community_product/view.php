<?php
defined('C5_EXECUTE') or die(_("Access Denied."));

if (is_object($product) && $product->isActive()) {
    ?>

    <form class="store-product store-product-block" id="store-form-add-to-cart-<?= $product->getID() ?>" itemscope itemtype="http://schema.org/Product">
        <div class="row">
            <?php if ($showImage){ ?>
            <div class="store-product-details col-md-6">
                <?php } else { ?>
                <div class="store-product-details col-md-12">
                    <?php } ?>
                    <?php if ($showProductName) { ?>
                        <h1 class="store-product-name" itemprop="name"><?= $product->getName() ?></h1>
                        <meta itemprop="sku" content="<?= $product->getSKU() ?>" />
                    <?php } ?>

                    <?php if ($showProductPrice) { ?>
                        <p class="store-product-price" itemprop="offers" itemscope itemtype="http://schema.org/Offer">
                            <meta itemprop="priceCurrency" content="<?= Config::get('community_store.currency');?>" />
                        <?php
                        $salePrice = $product->getSalePrice();
                        if (isset($salePrice) && $salePrice != "") {
                            echo '<span class="sale-price">' . t("On Sale: ") . $product->getFormattedSalePrice() . '</span>';
                            echo '&nbsp;'.t('was').'&nbsp;';
                            echo '<span class="original-price">' . $product->getFormattedOriginalPrice() . '</span>';
                            echo '<meta itemprop="price" content="' . $product->getSalePrice() .'" />';
                        } else {
                            echo $product->getFormattedPrice();
                            echo '<meta itemprop="price" content="' . $product->getPrice() .'" />';
                        }
                        ?>
                        </p>
                    <?php } ?>

                    <meta itemprop="description" content="<?= strip_tags($product->getDesc()); ?>" />

                    <?php if ($showProductDescription) { ?>
                        <div class="store-product-description">
                            <?= $product->getDesc() ?>
                        </div>
                    <?php } ?>

                    <?php if ($showDimensions) { ?>
                        <div class="store-product-dimensions">
                            <strong><?= t("Dimensions") ?>:</strong>
                            <?= $product->getDimensions() ?>
                            <?= Config::get('community_store.sizeUnit'); ?>
                        </div>
                    <?php } ?>

                    <?php if ($showWeight) { ?>
                        <div class="store-product-weight">
                            <strong><?= t("Weight") ?>:</strong>
                            <?= $product->getWeight() ?>
                            <?= Config::get('community_store.weightUnit'); ?>
                        </div>
                    <?php } ?>

                    <?php if ($showGroups && false) { ?>
                        <ul>
                            <?php
                            $productgroups = $product->getGroups();
                            foreach ($productgroups as $pg) { ?>
                                <li class="store-product-group"><?= $pg->getGroup()->getGroupName(); ?> </li>
                            <?php } ?>
                        </ul>
                    <?php } ?>

                    <?php if ($showIsFeatured) {
                        if ($product->isFeatured()) {
                            ?>
                            <span class="store-product-featured"><?= t("Featured Item") ?></span>
                        <?php }
                    } ?>
                    <?php if(is_array($product->getAttributes())) :
                        foreach($product->getAttributes() as $aName => $value){ ?>
                          <div class="store-product-attributes">
                            <strong><?= t($aName) ?>:</strong>
                            <?= $value ?>
                          </div>
                    <?php   }
                          endif;
                    ?>
                    <div class="store-product-options" id="product-options-<?= $bID; ?>">
                        <?php if ($product->allowQuantity() && $showQuantity) { ?>
                            <div class="store-product-quantity form-group">
                                <label class="store-product-option-group-label"><?= t('Quantity') ?></label>
                                <input type="number" name="quantity" class="store-product-qty form-control" value="1" min="1" step="1">
                            </div>
                        <?php } else { ?>
                            <input type="hidden" name="quantity" class="store-product-qty" value="1">
                        <?php } ?>
                        <?php

                        foreach ($product->getOptions() as $option) {
                            $optionItems = $option->getOptionItems();

                            ?>
                            <?php if (!empty($optionItems)) { ?>
                                <div class="store-product-option-group form-group">
                                    <label class="store-product-option-group-label"><?= $option->getName() ?></label>
                                    <select class="store-product-option form-control" name="po<?= $option->getID() ?>">
                                        <?php
                                        foreach ($optionItems as $optionItem) {
                                            if (!$optionItem->isHidden()) { ?>
                                            <option value="<?= $optionItem->getID() ?>"><?= $optionItem->getName() ?></option>
                                            <?php }
                                            // below is an example of a radio button, comment out the <select> and <option> tags to use instead
                                            //echo '<input type="radio" name="po'.$option->getID().'" value="'. $optionItem->getID(). '" />' . $optionItem->getName() . '<br />'; ?>
                                        <?php } ?>
                                    </select>
                                </div>
                            <?php }
                        } ?>
                    </div>

                    <?php if ($showCartButton) { ?>
                        <p class="store-product-button">
                            <input type="hidden" name="pID" value="<?= $product->getID() ?>">

                            <span><button data-add-type="none" data-product-id="<?= $product->getID() ?>"
                                  class="store-btn-add-to-cart btn btn-primary <?= ($product->isSellable() ? '' : 'hidden'); ?> "><?= ($btnText ? h($btnText) : t("Add to Cart")) ?></button>
                            </span>
                            <span
                                class="store-out-of-stock-label <?= ($product->isSellable() ? 'hidden' : ''); ?>"><?= t("Out of Stock") ?></span>
                        </p>
                    <?php } ?>

                </div>

                <?php if ($showImage) { ?>
                    <div class="store-product-image col-md-6">
                        <p>
                        <?php
                        $imgObj = $product->getImageObj();
                        if (is_object($imgObj)) {
                            $thumb = Core::make('helper/image')->getThumbnail($imgObj, 600, 600, true);
                            ?>
                            <div class="store-product-primary-image">
                                <a itemprop="image" href="<?= $imgObj->getRelativePath() ?>"
                                   title="<?= h($product->getName()); ?>" class="store-product-thumb">
                                    <img src="<?= $thumb->src ?>">
                                </a>
                            </div>
                        <?php } ?>

                        <?php
                        $images = $product->getImagesObjects();
                        if (count($images) > 0) {
                            echo '<div class="store-product-additional-images">';
                            foreach ($images as $secondaryimage) {
                                if (is_object($secondaryimage)) {
                                    $thumb = Core::make('helper/image')->getThumbnail($secondaryimage, 300, 300, true);
                                    ?>
                                    <a href="<?= $secondaryimage->getRelativePath() ?>"
                                       title="<?= h($product->getName()); ?>" class="store-product-thumb"><img
                                            src="<?= $thumb->src ?>"></a>

                                <?php }
                            }
                            echo '</div>';
                        }
                        ?>
                        </p>
                    </div>
                <?php } ?>
            </div>
            <div class="row">
                <?php if ($showProductDetails) { ?>
                    <div class="store-product-detailed-description col-md-12">
                        <?= $product->getDetail() ?>
                    </div>
                <?php } ?>
            </div>
    </form>

    <script type="text/javascript">
        $(function () {
            $('.store-product-thumb').magnificPopup({
                type: 'image',
                gallery: {enabled: true}
            });

            <?php if ($product->hasVariations() && !empty($variationLookup)) {?>

            <?php
            $varationData = array();
            foreach($variationLookup as $key=>$variation) {
                $product->setVariation($variation);

                $imgObj = $variation->getVariationImageObj();

                $thumb = false;

                if ($imgObj) {
                    $thumb = Core::make('helper/image')->getThumbnail($imgObj,600,800,true);
                }

                $varationData[$key] = array(
                'price'=>$product->getFormattedOriginalPrice(),
                'saleprice'=>$product->getFormattedSalePrice(),
                'available'=>($variation->isSellable()),
                'imageThumb'=>$thumb ? $thumb->src : '',
                'image'=>$imgObj ? $imgObj->getRelativePath() : ''

                );
            } ?>

            $('#product-options-<?= $bID; ?> select, #product-options-<?= $bID; ?> input').change(function () {
                var variationdata = <?= json_encode($varationData); ?>;
                var ar = [];

                $('#product-options-<?= $bID; ?> select, #product-options-<?= $bID; ?> input:checked').each(function () {
                    ar.push($(this).val());
                })

                ar.sort(communityStore.sortNumber);
                var pdb = $(this).closest('.store-product-block');

                if (variationdata[ar.join('_')]['saleprice']) {
                    var pricing = '<span class="store-sale-price"><?= t("On Sale: "); ?>' + variationdata[ar.join('_')]['saleprice'] + '</span>' +
                        '<span class="store-original-price">' + variationdata[ar.join('_')]['price'] + '</span>';

                    pdb.find('.store-product-price').html(pricing);
                } else {
                    pdb.find('.store-product-price').html(variationdata[ar.join('_')]['price']);
                }

                if (variationdata[ar.join('_')]['available']) {
                    pdb.find('.store-out-of-stock-label').addClass('hidden');
                    pdb.find('.store-btn-add-to-cart').removeClass('hidden');
                } else {
                    pdb.find('.store-out-of-stock-label').removeClass('hidden');
                    pdb.find('.store-btn-add-to-cart').addClass('hidden');
                }
                if (variationdata[ar.join('_')]['imageThumb']) {
                    var image = pdb.find('.store-product-primary-image img');

                    if (image) {
                        image.attr('src', variationdata[ar.join('_')]['imageThumb']);
                        var link = image.parent();
                        if (link) {
                            link.attr('href', variationdata[ar.join('_')]['image'])
                        }
                    }
                }

            });
            <?php } ?>

        });
    </script>

<?php } else { ?>
    <p class="alert alert-info"><?= t("Product not available") ?></p>
<?php } ?>
