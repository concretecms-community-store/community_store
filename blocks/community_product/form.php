<?php defined('C5_EXECUTE') or die("Access Denied."); ?>
<legend><?= t("Product"); ?></legend>

<div class="form-group">
    <?= $form->label('productLocation', t('Product')); ?>
    <?= $form->select('productLocation', ['search' => t('A selected product'), 'page' => t('Product associated with this page')], $productLocation, ['onChange' => 'updateProductLocation();']); ?>
</div>

<div class="form-group" id="product-search">
    <?= $form->label('productSearch', 'Search for a product'); ?>
    <input name="pID" id="product-select"  class="select2-select" style="width: 100%" placeholder="<?= t('Select Product'); ?>" />
</div>

<legend><?= t("Display Options"); ?></legend>

<div class="row">
    <div class="col-xs-6">
        <div class="checkbox">
            <label>
                <?= $form->checkbox('showProductName', 1, !isset($showProductName) ? true : $showProductName); ?>
                <?= t('Show Product Name'); ?>
            </label>
        </div>    
        <div class="checkbox">
            <label>
                <?= $form->checkbox('showProductDescription', 1, !isset($showProductDescription) ? true : $showProductDescription); ?>
                <?= t('Show Short Description'); ?>
            </label>
        </div> 
        <div class="checkbox">
            <label>
                <?= $form->checkbox('showProductDetails', 1, !isset($showProductDetails) ? true : $showProductDetails); ?>
                <?= t('Show Product Details'); ?>
            </label>
        </div>   
        <div class="checkbox">
            <label>
                <?= $form->checkbox('showProductPrice', 1, !isset($showProductPrice) ? true : $showProductPrice); ?>
                <?= t('Show Price'); ?>
            </label>
        </div>
        <div class="checkbox">
            <label>
                <?= $form->checkbox('showWeight', 1, $showWeight); ?>
                <?= t('Show Weight'); ?>
            </label>
        </div>
    </div>
    <div class="col-xs-6">
        <div class="checkbox">
            <label>
                <?= $form->checkbox('showImage', 1, !isset($showImage) ? true : $showImage); ?>
                <?= t('Show Product Image'); ?>
            </label>
        </div>
        <div class="checkbox">
            <label>
                <?= $form->checkbox('showCartButton', 1, !isset($showCartButton) ? true : $showCartButton); ?>
                <?= t('Display Add To Cart Button'); ?>
            </label>
        </div>
        <div class="checkbox">
            <label>
                <?= $form->checkbox('showIsFeatured', 1, $showIsFeatured); ?>
                <?= t('Show If Featured'); ?>
            </label>
        </div>
        <div class="checkbox">
            <label>
                <?= $form->checkbox('showDimensions', 1, $showDimensions); ?>
                <?= t('Show Dimensions'); ?>
            </label>
        </div>
        <div class="checkbox">
            <label>
                <?= $form->checkbox('showQuantity', 1, $showQuantity); ?>
                <?= t('Show Quantity Selector'); ?>
            </label>
        </div>
        
    </div>
</div>
<br />
<div class="row">
    <div class="col-xs-12">
        <div class="form-group">
            <?= $form->label('btnText', t("Add to Cart Button Text")); ?>
            <?= $form->text('btnText', $btnText, ['placeholder' => t('Add To Cart')]); ?>
        </div>
    </div>
</div>


<script type="text/javascript">
    function updateProductLocation(){
        if ( $("#productLocation").val() == "page" ) {
            $("#product-search").hide();
        } else {
            $("#product-search").show();
        }
    }
    updateProductLocation();

    $(document).ready(function () {

        $("#product-select").select2({
            ajax: {
                url: "<?= \Concrete\Core\Support\Facade\Url::to('/productfinder'); ?>",
                dataType: 'json',
                quietMillis: 250,
                data: function (term, page) {
                    return {
                        q: term // search term
                    };
                },
                results: function (data) {
                    var results = [];
                    $.each(data, function(index, item){
                        results.push({
                            id: item.pID,
                            text: item.name + (item.SKU ? ' (' + item.SKU + ')' : '')
                        });
                    });
                    return {
                        results: results
                    };
                },
                cache: true
            },
            minimumInputLength: 2,
            initSelection: function(element, callback) {
                callback({id: <?= ($pID ? $pID : 0); ?>, text: '<?= ($product ? $product->getName() : ''); ?>' });
            }
        }).select2('val', []);
    });
</script>