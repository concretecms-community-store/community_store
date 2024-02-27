<?php

use Concrete\Core\Page\Page;

defined('C5_EXECUTE') or die("Access Denied.");

/**
 * @var Concrete\Core\Form\Service\Form $form
 * @var string $usersort
 * @var Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product[] $products
 * @var string $pagination
 * @var Concrete\Core\Search\Pagination\Pagination $paginator
 * @var Concrete\Core\File\Image\Thumbnail\ThumbnailerInterface $ih
 * @var Concrete\Core\Utility\Service\Text $th
 * @var Concrete\Core\Validation\CSRF\Token $token
 * @var string $langpath
 * @var Concrete\Core\Application\Application $app
 * @var string $locale
 * @var Concrete\Core\Url\Resolver\Manager\ResolverManagerInterface $urlResolver
 * @var Concrete\Core\Utility\Service\Url $urlService
 * @var Concrete\Core\Config\Repository\Repository $config
 * @var Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Image $communityStoreImageHelper
 * @var Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Multilingual $csm
 * @var bool $isWholesale
 * @var int|string $bID
 * @var string|null $sortOrder
 * @var int|string|null $gID
 * @var string|null $filter
 * @var int|string|null $filterCID
 * @var int|string|null $relatedPID
 * @var bool|int|string|null $groupMatchAny
 * @var int|string|null $maxProducts
 * @var bool|int|string|null $showOutOfStock
 * @var int|string|null $productsPerRow
 * @var string|null $displayMode
 * @var bool|int|string|null $showPagination
 * @var bool|int|string|null $enableExternalFiltering
 * @var bool|int|string|null $showFeatured
 * @var bool|int|string|null $showSale
 * @var bool|int|string|null $showDescription
 * @var bool|int|string|null $showName
 * @var bool|int|string|null $showSKU
 * @var bool|int|string|null $showPrice
 * @var bool|int|string|null $showQuickViewLink
 * @var bool|int|string|null $showPageLink
 * @var bool|int|string|null $showSortOption
 * @var string|null $pageLinkText
 * @var bool|int|string|null $showAddToCart
 * @var string|null $btnText
 * @var bool|int|string|null $showQuantity
 * @var string|null $noProductsMessage
 * @var int|string|null $filterManufacturer
 * @var int|string|null $filterProductType
 */

$c = Page::getCurrentPage();
if (!$c || $c->isError()) {
    $c = null;
}

if (!$products) {
    if ($c !== null && $c->isEditMode()) {
        ?>
        <div class="store-product-list-block">
            <div class="ccm-edit-mode-disabled-item">
                <?= t('Empty Product List') ?>
            </div>
        </div>
        <?php
    } elseif ($noProductsMessage) {
        ?>
        <div class="store-product-list-block">
            <p class="alert alert-info">
                <?= h($noProductsMessage) ?>
            </p>
        </div>
        <?php
    }
    return;
}

