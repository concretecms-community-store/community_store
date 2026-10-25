<?php defined('C5_EXECUTE') or die("Access Denied."); ?>

<div class="container-fluid">
<div class="row">

    <div class="col-sm-6">

            <legend><?= t('Products'); ?></legend>

            <div class="form-group">
                <?= $form->label('filter', t('List Products')); ?>
                <?= $form->select('filter', [
                    'all' => '** ' . t("All") . ' **',
                    'current' => t('Categorized under current page'),
                    'current_children' => t('Categorized under current page and child pages'),
                    'page' => t('Categorized under a specified page'),
                    'page_children' => t('Categorized under a specified page and child pages'),
                    'related' => t('Related to product displayed on this page'),
                    'related_product' => t('Related to a specified product'),
                    'related_parent' => t('Related via parent page'),
                ], $filter); ?>
            </div>


            <div class="form-group" id="pageselector">
                <div
                    class="form-group" <?= 'page' == $filter || 'page_children' == $filter ? '' : 'style="display: none"'; ?> >
                    <?php
                    $app = \Concrete\Core\Support\Facade\Application::getFacadeApplication();
                    $ps = $app->make('helper/form/page_selector');
                    echo $ps->selectPage('filterCID', ($filterCID > 0 ? $filterCID : false)); ?>
                </div>
            </div>

            <div class="form-group" id="product-search" <?= 'related_product' == $filter ? '' : 'style="display: none"'; ?>>
                <input name="relatedPID" id="product-select"   style="width: 100%" placeholder="<?= t('Search for a Product'); ?>" />
            </div>

            <div class="form-group">
                <?= $form->label('sortOrder', t('Sort Order')); ?>
                <?= $form->select('sortOrder', [
                    'alpha' => t("Alphabetical"),
                    'alpha_desc' => t("Alphabetical, reversed"),
                    'sku' => t("SKU Alphabetical"),
                    'sku_desc' => t("SKU Alphabetical, reversed"),
                    'date' => t('Date Added'),
                    'price_asc' => t('Price Ascending'),
                    'price_desc' => t('Price Descending'),
                    'popular' => t('Best Sellers'),
                    'related' => t("Related Products Order"),
                    'category' => t("Category Sort Order"),
                    'group' => t("Group Sort Order"),
                    'random' => t('Random order, changing each display'),
                    'random_daily' => t('Random order, changing each day'),
                    ], $sortOrder); ?>
            </div>

            <div class="form-group form-check">
                <label>
                    <?= $form->checkbox('showSortOption', 1, $showSortOption); ?>
                    <?= t('Display Sort Option'); ?>
                </label>
            </div>


            <legend><?= t('Filtering'); ?></legend>

            <?php if (!empty($productTypes)) {
                $productTypesList = [''=>t('All Product Types')];
                foreach ($productTypes as $productType) {
                    $productTypesList[$productType->getTypeID()] = $productType->getTypeName();
                }
                ?>

                <div class="form-group">
                    <?= $form->label('filterProductType', t('Filter by Product Group')); ?>
                    <?= $form->select('filterProductType',$productTypesList, $filterProductType); ?>
                </div>


                <?php
            } ?>

            <?php
            foreach ($grouplist as $productgroup) {
                $productgroups[$productgroup->getGroupID()] = $productgroup->getGroupName();
            }
            ?>

            <?php if (!empty($productgroups)) {
                ?>

                <div class="form-group">
                    <?= $form->label('gID', t('Filter by Product Groups')); ?>

                    <div class="ccm-search-field-content ccm-search-field-content-select2">
                        <select multiple="multiple" name="filtergroups[]" id="groups-select"
                                class="existing-select2 select2-select" autocomplete="off" style="width: 100%" placeholder="<?= t('Select Product Groups'); ?>">
                            <?php foreach ($productgroups as $pgkey => $pglabel) {
                    ?>
                                <option
                                    value="<?= $pgkey; ?>" <?= in_array($pgkey, $groupfilters) ? 'selected="selected"' : ''; ?>><?= $pglabel; ?></option>
                            <?php
                } ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <?= $form->label('groupMatchAny', t('Matching')); ?>
                    <?= $form->select('groupMatchAny', ['0' => t("All groups selected"), '1' => t('Any group selected'), '-1' => t("Excluding all groups selected")], $groupMatchAny); ?>
                </div>

            <?php
            } ?>

            <?php
            $productmanufacturers = array("0" => t("None"));
            foreach ($manufacturersList as $productmanufacturer) {
                $productmanufacturers[$productmanufacturer->getID()] = $productmanufacturer->getName();
            }
            ?>
            <?php if (!empty($productmanufacturer)) {
            ?>
            <div class="form-group">
                <?= $form->label('mID', t('Filter by Brand / Manufacturer')); ?>
                <?= $form->select('filterManufacturer', $productmanufacturers, $filterManufacturer,  ['class' => 'selectize']); ?>
            </div>
                <?php
            } ?>

            <div class="form-group form-check">
                <label>
                    <?= $form->checkbox('showFeatured', 1, $showFeatured); ?>
                    <?= t('Include Featured Only'); ?>
                </label>
            </div>
            <div class="form-group form-check">
                <label>
                    <?= $form->checkbox('showSale', 1, $showSale); ?>
                    <?= t('Include On Sale Only'); ?>
                </label>
            </div>
            <div class="form-group form-check">
                <label>
                    <?= $form->checkbox('showOutOfStock', 1, $showOutOfStock); ?>
                    <?= t('Include Out of Stock Products'); ?>
                </label>
            </div>
            <div class="form-group form-check">
                <label>
                    <?= $form->checkbox('enableExternalFiltering', 1, $enableExternalFiltering); ?>
                    <?= t('Enable Other Blocks to Filter This Product List'); ?>
                </label>
            </div>
            <div class="form-group form-check">
                <label>
                    <?= $form->checkbox('excludeCurrentPage', 1, $excludeCurrentPage); ?>
                    <?= t('Exclude Product on Current Page'); ?>
                </label>
            </div>


    </div>
    <div class="col-sm-6">

            <legend><?= t('Pagination and Display Options'); ?></legend>

            <div class="form-group">
                <?= $form->label('maxProducts', t('Number of Products to Display')); ?>
                <?= $form->number('maxProducts', $maxProducts, ['min' => '0', 'step' => '1', 'placeholder' => t('Leave blank or 0 to list all matching products')]); ?>
            </div>

            <div class="form-group form-check">
                <label>
                    <?= $form->checkbox('showPagination', 1, $showPagination); ?>
                    <?= t('Display pagination interface if more products are available than are displayed.'); ?>
                </label>
            </div>

            <div class="form-group">
                <?= $form->label('displayMode', t('Display Mode')); ?>
                <?= $form->select('displayMode', ['grid' => 'Grid', 'list' => 'List'], $displayMode); ?>
            </div>

            <div class="form-group">
                <?= $form->label('productsPerRow', t('Products per Row')); ?>
                <?= $form->select('productsPerRow', [1 => 1, 2 => 2, 3 => 3, 4 => 4, 6 => 6], $productsPerRow ? $productsPerRow : 1); ?>
            </div>
            <div class="form-group">
                <?= $form->label('noProductsMessage', t("Display text when no products")); ?>
                <?= $form->text('noProductsMessage', $noProductsMessage); ?>
            </div>
            <div class="form-group form-check">
                <label>
                    <?= $form->checkbox('showName', 1, $showName); ?>
                    <?= t('Display Name'); ?>
                </label>
            </div>
            <div class="form-group form-check">
                <label>
                    <?= $form->checkbox('showSKU', 1, $showSKU); ?>
                    <?= t('Display SKU'); ?>
                </label>
            </div>
            <div class="form-group form-check">
                <label>
                    <?= $form->checkbox('showPrice', 1, $showPrice); ?>
                    <?= t('Display Price'); ?>
                </label>
            </div>
            <div class="form-group form-check">
                <label>
                <?= $form->checkbox('showAddToCart', 1, $showAddToCart); ?>
                <?= t('Display Add To Cart Button'); ?>
                </label>
            </div>
            <div class="form-group <?= $showAddToCart ? '' : 'hidden'; ?>" id="addToCartTextField">
                <?= $form->label('btnText', t("Add To Cart Button Text")); ?>
                <?= $form->text('btnText', $btnText, ['placeholder' => t("Defaults to: Add To Cart")]); ?>
            </div>
            <div class="form-group form-check">
                <label>
                    <?= $form->checkbox('showQuantity', 1, $showQuantity); ?>
                    <?= t('Display Quantity Selector'); ?>
                </label>
            </div>
            <div class="form-group form-check">
                <label>
                    <?= $form->checkbox('showDescription', 1, $showDescription); ?>
                    <?= t('Display Product Description'); ?>
                </label>
            </div>
            <div class="form-group form-check">
                <label>
                    <?= $form->checkbox('showPageLink', 1, $showPageLink); ?>
                    <?= t('Display Link To Product Page'); ?>
                </label>
            </div>
            <div class="form-group <?= $showPageLink ? '' : 'hidden'; ?>" id="pageLinkTextField">
                <?= $form->label('pageLinkText', t("Link To Product Page Text")); ?>
                <?= $form->text('pageLinkText', $pageLinkText, ['placeholder' => t("Defaults to: More Details")]); ?>
            </div>

    </div>
