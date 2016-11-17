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

        //this is done so we can get a type of active class if there's a product list on the product page
        if($c->getCollectionID()==$product->getPageID()){
            $activeclass =  'on-product-page';
        }

    ?>
    
        <div class="store-product-list-item <?= $columnClass; ?> <?= $activeclass; ?>">
            <form   id="store-form-add-to-cart-list-<?= $product->getID()?>">
                <h2 class="store-product-list-name"><?= $product->getName()?></h2>
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
                        $salePrice = $product->getSalePrice();
                        if(isset($salePrice) && $salePrice != ""){
                            echo '<span class="sale-price">'.$product->getFormattedSalePrice().'</span>';
                            echo ' ' . t('was') . ' ' . '<span class="original-price">'.$product->getFormattedOriginalPrice().'</span>';
                        } else {
                            echo $product->getFormattedPrice();
                        }
                    ?>
                </p>
                <?php } ?>
                <?php if($showDescription){ ?>
                <div class="store-product-list-description"><?= $product->getDesc()?></div>
                <?php } ?>
                <?php if($showPageLink){?>
                <p class="store-btn-more-details-container"><a href="<?= \URL::to(Page::getByID($product->getPageID()))?>" class="store-btn-more-details btn btn-default"><?= ($pageLinkText ? $pageLinkText : t("More Details"))?></a></p>
                <?php } ?>
                <?php if($showAddToCart){ ?>

                <?php if ($product->allowQuantity() && $showQuantity) { ?>
                    <div class="store-product-quantity form-group <?= $option->getHandle() ?>">
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
                                    foreach ($optionItems as $optionItem) {
                                        if (!$optionItem->isHidden()) { ?>
                                            <option value="<?= $optionItem->getID() ?>"><?= $optionItem->getName() ?></option>
                                        <?php }
                                        // below is an example of a radio button, comment out the <select> and <option> tags to use instead
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
                        <?php } elseif ($optionType == 'hidden') { ?>
                            <input type="hidden" class="store-product-option-hidden <?= $option->getHandle() ?>" name="ph<?= $option->getID() ?>" />
                        <?php } ?>
                    <?php } ?>


                <input type="hidden" name="pID" value="<?= $product->getID()?>">


                <p class="store-btn-add-to-cart-container"><button data-add-type="list" data-product-id="<?= $product->getID()?>" class="store-btn-add-to-cart btn btn-primary <?= ($product->isSellable() ? '' : 'hidden');?> "><?=  ($btnText ? h($btnText) : t("Add to Cart"))?></button></p>
                <p class="store-out-of-stock-label alert alert-warning <?= ($product->isSellable() ? 'hidden' : '');?>"><?= t("Out of Stock")?></p>

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
                                })

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
    <p class="alert alert-info"><?= t("Empty Product List")?></p>
<?php } ?>
