<?php
defined('C5_EXECUTE') or die("Access Denied.");

$csm = \Core::make('cshelper/multilingual');

$defaultimagewidth = 720;
$defaultimageheight = 720;

if (is_object($product) && $product->isActive()) {
    $options = $product->getOptions();
    $variationLookup = $product->getVariationLookup();
    $variationData = $product->getVariationData();
    $availableOptionsids = $variationData['availableOptionsids'];
    $firstAvailableVariation = $variationData['firstAvailableVariation'];

    if ($firstAvailableVariation) {
        $product = $firstAvailableVariation;
    }

    $isSellable = $product->isSellable(); ?>

    <form class="store-product store-product-block" id="store-form-add-to-cart-<?= $product->getID(); ?>"
          data-product-id="<?= $product->getID(); ?>" itemscope itemtype="http://schema.org/Product">
        <?= $token->output('community_store'); ?>
        <div class="row">
            <?php if ($showImage) {
        ?>
            <div class="store-product-details col-md-6">
                <?php
    } else {
        ?>
                <div class="store-product-details col-md-12">
                    <?php
    } ?>
                    <?php if ($showProductName) {
        ?>
                        <h1 class="store-product-name" itemprop="name"><?= $csm->t($product->getName(), 'productName', $product->getID()); ?></h1>
                        <meta itemprop="sku" content="<?= $product->getSKU(); ?>"/>
                    <?php
    } ?>

                    <?php if ($showProductPrice && !$product->allowCustomerPrice()) {
        ?>
                        <p class="store-product-price" itemprop="offers" itemscope itemtype="http://schema.org/Offer">
                            <meta itemprop="priceCurrency" content="<?= Config::get('community_store.currency'); ?>"/>
                            <?php
                            $salePrice = $product->getSalePrice();
        if (isset($salePrice) && "" != $salePrice) {
            $formattedSalePrice = $product->getFormattedSalePrice();
            $formattedOriginalPrice = $product->getFormattedOriginalPrice();
            echo '<span class="store-sale-price">' . t("On Sale: ") . $formattedSalePrice . '</span>';
            echo '&nbsp;' . t('was') . '&nbsp;';
            echo '<span class="store-original-price">' . $formattedOriginalPrice . '</span>';
            echo '<meta itemprop="price" content="' . $formattedSalePrice . '" />';
        } else {
            $price = $product->getPrice();

            $formattedPrice = $product->getFormattedPrice();

            echo $formattedPrice;
            echo '<meta itemprop="price" content="' . $price . '" />';
        } ?>
                        </p>
                    <?php
    } ?>

                    <?php if ($product->allowCustomerPrice()) {
        ?>
                        <div class="store-product-customer-price-entry form-group">
                            <?php
                            $pricesuggestions = $product->getPriceSuggestionsArray();
        if (!empty($pricesuggestions)) {
            ?>
                                <p class="store-product-price-suggestions"><?php
                                    foreach ($pricesuggestions as $suggestion) {
                                        ?>
                                        <a href="#" class="store-price-suggestion btn btn-default btn-sm"
                                           data-add-type="none"
                                           data-suggestion-value="<?= $suggestion; ?>"><?= Config::get('community_store.symbol') . $suggestion; ?></a>
                                    <?php
                                    } ?>
                                </p>
                                <label for="customerPrice"
                                       class="store-product-customer-price-label"><?= t('Enter Other Amount'); ?></label>
                            <?php
        } else {
            ?>
                                <label for="customerPrice"
                                       class="store-product-customer-price-label"><?= t('Amount'); ?></label>
                            <?php
        } ?>
                            <?php $min = $product->getPriceMinimum(); ?>
                            <?php $max = $product->getPriceMaximum(); ?>
                            <div class="input-group col-md-6 col-sm-6 col-xs-6">
                                <div class="input-group-addon"><?= Config::get('community_store.symbol'); ?></div>
                                <input type="number" <?= $min ? 'min="' . $min . '"' : ''; ?>
                                       <?= $max ? 'max="' . $max . '"' : ''; ?> step="0.01" class="store-product-customer-price-entry-field form-control"
                                       value="<?= $product->getBasePrice(); ?>" name="customerPrice"/>
                            </div>
                            <?php if ($min >= 0 || $max > 0) {
            ?>
                                <span class="store-min-max help-block">
                                    <?php
                                    if (!is_null($min)) {
                                        echo t('minimum') . ' ' . Config::get('community_store.symbol') . $min;
                                    }

            if (!is_null($max)) {
                if ($min >= 0) {
                    echo ', ';
                }
                echo t('maximum') . ' ' . Config::get('community_store.symbol') . $max;
            } ?>
                                    </span>
                            <?php
        } ?>
                        </div>
                    <?php
    } ?>

                    <?php
                    $showTiers = false; // adjust to enable displaying pricing tiers below

    if ($showTiers && $product->getQuantityPrice()) {
        $pricetiers = $product->getPriceTiers();

        if (!empty($pricetiers)) {
            echo '<p class="store-product-price-tiers">';

            $pricetiersoutput = [];

            foreach ($pricetiers as $pricetier) {
                $pricetiersoutput[] = $pricetier->getFrom() . ' ' . t('to') . ' ' . $pricetier->getTo() . ' - ' . Config::get('community_store.symbol') . $pricetier->getPrice();
            }

            echo implode('<br>', $pricetiersoutput);
            echo '</p>';
        }
    } ?>

                    <meta itemprop="description" content="<?= strip_tags($product->getDesc()); ?>"/>

                    <?php if ($showProductDescription) {
        ?>
                        <div class="store-product-description">
                            <?= $product->getDescription(); ?>
                        </div>
                    <?php
    } ?>

                    <?php if ($showDimensions) {
        ?>
                        <div class="store-product-dimensions">
                            <strong><?= t("Dimensions"); ?>:</strong>
                            <?= $product->getDimensions(); ?>
                            <?= Config::get('community_store.sizeUnit'); ?>
                        </div>
                    <?php
    } ?>

                    <?php if ($showWeight) {
        ?>
                        <div class="store-product-weight">
                            <strong><?= t("Weight"); ?>:</strong>
                            <?= $product->getWeight(); ?>
                            <?= Config::get('community_store.weightUnit'); ?>
                        </div>
                    <?php
    } ?>

                    <?php if ($showGroups && false) {
        ?>
                        <ul>
                            <?php
                            $productgroups = $product->getGroups();
        foreach ($productgroups as $pg) {
            ?>
                                <li class="store-product-group"><?= $pg->getGroup()->getGroupName(); ?> </li>
                            <?php
        } ?>
                        </ul>
                    <?php
    } ?>

                    <?php if ($showIsFeatured) {
        if ($product->isFeatured()) {
            ?>
                            <span class="store-product-featured"><?= t("Featured Item"); ?></span>
                        <?php
        }
    } ?>

                    <div class="store-product-options" id="product-options-<?= $bID; ?>">
                        <?php if ($product->allowQuantity() && $showQuantity) {
        ?>
                            <div class="store-product-quantity form-group">
                                <label class="store-product-option-group-label"><?= t('Quantity'); ?></label>

                                <?php $qtylabel = $product->getQtyLabel(); ?>

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
                                <div class="store-product-option-group form-group <?= $option->getHandle(); ?>">
                                    <label class="store-product-option-group-label"><?= $option->getName(); ?></label>
                                    <?php if ($displayType != 'radio') { ?>
                                    <select class="store-product-option <?= $option->getIncludeVariations() ? 'store-product-variation' : ''; ?> form-control"
                                            name="po<?= $option->getID(); ?>">
                                    <?php } ?>
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
                                                        <?= $disabled .  ($selected ? 'checked' : ''); ?> name="po<?= $option->getID();?>" value="<?= $optionItem->getID(); ?>" /><?= h($optionItem->getName());?></label>
                                            </div>
                                        <?php } else { ?>
                                        <option <?= $disabled . ' ' . $selected; ?>value="<?= $optionItem->getID(); ?>"><?= $optionItem->getName() . $outOfStock; ?></option>
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
                                    <label class="store-product-option-group-label"><?= $option->getName(); ?></label>
                                    <input class="store-product-option-entry form-control" <?= $requiredAttr; ?>
                                           name="pt<?= $option->getID(); ?>"/>
                                </div>
                            <?php
                            } elseif ('textarea' == $optionType) {
                                ?>
                                <div class="store-product-option-group form-group <?= $option->getHandle(); ?>">
                                    <label class="store-product-option-group-label"><?= $option->getName(); ?></label>
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
                                               name="pc<?= $option->getID(); ?>"/> <?= $option->getName(); ?></label>
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

                    <?php if ($showCartButton) {
                            ?>
                        <p class="store-product-button">
                            <input type="hidden" name="pID" value="<?= $product->getID(); ?>">
                            <span><button data-add-type="none" data-product-id="<?= $product->getID(); ?>"
                                          class="store-btn-add-to-cart btn btn-primary <?= ($isSellable ? '' : 'hidden'); ?> "><?= ($btnText ? h($btnText) : t("Add to Cart")); ?></button>
                            </span>
                        </p>
                        <p class="store-out-of-stock-label alert alert-warning <?= ($isSellable ? 'hidden' : ''); ?>"><?= t("Out of Stock"); ?></p>
                    <?php
                        } ?>

                </div>

                <?php if ($showImage) {
                            ?>
                    <div class="store-product-image col-md-6">
                        <div>&nbsp;</div>
                        <?php
                        $imgObj = $product->getImageObj();
                            if (is_object($imgObj)) {
                                $thumb = Core::make('helper/image')->getThumbnail($imgObj, $defaultimagewidth, $defaultimageheight, false); ?>
                            <div class="store-product-primary-image ">
                                <a itemprop="image" href="<?= $imgObj->getRelativePath(); ?>"
                                   title="<?= h($product->getName()); ?>"
                                   class="store-product-thumb text-center center-block">
                                    <img src="<?= $thumb->src; ?>">
                                </a>
                            </div>
                        <?php
                            } ?>

                        <?php
                        $images = $product->getImagesObjects();
                            if (count($images) > 0) {
                                $loop = 1;
                                echo '<div class="store-product-additional-images clearfix no-gutter">';

                                foreach ($images as $secondaryimage) {
                                    if (is_object($secondaryimage)) {
                                        $thumb = Core::make('helper/image')->getThumbnail($secondaryimage, $defaultimagewidth, $defaultimageheight, true); ?>
                                    <div class="store-product-additional-image col-md-6 col-sm-6"><a
                                                href="<?= $secondaryimage->getRelativePath(); ?>"
                                                title="<?= h($product->getName()); ?>"
                                                class="store-product-thumb text-center center-block"><img
                                                    src="<?= $thumb->src; ?>"/></a></div>
                                <?php
                                    }

                                    if ($loop > 0 && 0 == $loop % 2 && count($images) > $loop) {
                                        echo '</div><div class="clearfix no-gutter">';
                                    }
                                    ++$loop;
                                }
                                echo '</div>';
                            } ?>
                    </div>
                <?php
                        } ?>
            </div>
            <div class="row">
                <?php if ($showProductDetails) {
                            ?>
                    <div class="store-product-detailed-description col-md-12">
                        <?= $product->getDetail(); ?>
                    </div>
                <?php
                        } ?>
            </div>
    </form>

    <script type="text/javascript">
        $(function () {
            $('.store-product-thumb').magnificPopup({
                type: 'image',
                gallery: {enabled: true}
            });

            <?php if ($product->hasVariations() && !empty($variationLookup)) {
                            ?>

            <?php
            $varationData = [];
                            foreach ($variationLookup as $key => $variation) {
                                $product->setVariation($variation);
                                $imgObj = $product->getImageObj();

                                $thumb = false;

                                if ($imgObj) {
                                    $thumb = Core::make('helper/image')->getThumbnail($imgObj, $defaultimagewidth, $defaultimageheight, false);
                                }

                                $varationData[$key] = [
                    'price' => $product->getFormattedOriginalPrice(),
                    'saleprice' => $product->getFormattedSalePrice(),
                    'available' => ($variation->isSellable()),
                    'imageThumb' => $thumb ? $thumb->src : '',
                    'image' => $imgObj ? $imgObj->getRelativePath() : '',
                ];
                            } ?>

            $('#product-options-<?= $bID; ?> select, #product-options-<?= $bID; ?> input').change(function () {
                var variationdata = <?= json_encode($varationData); ?>;
                var ar = [];

                $('#product-options-<?= $bID; ?> select.store-product-variation, #product-options-<?= $bID; ?> .store-product-variation:checked').each(function () {
                    ar.push($(this).val());
                });

                ar.sort(communityStore.sortNumber);
                var pdb = $(this).closest('.store-product-block');

                if (variationdata[ar.join('_')]['saleprice']) {
                    var pricing = '<span class="store-sale-price"><?= t("On Sale: "); ?>' + variationdata[ar.join('_')]['saleprice'] + '</span>&nbsp;' +
                        '<?php echo t('was'); ?>' +
                        '&nbsp;<span class="store-original-price ">' + variationdata[ar.join('_')]['price'] + '</span>';

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
            <?php
                        } ?>

        });
    </script>

<?php
} else {
                            ?>
    <p class="alert alert-info"><?= t("Product not available"); ?></p>
<?php
                        } ?>
