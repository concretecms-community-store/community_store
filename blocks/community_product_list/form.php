<?php defined('C5_EXECUTE') or die("Access Denied."); ?>

<div class="row">

    <div class="col-xs-6">

        <fieldset>
            <legend><?php echo t('Products'); ?></legend>

            <div class="form-group">
                <?php echo $form->label('filter', t('List Products')); ?>
                <?php echo $form->select('filter', [
                    'all' => '** ' . t("All") . ' **',
                    'current' => t('Under current page'),
                    'current_children' => t('Under current page and child pages'),
                    'page' => t('Under a specified page'),
                    'page_children' => t('Under a specified page and child pages'),
                    'related' => t('Related to product displayed on this page'),
                    'related_product' => t('Related to a specified product'),
                ], $filter); ?>
            </div>

            <div class="form-group" id="pageselector">
                <div
                    class="form-group" <?php echo 'page' == $filter || 'page_children' == $filter ? '' : 'style="display: none"'; ?> >
                    <?php
                    $app = \Concrete\Core\Support\Facade\Application::getFacadeApplication();
                    $ps = $app->make('helper/form/page_selector');
                    echo $ps->selectPage('filterCID', ($filterCID > 0 ? $filterCID : false)); ?>
                </div>
            </div>

            <div class="form-group" id="product-search" <?php echo 'related_product' == $filter ? '' : 'style="display: none"'; ?>>
                <input name="relatedPID" id="product-select"   style="width: 100%" placeholder="<?php echo t('Search for a Product'); ?>" />
            </div>

            <div class="form-group">
                <?php echo $form->label('sortOrder', t('Sort Order')); ?>
                <?php echo $form->select('sortOrder', [
                    'alpha' => t("Alphabetical"),
                    'alpha_desc' => t("Alphabetical, reversed"),
                    'date' => t('Date Added'),
                    'price_asc' => t('Price Ascending'),
                    'price_desc' => t('Price Descending'),
                    'popular' => t('Best Sellers'),
                    'related' => t("Related Products Order"),
                    'category' => t("Category Sort Order"),
                    'random' => t('Random order, changing each display'),
                    'random_daily' => t('Random order, changing each day'),
                    ], $sortOrder); ?>
            </div>

            <div class="form-group checkbox">
                <label>
                    <?php echo $form->checkbox('showSortOption', 1, $showSortOption); ?>
                    <?php echo t('Display Sort Option'); ?>
                </label>
            </div>

        </fieldset>

        <fieldset>
            <legend><?php echo t('Filtering'); ?></legend>

            <?php
            foreach ($grouplist as $productgroup) {
                $productgroups[$productgroup->getGroupID()] = $productgroup->getGroupName();
            }
            ?>

            <?php if (!empty($productgroups)) {
                ?>

                <div class="form-group">
                    <?php echo $form->label('gID', t('Filter by Product Groups')); ?>

                    <div class="ccm-search-field-content ccm-search-field-content-select2">
                        <select multiple="multiple" name="filtergroups[]" id="groups-select"
                                class="existing-select2 select2-select" autocomplete="off" style="width: 100%" placeholder="<?php echo t('Select Product Groups'); ?>">
                            <?php foreach ($productgroups as $pgkey => $pglabel) {
                    ?>
                                <option
                                    value="<?php echo $pgkey; ?>" <?php echo in_array($pgkey, $groupfilters) ? 'selected="selected"' : ''; ?>><?php echo $pglabel; ?></option>
                            <?php
                } ?>
                        </select>
                    </div>
                </div>


                <div class="form-group">
                    <?php echo $form->label('groupMatchAny', t('Matching')); ?>
                    <?php echo $form->select('groupMatchAny', ['0' => t("All groups selected"), '1' => t('Any group selected')], $groupMatchAny); ?>
                </div>

            <?php
            } ?>

            <div class="form-group checkbox">
                <label>
                    <?php echo $form->checkbox('showFeatured', 1, $showFeatured); ?>
                    <?php echo t('Include Featured Only'); ?>
                </label>
            </div>
            <div class="form-group checkbox">
                <label>
                    <?php echo $form->checkbox('showSale', 1, $showSale); ?>
                    <?php echo t('Include On Sale Only'); ?>
                </label>
            </div>
            <div class="form-group checkbox">
                <label>
                    <?php echo $form->checkbox('showOutOfStock', 1, $showOutOfStock); ?>
                    <?php echo t('Include Out of Stock Products'); ?>
                </label>
            </div>
        </fieldset>


    </div>
    <div class="col-xs-6">
        <fieldset>
            <legend><?php echo t('Pagination and Display Options'); ?></legend>

            <div class="form-group">
                <?php echo $form->label('maxProducts', t('Number of Products to Display')); ?>
                <?php echo $form->number('maxProducts', $maxProducts, ['min' => '0', 'step' => '1', 'placeholder' => t('leave blank or 0 to list all matching products')]); ?>
            </div>

            <div class="form-group checkbox">
                <label>
                    <?php echo $form->checkbox('showPagination', 1, $showPagination); ?>
                    <?php echo t('Display pagination interface if more products are available than are displayed.'); ?>
                </label>
            </div>

            <div class="form-group">
                <?php echo $form->label('productsPerRow', t('Products per Row')); ?>
                <?php echo $form->select('productsPerRow', [1 => 1, 2 => 2, 3 => 3, 4 => 4, 6 => 6], $productsPerRow ? $productsPerRow : 1); ?>
            </div>
            <div class="form-group">
                <?php echo $form->label('noProductsMessage', t("Display text when no products")); ?>
                <?php echo $form->text('noProductsMessage', $noProductsMessage); ?>
            </div>
            <div class="form-group checkbox">
                <label>
                    <?php echo $form->checkbox('showName', 1, $showName); ?>
                    <?php echo t('Display Name'); ?>
                </label>
            </div>
            <div class="form-group checkbox">
                <label>
                    <?php echo $form->checkbox('showPrice', 1, $showPrice); ?>
                    <?php echo t('Display Price'); ?>
                </label>
            </div>
            <div class="form-group checkbox">
                <label>
                <?php echo $form->checkbox('showAddToCart', 1, $showAddToCart); ?>
                <?php echo t('Display Add To Cart Button'); ?>
                </label>
            </div>
            <div class="form-group <?php echo $showAddToCart ? '' : 'hidden'; ?>" id="addToCartTextField">
                <?php echo $form->label('btnText', t("Add To Cart Button Text")); ?>
                <?php echo $form->text('btnText', $btnText, ['placeholder' => t("Defaults to: Add To Cart")]); ?>
            </div>
            <div class="form-group checkbox">
                <label>
                    <?php echo $form->checkbox('showQuantity', 1, $showQuantity); ?>
                    <?php echo t('Display Quantity Selector'); ?>
                </label>
            </div>
            <div class="form-group checkbox">
                <label>
                    <?php echo $form->checkbox('showDescription', 1, $showDescription); ?>
                    <?php echo t('Display Product Description'); ?>
                </label>
            </div>
            <div class="form-group checkbox">
                <label>
                    <?php if (0 != $showQuickViewLink) {
                $showQuickViewLink = 1;
            } ?>
                    <?php echo $form->checkbox('showQuickViewLink', 1, $showQuickViewLink); ?>
                    <?php echo t('Display Quickview Link (Modal Window)'); ?>
                </label>
            </div>
            <div class="form-group checkbox">
                <label>
                    <?php echo $form->checkbox('showPageLink', 1, $showPageLink); ?>
                    <?php echo t('Display Link To Product Page'); ?>
                </label>
            </div>
            <div class="form-group <?php echo $showPageLink ? '' : 'hidden'; ?>" id="pageLinkTextField">
                <?php echo $form->label('pageLinkText', t("Link To Product Page Text")); ?>
                <?php echo $form->text('pageLinkText', $pageLinkText, ['placeholder' => t("Defaults to: More Details")]); ?>
            </div>
        </fieldset>
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
    $(document).ready(function () {

        $(function(){
            $("#product-select").select2({
                ajax: {
                    url: "<?php echo \Concrete\Core\Support\Facade\Url::to('/productfinder'); ?>",
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
                    callback({text:<?php echo json_encode($relatedProductName); ?>,id:'<?php echo $relatedPID; ?>'});
                }
            }).select2('val', []);

        });


        $('#groups-select').select2();

        var initfilter = $('#filter');

        if (initfilter.val() == 'related' || initfilter.val() == 'related_product') {
            $('#sortOrder option[value="related"]').prop('disabled', false);
        } else {
            $('#sortOrder option[value="related"]').prop('disabled', true);
        }

        if (initfilter.val() == 'current' || initfilter.val() == 'page') {
            $('#sortOrder option[value="category"]').prop('disabled', false);
        } else {
            $('#sortOrder option[value="category"]').prop('disabled', true);
        }

        $('#filter').change(function () {
            if ($(this).val() == 'page' || $(this).val() == 'page_children') {
                $('#pageselector>div').show();
            } else {
                $('#pageselector>div').hide();
            }

            if ($(this).val() == 'related_product') {
                $('#product-search').show();
            } else {
                $('#product-search').hide();
            }

            if ($(this).val() == 'related' || $(this).val() == 'related_product') {
                $('#sortOrder option[value="related"]').prop('disabled', false);
                $("#sortOrder").val('related');
            } else {
                $('#sortOrder option[value="related"]').prop('disabled', true);

                if ($('#sortOrder option:selected').val() == 'related') {
                    $("#sortOrder").val($("#sortOrder option:first").val());
                }
            }

            if ($(this).val() == 'current' || $(this).val() == 'page') {
                $('#sortOrder option[value="category"]').prop('disabled', false);
            } else {
                $('#sortOrder option[value="category"]').prop('disabled', true);
            }


        });

        $('#showPageLink').change(function () {
            if ($(this).prop('checked')) {
                $('#pageLinkTextField').removeClass('hidden');
            } else {
                $('#pageLinkTextField').addClass('hidden');
            }
        });

        $('#showAddToCart').change(function () {
            if ($(this).prop('checked')) {
                $('#addToCartTextField').removeClass('hidden');
            } else {
                $('#addToCartTextField').addClass('hidden');
            }
        });
    });
</script>
