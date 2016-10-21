<?php
defined('C5_EXECUTE') or die(_("Access Denied."));
use \Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductVariation\ProductVariation as StoreProductVariation;
?>
<?php
 if(!empty($grouplist) || !empty($akvList) || $showWidthFilter || $showHeightFilter || $showLengthFilter || $showPriceFilter || $showKeywordFilter){
   $hasFilters = true;
 } else{
   $hasFilters = false;
 }
?>
<div class="<?= $hasFilters ? 'col-sm-3' : ''?>" id="filter-area">
  <form role="form" class="ccm-search-fields" id="filterForm">
    <div class="row filter-container">
      <?php if(!empty($grouplist)): ?>
        <div class="form-group">
          <label for="group-filter">Groups:</label>
          <div class="list-area">
          <?php foreach($grouplist as $group):?>
          <div class="checkbox">
              <label>
                <input name="group-filter[]" type="checkbox" id="group_<?php echo $group->getGroupID(); ?>" value="<?php echo $group->getGroupID();?>" <?php echo in_array($group->getGroupID(),$filters['group-filter']) ? 'checked' : ''; ?>><?php echo $group->getGroupName();?>
              </label>
          </div>
          <?php endforeach;?>
          </div>
        </div>
      <?php endif; ?> <!-- END OF GROUP FILTER -->
      <?php if(!empty($akvList)): ?>
        <div class="form-group">
          <?php foreach($akvList as $id => $akv):?>
            <label for="<?php echo $akv['name']?>"><?php echo $akv['name']?>:</label>
            <div class="list-area">
            <?php foreach($akv['values'] as $key => $val):?>
              <div class="checkbox">
                  <label>
                    <input name="attribute-filter[<?php echo $id; ?>][]" type="checkbox" id="attribute_<?php echo $id; ?>" value="<?php echo $key?>" <?php echo is_array($filters['attribute-filter'][$id]) && in_array($key,$filters['attribute-filter'][$id]) ? 'checked' : ''; ?> ><?php echo $val;?>
                  </label>
              </div>
            <?php endforeach;?>
            </div>
          <?php endforeach;?>
        </div>
      <?php endif; ?> <!-- END OF GROUP FILTER -->
      <?php if ($showWidthFilter) { ?>
        <div class="form-group">
          <label for="width">Width (<?= Config::get('community_store.sizeUnit'); ?>):</label>
          <span id="width-range" class="min-max-values"></span>
          <input type="hidden" id="minwidth-filter" name="minwidth-filter" class="lower-value" value="<?php echo $filters['minWidth']!=null ? $filters['minWidth'] : $minWidth; ?>">
          <input type="hidden" id="maxwidth-filter" name="maxwidth-filter" class="higher-value" value="<?php echo $filters['maxWidth']!=null ? $filters['maxWidth'] : $maxWidth; ?>">
          <div id="slider-width-range" class="slider"></div>
        </div>
      <?php } ?>
      <?php if ($showHeightFilter) { ?>
        <div class="form-group">
          <label for="height">Height (<?= Config::get('community_store.sizeUnit'); ?>):</label>
          <span id="height-range" class="min-max-values"></span>
          <input type="hidden" id="minheight-filter" name="minheight-filter" class="lower-value" value="<?php echo $filters['minHeight']!=null ? $filters['minHeight'] : $minHeight; ?>">
          <input type="hidden" id="maxheight-filter" name="maxheight-filter" class="higher-value" value="<?php echo $filters['maxHeight']!=null ? $filters['maxHeight'] : $maxHeight; ?>">
          <div id="slider-height-range" class="slider"></div>
        </div>
      <?php } ?>
      <?php if ($showLengthFilter) { ?>
        <div class="form-group">
          <label for="length">Length (<?= Config::get('community_store.sizeUnit'); ?>):</label>
          <span id="length-range" class="min-max-values"></span>
          <input type="hidden" id="minlength-filter" name="minlength-filter" class="lower-value" value="<?php echo $filters['minLength']!=null ? $filters['minLength'] : $minLength; ?>">
          <input type="hidden" id="maxlength-filter" name="maxlength-filter" class="higher-value" value="<?php echo $filters['maxLength']!=null ? $filters['maxLength'] : $maxLength; ?>">
          <div id="slider-length-range" class="slider"></div>
        </div>
      <?php } ?>
      <?php if ($showPriceFilter) { ?>
        <div class="form-group">
          <label for="amount">Price (<?=  Config::get('community_store.symbol'); ?>):</label>
          <span id="price-range" class="min-max-values"></span>
          <input type="hidden" id="minprice-filter" name="minprice-filter" class="lower-value" value="<?php echo $filters['minPrice']!=null ? $filters['minPrice'] : $minPrice; ?>">
          <input type="hidden" id="maxprice-filter" name="maxprice-filter" class="higher-value" value="<?php echo $filters['maxPrice']!=null ? $filters['maxPrice'] : $maxPrice; ?>">
          <div id="slider-price-range" class="slider"></div>
        </div>
      <?php } ?>
      <?php if ($showKeywordFilter) { ?>
        <div class="form-group">
          <label for="keywords">Keywords:</label>
            <?= $form->search('keywords', $filters['keywords'], array('placeholder' => t('Search Name, Description or SKU')))?>
        </div>
      <?php } ?>
      <?php if ($hasFilters) { ?>
      <div class="btn-group btn-group-justified" role="group" aria-label="..." style="margin-top : 10px;">
        <div class="btn-group" role="group">
          <button type="submit" class="btn btn-primary"><?= t('Search')?></button>
        </div>
        <div class="btn-group" role="group">
          <input id="reset-button" type="button" class="btn btn-default" value="<?= t('Clear')?>"/>
        </div>
      </div>
      <?php } ?>
    </div>
  </form>
