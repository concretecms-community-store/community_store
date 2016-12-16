<?php defined('C5_EXECUTE') or die("Access Denied."); ?>

<div class="row">

    <div class="col-xs-6">

        <fieldset>
            <legend><?= t('Products') ?></legend>

            <div class="form-group">
                <?= $form->label('filter', t('List Products')); ?>
                <?= $form->select('filter', array(
                    'all' => '** ' . t("All") . ' **',
                    'current' => t('Under current page'),
                    'current_children' => t('Under current page and child pages'),
                    'page' => t('Under a specified page'),
                    'page_children' => t('Under a specified page and child pages'),
                    'related' => t('Related to product displayed on this page'),
                    'related_product' => t('Related to a specified product'),
                    'random' => t('Random order, changing each display'),
                    'random_daily' => t('Random order, changing each day')
                ), $filter); ?>
            </div>

            <div class="form-group" id="pageselector">
                <div
                    class="form-group" <?= ($filter == 'page' || $filter == 'page_children' ? '' : 'style="display: none"'); ?> >
                    <?php
                    $ps = Core::make('helper/form/page_selector');
                    echo $ps->selectPage('filterCID', ($filterCID > 0 ? $filterCID : false)); ?>
                </div>
            </div>

            <div class="form-group" id="product-search" <?= ($filter == 'related_product' ? '' : 'style="display: none"'); ?>>
                <input name="relatedPID" id="product-select"   style="width: 100%" placeholder="<?= t('Search for a Product') ?>" />
            </div>

            <div class="form-group">
                <?= $form->label('sortOrder', t('Sort Order')); ?>
                <?= $form->select('sortOrder', array(
                    'alpha' => t("Alphabetical"),
                    'date' => t('Date Added'),
                    'price_asc' => t('Price Ascending'),
                    'price_desc' => t('Price Descending'),
                    'popular' => t('Best Sellers'),
                    'related' => t("Related Products Order"),
                    'category' => t("Category Sort Order"),
                    ), $sortOrder); ?>
            </div>

        </fieldset>

        <fieldset>
            <legend><?= t('Filtering') ?></legend>

            <?php
            foreach ($grouplist as $productgroup) {
                $productgroups[$productgroup->getGroupID()] = $productgroup->getGroupName();
            }
            ?>

            <?php if (!empty($productgroups)) { ?>

                <div class="form-group">
                    <?= $form->label('gID', t('Filter by Product Groups')); ?>

                    <div class="ccm-search-field-content ccm-search-field-content-select2">
                        <select multiple="multiple" name="filtergroups[]" id="groups-select"
                                class="existing-select2 select2-select" style="width: 100%" placeholder="<?= t('Select Product Groups') ?>">
                            <?php foreach ($productgroups as $pgkey => $pglabel) { ?>
                                <option
                                    value="<?= $pgkey; ?>" <?= (in_array($pgkey, $groupfilters) ? 'selected="selected"' : ''); ?>><?= $pglabel; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>


                <div class="form-group">
                    <?= $form->label('groupMatchAny', t('Matching')); ?>
                    <?= $form->select('groupMatchAny', array('0' => t("All groups selected"), '1' => t('Any group selected')), $groupMatchAny); ?>
                </div>

            <?php } ?>

            <div class="form-group checkbox">
                <label>
                    <?= $form->checkbox('showFeatured', 1, $showFeatured); ?>
                    <?= t('Include Featured Only') ?>
                </label>
            </div>
            <div class="form-group checkbox">
                <label>
                    <?= $form->checkbox('showSale', 1, $showSale); ?>
                    <?= t('Include On Sale Only') ?>
                </label>
            </div>
            <div class="form-group checkbox">
                <label>
                    <?= $form->checkbox('showOutOfStock', 1, $showOutOfStock); ?>
                    <?= t('Include Out of Stock Products') ?>
                </label>
            </div>
        </fieldset>


    </div>
    <div class="col-xs-6">
        <fieldset>
            <legend><?= t('Pagination and Display Options') ?></legend>

            <div class="form-group">
                <?= $form->label('maxProducts', t('Number of Products to Display')); ?>
                <?= $form->number('maxProducts', $maxProducts, array('min'=>'0', 'step'=>'1','placeholder'=>t('leave blank or 0 to list all matching products'))); ?>
            </div>

            <div class="form-group checkbox">
                <label>
                    <?= $form->checkbox('showPagination', 1, $showPagination); ?>
                    <?= t('Display pagination interface if more products are available than are displayed.') ?>
                </label>
            </div>

            <div class="form-group">
                <?= $form->label('productsPerRow', t('Products per Row')); ?>
                <?= $form->select('productsPerRow', array(1 => 1, 2 => 2, 3 => 3, 4 => 4), $productsPerRow ? $productsPerRow : 1); ?>
            </div>
            <div class="form-group checkbox">
                <label>
                    <?= $form->checkbox('showName', 1, $showName); ?>
                    <?= t('Display Name') ?>
                </label>
            </div>
            <div class="form-group checkbox">
                <label>
                    <?= $form->checkbox('showPrice', 1, $showPrice); ?>
                    <?= t('Display Price') ?>
                </label>
            </div>
            <div class="form-group checkbox">
                <label>
                <?= $form->checkbox('showAddToCart', 1, $showAddToCart); ?>
                <?= t('Display Add To Cart Button') ?>
                </label>
            </div>
            <div class="form-group <?= ($showAddToCart ? '' : 'hidden'); ?>" id="addToCartTextField">
                <?= $form->label('btnText', t("Add To Cart Button Text"))?>
                <?= $form->text('btnText', $btnText, array('placeholder'=>t("Defaults to: Add To Cart")))?>
            </div>
            <div class="form-group checkbox">
                <label>
                    <?= $form->checkbox('showQuantity', 1, $showQuantity); ?>
                    <?= t('Display Quantity Selector') ?>
                </label>
            </div>
            <div class="form-group checkbox">
                <label>
                    <?= $form->checkbox('showDescription', 1, $showDescription); ?>
                    <?= t('Display Product Description') ?>
                </label>
            </div>
            <div class="form-group checkbox">
                <label>
                    <?php if ($showQuickViewLink != 0) {
                        $showQuickViewLink = 1;
                    } ?>
                    <?= $form->checkbox('showQuickViewLink', 1, $showQuickViewLink); ?>
                    <?= t('Display Quickview Link (Modal Window)') ?>
                </label>
            </div>
            <div class="form-group checkbox">
                <label>
                    <?= $form->checkbox('showPageLink', 1, $showPageLink); ?>
                    <?= t('Display Link To Product Page') ?>
                </label>
            </div>
            <div class="form-group <?= ($showPageLink ? '' : 'hidden'); ?>" id="pageLinkTextField">
                <?= $form->label('pageLinkText', t("Link To Product Page Text"))?>
                <?= $form->text('pageLinkText', $pageLinkText, array('placeholder'=>t("Defaults to: More Details")))?>
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
                    url: "<?= \URL::to('/productfinder')?>",
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
                    callback({text:<?php echo json_encode($relatedProductName);?>,id:'<?= $relatedPID; ?>'});
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



