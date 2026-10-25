<?php defined('C5_EXECUTE') or die("Access Denied."); ?>

<div class="form-group">
    <?= $form->label('productLocation', t('Product')); ?>
    <?= $form->select('productLocation', ['search' => t('A selected product'), 'page' => t('Product associated with this page')], isset($productLocation) ? $productLocation : false, ['onChange' => 'updateProductLocation();']); ?>
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
                <?= $form->checkbox('showProductSKU', 1, !isset($showProductSKU) ? false : $showProductSKU); ?>
                <?= t('Display SKU'); ?>
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
                <?= $form->checkbox('showWeight', 1, $showWeight ?? false); ?>
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
                <?= $form->checkbox('showIsFeatured', 1, $showIsFeatured ?? false); ?>
                <?= t('Display If Featured'); ?>
            </label>
        </div>
        <div class="form-check">
            <label>
                <?= $form->checkbox('showDimensions', 1, $showDimensions ?? false); ?>
                <?= t('Display Dimensions'); ?>
            </label>
        </div>
        <div class="form-check">
            <label>
                <?= $form->checkbox('showQuantity', 1, $showQuantity ?? false); ?>
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
            <?= $form->text('btnText', $btnText ?? false, ['placeholder' => t('Add To Cart')]); ?>
        </div>
    </div>
</div>


<script type="text/javascript">
    function updateProductLocation() {
        document.getElementById('product-search').style.display = document.getElementById('productLocation').value === 'page' ? 'none' : '';
    }
    updateProductLocation();

    communityStoreDashboard.productSearch('#product-select', {
        url: <?= json_encode((string) \Concrete\Core\Support\Facade\Url::to('/productfinder')); ?>,
        initial: {id: <?= (int) ($pID ?? 0); ?>, text: <?= json_encode(isset($product) && is_object($product) ? $product->getName() : ''); ?>}
    });
</script>
