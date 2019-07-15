<?php defined('C5_EXECUTE') or die("Access Denied.");
$app = \Concrete\Core\Support\Facade\Application::getFacadeApplication();
?>

<div class="row">

    <div class="col-xs-12">

        <fieldset>
            <legend><?= t('Filter Data Source'); ?></legend>

            <div class="form-group">
                <?= $form->label('filterSource', t('Filter Settings')); ?>
                <?= $form->select('filterSource', ['auto' => t("Automatically match settings from Product List block on page"), 'manual' => t('Manually configure')], $filterSource); ?>
            </div>


            <div id="manualsettings" class="<?= ($filterSource == 'manual' ? '' : 'hidden'); ?>">
                <div class="form-group">
                    <?= $form->label('filter', t('List Products')); ?>
                    <?= $form->select('filter', [
                        'all' => '** ' . t("All") . ' **',
                        'current' => t('Under current page'),
                        'current_children' => t('Under current page and child pages'),
                        'page' => t('Under a specified page'),
                        'page_children' => t('Under a specified page and child pages'),
                        'showAddToCartrelated' => t('Related to product displayed on this page'),
                        'related_product' => t('Related to a specified product'),
                    ], $filter); ?>
                </div>

                <div class="form-group" id="pageselector">
                    <div
                        class="form-group" <?= ('page' == $filter || 'page_children' == $filter ? '' : 'style="display: none"'); ?> >
                        <?php
                        $ps = $app->make('helper/form/page_selector');
                        echo $ps->selectPage('filterCID', ($filterCID > 0 ? $filterCID : false)); ?>
                    </div>
                </div>

                <div class="form-group" id="product-search" <?= ('related_product' == $filter ? '' : 'style="display: none"'); ?>>
                    <input name="relatedPID" id="product-select"   style="width: 100%" placeholder="<?= t('Search for a Product'); ?>" />
                </div>


                <?php
                foreach ($grouplist as $productgroup) {
                    $productgroups[$productgroup->getGroupID()] = $productgroup->getGroupName();
                }
                ?>

                <?php if (!empty($productgroups)) {
                    ?>

                    <div class="form-group">
                        <?= $form->label('setGroupIDs', t('Filter by Product Groups')); ?>

                        <div class="ccm-search-field-content ccm-search-field-content-select2">
                            <select multiple="multiple" name="filtergroups[]" id="groups-select"
                                    class="existing-select2 select2-select" style="width: 100%" placeholder="<?= t('Select Product Groups'); ?>">
                                <?php foreach ($productgroups as $pgkey => $pglabel) {
                                    ?>
                                    <option
                                        value="<?= $pgkey; ?>" <?= (in_array($pgkey, $groupfilters) ? 'selected="selected"' : ''); ?>><?= $pglabel; ?></option>
                                    <?php
                                } ?>
                            </select>
                        </div>
                    </div>


                    <div class="form-group">
                        <?= $form->label('groupMatchAny', t('Matching')); ?>
                        <?= $form->select('groupMatchAny', ['0' => t("All groups selected"), '1' => t('Any group selected')], $groupMatchAny); ?>
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

                <div class="form-group checkbox">
                    <label>
                        <?= $form->checkbox('showFeatured', 1, $showFeatured); ?>
                        <?= t('Include Featured Only'); ?>
                    </label>
                </div>
                <div class="form-group checkbox">
                    <label>
                        <?= $form->checkbox('showSale', 1, $showSale); ?>
                        <?= t('Include On Sale Only'); ?>
                    </label>
                </div>
                <div class="form-group checkbox">
                    <label>
                        <?= $form->checkbox('showOutOfStock', 1, $showOutOfStock); ?>
                        <?= t('Include Out of Stock Products'); ?>
                    </label>
                </div>
            </div>
        </fieldset>

        <fieldset>
            <legend><?= t('Attributes'); ?></legend>

            <?php

                $typelookup = array();
                $typelookup['price'] = t('Price');

                $otherfilters = array();
                $selectattkeys = array();
                if (!empty($selectedAttributes)) {
                    foreach($selectedAttributes as $selectedatt) {
                        if ($selectedatt['type'] == 'attr') {
                            $selectattkeys[] = $selectedatt['akID'];
                        } else {
                            $otherfilters[] = $selectedatt['type'];
                        }
                    }
                }
            ?>

            <div class="row">
                <div class="col-md-4">
                    <label><?= t('Available Attributes'); ?></label>

                    <ul class="list-group" id="availableatts">
                        <li class="list-group-item <?= (in_array('price', $otherfilters) ? 'hidden' : ''); ?>" data-id="price"><a class="block" data-id="0" data-type="price" href="#"> <?= t('Price');?>
                                <i class="pull-right fa fa-angle-right"></i> </a></li>
                        <?php
                        $attlookup = array();
                        foreach($attributes as $att) {
                            $attlookup[$att->getAttributeKeyID()] = $att->getAttributeKeyName();
                            ?>
                            <li class="list-group-item <?= (in_array($att->getAttributeKeyID(), $selectattkeys) ? 'hidden' : ''); ?>" data-id="<?= $att->getAttributeKeyID(); ?>" ><a class="block" data-type="attr" data-id="<?= $att->getAttributeKeyID(); ?>" href="#"> <?= h($att->getAttributeKeyName());?>
                                <i class="pull-right fa fa-angle-right"></i> </a></li>

                        <?php } ?>

                    </ul>

                </div>
                <div class="col-md-8">
                    <label><?= t('Displayed Attributes'); ?></label>

                    <ul class="list-group form-inline" id="activeatts">
                        <?php
                        if (!empty($selectedAttributes)) {
                            foreach ($selectedAttributes as $selectedatt) {
                                echo '<li data-id="' . ($selectedatt['akID'] > 0 ? $selectedatt['akID'] : $selectedatt['type']) . '" data-type="' . $selectedatt['type'] . '" class="clearfix list-group-item"><a href="#" class="attremove pull-right">&nbsp;&nbsp;<i class="fa fa-times"></i></a><i class="fa fa-arrows-v"></i>&nbsp;&nbsp;' . ($selectedatt['type'] == 'attr' ? $attlookup[$selectedatt['akID']] : $typelookup[$selectedatt['type']]);
                                echo '<select class="form-control input-sm pull-right '. ($selectedatt['type'] == 'attr' ? '' : 'hidden') . '" name="invalidHiding[]"><option value="disable" ' . ($selectedatt['invalidHiding'] == 'disable' ? 'selected="selected"' : '') . '>' . t('disable invalid') . '</option><option value="hide" ' . ($selectedatt['invalidHiding'] == 'hide' ? 'selected="selected"' : '') . '>' . t('hide invalid') . '</option></select>';
                                echo '<select class="form-control input-sm pull-right '. ($selectedatt['type'] == 'attr' ? '' : 'hidden') . '" name="matchingType[]"><option value="or" ' . ($selectedatt['matchingType'] == 'or' ? 'selected="selected"' : '') . '>' . t('match any') . '</option><option value="and" ' . ($selectedatt['matchingType'] == 'and' ? 'selected="selected"' : '') . '>' . t('match all') . '</option></select>';
                                echo '<input type="hidden" name="types[]" value="' . $selectedatt['type'] . '" /><input type="hidden" name="attributes[]" value="' . $selectedatt['akID'] . '" />';
                                echo '<br /><input class="form-control input-sm" placeholder="'.t('Custom Label') .'" type="text" name="labels[]" value="' . $selectedatt['label'] . '" />';
                                echo '</li>';
                            }
                        }?>
                    </ul>
                </div>

            </div>
        </fieldset>

        <fieldset>
            <legend><?= t('Display Options'); ?></legend>

            <div class="form-group">
                <label>
                    <?= $form->checkbox('showTotals', 1, $showTotals); ?>
                    <?= t('Display product counts against options if possible'); ?>
                </label>
            </div>


            <div class="form-group">
                <?= $form->label('updateType', t('Filter is applied')); ?>
                <?= $form->select('updateType', ['auto' => t("Automatically when filters are selected"), 'button' => t('When filter button is pressed')], $updateType); ?>
            </div>

            <div id="filterButtonTextField" class="form-group <?= $updateType == 'button' ? '' :'hidden'; ?>">
                <?= $form->label('filterButtonText', t("Filter Button Text")); ?>
                <?= $form->text('filterButtonText', $filterButtonText, ['placeholder' => t('Filter')]); ?>
            </div>

            <div class="form-group">
                <label>
                    <?= $form->checkbox('displayClear', 1, $displayClear); ?>
                    <?= t('Display filter clear button'); ?>
                </label>
            </div>

            <div id="clearButtonTextField" class="form-group <?= $displayClear ? '' :'hidden'; ?>">
                <?= $form->label('clearButtonText', t("Clear Button Text")); ?>
                <?= $form->text('clearButtonText', $clearButtonText, ['placeholder' => t('Clear')]); ?>
            </div>

            <div class="form-group">
                <label>
                    <?= $form->checkbox('jumpAnchor', 1, $jumpAnchor); ?>
                    <?= t('Scroll to top of filter block on page refresh'); ?>
                </label>
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

        $('#displayClear').change(function () {
            if ($(this).prop('checked')) {
                $('#clearButtonTextField').removeClass('hidden');
            } else {
                $('#clearButtonTextField').addClass('hidden');
            }
        });

        $('#updateType').change(function () {
            if ($(this).val() === 'button') {
                $('#filterButtonTextField').removeClass('hidden');
            } else {
                $('#filterButtonTextField').addClass('hidden');
            }
        });

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
                callback({text:<?= json_encode($relatedProductName); ?>,id:'<?= $relatedPID; ?>'});
            }
        }).select2('val', []);

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

        $('#filterSource').change(function () {
            if ($(this).val() == 'auto') {
                $('#manualsettings').addClass('hidden');
            } else {
                $('#manualsettings').removeClass('hidden');
            }
        });

        $('#activeatts').sortable({
            axis: 'y'
        });

        $('#availableatts a').click(function (e) {
            e.preventDefault();

            var newitem = '<li data-id="' + $(this).data('id') + '" class="clearfix list-group-item"><a href="#" class="attremove pull-right">&nbsp;&nbsp;<i class="fa fa-times"></i></a><i class="fa fa-arrows-v"></i>&nbsp;&nbsp;' + $(this).text();
                if($(this).data('type')==='attr') {
                    newitem += '<select class="form-control input-sm pull-right" name="invalidHiding[]"><option value="disable"><?= t('disable invalid'); ?></option><option value="hide"><?= t('hide invalid'); ?></option></select>';
                    newitem += '<select class="form-control input-sm pull-right" name="matchingType[]"><option value="or"><?= t('match any'); ?></option><option value="and"><?= t('match all'); ?></option></select>';
                }

            newitem += '<input type="hidden" name="attributes[]" value="' + $(this).data('id') + '" /><input type="hidden" name="types[]" value="' + $(this).data('type') + '" />';
            newitem += '<br /><input class="form-control input-sm" placeholder="<?= t('Custom Label'); ?>" type="text" name="labels[]" value="" /></li>';
            newitem += '</li>';

            $("#activeatts").append(newitem);
            $(this).parent().addClass('hidden');
        });

    });

    $(document).on('click', '.attremove', function(e) {
        e.preventDefault();
        var element = $(this).closest('li');
        var id = element.data('id');
        $("#availableatts").find("[data-id='" + id + "']").removeClass('hidden');
        element.remove();
    });


</script>
