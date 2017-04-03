<?php
defined('C5_EXECUTE') or die(_("Access Denied."));
use \Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductVariation\ProductVariation as StoreProductVariation;
$c = Page::getCurrentPage();

if($products){
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
    ?>


    <?php if ($showSortOption) { ?>
    <div class="store-product-list-sort row">
        <div class="col-md-12 form-inline text-right pull-right">
            <div class="form-group">
                <?= $form->label('sort' . $bID,  t('Sort by')); ?>
                <?= $form->select('sort' . $bID,
                    array(
                    '0' => '',
                    'price_asc' => t('price, lowest to highest'),
                    'price_desc' => t('price, highest to lowest'),
                    ));
                ?>
            </div>
        </div>
    </div>

    <script type="text/javascript">
    $(function () {
        $('#sort<?= $bID;?>').change(function(){
            var sortstring = '<?php echo \Core::make('helper/url')->setVariable(array('sort'. $bID=>'%sort%'));?>';
            window.location.href=  sortstring.replace('%sort%', $(this).val());
        });
    });
    </script>
    <?php } ?>


    <?php

    echo '<div class="store-product-list row store-product-list-per-row-'. $productsPerRow .'">';

    $i=1;
    
    foreach($products as $product){
        $options = $product->getOptions();

        if ($product->hasVariations()) {
            $variations = StoreProductVariation::getVariationsForProduct($product);
            $variationLookup = array();

            if (!empty($variations)) {
                foreach ($variations as $variation) {
                    // returned pre-sorted
                    $ids = $variation->getOptionItemIDs();
                    $variationLookup[implode('_', $ids)] = $variation;
                }
            }
        }
        // This determines which is the first available (not out of stock) option
        $firstAvailableVariation = false;
        if (count($variations)) {
            $availableOptionsids = false;
            foreach ($variations as $variation) {
                $isAvailable = false;
                if ($variation->isSellable()) {
                    $variationOptions = $variation->getOptions();

                    foreach ($variationOptions as $variationOption) {
                        $opt = $variationOption->getOption();
                        if ($opt->isHidden()) {
                            $isAvailable = false;
                            break;
                        } else {
                            $isAvailable = true;
                        }
                    }
                    if ($isAvailable) {
                        $availableOptionsids = $variation->getOptionItemIDs();
                        $firstAvailableVariation = $variation;
                        break;
                    }
                }
            }
        }
        
        $isSellable = (!$firstAvailableVariation && !$product->isSellable()) ? false : true;
 
        //this is done so we can get a type of active class if there's a product list on the product page
        if($c->getCollectionID()==$product->getPageID()){
            $activeclass =  'on-product-page';
        }

    ?>
    
        <div class="store-product-list-item <?= $columnClass; ?> <?= $activeclass; ?>">
            <form   id="store-form-add-to-cart-list-<?= $product->getID()?>" data-product-id="<?= $product->getID() ?>">
		<?php if ($showName) { ?>
                <h2 class="store-product-list-name"><?= $product->getName()?></h2>
		<?php } ?>
                <?php 
                    $imgObj = $product->getImageObj();
                    if(is_object($imgObj)){
                        $thumb = $ih->getThumbnail($imgObj,400,280,true);?>
                        <p class="store-product-list-thumbnail">
                            <?php if($showQuickViewLink){ ?>
                                <a class="store-product-quick-view" data-product-id="<?= $product->getID()?>" href="#">
                                    <img src="<?= $thumb->src?>" class="img-responsive">
                                </a>
                            <?php } elseif ($showPageLink) { ?>
                                <a href="<?= \URL::to(Page::getByID($product->getPageID()))?>">
                                    <img src="<?= $thumb->src?>" class="img-responsive">
                                </a>
                            <?php } else { ?>
                                <img src="<?= $thumb->src?>" class="img-responsive">
                            <?php } ?>
                        </p>
                <?php
                    }// if is_obj
                ?>
                <?php if ($showPrice) { ?>
                <p class="store-product-list-price">
		            <?php
                        $salePrice = !$firstAvailableVariation ? $product->getSalePrice() : $firstAvailableVariation->getVariationSalePrice();
		                if(isset($salePrice) && $salePrice != ""){
                            $formattedSalePrice = !$firstAvailableVariation ? $product->getFormattedSalePrice() : $firstAvailableVariation->getFormattedVariationSalePrice();
                            $formattedOriginalPrice = !$firstAvailableVariation ? $product->getFormattedOriginalPrice() : $firstAvailableVariation->getFormattedVariationPrice();
		                    echo '<span class="store-sale-price">'.$formattedSalePrice.'</span>';
		                    echo ' ' . t('was') . ' ' . '<span class="store-original-price">'.$formattedOriginalPrice.'</span>';
		                } else {
                            $formattedPrice = !$firstAvailableVariation ? $product->getFormattedPrice() : $firstAvailableVariation->getFormattedVariationPrice();
		                    echo $formattedPrice; 
		                }
		            ?>
                </p>
                <?php } ?>

                <?php if ($product->allowCustomerPrice()) { ?>
                    <div class="store-product-customer-price-entry form-group">
                        <?php
                        $pricesuggestions = $product->getPriceSuggestionsArray();
                        if (!empty($pricesuggestions)) { ?>
                            <p class="store-product-price-suggestions"><?php
                                foreach($pricesuggestions as $suggestion) { ?>
                                    <a href="#" class="store-price-suggestion btn btn-default btn-sm" data-suggestion-value="<?= $suggestion; ?>" data-add-type="list"><?= Config::get('community_store.symbol') . $suggestion;?></a>
                                <?php } ?>
                            </p>
                            <label for="customerPrice" class="store-product-customer-price-label"><?= t('Enter Other Amount') ?></label>
                        <?php } else { ?>
                            <label for="customerPrice" class="store-product-customer-price-label"><?= t('Amount') ?></label>
                        <?php } ?>
                        <?php $min = $product->getPriceMinimum(); ?>
                        <?php $max = $product->getPriceMaximum(); ?>
                        <div class="input-group col-md-6 col-sm-6 col-xs-6">
                            <div class="input-group-addon"><?= Config::get('community_store.symbol');?></div>
                            <input type="number" <?= $min ? 'min="'.$min.'"' : ''; ?>  <?= $max ? 'max="'.$max.'"' : ''; ?>class="store-product-customer-price-entry-field form-control" value="<?= $product->getPrice(); ?>" name="customerPrice" />
                        </div>
                        <?php if ($min >=0 || $max > 0) { ?>
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
                                    }
                                    ?>
                                    </span>
                        <?php } ?>
                    </div>
                <?php } ?>


                <?php if($showDescription){ ?>
                <div class="store-product-list-description"><?= $product->getDesc()?></div>
                <?php } ?>
                <?php if($showPageLink){?>
                <p class="store-btn-more-details-container"><a href="<?= \URL::to(Page::getByID($product->getPageID()))?>" class="store-btn-more-details btn btn-default"><?= ($pageLinkText ? $pageLinkText : t("More Details"))?></a></p>
                <?php } ?>
                <?php if($showAddToCart){ ?>

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
                        $optionType = $option->getType();
                        $required = $option->getRequired();

                        $requiredAttr = '';

                        if ($required) {
                            $requiredAttr = ' required="required" placeholder="'.t('Required').'" ';
                        }
                        ?>

                        <?php if (!$optionType || $optionType == 'select') { ?>
                            <div class="store-product-option-group form-group <?= $option->getHandle() ?>">
                                <label class="store-product-option-group-label"><?= $option->getName() ?></label>
                                <select class="store-product-option form-control" name="po<?= $option->getID() ?>">
                                    <?php
                                    $firstAvailableVariation = false;
                                    $variation = false;
                                    foreach ($optionItems as $optionItem) {
                                        if (!$optionItem->isHidden()) {
                                           $variation = $variationLookup[$optionItem->getID()];
                                            if (!empty($variation)) {
                                                $firstAvailableVariation = (!$firstAvailableVariation && $variation->isSellable()) ? $variation : $firstAvailableVariation;
                                                $disabled = $variation->isSellable() ? '' : 'disabled="disabled" ';
                                                $outOfStock = $variation->isSellable() ? '' : ' ('.t('out of stock').')';
                                            }
                                            $selected = '';
                                            if (is_array($availableOptionsids) && in_array($optionItem->getID(), $availableOptionsids)) {
                                                $selected = 'selected="selected"';
                                            }
                                            ?>
                                            <option <?= $disabled . ' ' . $selected ?>value="<?= $optionItem->getID() ?>"><?= $optionItem->getName().$outOfStock ?></option>
                                        <?php }
                                        // below is an example of a radio button, comment out the <select> and <option> tags to use instead
                                        // Make sure to add the $disabled and $selected variables here and make $selected use "checked" instead
                                        //echo '<input type="radio" name="po'.$option->getID().'" value="'. $optionItem->getID(). '" />' . $optionItem->getName() . '<br />'; ?>
                                    <?php } ?>
                                </select>
                            </div>
                        <?php } elseif ($optionType == 'text') { ?>
                            <div class="store-product-option-group form-group <?= $option->getHandle() ?>">
                                <label class="store-product-option-group-label"><?= $option->getName() ?></label>
                                <input class="store-product-option-entry form-control" <?= $requiredAttr; ?> name="pt<?= $option->getID() ?>" />
                            </div>
                        <?php } elseif ($optionType == 'textarea') { ?>
                            <div class="store-product-option-group form-group <?= $option->getHandle() ?>">
                                <label class="store-product-option-group-label"><?= $option->getName() ?></label>
                                <textarea class="store-product-option-entry form-control" <?= $requiredAttr; ?> name="pa<?= $option->getID() ?>"></textarea>
                            </div>
                        <?php } elseif ($optionType == 'checkbox') { ?>
                            <div class="store-product-option-group form-group <?= $option->getHandle() ?>">
                                <label class="store-product-option-group-label">
                                    <input type="hidden" value="<?= t('No'); ?>" class="store-product-option-checkbox-hidden <?= $option->getHandle() ?>" name="pc<?= $option->getID() ?>" />
                                    <input type="checkbox" value="<?= t('Yes'); ?>" class="store-product-option-checkbox <?= $option->getHandle() ?>" name="pc<?= $option->getID() ?>" /> <?= $option->getName() ?></label>
                            </div>
                        <?php } elseif ($optionType == 'hidden') { ?>
                            <input type="hidden" class="store-product-option-hidden <?= $option->getHandle() ?>" name="ph<?= $option->getID() ?>" />
                        <?php } ?>
                    <?php } ?>


                <input type="hidden" name="pID" value="<?= $product->getID()?>">

                <p class="store-btn-add-to-cart-container"><button data-add-type="list" data-product-id="<?= $product->getID()?>" class="store-btn-add-to-cart btn btn-primary <?= ($isSellable ? '' : 'hidden');?> "><?=  ($btnText ? h($btnText) : t("Add to Cart"))?></button></p>
                <p class="store-out-of-stock-label alert alert-warning <?= ($isSellable ? 'hidden' : '');?>"><?= t("Out of Stock")?></p>

                <?php } ?>

                <?php if ($product->hasVariations() && !empty($variationLookup)) {?>
                    <script>
                        $(function() {
                            <?php
                            $varationData = array();
                            foreach($variationLookup as $key=>$variation) {
                                $product->setVariation($variation);

                                $imgObj = $product->getImageObj();

                                if ($imgObj) {
                                    $thumb = Core::make('helper/image')->getThumbnail($imgObj,400,280,true);
                                }

                                $varationData[$key] = array(
                                'price'=>$product->getFormattedOriginalPrice(),
                                'saleprice'=>$product->getFormattedSalePrice(),
                                'available'=>($variation->isSellable()),
                                'imageThumb'=>$thumb ? $thumb->src : '',
                                'image'=>$imgObj ? $imgObj->getRelativePath() : '');

                            } ?>


                            $('#store-form-add-to-cart-list-<?= $product->getID()?> select').change(function(){
                                var variationdata = <?= json_encode($varationData); ?>;
                                var ar = [];

                                $('#store-form-add-to-cart-list-<?= $product->getID()?> select').each(function(){
                                    ar.push($(this).val());
                                });

                                ar.sort(communityStore.sortNumber);

                                var pli = $(this).closest('.store-product-list-item');

                                if (variationdata[ar.join('_')]['saleprice']) {
                                    var pricing =  '<span class="store-sale-price">'+ variationdata[ar.join('_')]['saleprice']+'</span>' +
                                        ' <?= t('was');?> ' + '<span class="store-original-price">' + variationdata[ar.join('_')]['price'] +'</span>';

                                    pli.find('.store-product-list-price').html(pricing);

                                } else {
                                    pli.find('.store-product-list-price').html(variationdata[ar.join('_')]['price']);
                                }

                                if (variationdata[ar.join('_')]['available']) {
                                    pli.find('.store-out-of-stock-label').addClass('hidden');
                                    pli.find('.store-btn-add-to-cart').removeClass('hidden');
                                } else {
                                    pli.find('.store-out-of-stock-label').removeClass('hidden');
                                    pli.find('.store-btn-add-to-cart').addClass('hidden');
                                }

                                if (variationdata[ar.join('_')]['imageThumb']) {
                                    var image = pli.find('.store-product-list-thumbnail img');

                                    if (image) {
                                        image.attr('src', variationdata[ar.join('_')]['imageThumb']);

                                    }
                                }

                            });
                        });
                    </script>
                <?php } ?>

            </form><!-- .product-list-item-inner -->
        </div><!-- .product-list-item -->
        
        <?php 
            if($i%$productsPerRow==0){
                echo "</div>";
                echo '<div class="store-product-list row store-product-list-per-row-'. $productsPerRow .'">';
            }
        
        $i++;
    
    }// foreach    
    echo "</div><!-- .product-list -->";
    
    if($showPagination){
        if ($paginator->getTotalPages() > 1) {
            echo '<div class="row">';
            echo $pagination;
            echo '</div>';
        }
    }
    
} //if products
elseif (is_object($c) && $c->isEditMode()) { ?>
    <div class="ccm-edit-mode-disabled-item"><?= t("Empty Product List")?></div>
<?php } ?>
