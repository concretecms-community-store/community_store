<?php
defined('C5_EXECUTE') or die("Access Denied.");

/**
 * @var Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product|false $product
 * @var Concrete\Core\Validation\CSRF\Token $token
 * @var string $langpath
 * @var bool $isWholesale
 * @var Concrete\Core\Application\Application $app
 * @var string $csPath
 */

if (empty($product) || !$product->isActive()) {
    ?>
    <p class="alert alert-info"><?= t("Product not available") ?></p>
    <?php
    return;
}

/**
 * @var bool|int|string|null $showProductName
 * @var bool|int|string|null $showProductSKU
 * @var bool|int|string|null $showProductPrice
 * @var bool|int|string|null $showProductDescription
 * @var bool|int|string|null $showManufacturer
 * @var bool|int|string|null $showManufacturerDescription
 * @var bool|int|string|null $showDimensions
 * @var bool|int|string|null $showWeight
 * @var bool|int|string|null $showGroups
 * @var bool|int|string|null $showCartButton
 * @var bool|int|string|null $showIsFeatured
 * @var bool|int|string|null $showQuantity
 * @var bool|int|string|null $showImage
 * @var bool|int|string|null $showProductDetails
 * @var string|false $btnText
 * @var Concrete\Core\Url\Resolver\Manager\ResolverManagerInterface $urlResolver
 * @var Concrete\Core\Config\Repository\Repository $config
 */

$communityStoreImageHelper = $app->make('cs/helper/image', ['resizingScheme' => 'single_product']);
$csm = $app->make('cs/helper/multilingual');

$variationLookup = $product->getVariationLookup();
$variationData = $product->getVariationData();
$availableOptionsids = $variationData['availableOptionsids'];
$firstAvailableVariation = $variationData['firstAvailableVariation'];

if ($firstAvailableVariation) {
    $product = $firstAvailableVariation;
}

