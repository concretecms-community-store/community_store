<?php defined('C5_EXECUTE') or die("Access Denied."); ?>

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
    <div class="col-sm-6">
        <div class="form-check">
            <label>
                <?= $form->checkbox('showProductName', 1, !isset($showProductName) ? true : $showProductName); ?>
                <?= t('Display Product Name'); ?>
            </label>
        </div>

        <div class="form-check">
            <label>
                <?= $form->checkbox('showProductDescription', 1, !isset($showProductDescription) ? true : $showProductDescription); ?>
                <?= t('Display Short Description'); ?>
            </label>
        </div>
        <div class="form-check">
            <label>
                <?= $form->checkbox('showProductDetails', 1, !isset($showProductDetails) ? true : $showProductDetails); ?>
                <?= t('Display Product Details'); ?>
            </label>
        </div>

        <div class="form-check">
            <label>
                <?= $form->checkbox('showManufacturer', 1, !isset($showManufacturer) ? false : $showManufacturer); ?>
                <?= t('Display Manufacturer Name'); ?>
            </label>
        </div>

        <div class="form-check">
            <label>
                <?= $form->checkbox('showManufacturerDescription', 1, !isset($showManufacturerDescription) ? false : $showManufacturerDescription); ?>
                <?= t('Display Manufacturer Description'); ?>
            </label>
        </div>

        <div class="form-check">
            <label>
                <?= $form->checkbox('showProductPrice', 1, !isset($showProductPrice) ? true : $showProductPrice); ?>
                <?= t('Display Price'); ?>
            </label>
        </div>
        <div class="form-check">
            <label>
                <?= $form->checkbox('showWeight', 1, $showWeight); ?>
                <?= t('Display Weight'); ?>
            </label>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="form-check">
            <label>
                <?= $form->checkbox('showImage', 1, !isset($showImage) ? true : $showImage); ?>
                <?= t('Display Product Image'); ?>
            </label>
        </div>
        <div class="form-check">
            <label>
                <?= $form->checkbox('showCartButton', 1, !isset($showCartButton) ? true : $showCartButton); ?>
                <?= t('Display Add To Cart Button'); ?>
            </label>
        </div>
        <div class="form-check">
            <label>
                <?= $form->checkbox('showIsFeatured', 1, $showIsFeatured); ?>
                <?= t('Display If Featured'); ?>
            </label>
        </div>
        <div class="form-check">
            <label>
                <?= $form->checkbox('showDimensions', 1, $showDimensions); ?>
                <?= t('Display Dimensions'); ?>
            </label>
        </div>
        <div class="form-check">
            <label>
                <?= $form->checkbox('showQuantity', 1, $showQuantity); ?>
                <?= t('Display Quantity Selector'); ?>
            </label>
        </div>

    </div>
</div>
<br />
<div class="row">
    <div class="col-sm-12">
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
                callback({id: <?= ($pID ? $pID : 0); ?>, text: <?= ($product ? json_encode($product->getName()) : "''"); ?> });
            }
        }).select2('val', []);
    });
</script>
