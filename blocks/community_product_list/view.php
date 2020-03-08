<?php
defined('C5_EXECUTE') or die("Access Denied.");

// We want to crop the image so we're providing an object
// with the crop property set to true since default is false
$legacyThumbProps = new \stdClass();
$legacyThumbProps->crop = true;
$communityStoreImageHelper = $app->make('cs/helper/image', ['product_list', null, $legacyThumbProps]);
$csm = $app->make('cs/helper/multilingual');

$c = \Concrete\Core\Page\Page::getCurrentPage();


$columnClass = 'col-md-12';

if ($productsPerRow == 2) {
    $columnClass = 'col-md-6';
}

if ($productsPerRow == 3) {
    $columnClass = 'col-md-4';
}

if ($productsPerRow == 4) {
    $columnClass = 'col-md-3';
}

if ($productsPerRow == 6) {
    $columnClass = 'col-md-2';
}

$isWholesale = \Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Wholesale::isUserWholesale();

?>

<div class="store-product-list-block">
    <?php if ($products) { ?>

        <?php if ($showSortOption) {
        ?>
        <div class="store-product-list-sort row">
            <div class="col-md-12 form-inline text-right pull-right">
                <div class="form-group">
                    <?= $form->label('sort' . $bID, t('Sort by')); ?>
                    <?= $form->select('sort' . $bID,
                        [
                            '0' => '',
                            'price_asc' => t('price, lowest to highest'),
                            'price_desc' => t('price, highest to lowest'),
                        ]); ?>
                </div>
            </div>
        </div>

        <script type="text/javascript">
            $(function () {
                $('#sort<?= $bID; ?>').change(function () {
                    var sortstring = '<?= $app->make('helper/url')->setVariable(['sort' . $bID => '%sort%']); ?>';
                    window.location.href = sortstring.replace('%sort%', $(this).val());
                });
            });
        </script>
    <?php
    } ?>


    <?php

    echo '<div class="store-product-list row store-product-list-per-row-' . $productsPerRow . '">';

    $i = 1;

    foreach ($products as $product) {
        $options = $product->getOptions();

        $variationLookup = $product->getVariationLookup();
        $variationData = $product->getVariationData();
        $availableOptionsids = $variationData['availableOptionsids'];
        $firstAvailableVariation = $variationData['firstAvailableVariation'];

        if ($firstAvailableVariation) {
            $product = $firstAvailableVariation;
        } else {
            $product->setInitialVariation();
        }

        $product->setAdjustment($variationData['priceAdjustment']);

        $isSellable = $product->isSellable();

        //this is done so we can get a type of active class if there's a product list on the product page
        if ($c->getCollectionID() == $product->getPageID()) {
            $activeclass = 'on-product-page';
        }

        $productPage = $product->getProductPage();

        if (!$productPage || $productPage->isError() || $productPage->isInTrash()) {
            $productPage = false;
        } ?>

            <div class="store-product-list-item <?= $columnClass; ?> <?= $activeclass; ?>">
                <form id="store-form-add-to-cart-list-<?= $product->getID(); ?>" data-product-id="<?= $product->getID(); ?>">
                    <?= $token->output('community_store'); ?>
                    <?php if ($showName) {
                        ?>
                        <h2 class="store-product-list-name"><?= $csm->t($product->getName(), 'productName', $product->getID()); ?></h2>
                        <?php
                    } ?>
                    <?php
                    $imgObj = $product->getImageObj();
                    if (is_object($imgObj)) {
                        $thumb = $communityStoreImageHelper->getThumbnail($imgObj); ?>
                        <p class="store-product-list-thumbnail">
                            <?php if ($showQuickViewLink) {
                                ?>
                                <a class="store-product-quick-view" data-product-id="<?= $product->getID(); ?>" data-locale="<?= $locale; ?>" href="#">
                                    <img src="<?= $thumb->src; ?>" class="img-responsive">
                                </a>
                                <?php
                            } elseif ($showPageLink && $productPage) {
                                ?>
                                <a href="<?= \Concrete\Core\Support\Facade\Url::to($productPage); ?>">
                                    <img src="<?= $thumb->src; ?>" class="img-responsive">
                                </a>
                                <?php
                            } else {
                                ?>
                                <img src="<?= $thumb->src; ?>" class="img-responsive">
                                <?php
                            } ?>
                        </p>
                        <?php
                    }// if is_obj?>
                    <?php if ($showPrice && !$product->allowCustomerPrice()) {
                        $salePrice = $product->getSalePrice();
                        $price = $product->getPrice();

                        $activePrice = ($salePrice ? $salePrice : $price ) - $product->getAdjustment();
                        ?>
                        <p class="store-product-list-price" data-price="<?= $activePrice; ?>">
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
                                        <a href="#" class="store-price-suggestion btn btn-default btn-sm" data-suggestion-value="<?= $suggestion; ?>" data-add-type="list"><?= Config::get('community_store.symbol') . $suggestion; ?></a>
                                        <?php
                                    } ?>
                                </p>
                                <label for="customerPrice" class="store-product-customer-price-label"><?= t('Enter Other Amount'); ?></label>
                                <?php
                            } else {
                                ?>
                                <label for="customerPrice" class="store-product-customer-price-label"><?= t('Amount'); ?></label>
                                <?php
                            } ?>
                            <?php $min = $product->getPriceMinimum(); ?>
                            <?php $max = $product->getPriceMaximum(); ?>
                            <div class="input-group col-md-6 col-sm-6 col-xs-6">
                                <div class="input-group-addon"><?= Config::get('community_store.symbol'); ?></div>
                                <input type="number" <?= $min ? 'min="' . $min . '"' : ''; ?>  <?= $max ? 'max="' . $max . '"' : ''; ?> step="0.01" class="store-product-customer-price-entry-field form-control" value="<?= $product->getPrice(); ?>" name="customerPrice"/>
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


                    <?php if ($showDescription) {
                        ?>
                        <div class="store-product-list-description"><?= $csm->t($product->getDesc(), 'productDescription', $product->getID()); ?></div>
                        <?php
                    } ?>
                    <?php if ($showPageLink && $productPage) {
                        ?>
                        <p class="store-btn-more-details-container"><a href="<?= \Concrete\Core\Support\Facade\Url::to($productPage); ?>" class="store-btn-more-details btn btn-default"><?= ($pageLinkText ? $pageLinkText : t("More Details")); ?></a></p>
                        <?php
                    } ?>
                    <?php if ($showAddToCart) {
                        ?>
                    <div class="store-product-options">
                        <?php if ($product->allowQuantity() && $showQuantity) {
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
                                        <input type="number" name="quantity" class="store-product-qty form-control" min="<?= $product->getQtySteps() ? $product->getQtySteps() : 0.001; ?>" step="<?= $product->getQtySteps() ? $product->getQtySteps() : 0.001; ?>" <?= ($max ? 'max="' . $max . '"' : ''); ?>>
                                        <?php
                                    } else {
                                        ?>
                                        <input type="number" name="quantity" class="store-product-qty form-control" value="1" min="1" step="1" <?= ($max ? 'max="' . $max . '"' : ''); ?>>
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
                                <div class="store-product-option-group form-group <?= $option->getHandle(); ?>">
                                    <label class="store-product-option-group-label"><?= h($csm->t($option->getName(), 'optionName', $product->getID(), $option->getID())); ?></label>

                                    <?php if ($details) { ?>
                                        <span class="store-product-option-help-text help-block"><?= h($csm->t($details, 'optionDetails', $product->getID(), $option->getID())); ?></span>
                                    <?php } ?>

                                    <?php if ($displayType != 'radio') { ?>
                                    <select class="store-product-option <?= $option->getIncludeVariations() ? 'store-product-variation' : ''; ?> form-control" name="po<?= $option->getID(); ?>">
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
                                                        <label><input type="radio" required class="store-product-option <?= $option->getIncludeVariations() ? 'store-product-variation' : '' ?> "
                                                                <?= $disabled . ($selected ? 'checked' : ''); ?>
                                                                      name="po<?= $option->getID(); ?>"
                                                                      value="<?= $optionItem->getID(); ?>"
                                                                      data-adjustment="<?= (float)$optionItem->getPriceAdjustment(); ?>"
                                                            /><?= h($csm->t($optionLabel, $translateHandle, $product->getID(), $optionItem->getID())) . $outOfStock; ?>

                                                        </label>
                                                    </div>
                                                <?php } else { ?>
                                                    <option <?= $disabled . ' ' . $selected; ?>
                                                            value="<?= $optionItem->getID(); ?>"
                                                            data-adjustment="<?= (float)$optionItem->getPriceAdjustment(); ?>"
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
                            } elseif ($optionType == 'text' ) {
                                ?>
                                <div class="store-product-option-group form-group <?= $option->getHandle(); ?>">
                                    <label class="store-product-option-group-label"><?= h($csm->t($option->getName(), 'optionName', $product->getID(), $option->getID())); ?></label>

                                    <?php if ($details) { ?>
                                        <span class="store-product-option-help-text help-block"><?= h($csm->t($details, 'optionDetails', $product->getID(), $option->getID())); ?></span>
                                    <?php } ?>

                                    <input class="store-product-option-entry form-control" <?= $requiredAttr; ?> name="pt<?= $option->getID(); ?>"/>
                                </div>
                                <?php
                            } elseif ($optionType == 'textarea') {
                                ?>
                                <div class="store-product-option-group form-group <?= $option->getHandle(); ?>">
                                    <label class="store-product-option-group-label"><?= h($csm->t($option->getName(), 'optionName', $product->getID(), $option->getID())); ?></label>

                                    <?php if ($details) { ?>
                                        <span class="store-product-option-help-text help-block"><?= h($csm->t($details, 'optionDetails', $product->getID(), $option->getID())); ?></span>
                                    <?php } ?>

                                    <textarea class="store-product-option-entry form-control" <?= $requiredAttr; ?> name="pa<?= $option->getID(); ?>"></textarea>
                                </div>
                                <?php
                            } elseif ($optionType == 'checkbox') {
                                ?>
                                <div class="store-product-option-group form-group <?= $option->getHandle(); ?>">
                                    <label class="store-product-option-group-label">
                                        <input type="hidden" value="<?= t('no'); ?>" class="store-product-option-checkbox-hidden <?= $option->getHandle(); ?>" name="pc<?= $option->getID(); ?>"/>
                                        <input type="checkbox" value="<?= t('yes'); ?>" class="store-product-option-checkbox <?= $option->getHandle(); ?>" name="pc<?= $option->getID(); ?>"/> <?= h($csm->t($option->getName(), 'optionName', $product->getID(), $option->getID())); ?></label>

                                    <?php if ($details) { ?>
                                        <span class="store-product-option-help-text help-block"><?= h($csm->t($details, 'optionDetails', $product->getID(), $option->getID())); ?></span>
                                    <?php } ?>

                                </div>
                                <?php
                            } elseif ($optionType == 'hidden') {
                                ?>
                                <input type="hidden" class="store-product-option-hidden <?= $option->getHandle(); ?>" name="ph<?= $option->getID(); ?>"/>
                                <?php
                            } elseif ($optionType == 'static') {
                            ?>
                                <div class="store-product-option-static">
                                    <?= $csm->t($details, 'optionDetails', $product->getID(), $option->getID()); ?>
                                </div>
                                <?php
                        } ?>
                            <?php
                        } ?>
                        </div>

                        <input type="hidden" name="pID" value="<?= $product->getID(); ?>">

                        <p class="store-btn-add-to-cart-container">
                            <button data-add-type="list" data-product-id="<?= $product->getID(); ?>" class="store-btn-add-to-cart btn btn-primary <?= ($isSellable ? '' : 'hidden'); ?> "><?= ($btnText ? h($btnText) : t("Add to Cart")); ?></button>
                        </p>
                        <p class="store-out-of-stock-label alert alert-warning <?= ($isSellable ? 'hidden' : ''); ?>"><?= t("Out of Stock"); ?></p>

                        <?php
                    } ?>

                    <?php if (count($product->getOptions()) > 0) {
                        ?>
                        <script>
                            $(function () {
                                <?php
                                $varationData = [];
                                foreach ($variationLookup as $key => $variation) {
                                    $product->setVariation($variation);

                                    $imgObj = $product->getImageObj();

                                    if ($imgObj) {
                                        $thumb = $communityStoreImageHelper->getThumbnail($imgObj);
                                    }

                                    $varationData[$key] = [
                                        'price' => $product->getPrice(),
                                        'saleprice' => $product->getSalePrice(),
                                        'available' => ($variation->isSellable()),
                                        'imageThumb' => $thumb ? $thumb->src : '',
                                        'image' => $imgObj ? $imgObj->getRelativePath() : '',];

                                    if($isWholesale){
                                        $varationData[$key]['price'] = $product->getFormattedWholesalePrice();
                                    }


                                } ?>


                                $('#store-form-add-to-cart-list-<?= $product->getID(); ?> select, #store-form-add-to-cart-list-<?= $product->getID(); ?> input').change(function () {
                                    var variationData = <?= json_encode($varationData); ?>;
                                    var ar = [];


                                    $('#store-form-add-to-cart-list-<?= $product->getID(); ?> select.store-product-variation, #store-form-add-to-cart-list-<?= $product->getID(); ?> .store-product-variation:checked').each(function () {
                                        ar.push($(this).val());
                                    });

                                    var priceAdjust = 0;

                                    $('#store-form-add-to-cart-list-<?= $product->getID(); ?> select option:selected').each(function(){
                                        priceAdjust += parseFloat($(this).data('adjustment'));
                                    });

                                    ar.sort(communityStore.sortNumber);

                                    var pli = $(this).closest('.store-product-list-item');
                                    var variation = variationData[ar.join('_')];
                                    var priceHolder = pli.find('.store-product-list-price');


                                    if (variation) {
                                        if (variation['saleprice']) {
                                            var pricing = '<span class="store-sale-price">' + variation['saleprice'] + '</span>' +
                                                ' <?= t('was'); ?> ' + '<span class="store-original-price">' + variation['price'] + '</span>';

                                            pli.find('.store-product-list-price').html(pricing);

                                        } else {
                                            pli.find('.store-product-list-price').html(variation['price']);
                                        }


                                        if (variation['available']) {
                                            pli.find('.store-out-of-stock-label').addClass('hidden');
                                            pli.find('.store-btn-add-to-cart').removeClass('hidden');
                                        } else {
                                            pli.find('.store-out-of-stock-label').removeClass('hidden');
                                            pli.find('.store-btn-add-to-cart').addClass('hidden');
                                        }

                                        if (variation['imageThumb']) {
                                            var image = pli.find('.store-product-list-thumbnail img');

                                            if (image) {
                                                image.attr('src', variationData[ar.join('_')]['imageThumb']);

                                            }
                                        }
                                    } else {
                                        var total = parseFloat(priceHolder.data('price')) + priceAdjust;
                                        var result = Intl.NumberFormat('en', { style: 'currency', currency: CURRENCYCODE}).format(total);
                                        priceHolder.html(result);
                                    }

                                });
                            });
                        </script>
                        <?php
                    } ?>

                </form><!-- .product-list-item-inner -->
            </div><!-- .product-list-item -->

        <?php
        if ($i % $productsPerRow ==  0) {
            echo "</div>";
            echo '<div class="store-product-list row store-product-list-per-row-' . $productsPerRow . '">';
        }

        ++$i;
    }// foreach
    echo "</div><!-- .product-list -->";

    if ($showPagination) {
        if ($paginator->getTotalPages() > 1) {
            echo '<div class="row">';
            echo $pagination;
            echo '</div>';
        }
    }

    } elseif (is_object($c) && $c->isEditMode()) {
    ?>
        <div class="ccm-edit-mode-disabled-item"><?= t("Empty Product List"); ?></div>
    <?php } elseif ($noProductsMessage) { ?>
        <p class="alert alert-info"><?= h($noProductsMessage); ?></p>
    <?php } ?>
</div>
