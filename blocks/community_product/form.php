<?php defined('C5_EXECUTE') or die("Access Denied."); ?>
<style type="text/css">
    #product-search { position: relative; }
    #product-search-results { position: absolute; z-index: 2; display: none; top: 57px; padding: 10px 20px;background: #fff; width: 100%; height: 90px; overflow-y: scroll; border: 1px solid #ccc; box-shadow: 0 0 10px #ccc; }
    #product-search-results.active { display: block; }
        #product-search-results ul { padding: 0; }
        #product-search-results ul li { list-style: none; padding: 2px 5px; cursor: pointer; }
        #product-search-results ul li:hover { background: #0088ff; color: #fff; }
</style>
<script type="text/javascript">
$(function(){
    //search accounts
    $("input#productSearch").on("keyup", function(e) {

        // Set Search String
        var searchString = $(this).val();
    
        // Do Search
        if(searchString.length > 0){
            $("#product-search-results").addClass("active");
            $.ajax({
                type: "post",
                url: "<?=URL::to('/productfinder')?>",
                data: {query: searchString},
                success: function(html){
                    $("ul#results-list").html(html);
                    $("#product-search-results ul li").click(function(){
                        var pID = $(this).attr('data-product-id'); 
                        var productName = $(this).text();
                        $("#pID").val(pID);
                        $("#product-search-results").removeClass("active");
                        $('#productSearch').val('');
                        $("#selected-product").html(productName);
                    });
                    $("*:not(#product-search-results ul li)").click(function(){
                        $("#product-search-results").removeClass("active");
                    })
                }
            });
            
        }
        else{
            $("#product-search-results").removeClass("active");
        }
    });
    
    
});  
</script>

<legend><?= t("Product")?></legend>

<div class="form-group">
    <?= $form->label('productLocation', t('Product'))?>
    <?= $form->select('productLocation', array('page' => t('Find product associated with this page'), 'search' => t('Search and select product')), $productLocation, array('onChange' => 'updateProductLocation();'))?>
</div>

<div class="form-group" id="product-search">
    <?= $form->label('productSearch', 'Search for a product')?>
    <?= $form->text('productSearch')?>
    <?= $form->hidden('pID', $pID)?>
    <div id="product-search-results">
        <ul id="results-list">
            
        </ul>
    </div>
    <div class="alert alert-info">
        <strong><?= t("Selected Product:")?></strong>
        <span id="selected-product"></span>
    </div>
   
</div>

<legend><?= t("Display Options")?></legend>

<div class="row">
    <div class="col-xs-6">
        <div class="checkbox">
            <label>
                <?= $form->checkbox('showProductName', 1, !isset($showProductName) ? true : $showProductName);?>
                <?= t('Show Product Name')?>
            </label>
        </div>    
        <div class="checkbox">
            <label>
                <?= $form->checkbox('showProductDescription', 1, !isset($showProductDescription) ? true : $showProductDescription);?>
                <?= t('Show Short Description')?>
            </label>
        </div> 
        <div class="checkbox">
            <label>
                <?= $form->checkbox('showProductDetails', 1, !isset($showProductDetails) ? true : $showProductDetails);?>
                <?= t('Show Product Details')?>
            </label>
        </div>   
        <div class="checkbox">
            <label>
                <?= $form->checkbox('showProductPrice', 1, !isset($showProductPrice) ? true : $showProductPrice);?>
                <?= t('Show Price')?>
            </label>
        </div>
        <div class="checkbox">
            <label>
                <?= $form->checkbox('showWeight', 1, $showWeight);?>
                <?= t('Show Weight')?>
            </label>
        </div>
    </div>
    <div class="col-xs-6">
        <div class="checkbox">
            <label>
                <?= $form->checkbox('showImage', 1, !isset($showImage) ? true : $showImage);?>
                <?= t('Show Product Image')?>
            </label>
        </div>
        <div class="checkbox">
            <label>
                <?= $form->checkbox('showCartButton', 1, !isset($showCartButton) ? true : $showCartButton);?>
                <?= t('Show "Add to Cart" Button')?>
            </label>
        </div>
        <div class="checkbox">
            <label>
                <?= $form->checkbox('showIsFeatured', 1, $showIsFeatured);?>
                <?= t('Show if featured')?>
            </label>
        </div>
        <div class="checkbox">
            <label>
                <?= $form->checkbox('showGroups', 1, !isset($showGroups) ? true : $showGroups);?>
                <?= t('Show Product Groups')?>
            </label>
        </div>
        <div class="checkbox">
            <label>
                <?= $form->checkbox('showDimensions', 1, $showDimensions);?>
                <?= t('Show Dimensions')?>
            </label>
        </div>
        
    </div>
</div>    

<div class="form-group">
    <?= $form->label('btnText', t("Add to Cart Button Text"))?>
    <?= $form->text('btnText', $btnText, array('placeholder'=>t('Add To Cart')))?>
</div>


<script type="text/javascript">
    function updateProductLocation(){
        if ( $("#productLocation").val() == "page" ) {
            $("#product-search").hide();
        } else {
            $("#product-search").show();
        }
    };
    updateProductLocation();
</script>