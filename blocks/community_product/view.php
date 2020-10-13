<?php
defined('C5_EXECUTE') or die("Access Denied.");

$communityStoreImageHelper = $app->make('cs/helper/image', ['single_product']);
$csm = $app->make('cs/helper/multilingual');

if (is_object($product) && $product->isActive()) {
    $options = $product->getOptions();
    $variationLookup = $product->getVariationLookup();
    $variationData = $product->getVariationData();
    $availableOptionsids = $variationData['availableOptionsids'];
    $firstAvailableVariation = $variationData['firstAvailableVariation'];

    if ($firstAvailableVariation) {
        $product = $firstAvailableVariation;
    }

    $product->setPriceAdjustment($variationData['priceAdjustment']);
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
                        <h1 class="store-product-name"
                            itemprop="name"><?= $csm->t($product->getName(), 'productName', $product->getID()); ?></h1>
                        <meta itemprop="sku" content="<?= $product->getSKU(); ?>"/>
                    <?php } ?>

                    <?php
                    if ($showManufacturer) {
                        $manufacturer = $product->getManufacturer();
                        if ($manufacturer) { ?>
                            <p><?= t('Manufacturer') ?>:
                                <?php
                                $manufacturerPage = $manufacturer->getManufacturerPage() ?>
                                <?php if ($manufacturerPage) {
                                    if ($manufacturerPage->getCollectionPointerExternalLink() != '') {
                                        if ($manufacturerPage->openCollectionPointerExternalLinkInNewWindow()) {
                                            $target = '_blank';
                                        }
                                    } else {
                                        $target = $manufacturerPage->getAttribute('nav_target');
                                    }

                                    ?>
                                    <a class="store-product-manufacturer" target="<?php echo h($target) ?>" itemprop="brand" href="<?= URL::to($manufacturerPage) ?>"><?= h($manufacturer->getName()); ?></a>
                                <?php } else { ?>
                                    <span class="store-product-manufacturer" itemprop="brand"><?= h($manufacturer->getName()); ?> </span>
                                <?php } ?>
                            </p>

                        <?php }
                    } ?>

                    <?php
                    if ($showManufacturerDescription) {
                        $manufacturer = $product->getManufacturer();
                        if ($manufacturer) {
                            $manufacturerDescription = $manufacturer->getDescription();

                            if ($manufacturerDescription) { ?>
                                <div class="store-product-manufacturer-description"><?= $manufacturerDescription; ?></div>
                            <?php }
                        }
                    }
                    ?>

                    <?php if ($showProductPrice && !$product->allowCustomerPrice()) {
                        $salePrice = $product->getSalePrice();
                        $price = $product->getPrice();
                        $activePrice = ($salePrice ? $salePrice : $price ) - $product->getPriceAdjustment($product->getDiscountRules());

                        if ($isWholesale) {
                            $msrp = $product->getFormattedOriginalPrice();
                            $wholesalePrice = $product->getWholesalePrice() - $product->getPriceAdjustment($product->getDiscountRules());
                            $formattedWholesalePrice = $product->getFormattedWholesalePrice();
                        }

                        ?>
                        <p class="store-product-price" data-price="<?= $activePrice; ?>" data-original-price="<?= $salePrice ? $price : ''; ?>" data-list-price="<?= ($isWholesale && $wholesalePrice) ? $price : ''; ?>"
                           itemprop="offers" itemscope itemtype="http://schema.org/Offer">
                            <meta itemprop="priceCurrency" content="<?= Config::get('community_store.currency'); ?>"/>
                            <?php
                            $stockstatus = $product->isSellable() ? 'http://schema.org/InStock' : 'http://schema.org/OutOfStock';

                            if ($isWholesale && $wholesalePrice > 0 && $wholesalePrice != $activePrice) {
                                echo t('List Price') . ': <span class="store-list-price">' . $msrp . '</span><br />' . t('Wholesale Price') . ': <span class="store-wholesale-price">' . $formattedWholesalePrice . '</span>';
                                echo '<meta itemprop="price" content="' . $wholesalePrice . '" />';
                                echo '<link itemprop="availability " href="' . $stockstatus . '"/>';

                            } else {

                                if (isset($salePrice) && "" != $salePrice) {
                                    $formattedSalePrice = $product->getFormattedSalePrice();
                                    $formattedOriginalPrice = $product->getFormattedOriginalPrice();
                                    echo t('On Sale') . ': <span class="store-sale-price">' . $formattedSalePrice . '</span>';
                                    echo '&nbsp;' . t('was') . '&nbsp;';
                                    echo '<span class="store-original-price">' . $formattedOriginalPrice . '</span>';
                                    echo '<meta itemprop="price" content="' . $formattedSalePrice . '" />';
                                    echo '<link itemprop="availability " href="' . $stockstatus . '"/>';
                                } else {

                                    $formattedPrice = $product->getFormattedPrice();

                                    echo $formattedPrice;
                                    echo '<meta itemprop="price" content="' . $price . '" />';
                                    echo '<link itemprop="availability " href="' . $stockstatus . '"/>';
                                }
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
                                    <?= $max ? 'max="' . $max . '"' : ''; ?> step="0.01"
                                       class="store-product-customer-price-entry-field form-control"
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
                            <?= $csm->t($product->getDescription(), 'productDescription', $product->getID()); ?>
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

                    <div  class="store-product-options">
                        <?php if ($isSellable && $product->allowQuantity() && $showQuantity) {
                            ?>
                            <div class="store-product-quantity form-group">
                                <label class="store-product-option-group-label"><?= t('Quantity'); ?></label>

                                <?php $quantityLabel = $csm->t($product->getQtyLabel(), 'productQuantityLabel', $product->getID()); ?>

                                <?php if ($quantityLabel) {
                                ?>
                                <div class="input-group">
                                    <?php
                                    }
                                    $max = $product->getMaxCartQty(); ?>

                                    <?php if ($product->allowDecimalQuantity()) {
                                        ?>
                                        <input type="number" name="quantity" class="store-product-qty form-control"
                                               min="<?= $product->getQtySteps() ? $product->getQtySteps() : 0.001; ?>"
                                               step="<?= $product->getQtySteps() ? $product->getQtySteps() : 0.001; ?>" <?= ($max ? 'max="' . $max . '"' : ''); ?>>
                                        <?php
                                    } else {
                                        ?>
                                        <input type="number" name="quantity" class="store-product-qty form-control"
                                               value="1" min="1" step="1" <?= ($max ? 'max="' . $max . '"' : ''); ?>>
                                        <?php
                                    } ?>

                                    <?php if ($quantityLabel) {
                                    ?>
                                    <div class="input-group-addon"><?= $quantityLabel; ?></div>
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
                            $details = $option->getDetails();

                            $requiredAttr = '';

                            if ($required) {
                                $requiredAttr = ' required="required" placeholder="' . t('Required') . '" ';
                            } ?>

                            <?php if (!$optionType || $optionType == 'select') {
                                ?>
                                <div class="store-product-option-group form-group <?= h($option->getHandle()); ?>">
                                    <label class="store-product-option-group-label"><?= h($csm->t($option->getName(), 'optionName', $product->getID(), $option->getID())); ?></label>

                                    <?php if ($details) { ?>
                                        <span class="store-product-option-help-text help-block"><?= h($csm->t($details, 'optionDetails', $product->getID(), $option->getID())); ?></span>
                                    <?php } ?>

                                    <?php if ('radio' != $displayType) {
                                    ?>
                                    <select <?= $required ? ' required="required" ' : ''; ?> class="store-product-option <?= $option->getIncludeVariations() ? 'store-product-variation' : ''; ?> form-control"
                                            name="po<?= $option->getID(); ?>">
                                        <?php
                                        } ?>
                                        <?php
                                        $firstAvailableVariation = false;
                                        $variation = false;
                                        $disabled = false;
                                        $outOfStock = false;
                                        $firstOptionItem = true;
                                        foreach ($optionItems as $optionItem) {
                                            $noValue = false;
                                            if (!$optionItem->isHidden()) {
                                                $variation = $variationLookup[$optionItem->getID()];
                                                $selected = '';

                                                if (!empty($variation)) {
                                                    $firstAvailableVariation = (!$firstAvailableVariation && $variation->isSellable()) ? $variation : $firstAvailableVariation;
                                                    $disabled = $variation->isSellable() ? '' : 'disabled="disabled" ';
                                                    $outOfStock = $variation->isSellable() ? '' : ' (' . t('out of stock') . ')';

                                                    if (is_array($availableOptionsids) && in_array($optionItem->getID(), $availableOptionsids)) {
                                                        $selected = 'selected="selected"';
                                                    }
                                                } else {
                                                    $disabled = false;
                                                    if ($firstOptionItem) {
                                                        $selected = 'selected="selected"';
                                                        if(!$optionItem->getName()) {
                                                            $disabled = 'disabled="disabled" ';
                                                            $noValue = true;
                                                            if($displayType == 'radio') $selected = '';
                                                        }
                                                        $firstOptionItem = false;
                                                    }
                                                }

                                                $optionLabel = $optionItem->getName();
                                                $translateHandle = 'optionValue';

                                                if ($optionItem->getSelectorName()) {
                                                    $optionLabel = $optionItem->getSelectorName();
                                                    $translateHandle = 'optionSelectorName';
                                                }
                                                ?>

                                                <?php if ($displayType == 'radio') { ?>
                                                    <div class="radio">
                                                        <label><input type="radio" required
                                                                      class="store-product-option <?= $option->getIncludeVariations() ? 'store-product-variation' : '' ?> "
                                                                <?= $disabled . ($selected ? 'checked' : ''); ?>
                                                                      name="po<?= $option->getID(); ?>"
                                                                      value="<?= $optionItem->getID(); ?>"
                                                                      data-adjustment="<?= (float)$optionItem->getPriceAdjustment($product->getDiscountRules()); ?>" />

                                                            <?= h($csm->t($optionLabel, $translateHandle, $product->getID(), $optionItem->getID())); ?>
                                                        </label>
                                                    </div>
                                                <?php } else { ?>
                                                    <option
                                                        <?= $disabled . ' ' . $selected; ?> value="<?= $noValue && $required ? '' : $optionItem->getID(); ?>"
                                                                                    data-adjustment="<?= $optionItem->getPriceAdjustment($product->getDiscountRules()); ?>"

                                                    ><?= h($csm->t($optionLabel, $translateHandle, $product->getID(), $optionItem->getID())) . $outOfStock; ?></option>
                                                <?php } ?>

                                                <?php
                                            }
                                        } ?>
                                        <?php if ($displayType != 'radio') { ?>
                                    </select>
                                <?php } ?>
                                </div>
                                <?php
                            } elseif ($optionType == 'text') {
                                ?>
                                <div class="store-product-option-group form-group <?= $option->getHandle(); ?>">
                                    <label class="store-product-option-group-label"><?= h($csm->t($option->getName(), 'optionName', $product->getID(), $option->getID())); ?></label>

                                    <?php if ($details) { ?>
                                        <span class="store-product-option-help-text help-block"><?= h($csm->t($details, 'optionDetails', $product->getID(), $option->getID())); ?></span>
                                    <?php } ?>

                                    <input class="store-product-option-entry form-control" <?= $requiredAttr; ?>
                                           name="pt<?= $option->getID(); ?>" type="text" />
                                </div>
                                <?php
                            } elseif ($optionType == 'textarea') {
                                ?>
                                <div class="store-product-option-group form-group <?= $option->getHandle(); ?>">
                                    <label class="store-product-option-group-label"><?= h($csm->t($option->getName(), 'optionName', $product->getID(), $option->getID())); ?></label>

                                    <?php if ($details) { ?>
                                        <span class="store-product-option-help-text help-block"><?= h($csm->t($details, 'optionDetails', $product->getID(), $option->getID())); ?></span>
                                    <?php } ?>

                                    <textarea class="store-product-option-entry form-control" <?= $requiredAttr; ?>
                                              name="pa<?= $option->getID(); ?>"></textarea>
                                </div>
                                <?php
                            } elseif ($optionType == 'checkbox') {
                                ?>
                                <div class="store-product-option-group form-group <?= $option->getHandle(); ?>">
                                    <label class="store-product-option-group-label">
                                        <input type="hidden" value="<?= t('no'); ?>"
                                               class="store-product-option-checkbox-hidden <?= $option->getHandle(); ?>"
                                               name="pc<?= $option->getID(); ?>"/>
                                        <input type="checkbox" value="<?= t('yes'); ?>"
                                               class="store-product-option-checkbox <?= $option->getIncludeVariations() ? 'store-product-variation' : ''; ?> <?= $option->getHandle(); ?>"
                                               name="pc<?= $option->getID(); ?>"/> <?= h($csm->t($option->getName(), 'optionName', $product->getID(), $option->getID())); ?></label>

                                    <?php if ($details) { ?>
                                        <span class="store-product-option-help-text help-block"><?= h($csm->t($details, 'optionDetails', $product->getID(), $option->getID())); ?></span>
                                    <?php } ?>

                                </div>
                                <?php
                            } elseif ($optionType == 'hidden') {
                                ?>
                                <input type="hidden" class="store-product-option-hidden <?= $option->getHandle(); ?>"
                                       name="ph<?= $option->getID(); ?>"/>
                                <?php
                            }  elseif ($optionType == 'static') {
                                ?>
                                <div class="store-product-option-static">
                                    <?= $csm->t($details, 'optionDetails', $product->getID(), $option->getID()); ?>
                                </div>
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
                                          class="store-btn-add-to-cart btn btn-primary <?= ($isSellable ? '' : 'hidden'); ?> ">
                                    <?php
                                    if ($btnText) {
                                        $buttonText = $btnText;
                                    } else {
                                        $buttonText = $csm->t($product->getAddToCartText(), 'productAddToCartText', $product->getID());
                                    }
                                    ?>

                                    <?= ($buttonText ? h($buttonText) : t("Add to Cart")); ?>
                                </button>
                            </span>
                        </p>
                        <p class="store-out-of-stock-label alert alert-warning <?= ($isSellable ? 'hidden' : ''); ?>">
                            <?php $outOfStock = $csm->t($product->getOutOfStockMessage(), 'productOutOfStockMessage', $product->getID()); ?>
                            <?= $outOfStock ? h($outOfStock) : t("Out of Stock"); ?>
                        </p>
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
                            $thumb = $communityStoreImageHelper->getThumbnail($imgObj);
                            $imgDescription = $imgObj->getDescription();
                            if ($imgDescription) {
                                $imgTitle = $imgDescription;
                            } else {
                                $imgTitle = $imgObj->getTitle();
                            }
                            ?>
                            <div class="store-product-primary-image ">
                                <a itemprop="image" href="<?= $imgObj->getRelativePath(); ?>"
                                   title="<?= h($imgObj->getTitle()); ?>"
                                   class="store-product-thumb text-center center-block">
                                    <img class="img-responsive" src="<?= $thumb->src; ?>" title="<?= h($imgObj->getTitle()); ?>"
                                         alt="<?= h($imgTitle); ?>">
                                </a>
                            </div>
                            <?php
                        } ?>

                        <?php
                        $images = $product->getImagesObjects();
                        if (count($images) > 0) {
                            $loop = 1;
                            echo '<div class="store-product-additional-images clearfix no-gutter">';

                            // This is only needed if no thumbnail type was defined or for some reason
                            // we need to fallback on the legacy thumbnailer.
                            // We are setting crop to true as it's false by default
                            $communityStoreImageHelper->setLegacyThumbnailCrop(true);

                            foreach ($images as $secondaryImage) {
                                if (is_object($secondaryImage)) {
                                    $thumb = $communityStoreImageHelper->getThumbnail($secondaryImage);
                                    $imgDescription = $secondaryImage->getDescription();
                                    if ($imgDescription) {
                                        $imgTitle = $imgDescription;
                                    } else {
                                        $imgTitle = $secondaryImage->getTitle();
                                    }
                                    ?>
                                    <div class="store-product-additional-image col-md-6 col-sm-6"><a
                                                href="<?= $secondaryImage->getRelativePath(); ?>"
                                                title="<?= h($product->getName()); ?>"
                                                class="store-product-thumb text-center center-block"><img
                                                    src="<?= $thumb->src; ?>"
                                                    title="<?= h($secondaryImage->getTitle()) ?>"
                                                    alt="<?= h($imgTitle); ?>"/></a></div>
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
                        <?= $csm->t($product->getDetail(), 'productDetails', $product->getID()); ?>
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
        });

            <?php
            $varationData = [];
            // This is only needed if no thumbnail type was defined or for some reason
            // we need to fallback on the legacy thumbnailer.
            // We set it to false again because we set it to true above
            $communityStoreImageHelper->setLegacyThumbnailCrop(false);

            foreach ($variationLookup as $key => $variation) {
                $product->setVariation($variation);
                $product->setPriceAdjustment(0);
                $imgObj = $product->getImageObj();

                $thumb = false;

                if ($imgObj) {
                    $thumb = $communityStoreImageHelper->getThumbnail($imgObj);
                }

                $varationData[$key] = [
                    'price' => $product->getPrice(),
                    'salePrice' => $product->getSalePrice(),
                    'available' => $variation->isSellable(),
                    'imageThumb' => $thumb ? $thumb->src : '',
                    'image' => $imgObj ? $imgObj->getRelativePath() : '',
                    'saleTemplate'=> t('On Sale') .': <span class="store-sale-price"></span>&nbsp;' . t('was') . '&nbsp;<span class="store-original-price"></span>'
                ];

                if ($isWholesale) {
                    $varationData[$key]['wholesalePrice'] = $product->getWholesalePrice();
                    $varationData[$key]['wholesaleTemplate'] =  t('List Price') . ':&nbsp;<span class="store-list-price"></span><br />' . t('Wholesale Price') . ':&nbsp;<span class="store-wholesale-price"></span>';
                }
            }

            ?>

            var variationData = variationData || [];
            variationData[<?= $product->getID(); ?>] = <?= json_encode($varationData); ?>;

    </script>

    <?php
} else {
    ?>
    <p class="alert alert-info"><?= t("Product not available"); ?></p>
    <?php
} ?>