</div>


<?php
if ($relatedProduct) {
                $relatedProductName = $relatedProduct->getName();
            } else {
                $relatedProductName = '';
            }
?>

<script>
    (function () {
        communityStoreDashboard.productSearch('#product-select', {
            url: <?= json_encode((string) \Concrete\Core\Support\Facade\Url::to('/productfinder')); ?>,
            initial: {id: <?= (int) $relatedPID; ?>, text: <?= json_encode((string) $relatedProductName); ?>}
        });

        communityStoreDashboard.select('#groups-select');

        var filter = document.getElementById('filter');
        var sortOrder = document.getElementById('sortOrder');

        var setSortOptionEnabled = function (value, enabled) {
            var option = sortOrder && sortOrder.querySelector('option[value="' + value + '"]');
            if (option) {
                option.disabled = !enabled;
            }
        };

        var updateSortOptions = function () {
            setSortOptionEnabled('related', filter.value === 'related' || filter.value === 'related_product');
            setSortOptionEnabled('category', filter.value === 'current' || filter.value === 'page');
        };

        updateSortOptions();

        filter.addEventListener('change', function () {
            document.querySelectorAll('#pageselector > div').forEach(function (element) {
                element.style.display = (filter.value === 'page' || filter.value === 'page_children') ? '' : 'none';
            });

            document.getElementById('product-search').style.display = filter.value === 'related_product' ? '' : 'none';

            updateSortOptions();

            if (!sortOrder) {
                return;
            }

            if (filter.value === 'related' || filter.value === 'related_product') {
                sortOrder.value = 'related';
            } else if (sortOrder.value === 'related') {
                sortOrder.value = sortOrder.options[0].value;
            }
        });

        var displayMode = document.getElementById('displayMode');
        var productsPerRow = document.getElementById('productsPerRow');
        var updateDisplayMode = function () {
            productsPerRow.disabled = displayMode.value === 'list';
        };
        updateDisplayMode();
        displayMode.addEventListener('change', updateDisplayMode);

        [['showPageLink', 'pageLinkTextField'], ['showAddToCart', 'addToCartTextField']].forEach(function (pair) {
            var checkbox = document.getElementById(pair[0]);
            checkbox.addEventListener('change', function () {
                document.getElementById(pair[1]).classList.toggle('hidden', !checkbox.checked);
            });
        });
    })();
</script>