</div>


<div class="<?= $hasFilters ? 'col-sm-9' : 'col-sm-12'?>">
<?php
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
        if(Page::getCurrentPage()->getCollectionID()==$product->getPageID()){
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
                <?php if(is_array($product->getAttributes())) :
                        foreach($product->getAttributes() as $aName => $value){ ?>
                          <div class="store-product-attributes">
                            <strong><?= t($aName) ?>:</strong>
                            <?= $value ?>
                          </div>
                <?php   }
                      endif;
                ?>
                <?php// if ($showDimensions) { ?>
                    <div class="store-product-dimensions">
                        <strong><?= t("Dimensions") ?>:</strong>
                        <?= $product->getDimensions() ?>
                        <?= Config::get('community_store.sizeUnit'); ?>
                    </div>
                <?php// } ?>
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
                foreach($options as $option) {
                    $optionItems = $option->getOptionItems();
                    ?>
                    <?php if (!empty($optionItems)) { ?>
                        <div class="store-product-option-group form-group">
                            <label class="store-option-group-label"><?= $option->getName() ?></label>
                            <select class="form-control" name="po<?= $option->getID() ?>">
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
                }?>

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

                                ar.sort();

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
else { ?>
    <p class="alert alert-info"><?= t("No Products Available")?></p>
<?php } ?>
</div>
<script>
$( function() {
  <?php if ($showPriceFilter) { ?>
  $( "#slider-price-range" ).slider({
    range: true,
    step: 50,
    min: <?php echo $minPrice; ?>,
    max:  <?php echo $maxPrice; ?>,
    values: [ <?php echo $filters['minPrice']!=null ? $filters['minPrice'] : $minPrice; ?>, <?php echo $filters['maxPrice']!=null ? $filters['maxPrice'] : $maxPrice; ?> ],
    slide: function( event, ui ) {
      $( "#price-range" ).text(  ui.values[ 0 ] + " - " + ui.values[ 1 ] );
      $("#minprice-filter").val(ui.values[ 0 ]);
      $("#maxprice-filter").val(ui.values[ 1 ]);
    }
  });
  $( "#price-range" ).text( $( "#slider-price-range" ).slider( "values", 0 ) +
    " - " + $( "#slider-price-range" ).slider( "values", 1 ) );
  <?php } ?>
  <?php if ($showWidthFilter) { ?>
  $( "#slider-width-range" ).slider({
    range: true,
    step: 20,
    min: <?php echo $minWidth; ?>,
    max:  <?php echo $maxWidth; ?>,
    values: [ <?php echo $filters['minWidth']!=null ? $filters['minWidth'] : $minWidth; ?>, <?php echo $filters['maxWidth']!=null ? $filters['maxWidth'] : $maxWidth; ?> ],
    slide: function( event, ui ) {
      $( "#width-range" ).text(  ui.values[ 0 ] + " - " + ui.values[ 1 ] );
      $("#minwidth-filter").val(ui.values[ 0 ]);
      $("#maxwidth-filter").val(ui.values[ 1 ]);
    }
  });
  $( "#width-range" ).text( $( "#slider-width-range" ).slider( "values", 0 ) +
    " - " + $( "#slider-width-range" ).slider( "values", 1 ) );
  <?php } ?>
  <?php if ($showHeightFilter) { ?>
  $( "#slider-height-range" ).slider({
    range: true,
    step: 20,
    min: <?php echo $minHeight; ?>,
    max:  <?php echo $maxHeight; ?>,
    values: [ <?php echo $filters['minHeight']!=null ? $filters['minHeight'] : $minHeight; ?>, <?php echo $filters['maxHeight']!=null ? $filters['maxHeight'] : $maxHeight; ?> ],
    slide: function( event, ui ) {
      $( "#height-range" ).text(  ui.values[ 0 ] + " - " + ui.values[ 1 ] );
      $("#minheight-filter").val(ui.values[ 0 ]);
      $("#maxheight-filter").val(ui.values[ 1 ]);
    }
  });
  $( "#height-range" ).text( $( "#slider-height-range" ).slider( "values", 0 ) +
    " - " + $( "#slider-height-range" ).slider( "values", 1 ) );

  <?php } ?>
  <?php if ($showLengthFilter) { ?>
    $( "#slider-length-range" ).slider({
      range: true,
      step: 20,
      min: <?php echo $minLength; ?>,
      max:  <?php echo $maxLength; ?>,
      values: [ <?php echo $filters['minLength']!=null ? $filters['minLength'] : $minLength; ?>, <?php echo $filters['maxLength']!=null ? $filters['maxLength'] : $maxLength; ?> ],
      slide: function( event, ui ) {
        $( "#length-range" ).text(  ui.values[ 0 ] + " - " + ui.values[ 1 ] );
        $("#minlength-filter").val(ui.values[ 0 ]);
        $("#maxlength-filter").val(ui.values[ 1 ]);
      }
    });
    $( "#length-range" ).text( $( "#slider-length-range" ).slider( "values", 0 ) +
      " - " + $( "#slider-length-range" ).slider( "values", 1 ) );
  <?php } ?>
  <?php if ($hasFilters) { ?>
  $('#reset-button').on('click', function(){
    $(':input').not(':button, :submit, :reset, :hidden, :checkbox, :radio').val('');
    $(':checkbox, :radio').prop('checked', false);
    $('.slider').each(function(){
      var options = $(this).slider( 'option' );
      $(this).slider( 'values', [ options.min, options.max ] );
      $(this).siblings('.lower-value').val(options.min);
      $(this).siblings('.higher-value').val(options.max);
    });
  });
  $('.slider').each(function(){
    $(this).draggable();
  });
  <?php } ?>

} );
</script>