$productsPerRow = empty($productsPerRow) ? 1 : (int) $productsPerRow;
switch ($productsPerRow) {
    case 2:
        $columnClass = 'col-md-6 col-sm-6 col-xs-12';
        break;
    case 3:
        $columnClass = 'col-md-4 col-sm-4 col-xs-12';
        break;
    case 4:
        $columnClass = 'col-md-3 col-sm-6 col-xs-12';
        break;
    case 6:
        $columnClass = 'col-md-2 col-sm-6 col-xs-12';
        break;
    default:
        $columnClass = 'col-md-12';
}
?>
<div class="store-product-list-block">
    <?php
    if ($showSortOption) {
        ?>
        <div class="store-product-list-sort row">
            <div class="col-md-12 form-inline text-right pull-right">
                <div class="form-group">
                    <?= $form->label('sort' . $bID, t('Sort by')) ?>
                    <?= $form->select('sort' . $bID,
                        [
                            '0' => '',
                            'price_asc' => t('price, lowest to highest'),
                            'price_desc' => t('price, highest to lowest'),
                        ]
                    ) ?>
                </div>
            </div>
        </div>
        <script>
        $(function () {
            $('#sort<?= $bID ?>').change(function () {
                const sortstring = <?= json_encode((string) $urlService->setVariable(['sort' . $bID => '%sort%'])) ?>;
                window.location.href = sortstring.replace('%sort%', $(this).val());
            });
        });
        </script>
        <?php
    }
    ?>
    <div class="store-product-list row store-product-list-per-row-<?= $productsPerRow ?>">
        <?php
        $productIndex = 1;
        foreach ($products as $product) {
            $variationLookup = $product->getVariationLookup();
            $variationData = $product->getVariationData();
            $availableOptionsids = $variationData['availableOptionsids'];
            $firstAvailableVariation = $variationData['firstAvailableVariation'];
            if ($firstAvailableVariation) {
                $product = $firstAvailableVariation;
            } else {
                $product->setInitialVariation();
            }
            $product->setPriceAdjustment($variationData['priceAdjustment']);
            $isSellable = $product->isSellable();
            // This is done so we can get a type of active class if there's a product list on the product page
            $activeclass = $c !== null && $c->getCollectionID() == $product->getPageID() ? 'on-product-page' : '';
            $productPage = $product->getProductPage();
            ?>
            <div class="store-product-list-item mb-3 <?= $columnClass ?> <?= $activeclass ?>">
                <form data-product-id="<?= $product->getID() ?>">
                    <?php $token->output('community_store') ?>
                    <?php
                    if ($displayMode == 'list') {
                        ?>
                        <div class="row">
                        <?php
                    } elseif ($showName) {
                        ?>
                        <h2 class="store-product-list-name">
                            <?= $csm->t($product->getName(), 'productName', $product->getID()) ?>
                            <?php
                            if ($showSKU && $product->getSKU()) {
                                ?>
                                <small class="store-product-sku">(<span><?= h($product->getSKU()) ?></span>)</small>
                                <?php
                            }
                            ?>
                        </h2>
                        <?php
                    }
                    $imgObj = $product->getImageObj();
                    if ($imgObj !== null) {
                        $thumb = $communityStoreImageHelper->getThumbnail($imgObj);
                        if ($displayMode == 'list') {
                            ?>
                            <div class="col-md-3 col-sm-6">
                            <?php
                        }
                        ?>
                        <p class="store-product-list-thumbnail">
                            <?php
                            if ($showPageLink && $productPage) {
                                ?>
                                <a href="<?= h((string) $urlResolver->resolve([$productPage])) ?>">
                                    <img src="<?= $thumb->src ?>" class="img-responsive img-fluid" alt="<?= $product->getName() ?>" />
                                </a>
                                <?php
                            } else {
                                ?>
                                <img src="<?= $thumb->src ?>" class="img-responsive img-fluid" alt="<?= $product->getName() ?>" />
                                <?php
                            }
                            ?>
                        </p>
                        <?php
                        if ($displayMode == 'list') {
                            ?>
                            </div>
                            <?php
                        }
                    }
                    if ($displayMode == 'list') {
                        ?>
                        <div class="col-md-9 col-sm-6">
                        <?php
                    }
                    if ($showName && $displayMode == 'list') {
                        ?>
                        <h2 class="store-product-list-name">
                            <?= $csm->t($product->getName(), 'productName', $product->getID()) ?>
                            <?php
                            if ($showSKU && $product->getSKU()) {
                                ?>
                                <small class="store-product-sku">(<?= h($product->getSKU()) ?>)</small>
                                <?php
                            }
                            ?>
                        </h2>
                        <?php
                    }
                    if ($showPrice && !$product->allowCustomerPrice()) {
                        $salePrice = $product->getSalePrice() ?: $product->getPrice();
                        $price = $product->getPrice(1, true);
                        if ($salePrice == $price) {
                            $salePrice = false;
                        }
                        $activePrice = ($salePrice ?: $price) - $product->getPriceAdjustment($product->getDiscountRules());
                        ?>
                        <p class="store-product-price store-product-list-price" data-price="<?= $activePrice ?>" data-original-price="<?= $salePrice ? $price : '' ?>">
                            <?php
                            if ((string) $salePrice !== '') {
                                $formattedSalePrice = $product->getFormattedSalePrice() ?: $product->getFormattedPrice(1, false);
                                $formattedOriginalPrice = $product->getFormattedOriginalPrice();
                                ?>
                                <span class="store-sale-price"><?= $formattedSalePrice ?></span>&nbsp;<?= t('was') ?>&nbsp;<span class="store-original-price"><?= $formattedOriginalPrice ?></span>
                                <?php
                            } else {
                                echo $product->getFormattedPrice();
                            }
                            ?>
                        </p>
                        <?php
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
                                            data-suggestion-value="<?= $suggestion ?>"
                                            data-add-type="list"
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
                                <div class="input-group-addon"><?= $config->get('community_store.symbol') ?></div>
                                <input
                                    type="number"
                                    <?= $min ? "min=\"{$min}\"" : '' ?>
                                    <?= $max ? "max=\"{$max}\"" : '' ?>
                                    step="0.01"
                                    class="store-product-customer-price-entry-field form-control"
                                    value="<?= $product->getPrice() ?>"
                                    name="customerPrice"
                                />
                            </div>
                            <?php
                            if ($min || $max) {
                                ?>
                                <span class="store-min-max help-block">
                                    <?php
                                    if ($min) {
                                        echo t('minimum') . ' ' . $config->get('community_store.symbol') . $min;
                                    }
                                    if ($max) {
                                        if ($min) {
                                            echo ', ';
                                        }
                                        echo t('maximum') . ' ' . $config->get('community_store.symbol') . $max;
                                    }
                                    ?>
                                </span>
                                <?php
                            }
                            ?>
                        </div>
                        <?php
                    }
                    if ($showDescription) {
                        ?>
                        <div class="store-product-list-description"><?= $csm->t($product->getDesc(), 'productDescription', $product->getID()) ?></div>
                        <?php
                    }
                    if ($showPageLink && $productPage) {
                        ?>
                        <p class="store-btn-more-details-container">
                            <a href="<?= h((string) $urlResolver->resolve([$productPage])) ?>" class="store-btn-more-details btn btn-default btn-secondary">
                                <?= ($pageLinkText ? $pageLinkText : t('More Details')) ?>
                            </a>
                        </p>
                        <?php
                    }
                    if ($showAddToCart) {
                        ?>
                        <div class="store-product-options">
                            <?php
                            if ($isSellable && $product->allowQuantity() && $showQuantity) {
                                ?>
                                <div class="store-product-quantity form-group mb-3">
                                    <label class="store-product-option-group-label">
                                        <?= t('Quantity') ?>
                                    </label>
                                    <?php
                                    $quantityLabel = $csm->t($product->getQtyLabel(), 'productQuantityLabel', $product->getID());
                                    if ($quantityLabel) {
                                        ?>
                                        <div class="input-group">
                                        <?php
                                    }
                                    $max = $product->getMaxCartQty();
                                    if ($product->allowDecimalQuantity()) {
                                        ?>
                                        <input
                                            type="number"
                                            name="quantity"
                                            class="store-product-qty form-control"
                                            min="<?= $product->getQtySteps() ?: '0.001' ?>"
                                            step="<?= $product->getQtySteps() ?: '0.001' ?>"
                                            <?= $max ? "max=\"{$max}\"" : '' ?>
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
                                            <?= $max ? "max=\"{$max}\"" : '' ?>
                                        />
                                        <?php
                                    }
                                    if ($quantityLabel) {
                                        ?>
                                            <div class="input-group-addon"><?= $quantityLabel ?></div>
                                        </div>
                                        <?php
                                    }
                                    ?>
                                </div>
                                <?php
                            } else {
                                ?>
                                <input type="hidden" name="quantity" class="store-product-qty" value="1" />
                                <?php
                            }
                            foreach ($product->getOptions() as $option) {
                                $optionItems = $option->getOptionItems();
                                $optionType = (string) $option->getType();
                                $required = $option->getRequired();
                                $requiredAttr = $required ? (' required="required" placeholder="' . t('Required') . '" ') : '';
                                $displayType = $option->getDisplayType();
                                $details = $option->getDetails();
                                switch ($optionType) {
                                    case 'select':
                                    case '':
                                        ?>
                                        <div class="store-product-option-group form-group mb-3 <?= $option->getHandle() ?>">
                                            <label class="store-product-option-group-label">
                                                <?= h($csm->t($option->getName(), 'optionName', $product->getID(), $option->getID())) ?>
                                            </label>
                                            <?php
                                            if ($details) {
                                                ?>
                                                <span class="store-product-option-help-text help-block">
                                                    <?= h($csm->t($details, 'optionDetails', $product->getID(), $option->getID())) ?>
                                                </span>
                                                <?php
                                            }
                                            if ($displayType != 'radio') {
                                                ?>
                                                <select
                                                    <?= $required ? ' required="required" ' : '' ?>
                                                    class="store-product-option <?= $option->getIncludeVariations() ? 'store-product-variation' : '' ?> form-control form-select"
                                                    name="po<?= $option->getID() ?>"
                                                >
                                                <?php
                                            }
                                            $firstOptionItem = true;
                                            foreach ($optionItems as $optionItem) {
                                                if ($optionItem->isHidden()) {
                                                    continue;
                                                }
                                                $noValue = false;
                                                if (isset($variationLookup[$optionItem->getID()])) {
                                                    $variation = $variationLookup[$optionItem->getID()];
                                                } else {
                                                    $variation = false;
                                                }
                                                if (is_array($availableOptionsids) && in_array($optionItem->getID(), $availableOptionsids)) {
                                                    $selected = 'selected="selected"';
                                                } else {
                                                    $selected = '';
                                                }
                                                if (!empty($variation)) {
                                                    $disabled = $variation->isSellable() ? '' : 'disabled="disabled" ';
                                                    $outOfStock = $variation->isSellable() ? '' : ' (' . t('out of stock') . ')';
                                                } else {
                                                    $disabled = false;
                                                    $outOfStock = false;
                                                    if ($firstOptionItem) {
                                                        $firstOptionItem = false;
                                                        $selected = 'selected="selected"';
                                                        if (!$optionItem->getName()) {
                                                            $disabled = 'disabled="disabled" ';
                                                            $noValue = true;
                                                            if ($displayType == 'radio') {
                                                                $selected = '';
                                                            }
                                                        }
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
                                                                required
                                                                class="store-product-option <?= $option->getIncludeVariations() ? 'store-product-variation' : '' ?>"
                                                                <?= $disabled . ($selected ? 'checked' : '') ?>
                                                                name="po<?= $option->getID() ?>"
                                                                value="<?= $optionItem->getID() ?>"
                                                                data-adjustment="<?= (float)$optionItem->getPriceAdjustment($product->getDiscountRules()) ?>"
                                                            />
                                                            <?= h($csm->t($optionLabel, $translateHandle, $product->getID(), $optionItem->getID())) . $outOfStock ?>
                                                        </label>
                                                    </div>
                                                    <?php
                                                } else {
                                                    ?>
                                                    <option
                                                        <?= $disabled . ' ' . $selected ?>
                                                        value="<?= $noValue && $required ? '' : $optionItem->getID() ?>"
                                                        data-adjustment="<?= (float)$optionItem->getPriceAdjustment($product->getDiscountRules()) ?>"
                                                    ><?= h($csm->t($optionLabel, $translateHandle, $product->getID(), $optionItem->getID())) . $outOfStock ?></option>
                                                    <?php
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
                                        break;
                                    case 'text':
                                        ?>
                                        <div class="store-product-option-group form-group mb-3 <?= $option->getHandle() ?>">
                                            <label class="store-product-option-group-label">
                                                <?= h($csm->t($option->getName(), 'optionName', $product->getID(), $option->getID())) ?>
                                            </label>
                                            <?php
                                            if ($details) {
                                                ?>
                                                <span class="store-product-option-help-text help-block">
                                                    <?= h($csm->t($details, 'optionDetails', $product->getID(), $option->getID())) ?>
                                                </span>
                                                <?php
                                            }
                                            ?>
                                            <input class="store-product-option-entry form-control" <?= $requiredAttr ?> name="pt<?= $option->getID() ?>" type="text" />
                                        </div>
                                        <?php
                                        break;
                                    case 'textarea':
                                        ?>
                                        <div class="store-product-option-group form-group mb-3 <?= $option->getHandle() ?>">
                                            <label class="store-product-option-group-label">
                                                <?= h($csm->t($option->getName(), 'optionName', $product->getID(), $option->getID())) ?>
                                            </label>
                                            <?php
                                            if ($details) {
                                                ?>
                                                <span class="store-product-option-help-text help-block">
                                                    <?= h($csm->t($details, 'optionDetails', $product->getID(), $option->getID())) ?>
                                                </span>
                                                <?php
                                            }
                                            ?>
                                            <textarea class="store-product-option-entry form-control" <?= $requiredAttr ?> name="pa<?= $option->getID() ?>"></textarea>
                                        </div>
                                        <?php
                                        break;
                                    case 'checkbox':
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
                                                    class="store-product-option-checkbox <?= $option->getHandle() ?>"
                                                    name="pc<?= $option->getID() ?>"
                                                />
                                                <?= h($csm->t($option->getName(), 'optionName', $product->getID(), $option->getID())) ?>
                                            </label>
                                            <?php
                                            if ($details) {
                                                ?>
                                                <span class="store-product-option-help-text help-block">
                                                    <?= h($csm->t($details, 'optionDetails', $product->getID(), $option->getID())) ?>
                                                </span>
                                                <?php
                                            }
                                            ?>
                                        </div>
                                        <?php
                                        break;
                                    case 'hidden':
                                        ?>
                                        <input type="hidden" class="store-product-option-hidden <?= $option->getHandle() ?>" name="ph<?= $option->getID() ?>" />
                                        <?php
                                        break;
                                    case 'static':
                                        ?>
                                        <div class="store-product-option-static">
                                            <?= $csm->t($details, 'optionDetails', $product->getID(), $option->getID()) ?>
                                        </div>
                                        <?php
                                        break;
                                }
                            }
                            ?>
                        </div>
                        <input type="hidden" name="pID" value="<?= $product->getID() ?>" />
                        <p class="store-btn-add-to-cart-container">
                            <button
                                data-add-type="list"
                                data-product-id="<?= $product->getID() ?>"
                                class="store-btn-add-to-cart btn btn-primary <?= ($isSellable ? '' : 'hidden') ?> "
                            >
                                <?php
                                if ($btnText) {
                                    $buttonText = $btnText;
                                } else {
                                    $buttonText = $csm->t($product->getAddToCartText(), 'productAddToCartText', $product->getID());
                                }
                                ?>
                                <?= $buttonText ? h($buttonText) : t('Add to Cart') ?>
                            </button>
                        </p>
                        <p class="store-out-of-stock-label alert alert-warning <?= ($isSellable ? 'hidden' : '') ?>">
                            <?php
                            $outOfStock = $csm->t($product->getOutOfStockMessage(), 'productOutOfStockMessage', $product->getID());
                            ?>
                            <?= $outOfStock ? h($outOfStock) : t('Out of Stock') ?>
                        </p>
                        <p class="store-not-available-label alert alert-warning hidden">
                            <?php
                            $notAvailable = $csm->t('Not Available', 'productNotAvailableMessage', $product->getID());
                            ?>
                            <?= $notAvailable ? h($notAvailable) : t('Not Available') ?>
                        </p>
                        <?php
                    }
                    if (count($product->getOptions()) > 0) {
                        ?>
                        <script>
                        <?php
                        $varationData = [];
                        foreach ($variationLookup as $key => $variation) {
                            $product->setVariation($variation);
                            $product->setPriceAdjustment(0);
                            $imgObj = $product->getImageObj();
                            $thumb = $imgObj ? $communityStoreImageHelper->getThumbnail($imgObj) : false;
                            $varationData[$key] = [
                                'price' => $product->getPrice(),
                                'salePrice' => $product->getSalePrice(),
                                'available' => $variation->isSellable(),
                                'disabled' => $variation->getVariationDisabled(),
                                'maxCart' => $variation->getMaxCartQty(),
                                'imageThumb' => $thumb ? $thumb->src : '',
                                'image' => $imgObj ? $imgObj->getRelativePath() : '',
                                'sku' => $variation->getVariationSKU(),
                                'saleTemplate'=>'<span class="store-sale-price"></span>&nbsp;' . t('was') . '&nbsp;<span class="store-original-price"></span>'
                            ];
                            if ($isWholesale){
                                $varationData[$key]['price'] = $product->getWholesalePrice();
                            }
                        }
                        ?>
                        var variationData = variationData || [];
                        variationData[<?= $product->getID() ?>] = <?= json_encode($varationData) ?>;
                        </script>
                        <?php
                    }
                    if ($displayMode == 'list') {
                        ?>
                            </div>
                        </div>
                        <?php
                    }
                    ?>
                </form>
            </div>
            <?php
            if ($productIndex % $productsPerRow == 0 && $productIndex < count($products)) {
                echo "</div>";
                if ($displayMode == 'list') {
                    echo '<hr class="store-product-divider">';
                }
                echo '<div class="store-product-list row store-product-list-per-row-' . $productsPerRow . '">';
            }
            ++$productIndex;
        }
        ?>
    </div>
    <?php
    if ($showPagination && $paginator->getTotalPages() > 1) {
        ?>
        <div class="row">
            <?= $pagination ?>
        </div>
        <?php
    }
    ?>
</div>
