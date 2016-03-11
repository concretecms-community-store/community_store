<?php defined('C5_EXECUTE') or die("Access Denied.");?>
<fieldset>
    <legend><?= t('Product Arrangement')?></legend>
    <?php
        foreach($grouplist as $productgroup){
            $productgroups[$productgroup->getGroupID()] = $productgroup->getGroupName();
        }
    ?>
    <div class="row">
        <div class="col-xs-12 col-sm-6">
            <div class="form-group">
                <?= $form->label('filter',t('List Products'));?>
                <?= $form->select('filter',array(
                    'all'=>t("All"),
                    'current'=>t('Under current page'),
                    'current_children'=>t('Under current page and child pages'),
                    'page'=>t('Under a specified page'),
                    'page_children'=>t('Under a specified page and child pages')
                ),$filter);?>
            </div>

            <div class="form-group" id="pageselector">
                <div class="form-group" <?= ($filter == 'page' || $filter == 'page_children' ? '' : 'style="display: none"'); ?> >
                    <?php
                    $ps = Core::make('helper/form/page_selector');
                    echo $ps->selectPage('filterCID', ($filterCID > 0 ? $filterCID : false) ); ?>
                </div>
            </div>

        </div>
        <div class="col-xs-12 col-sm-6">
        	<div class="form-group">
                <?= $form->label('sortOrder',t('Sort Order'));?>
                <?= $form->select('sortOrder',array('alpha'=>t("Alphabetical"),'date'=>t('Recently Added'),'popular'=>t('Most Popular')),$sortOrder);?>
            </div>
        </div>
    </div>

    <div class="row">
        <?php if(!empty($productgroups)){?>
        <div class="col-xs-12 col-sm-6">
            <div class="form-group">
                <?= $form->label('gID',t('Filter by Groups'));?>

                <div class="ccm-search-field-content ccm-search-field-content-select2">
                    <select multiple="multiple" name="filtergroups[]" id="groups-select" class="existing-select2 select2-select" style="width: 100%">
                        <?php foreach ($productgroups as $pgkey=>$pglabel) { ?>
                            <option value="<?= $pgkey;?>" <?= (in_array($pgkey, $groupfilters) ? 'selected="selected"' : ''); ?>><?= $pglabel; ?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>
        </div>

        <?php } ?>

        <div class="col-xs-12 col-sm-6">
            <div class="form-group">
                <?= $form->label('groupMatchAny',t('Matching'));?>
                <?= $form->select('groupMatchAny',array('0'=>t("All groups listed"),'1'=>t('Any group listed')),$groupMatchAny);?>
            </div>
        </div>

    </div>


    <legend><?= t('Pagination')?></legend>
    <div class="row">
        <div class="col-xs-6">
            <div class="form-group">
                <?= $form->label('productsPerRow',t('Products per Row')); ?>
                <?= $form->select('productsPerRow', array(1=>1,2=>2,3=>3,4=>4),$productsPerRow?$productsPerRow:1, array('style'=>'width:70px')); ?>
            </div>
        </div>
        <div class="col-xs-6">
            <div class="form-group">
                <?= $form->label('maxProducts',t('Max Number of Products')); ?>
                <?= $form->text('maxProducts', isset($maxProducts)?$maxProducts:"10", array('style'=>'width:50px')); ?>
            </div>
        </div>
    </div>
    <div class="checkbox">
        <label>
            <?= $form->checkbox('showPagination',1,$showPagination);?>
            <?= t('Show Pagination')?>
        </label>
    </div>  
    
    <legend><?= t('Display Options')?></legend>
    
    <div class="checkbox">
        <label>
            <?= $form->checkbox('showOutOfStock',1,$showOutOfStock);?>
            <?= t('Show Out of Stock Products')?>
        </label>
    </div>
    
    <label><?= t('Show Featured')?></label>
    
    <div class="radio">
        <label>
            <?= $form->radio('showFeatured','all',$showFeatured=='all' || !isset($showFeatured)?true:false);?>
            <?= t('Show Both Featured &amp; Non-featured')?>
        </label>
    </div>
    <div class="radio">
        <label>
            <?= $form->radio('showFeatured','featured',$showFeatured=='featured'?true:false);?>
            <?= t('Featured Only')?>
        </label>
    </div>
    <div class="radio">
        <label>
            <?= $form->radio('showFeatured','nonfeatured',$showFeatured=='nonfeatured'?true:false);?>
            <?= t('Non-Featured Only')?>
        </label>
    </div>
    
    <label><?= t('Show:')?></label>
    <div class="checkbox">
        <label>
            <?= $form->checkbox('showAddToCart',1,$showAddToCart);?>
            <?= t('Add to Cart Button')?>
        </label>
    </div>
    <div class="checkbox">
        <label>
            <?= $form->checkbox('showPageLink',1,$showPageLink);?>
            <?= t('Page Link')?>
        </label>
    </div>
    <div class="checkbox">
        <label>
            <?= $form->checkbox('showDescription',1,$showDescription);?>
            <?= t('Description')?>
        </label>
    </div>
    <div class="checkbox">
        <label>
            <?php if($showQuickViewLink!=0){
                $showQuickViewLink=1;
            }?>
            <?= $form->checkbox('showQuickViewLink',1,$showQuickViewLink);?>
            <?= t('Quickview Link (Modal Window)')?>
        </label>
    </div>
    <div class="checkbox">
        <label>
            <?= $form->checkbox('showQuantity',1,$showQuantity);?>
            <?= t('Show Quantity Selector')?>
        </label>
    </div>
</fieldset>


<script>
    $(document).ready(function(){
        $('#groups-select').select2();

        $('#filter').change(function(){
            if ($(this).val() == 'page' || $(this).val() == 'page_children') {
                $('#pageselector>div').show();
            }  else {
                $('#pageselector>div').hide();
            }
        });

    });
</script>