$product->setPriceAdjustment($variationData['priceAdjustment']);
$isSellable = $product->isSellable();
?>
<form class="store-product store-product-block" data-product-id="<?= $product->getID() ?>" itemscope itemtype="http://schema.org/Product">
    <?php $token->output('community_store') ?>
    <div class="row">
        <div class="store-product-details <?= $showImage ? 'col-md-6' : 'col-md-12' ?>">
            <?php
            if ($showProductName) {
                ?>
                <h1 class="store-product-name" itemprop="name">
                    <?= $csm->t($product->getName(), 'productName', $product->getID()) ?>
                    <?php
                    if ($showProductSKU && $product->getSKU()) {
                        ?>
                        <small class="store-product-sku">(<span><?= h($product->getSKU()) ?></span>)</small>
                        <?php
                    }
                    ?>
                </h1>
                <meta itemprop="sku" content="<?= $product->getSKU() ?>" />
                <?php
            }
            $manufacturer = $showManufacturer ? $product->getManufacturer() : null;
            if ($manufacturer) {
                $manufacturerPage = $manufacturer->getManufacturerPage();
                ?>
                <p>
                    <?= t('Manufacturer') ?>:
                    <?php
                    if ($manufacturerPage) {
                        if ($manufacturerPage->getCollectionPointerExternalLink() != '') {
                            $target = $manufacturerPage->openCollectionPointerExternalLinkInNewWindow() ? '_blank' : '';
                        } else {
                            $target = $manufacturerPage->getAttribute('nav_target');
                        }
                        ?>
                        <a class="store-product-manufacturer" target="<?= h($target) ?>" itemprop="brand" href="<?= $urlResolver->resolve([$manufacturerPage]) ?>"><?= h($manufacturer->getName()) ?></a>
                        <?php
                    } else {
                        ?>
                        <span class="store-product-manufacturer" itemprop="brand"><?= h($manufacturer->getName()) ?></span>
                        <?php
                    }
                    ?>
                </p>
                <?php
            }
            $manufacturer = $showManufacturerDescription ? $product->getManufacturer() : null;
            if ($manufacturer) {
                $manufacturerDescription = $manufacturer->getDescription();
                if ($manufacturerDescription) {
                    ?>
                    <div class="store-product-manufacturer-description"><?= $manufacturerDescription ?></div>
                    <?php
                }
            }
            if ($product->allowCustomerPrice()) {
                ?>
                <div class="store-product-customer-price-entry form-group">
                    <?php
                    $pricesuggestions = $product->getPriceSuggestionsArray();
                    if (!empty($pricesuggestions)) {
                        ?>
                        <p class="store-product-price-suggestions">
                            <?php
                            foreach ($pricesuggestions as $suggestion) {
                                ?>
                                <a
                                    href="#"
                                    class="store-price-suggestion btn btn-default btn-secondary btn-sm"
                                    data-add-type="none"
                                    data-suggestion-value="<?= $suggestion ?>"
                                ><?= $config->get('community_store.symbol') . $suggestion ?></a>
                                <?php
                            }
                            ?>
                        </p>
                        <label for="customerPrice" class="store-product-customer-price-label"><?= t('Enter Other Amount') ?></label>
                        <?php
                    } else {
                        ?>
                        <label for="customerPrice" class="store-product-customer-price-label"><?= t('Amount') ?></label>
                        <?php
                    }
                    $min = $product->getPriceMinimum();
                    $max = $product->getPriceMaximum();
                    ?>
                    <div class="input-group col-md-6 col-sm-6 col-sm-6">
                        <div class="input-group-addon input-group-text"><?= $config->get('community_store.symbol') ?></div>
                        <input
                            type="number"
                            <?= $min ? " min=\"{$min}\"" : '' ?>
                            <?= $max ? " max=\"{$max}\"" : '' ?>
                            step="0.01"
                            class="store-product-customer-price-entry-field form-control"
                            value="<?= $product->getBasePrice() ?>"
                            name="customerPrice"
                        />
                    </div>
                    <?php
                    if ($min || $max) {
                        ?>
                        <span class="store-min-max help-block">
                            <?php
                            if ($min) {
                                echo t(/* i18n: %1$s is a currency symbol, %2$s is an amount */'minimum %1$s%2$s', $config->get('community_store.symbol'), $min);
                            }
                            if ($max) {
                                if ($min) {
                                    echo ', ';
                                }
                                echo t(/* i18n: %1$s is a currency symbol, %2$s is an amount */'maximum %1$s%2$s', $config->get('community_store.symbol'), $max);
                            }
                            ?>
                        </span>
                        <?php
                    }
                    ?>
                </div>
                <?php
            } elseif ($showProductPrice) {
                $salePrice = $product->getSalePrice() ?: $product->getPrice();
                $price = $product->getPrice(1, true);
                if ($salePrice == $price) {
                    $salePrice = false;
                }
                $activePrice = ($salePrice ?: $price) - $product->getPriceAdjustment($product->getDiscountRules());
                if ($isWholesale) {
                    $msrp = $product->getFormattedOriginalPrice();
                    $wholesalePrice = $product->getWholesalePrice() - $product->getPriceAdjustment($product->getDiscountRules());
                    $formattedWholesalePrice = $product->getFormattedWholesalePrice();
                }
                ?>
                <p
                    class="store-product-price"
                    data-price="<?= $activePrice ?>"
                    data-original-price="<?= $salePrice ? $price : '' ?>"
                    data-list-price="<?= $isWholesale && $wholesalePrice ? $price : '' ?>"
                    itemprop="offers" itemscope itemtype="http://schema.org/Offer"
                >
                    <meta itemprop="priceCurrency" content="<?= $config->get('community_store.currency') ?>" />
                    <?php
                    $stockstatus = $product->isSellable() ? 'http://schema.org/InStock' : 'http://schema.org/OutOfStock';
                    if ($isWholesale && $wholesalePrice > 0 && $wholesalePrice != $activePrice) {
                        ?>
                        <?= t('List Price') ?>: <span class="store-list-price"><?= $msrp ?></span><br />
                        <?= t('Wholesale Price') ?>: <span class="store-wholesale-price"><?= $formattedWholesalePrice ?></span>
                        <meta itemprop="price" content="<?= $wholesalePrice ?>" />
                        <link itemprop="availability " href="<?= $stockstatus ?>" />
                        <?php
                    } else {
                        if (isset($salePrice) && "" != $salePrice) {
                            $formattedSalePrice = $product->getFormattedSalePrice() ?: $product->getFormattedPrice(1, false);
                            $formattedOriginalPrice = $product->getFormattedOriginalPrice();
                            ?>
                            <?= t('On Sale') ?>: <span class="store-sale-price"><?= $formattedSalePrice ?></span>&nbsp;<?= t('was') ?>&nbsp;<span class="store-original-price"><?= $formattedOriginalPrice ?></span>
                            <meta itemprop="price" content="<?= $salePrice ?>" />
                            <link itemprop="availability" href="<?= $stockstatus ?>"/>
                            <?php
                        } else {
                            ?>
                            <?= $product->getFormattedPrice() ?>
                            <meta itemprop="price" content="<?= $price ?>" />
                            <link itemprop="availability " href="<?= $stockstatus ?>" />
                            <?php
                        }
                    }
                    ?>
                </p>
                <?php
            }

            $showTiers = false; // adjust to enable displaying pricing tiers below
            if ($showTiers && $product->getQuantityPrice()) {
                $pricetiers = $product->getPriceTiers();
                if (!empty($pricetiers)) {
                    ?>
                    <p class="store-product-price-tiers">
                        <?php
                        $pricetiersoutput = [];
                        foreach ($pricetiers as $pricetier) {
                            $pricetiersoutput[] = t(
                                /* i18n: %1$s is the start of a quantity tier, %2$s is the end of a quantity tier, %3$s is a currency symbol, %4$s the tier price */
                                '%1$s to %2$s - %3$s%4$s',
                                $pricetier->getFrom(),
                                $pricetier->getTo(),
                                $config->get('community_store.symbol'),
                                $pricetier->getPrice()
                            );
                        }
                        echo implode('<br>', $pricetiersoutput);
                        ?>
                    </p>
                    <?php
                }
            }
            ?>
            <meta itemprop="description" content="<?= strip_tags($product->getDesc()) ?>" />
            <?php
            if ($showProductDescription) {
                ?>
                <div class="store-product-description">
                    <?= $csm->t($product->getDescription(), 'productDescription', $product->getID()) ?>
                </div>
                <?php
            }
            if ($showDimensions) {
                ?>
                <div class="store-product-dimensions">
                    <strong><?= t("Dimensions") ?>:</strong>
                    <?= $product->getDimensions() ?>
                    <?= $config->get('community_store.sizeUnit') ?>
                </div>
                <?php
            }
            if ($showWeight) {
                ?>
                <div class="store-product-weight">
                    <strong><?= t("Weight") ?>:</strong>
                    <span class="store-product-weight-value"><?= $product->getWeight() ?></span>
                    <?= $config->get('community_store.weightUnit') ?>
                </div>
                <?php
            }
            if ($showGroups && false) {
                ?>
                <ul>
                    <?php
                    $productgroups = $product->getGroups();
                    foreach ($productgroups as $pg) {
                        ?>
                        <li class="store-product-group"><?= $pg->getGroup()->getGroupName() ?></li>
                        <?php
                    }
                    ?>
                </ul>
                <?php
            }
            if ($showIsFeatured && $product->isFeatured()) {
                ?>
                <span class="store-product-featured"><?= t("Featured Item") ?></span>
                <?php
            }
            ?>
            <div  class="store-product-options">
                <?php
                if ($isSellable && $product->allowQuantity() && $showQuantity) {
                    $quantityLabel = $csm->t($product->getQtyLabel(), 'productQuantityLabel', $product->getID());
                    ?>
                    <div class="store-product-quantity form-group mb-3">
                        <label class="store-product-option-group-label"><?= t('Quantity') ?></label>
                        <?php
                        $max = $product->getMaxCartQty();
                        if ($quantityLabel) {
                            ?>
                            <div class="input-group">
                                <?php
                        }
                        if ($product->allowDecimalQuantity()) {
                            ?>
                            <input
                                type="number"
                                name="quantity"
                                class="store-product-qty form-control"
                                min="<?= $product->getQtySteps() ? $product->getQtySteps() : 0.001 ?>"
                                step="<?= $product->getQtySteps() ? $product->getQtySteps() : 0.001 ?>"
                                <?= $max ? " max=\"{$max}\"" : '' ?>
                            />
                            <?php
                        } else {
                            ?>
                            <input
                                type="number"
                                name="quantity"
                                class="store-product-qty form-control"
                                value="1"
                                min="1"
                                step="1"
                                <?= $max ? " max=\"{$max}\"" : '' ?>
                            />
                            <?php
                        }
                        if ($quantityLabel) {
                            ?>
                                <div class="input-group-addon input-group-text"><?= $quantityLabel ?></div>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                    <?php
                } else {
                    ?>
                    <input type="hidden" name="quantity" class="store-product-qty" value="1">
                    <?php
                }
                foreach ($product->getOptions() as $option) {
                    $optionItems = $option->getOptionItems();
                    $optionType = $option->getType();
                    $required = $option->getRequired();
                    $displayType = $option->getDisplayType();
                    $details = $option->getDetails();
                    $requiredAttr = $required ? ' required="required" placeholder="' . t('Required') . '" ' : '';
                    if (!$optionType || $optionType == 'select') {
                        ?>
                        <div class="store-product-option-group form-group mb-3 <?= h($option->getHandle()) ?>">
                            <label class="store-product-option-group-label"><?= h($csm->t($option->getName(), 'optionName', $product->getID(), $option->getID())) ?></label>
                            <?php
                            if ($details) {
                                ?>
                                <span class="store-product-option-help-text help-block"><?= h($csm->t($details, 'optionDetails', $product->getID(), $option->getID())) ?></span>
                                <?php
                            }
                            if ($displayType !== 'radio') {
                                ?>
                                <select
                                    <?= $required ? ' required="required" ' : '' ?>
                                    class="store-product-option <?= $option->getIncludeVariations() ? 'store-product-variation' : '' ?> form-control form-select"
                                    name="po<?= $option->getID() ?>"
                                >
                                <?php
                            }
                            $variation = false;
                            $disabled = false;
                            $outOfStock = false;
                            $firstOptionItem = true;
                            /** @var \Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductOption\ProductOptionItem[] $optionItems */
                            foreach ($optionItems as $optionItem) {
                                $noValue = false;
                                if (!$optionItem->isHidden()) {
                                    if (isset($variationLookup[$optionItem->getID()])) {
                                        $variation = $variationLookup[$optionItem->getID()];
                                    }
                                    $selected = is_array($availableOptionsids) && in_array($optionItem->getID(), $availableOptionsids) ? 'selected="selected"' : '';
                                    if (!empty($variation)) {
                                        $disabled = $variation->isSellable() ? '' : 'disabled="disabled" ';
                                        $outOfStock = $variation->isSellable() ? '' : ' (' . t('out of stock') . ')';
                                    } else {
                                        $disabled = false;
                                        if ($firstOptionItem) {
                                            $selected = 'selected="selected"';
                                            if (!$optionItem->getName()) {
                                                $disabled = 'disabled="disabled" ';
                                                $noValue = true;
                                                if($displayType == 'radio') {
                                                    $selected = '';
                                                }
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
                                    if ($displayType == 'radio') {
                                        ?>
                                        <div class="radio">
                                            <label>
                                                <input
                                                    type="radio"
                                                    required="required"
                                                    class="store-product-option <?= $option->getIncludeVariations() ? 'store-product-variation' : '' ?>"
                                                    <?= $disabled . ($selected ? 'checked' : '') ?>
                                                    name="po<?= $option->getID() ?>"
                                                    value="<?= $optionItem->getID() ?>"
                                                    data-adjustment="<?= (float)$optionItem->getPriceAdjustment($product->getDiscountRules()) ?>"
                                                />
                                                <?= h($csm->t($optionLabel, $translateHandle, $product->getID(), $optionItem->getID())) ?>
                                            </label>
                                        </div>
                                        <?php
                                    } else {
                                        ?>
                                        <option
                                            <?= $disabled . ' ' . $selected ?>
                                            value="<?= $noValue && $required ? '' : $optionItem->getID() ?>"
                                            data-adjustment="<?= $optionItem->getPriceAdjustment($product->getDiscountRules()) ?>"
                                        >
                                            <?= h($csm->t($optionLabel, $translateHandle, $product->getID(), $optionItem->getID())) . $outOfStock ?>
                                        </option>
                                        <?php
                                    }
                                }
                            }
                            if ($displayType != 'radio') {
                                ?>
                                </select>
                                <?php
                            }
                            ?>
                        </div>
                        <?php
                    } elseif ($optionType == 'text') {
                        ?>
                        <div class="store-product-option-group form-group mb-3 <?= $option->getHandle() ?>">
                            <label class="store-product-option-group-label"><?= h($csm->t($option->getName(), 'optionName', $product->getID(), $option->getID())) ?></label>
                            <?php
                            if ($details) {
                                ?>
                                <span class="store-product-option-help-text help-block"><?= h($csm->t($details, 'optionDetails', $product->getID(), $option->getID())) ?></span>
                                <?php
                            }
                            ?>
                            <input class="store-product-option-entry form-control" <?= $requiredAttr ?> name="pt<?= $option->getID() ?>" type="text" />
                        </div>
                        <?php
                    } elseif ($optionType == 'textarea') {
                        ?>
                        <div class="store-product-option-group form-group mb-3 <?= $option->getHandle() ?>">
                            <label class="store-product-option-group-label"><?= h($csm->t($option->getName(), 'optionName', $product->getID(), $option->getID())) ?></label>
                            <?php
                            if ($details) {
                                ?>
                                <span class="store-product-option-help-text help-block"><?= h($csm->t($details, 'optionDetails', $product->getID(), $option->getID())) ?></span>
                                <?php
                            }
                            ?>
                            <textarea class="store-product-option-entry form-control" <?= $requiredAttr ?> name="pa<?= $option->getID() ?>"></textarea>
                        </div>
                        <?php
                    } elseif ($optionType == 'checkbox') {
                        ?>
                        <div class="store-product-option-group form-group mb-3 <?= $option->getHandle() ?>">
                            <label class="store-product-option-group-label">
                                <input
                                    type="hidden"
                                    value="<?= t('no') ?>"
                                    class="store-product-option-checkbox-hidden <?= $option->getHandle() ?>"
                                    name="pc<?= $option->getID() ?>"
                                />
                                <input
                                    type="checkbox"
                                    value="<?= t('yes') ?>"
                                    <?= $requiredAttr ?>
                                    class="store-product-option-checkbox <?= $option->getIncludeVariations() ? 'store-product-variation' : '' ?> <?= $option->getHandle() ?>"
                                    name="pc<?= $option->getID() ?>"
                                />
                                <?= h($csm->t($option->getName(), 'optionName', $product->getID(), $option->getID())) ?>
                            </label>
                            <?php
                            if ($details) {
                                ?>
                                <span class="store-product-option-help-text help-block"><?= h($csm->t($details, 'optionDetails', $product->getID(), $option->getID())) ?></span>
                                <?php
                            }
                            ?>
                        </div>
                        <?php
                    } elseif ($optionType == 'hidden') {
                        ?>
                        <input type="hidden" class="store-product-option-hidden <?= $option->getHandle() ?>" name="ph<?= $option->getID() ?>" />
                        <?php
                    }  elseif ($optionType == 'static') {
                        ?>
                        <div class="store-product-option-static">
                            <?= $csm->t($details, 'optionDetails', $product->getID(), $option->getID()) ?>
                        </div>
                        <?php
                    }
                }
                ?>
            </div>
            <?php
            if ($showCartButton) {
                if ($btnText) {
                    $buttonText = $btnText;
                } else {
                    $buttonText = $csm->t($product->getAddToCartText(), 'productAddToCartText', $product->getID());
                }
                $outOfStock = $csm->t($product->getOutOfStockMessage(), 'productOutOfStockMessage', $product->getID());
                $notAvailable = $csm->t('Not Available', 'productNotAvailableMessage', $product->getID());
                ?>
                <p class="store-product-button">
                    <input type="hidden" name="pID" value="<?= $product->getID() ?>" />
                    <span>
                        <button
                            data-add-type="none"
                            data-product-id="<?= $product->getID() ?>"
                            class="store-btn-add-to-cart btn btn-primary <?= ($isSellable ? '' : 'hidden') ?>"
                        >
                            <?= ($buttonText ? h($buttonText) : t("Add to Cart")) ?>
                        </button>
                    </span>
                </p>
                <p class="store-out-of-stock-label alert alert-warning <?= ($isSellable ? 'hidden' : '') ?>">
                    <?= $outOfStock ? h($outOfStock) : t("Out of Stock") ?>
                </p>
                <p class="store-not-available-label alert alert-warning hidden">
                    <?= $notAvailable ? h($notAvailable) : t('Not Available') ?>
                </p>
                <?php
            }
            ?>
        </div>
        <?php
        if ($showImage) {
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
                    <div class="store-product-primary-image mb-sm-5 mb-2">
                        <a
                            itemprop="image" href="<?= $imgObj->getRelativePath() ?>"
                            title="<?= h($imgObj->getTitle()) ?>"
                            class="store-product-thumb text-center center-block"
                            data-pswp-width="<?= $imgObj->getAttribute('width') ?>"
                            data-pswp-height="<?= $imgObj->getAttribute('height') ?>"
                        >
                            <img
                                class="img-responsive img-fluid"
                                src="<?= $thumb->src ?>"
                                title="<?= h($imgObj->getTitle()) ?>"
                                alt="<?= h($imgTitle) ?>"
                            />
                        </a>
                    </div>
                    <?php
                }
                $images = $product->getImagesObjects();
                $numImages = count($images);
                if ($numImages > 0) {
                    $loop = 1;
                    ?>
                    <div class="store-product-additional-images row">
                        <?php
                        /*
                         * This is only needed if no thumbnail type was defined or for some reason
                         * we need to fallback on the legacy thumbnailer.
                         * We are setting crop to true as it's false by default
                         */
                        $communityStoreImageHelper->setLegacyThumbnailCrop(true);
                        foreach ($images as $secondaryImage) {
                            $thumb = $communityStoreImageHelper->getThumbnail($secondaryImage);
                            $imgDescription = $secondaryImage->getDescription();
                            $imgTitle = $imgDescription ?: $secondaryImage->getTitle();
                            ?>
                            <div class="store-product-additional-image col-md-6 col-sm-6 mb-sm-5 mb-2">
                                <a
                                    href="<?= $secondaryImage->getRelativePath() ?>"
                                    title="<?= h($product->getName()) ?>"
                                    class="store-product-thumb text-center center-block"
                                    data-pswp-width="<?= $secondaryImage->getAttribute('width') ?>"
                                    data-pswp-height="<?= $secondaryImage->getAttribute('height') ?>"
                                >
                                    <img
                                        class="img-responsive img-fluid"
                                        src="<?= $thumb->src ?>"
                                        title="<?= h($secondaryImage->getTitle()) ?>"
                                        alt="<?= h($imgTitle) ?>"
                                    />
                                </a>
                            </div>
                            <?php
                            if ($loop > 0 && 0 == $loop % 2 && $numImages > $loop) {
                                echo '</div><div class="store-product-additional-images row">';
                            }
                            ++$loop;
                        }
                        ?>
                    </div>
                    <?php
                }
                ?>
            </div>
            <?php
        }
        ?>
    </div>
    <div class="row">
        <?php
        if ($showProductDetails) {
            ?>
            <div class="store-product-detailed-description col-md-12">
                <?= $csm->t($product->getDetail(), 'productDetails', $product->getID()) ?>
            </div>
            <?php
        }
        ?>
    </div>
</form>
<script type="module">
import PhotoSwipeLightbox from <?= json_encode("{$csPath}/js/photoswipe/photoswipe-lightbox.esm.min.js") ?>;
const lightbox = new PhotoSwipeLightbox({
    loop: false,
    gallery: '.store-product-block[data-product-id="<?= $product->getID() ?>"]',
    children: 'a.store-product-thumb',
    pswpModule: () => import(<?= json_encode("{$csPath}/js/photoswipe/photoswipe.esm.min.js") ?>),
    errorMsg: <?= json_encode(t('The image cannot be loaded')) ?>,
    closeTitle: <?= json_encode(t('Close')) ?>,
    zoomTitle: <?= json_encode(t('Zoom')) ?>,
    arrowPrevTitle: <?= json_encode(t('Previous')) ?>,
    arrowNextTitle: <?= json_encode(t('Next')) ?>,
});
lightbox.addFilter('useContentPlaceholder', (useContentPlaceholder, content) => {
    return content.index === 0;
});
lightbox.init();
</script>
<script>
<?php
$varationData = [];
/**
 * This is only needed if no thumbnail type was defined or for some reason
 * we need to fallback on the legacy thumbnailer.
 * We set it to false again because we set it to true above
 */
$communityStoreImageHelper->setLegacyThumbnailCrop(false);
foreach ($variationLookup as $key => $variation) {
    $product->setVariation($variation);
    $product->setPriceAdjustment(0);
    $imgObj = $product->getImageObj();
    $thumb = $imgObj ? $communityStoreImageHelper->getThumbnail($imgObj) : false;
    $price = $product->getPrice(1, true);
    $salePrice = $product->getSalePrice() ?: $product->getPrice();
    if ($salePrice == $price) {
        $salePrice = null;
    }
    $varationData[$key] = [
        'price' => $price,
        'salePrice' => $salePrice,
        'available' => $variation->isSellable(),
        'disabled' => $variation->getVariationDisabled(),
        'maxCart' => $variation->getMaxCartQty(),
        'imageThumb' => $thumb ? $thumb->src : '',
        'image' => $imgObj ? $imgObj->getRelativePath() : '',
        'sku' => $variation->getVariationSKU(),
        'weight' => $variation->getVariationWeight(),
        'saleTemplate'=> t('On Sale') .': <span class="store-sale-price"></span>&nbsp;' . t('was') . '&nbsp;<span class="store-original-price"></span>'
    ];
    if ($isWholesale) {
        $varationData[$key]['wholesalePrice'] = $product->getWholesalePrice();
        $varationData[$key]['wholesaleTemplate'] =  t('List Price') . ':&nbsp;<span class="store-list-price"></span><br />' . t('Wholesale Price') . ':&nbsp;<span class="store-wholesale-price"></span>';
    }
}
?>
(window.variationData = window.variationData || [])[<?= $product->getID() ?>] = <?= json_encode($varationData) ?>;
</script>
