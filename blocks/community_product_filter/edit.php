<?php defined('C5_EXECUTE') or die("Access Denied.");
$app = \Concrete\Core\Support\Facade\Application::getFacadeApplication();
?>

<div class="row">

    <div class="col-sm-12">

        <fieldset>
            <legend><?= t('Filter Data Source'); ?></legend>

            <div class="form-group">
                <?= $form->label('filterSource', t('Filter Settings')); ?>
                <?= $form->select('filterSource', ['auto' => t("Automatically match settings from Product List block on page"), 'manual' => t('Manually configure')], $filterSource); ?>
            </div>


            <div id="manualsettings" class="<?= ($filterSource == 'manual' ? '' : 'hidden d-none'); ?>">
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
                        <li class="list-group-item <?= (in_array('price', $otherfilters) ? 'hidden d-none' : ''); ?>" data-id="price"><a class="block" data-id="0" data-type="price" href="#"> <?= t('Price');?>
                                <i class="pull-right float-end fa fa-angle-right"></i> </a></li>
                        <?php
                        $attlookup = array();
                        foreach($attributes as $att) {
                            $attlookup[$att->getAttributeKeyID()] = $att->getAttributeKeyName();
                            ?>
                            <li class="list-group-item <?= (in_array($att->getAttributeKeyID(), $selectattkeys) ? 'hidden d-none' : ''); ?>" data-id="<?= $att->getAttributeKeyID(); ?>" ><a class="block" data-type="attr" data-id="<?= $att->getAttributeKeyID(); ?>" href="#"> <?= h($att->getAttributeKeyName());?>
                                <i class="pull-right float-end fa fa-angle-right"></i> </a></li>

                        <?php } ?>

                    </ul>

                </div>
                <div class="col-md-8">
                    <label><?= t('Displayed Attributes'); ?></label>

                    <ul class="list-group form-inline" id="activeatts">
                        <?php
                        if (!empty($selectedAttributes)) {
                            foreach ($selectedAttributes as $selectedatt) {
                                echo '<li data-id="' . ($selectedatt['akID'] > 0 ? $selectedatt['akID'] : $selectedatt['type']) . '" data-type="' . $selectedatt['type'] . '" class="clearfix list-group-item mb-1"><a href="#" class="attremove pull-right float-end">&nbsp;&nbsp;<i class="fa fa-times"></i></a><i class="fa fa-arrows-v fa-arrows-alt-v"></i>&nbsp;&nbsp;' . ($selectedatt['type'] == 'attr' ? $attlookup[$selectedatt['akID']] : $typelookup[$selectedatt['type']]);
                                echo '<select class="form-control form-select input-sm pull-right float-end mb-2 '. ($selectedatt['type'] == 'attr' ? '' : 'hidden d-none') . '" name="invalidHiding[]"><option value="disable" ' . ($selectedatt['invalidHiding'] == 'disable' ? 'selected="selected"' : '') . '>' . t('disable invalid') . '</option><option value="hide" ' . ($selectedatt['invalidHiding'] == 'hide' ? 'selected="selected"' : '') . '>' . t('hide invalid') . '</option></select>';
                                echo '<select class="form-control form-select input-sm pull-right float-end mb-2 '. ($selectedatt['type'] == 'attr' ? '' : 'hidden d-none') . '" name="matchingType[]"><option value="or" ' . ($selectedatt['matchingType'] == 'or' ? 'selected="selected"' : '') . '>' . t('match any') . '</option><option value="and" ' . ($selectedatt['matchingType'] == 'and' ? 'selected="selected"' : '') . '>' . t('match all') . '</option></select>';
                                echo '<input type="hidden" name="types[]" value="' . $selectedatt['type'] . '" /><input type="hidden" name="attributes[]" value="' . $selectedatt['akID'] . '" />';
                                echo '<br /><input class="form-control input-sm" placeholder="'.t('Custom Label') .'" type="text" name="labels[]" value="' . $selectedatt['label'] . '" />';
                                echo '</li>';
                            }
                        }?>
                    </ul>
                </div>

            </div>
        </fieldset>
        <br />
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

            <div id="filterButtonTextField" class="form-group <?= $updateType == 'button' ? '' :'hidden d-none'; ?>">
                <?= $form->label('filterButtonText', t("Filter Button Text")); ?>
                <?= $form->text('filterButtonText', $filterButtonText, ['placeholder' => t('Filter')]); ?>
            </div>

            <div class="form-group">
                <label>
                    <?= $form->checkbox('displayClear', 1, $displayClear); ?>
                    <?= t('Display filter clear button'); ?>
                </label>
            </div>

            <div id="clearButtonTextField" class="form-group <?= $displayClear ? '' :'hidden d-none'; ?>">
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
    (function () {
        var displayClear = document.getElementById('displayClear');
        displayClear.addEventListener('change', function () {
            communityStoreDashboard.setHidden(document.getElementById('clearButtonTextField'), !displayClear.checked);
        });

        var updateType = document.getElementById('updateType');
        updateType.addEventListener('change', function () {
            communityStoreDashboard.setHidden(document.getElementById('filterButtonTextField'), updateType.value !== 'button');
        });

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

        var filterSource = document.getElementById('filterSource');
        filterSource.addEventListener('change', function () {
            communityStoreDashboard.setHidden(document.getElementById('manualsettings'), filterSource.value === 'auto');
        });

        var activeAttributes = document.getElementById('activeatts');
        var availableAttributes = document.getElementById('availableatts');

        communityStoreDashboard.sortable(activeAttributes);

        var escapeHTML = function (text) {
            var div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        };

        availableAttributes.querySelectorAll('a').forEach(function (link) {
            link.addEventListener('click', function (e) {
                e.preventDefault();

                var id = escapeHTML(link.getAttribute('data-id'));
                var type = escapeHTML(link.getAttribute('data-type'));
                var newitem = '<li data-id="' + id + '" class="clearfix list-group-item"><a href="#" class="attremove pull-right float-end">&nbsp;&nbsp;<i class="fa fa-times"></i></a><i class="fa fa-arrows-v fa-arrows-alt-v"></i>&nbsp;&nbsp;' + escapeHTML(link.textContent);
                if (type === 'attr') {
                    newitem += '<select class="form-control form-select input-sm pull-right float-end mb-2" name="invalidHiding[]"><option value="disable">' + <?= json_encode(t('disable invalid')); ?> + '</option><option value="hide">' + <?= json_encode(t('hide invalid')); ?> + '</option></select>';
                    newitem += '<select class="form-control form-select input-sm pull-right float-end mb-2" name="matchingType[]"><option value="or">' + <?= json_encode(t('match any')); ?> + '</option><option value="and">' + <?= json_encode(t('match all')); ?> + '</option></select>';
                }

                newitem += '<input type="hidden" name="attributes[]" value="' + id + '" /><input type="hidden" name="types[]" value="' + type + '" />';
                newitem += '<br /><input class="form-control input-sm" placeholder="' + <?= json_encode(h(t('Custom Label'))); ?> + '" type="text" name="labels[]" value="" /></li>';

                activeAttributes.insertAdjacentHTML('beforeend', newitem);
                communityStoreDashboard.setHidden(link.parentElement, true);
            });
        });

        communityStoreDashboard.on(activeAttributes, 'click', '.attremove', function (e, link) {
            e.preventDefault();
            var element = link.closest('li');
            var id = element.getAttribute('data-id');
            availableAttributes.querySelectorAll('[data-id]').forEach(function (available) {
                if (available.getAttribute('data-id') === id) {
                    communityStoreDashboard.setHidden(available.parentElement.tagName === 'LI' ? available.parentElement : available, false);
                }
            });
            element.remove();
        });
    })();
</script>
