<?php
defined('C5_EXECUTE') or die("Access Denied.");

$listViews = array('view','updated','removed','success');
$addViews = array('add','edit','save');
$attributeViews = array('attributes','attributeadded','attributeremoved');
$ps = Core::make('helper/form/page_selector');

use \Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product as StoreProduct;

?>

<?php if (in_array($controller->getTask(),$addViews)){ //if adding or editing a product
    if(!is_object($product)) {
        $product = new StoreProduct();
        $product->setIsUnlimited(true);
        $product->setIsTaxable(true);
        $product->setIsShippable(true);
    } else {
        $images = $product->getImages();
        $options = $product->getOptions();
        $locationPages = $product->getLocationPages();
        $pgroups = $product->getGroupIDs();
    }

    $pID = $product->getID()
 ?>

    <?php if ($pID > 0) { ?>
    <div class="ccm-dashboard-header-buttons">

        <form class="pull-right"  method="post" id="delete" action="<?= \URL::to('/dashboard/store/products/delete/', $pID)?>" >
            <?= $token->output('community_store'); ?>&nbsp;
            <button class="btn btn-danger"><?= t("Delete Product")?></button>
        </form>

        <form class="pull-right" method="get" id="duplicate" action="<?= \URL::to('/dashboard/store/products/duplicate/', $pID)?>" >
            <button class="btn btn-default"><?= t("Duplicate Product")?></button>
        </form>



        <script type="text/javascript">
        $(function(){
            $('#delete').submit(function() {
                return confirm('<?=  t("Are you sure you want to delete this product?"); ?>');
            });
        });
        </script>
    </div>
    <?php } ?>

    <form method="post" action="<?= $view->action('save')?>">
        <?= $token->output('community_store'); ?>
        <input type="hidden" name="pID" value="<?= $product->getID()?>"/>

        <div class="row">
            <div class="col-sm-3">
                <ul class="nav nav-pills nav-stacked">
                    <li class="active"><a href="#product-overview" data-pane-toggle ><?= t('Overview')?></a></li>
                    <li><a href="#product-categories" data-pane-toggle><?= t('Categories and Groups')?></a></li>
                    <li><a href="#product-shipping" data-pane-toggle><?= t('Shipping')?></a></li>
                    <li><a href="#product-images" data-pane-toggle><?= t('Images')?></a></li>
                    <li><a href="#product-options" data-pane-toggle><?= t('Options')?></a></li>
                    <li><a href="#product-related" data-pane-toggle><?= t('Related Products')?></a></li>
                    <li><a href="#product-attributes" data-pane-toggle><?= t('Attributes')?></a></li>
                    <li><a href="#product-digital" data-pane-toggle><?= t("Memberships and Downloads")?></a></li>
                    <li><a href="#product-page" data-pane-toggle><?= t('Detail Page')?></a></li>
                </ul>
            </div>

            <div class="col-sm-9" id="product-header">
                <div class="row">
                    <div class="col-xs-8">
                        <div class="form-group">
                            <?= $form->label("pName", t("Product Name"));?>
                            <?= $form->text("pName", $product->getName(), array('required'=>'required'));?>
                        </div>
                    </div>
                    <div class="col-xs-4">
                        <div class="form-group">
                            <?= $form->label("pSKU", t("Code / SKU"));?>
                            <?= $form->text("pSKU", $product->getSKU());?>
                        </div>
                    </div>
                </div>
                <hr />
            </div>


            <div class="col-sm-9 store-pane active" id="product-overview">
                <div class="row">
                    <div class="col-xs-6">
                        <div class="form-group">
                            <?= $form->label("pActive", t("Active"));?>
                            <?= $form->select("pActive", array('1'=>t('Active'),'0'=>t('Inactive')), $product->isActive() ? '1' : '0');?>
                        </div>
                    </div>
                    <div class="col-xs-6">
                        <div class="form-group">
                            <?= $form->label("pFeatured", t("Featured Product"));?>
                            <?= $form->select("pFeatured",array('0'=>t('No'),'1'=>t('Yes')), $product->isFeatured() ? '1' : '0');?>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-6">
                        <div class="form-group">
                            <?php
                            $priceclass = 'nonpriceentry';
                            $defaultpriceclass = 'priceentry';
                            if ($product->allowCustomerPrice()) {
                                $priceclass .= ' hidden';
                            } else {
                                $defaultpriceclass .= ' hidden';
                            }
                            ?>
                            <?= $form->label("pPrice", t("Price"), array('class'=>$priceclass));?>
                            <?= $form->label("pPrice", t("Default Price"), array('class'=>$defaultpriceclass));?>
                            <div class="input-group">
                                <div class="input-group-addon">
                                    <?=  Config::get('community_store.symbol');?>
                                </div>
                                <?php $price = $product->getBasePrice(); ?>
                                <?= $form->text("pPrice", $price, array('placeholder'=>($product->allowCustomerPrice() ? t('No Price Set') : '')));?>
                            </div>
                        </div>
                    </div>
                    <div class="col-xs-6">
                        <div class="form-group nonpriceentry <?= ($product->allowCustomerPrice() ? 'hidden' : '');?>">
                            <?= $form->label("pSalePrice", t("Sale Price"), array('class'=>$priceclass));?>
                            <div class="input-group">
                                <div class="input-group-addon">
                                    <?= Config::get('community_store.symbol');?>
                                </div>
                                <?php $salePrice = $product->getSalePrice(); ?>
                                <?= $form->text("pSalePrice", $salePrice, array('placeholder'=>'No Sale Price Set'));?>
                            </div>
                        </div>
                        <div class="form-group priceentry <?= ($product->allowCustomerPrice() ? '' : 'hidden');?>">
                            <?= $form->label('pPriceSuggestions', t('Price Suggestions'))?>
                            <?= $form->text('pPriceSuggestions', $product->getPriceSuggestions(), array('placeholder'=>'e.g. 10,20,30'))?>
                        </div>
                    </div>

                    <script>
                        $(document).ready(function(){
                            $('#pCustomerPrice').change(function(){
                                if ($(this).prop('checked')) {
                                    $('.priceentry').removeClass('hidden');
                                    $('.nonpriceentry').addClass('hidden');
                                } else {
                                    $('.priceentry').addClass('hidden');
                                    $('.nonpriceentry').removeClass('hidden');
                                }
                            });
                        });
                    </script>

                </div>
                <div class="row priceentry <?= ($product->allowCustomerPrice() ? '' : 'hidden');?>">
                    <div class="col-xs-6">
                        <div class="form-group">
                            <?= $form->label("pPriceMinimum", t("Minimum Price"));?>
                            <div class="input-group">
                                <div class="input-group-addon">
                                    <?=  Config::get('community_store.symbol');?>
                                </div>
                                <?php $minimumPrice = $product->getPriceMinimum(); ?>
                                <?= $form->text("pPriceMinimum", $minimumPrice, array('placeholder'=>'No Minimum Price Set'));?>
                            </div>
                        </div>
                    </div>
                    <div class="col-xs-6">
                        <div class="form-group">
                            <?= $form->label("pPriceMaximum", t("Maximum Price"));?>
                            <div class="input-group">
                                <div class="input-group-addon">
                                    <?=  Config::get('community_store.symbol');?>
                                </div>
                                <?php $maximumPrice = $product->getPriceMaximum(); ?>
                                <?= $form->text("pPriceMaximum", $maximumPrice, array('placeholder'=>'No Maximum Price Set'));?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-6">
                        <div class="form-group">
                            <?= $form->checkbox('pCustomerPrice', '1', $product->allowCustomerPrice())?>
                            <?= $form->label('pCustomerPrice', t('Allow customer to enter price'))?>
                        </div>
                    </div>

                </div>

                <div class="row">

                    <div class="col-xs-6">
                        <div class="form-group">
                            <?= $form->checkbox('pQuantityPrice', '1', $product->hasQuantityPrice())?>
                            <?= $form->label('pQuantityPrice', t('Quantity based pricing'))?>
                        </div>

                    </div>

                    <div id="tieredoptionscontainer" class="col-xs-12  <?= $product->hasQuantityPrice() ? '' : 'hidden'?>">

                        <div id="tierscontainer">
                            <div class="row">
                                <div class="col-xs-3"><strong><?= t('From');?></strong></div>
                                <div class="col-xs-3"><strong><?= t('To');?></strong></div>
                                <div class="col-xs-3"><strong><?= t('Price');?></strong></div>

                                <th class="col-xs-3"></th>
                            </div>
                        </div>

                        <p><a href="javascript:addtier()" class="btn btn-default"><?= t('Add Tier');?></a></p>


                        <script type="text/template" id="price-tier-template">
                            <div class="row" data-order="<%=sort%>">
                                <div class="col-xs-3">
                                    <div class="form-group">
                                        <input type="text" name="ptFrom[]" class="form-control ccm-input-text" value="<%=from%>">
                                    </div>
                                </div>

                                <div  class="col-xs-3">
                                    <div class="form-group">
                                    <input type="text" name="ptTo[]" class="form-control ccm-input-text" value="<%=to%>">
                                    </div>
                                </div>
                                <div  class="col-xs-5">
                                    <div class="form-group">
                                        <div class="input-group">
                                            <div class="input-group-addon">
                                                <?=  Config::get('community_store.symbol');?>
                                            </div>
                                            <input type="text" name="ptPrice[]" class="form-control ccm-input-text" value="<%=price%>">
                                        </div>
                                    </div>
                                </div>

                                <div  class="col-xs-1">
                                    <a  href="javascript:deletetier(<%=sort%>)" class="btn btn-sm btn-danger remove"><i class="fa fa-times"></i></a>
                                </div>
                            </div>
                        </script>

                        <script type="text/javascript">
                            $(function(){

                                //Define container and items
                                var tiersContainer = $('#tierscontainer');
                                var tierTemplate = _.template($('#price-tier-template').html());

                                //load up existing option groups
                                <?php

                                $priceTiers = $product->getPriceTiers();

                                if($priceTiers) {
                                $tiersort = 0;
                                foreach ($priceTiers as $priceTier) {

                                    ?>
                                    tiersContainer.append(tierTemplate({
                                        from: '<?= $priceTier->getFrom(); ?>',
                                        to: '<?= $priceTier->getTo(); ?>',
                                        price: '<?= $priceTier->getPrice(); ?>',
                                        sort: '<?= $tiersort ?>'
                                    }));
                                    <?php

                                    $tiersort++;
                                    }
                                }
                                ?>

                                if ($('#tierscontainer .row').size() == 1) {
                                    addtier();
                                }

                            });

                            function deletetier(id){
                                $("#tierscontainer .row[data-order='"+id+"']").remove();

                                if ($('#tierscontainer .row').size() == 1) {
                                    addtier();
                                }
                            }

                            function addtier(){
                                var tiersContainer = $('#tierscontainer');
                                var tierTemplate = _.template($('#price-tier-template').html());

                                tiersContainer.append(tierTemplate({
                                    from: '',
                                    to: '',
                                    price: '',
                                    sort: $('#tierscontainer .row').size()
                                }));

                            }
                        </script>


                        <hr />
                    </div>


                </div>


                <div class="row">
                    <div class="col-xs-6">
                        <div class="form-group">
                            <?= $form->label("pTaxable", t("Taxable"));?>
                            <?= $form->select("pTaxable",array('1'=>t('Yes'),'0'=>t('No')), $product->isTaxable() ? '1' : '0');?>
                        </div>
                    </div>
                    <div class="col-xs-6">
                        <div class="form-group">
                            <?= $form->label("pTaxClass", t("Tax Class"));?>
                            <?= $form->select("pTaxClass",$taxClasses, $product->getTaxClassID());?>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-6">
                        <div class="form-group">
                            <?= $form->label("pQty", t("Stock Level"));?>
                            <?php $qty = $product->getQty(); ?>
                            <div class="input-group">
                                <?= $form->number("pQty", $qty!==''? round($qty, 3) :'999', array(($product->isUnlimited() ? 'disabled' : '')=>($product->isUnlimited() ? 'disabled' : ''),'step'=>0.001));?>
                                <div class="input-group-addon">
                                    <?= $form->checkbox('pQtyUnlim', '1', $product->isUnlimited())?>
                                    <?= $form->label('pQtyUnlim', t('Unlimited'))?>
                                </div>

                                <script>
                                    $(document).ready(function(){
                                        $('#pQtyUnlim').change(function(){
                                            $('#pQty').prop('disabled',this.checked);
                                            $('#backorders').toggle();
                                        });


                                        $('#pAllowDecimalQty').change(function(){
                                            $('#quantitystepscontainer').toggleClass('hidden');
                                        });

                                        $('#pQuantityPrice').change(function(){
                                            $('#tieredoptionscontainer').toggleClass('hidden');
                                        });

                                        $('#pNoQty').change(function(){
                                            if ($(this).val() == '1') {
                                                $('#quantityoptions').addClass('hidden');
                                                $('#quantitystepscontainer').addClass('hidden');
                                            } else {
                                                $('#quantityoptions').removeClass('hidden');

                                                if ( $('#pAllowDecimalQty').val() == 1) {
                                                    $('#quantitystepscontainer').removeClass('hidden');
                                                } else {
                                                    $('#quantitystepscontainer').addClass('hidden');
                                                }
                                            }

                                        });

                                        $('#pVariations').change(function(){
                                            if ($(this).prop('checked')) {
                                                $('#variations,#variationnotice').removeClass('hidden');
                                            } else {
                                                $('#variations,#variationnotice').addClass('hidden');
                                            }
                                        });

                                        $('input[name^="pvQtyUnlim"]').change(function(){
                                            $(this).closest('.input-group').find('.ccm-input-number').prop('readonly',this.checked);
                                        });

                                    });
                                </script>
                            </div>

                        </div>
                        <div class="form-group" id="backorders" <?=  ($product->isUnlimited() ? 'style="display: none"' : '');?>>
                            <?= $form->checkbox('pBackOrder', '1', $product->allowBackOrders())?>
                            <?= $form->label('pBackOrder', t('Allow Back Orders'))?>
                        </div>
                    </div>
                    <div class="col-xs-6">
                        <div class="form-group">
                            <?= $form->label("pNoQty", t("Offer quantity selection"));?>
                            <?= $form->select("pNoQty",array('0'=>t('Yes'),'1'=>t('No, only allow one of this product in a cart')), !$product->allowQuantity());?>
                        </div>
                    </div>
                </div>

                <div class="row <?= !$product->allowQuantity() ? 'hidden': '';?>" id="quantityoptions">
                    <div class="col-xs-6">
                        <div class="form-group">
                            <?= $form->label("pAllowDecimalQty", t("Allow Decimal Quantities"));?>
                            <?= $form->select("pAllowDecimalQty",array('0'=>t('No, whole number quantities only'),'1'=>t('Yes')), $product->getAllowDecimalQty());?>
                        </div>
                    </div>
                    <div class="col-xs-6">
                        <div class="form-group">
                            <?= $form->label("pMaxQty", t("Maximum Quantity (as single cart item)"));?>
                            <?= $form->number("pMaxQty", $product->getMaxQty(), array('min'=>0, 'step'=>0.01));?>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xs-6">
                        <div class="form-group">
                            <?= $form->label("pQtyLabel", t("Quantity Label"));?>
                            <?= $form->text("pQtyLabel", $product->getQtyLabel(), array('placeholder'=>'e.g. cm'));?>
                        </div>
                    </div>

                    <div class="col-xs-6 <?= ($product->getAllowDecimalQty() ? '' : 'hidden');?>" id="quantitystepscontainer">
                        <div class="form-group">
                            <?= $form->label("pQtySteps", t("Quantity Steps"));?>
                            <?= $form->number("pQtySteps",  $product->getQtySteps() > 0  ? round($product->getQtySteps(), 4) : '', array('min'=>0,'step'=>0.001, 'placeholder'=>'e.g. 0.1'));?>
                        </div>
                    </div>
                </div>


                <?php if ($controller->getTask() == 'edit') { ?>
                <div class="row">
                    <div class="col-xs-12">
                        <div class="form-group">
                            <?= $form->label("pDateAdded", t("Date Added"));?>
                            <?= \Core::make('helper/form/date_time')->datetime('pDateAdded', $product->getDateAdded()); ?>
                        </div>
                        <style>
                            #ui-datepicker-div {
                                z-index: 100 !important;
                            }
                        </style>
                    </div>
                </div>
                <?php } ?>

                <div class="form-group">
                    <?= $form->label("pDesc", t("Short Description"));?><br>
                    <?php
                    $editor = Core::make('editor');
                    echo $editor->outputStandardEditor('pDesc', $product->getDesc());
                    ?>
                </div>

                <div class="form-group">
                    <?= $form->label("pDesc", t("Product Details (Long Description)"));?><br>
                    <?php
                    $editor = Core::make('editor');
                    echo $editor->outputStandardEditor('pDetail', $product->getDetail());
                    ?>
                </div>


            </div><!-- #product-overview -->

            <div class="col-sm-9 store-pane" id="product-categories">
                <?= $form->label('',t("Categorized under pages")); ?>

                <div class="form-group" id="page_pickers">

                    <ul class="list-group multi-select-list multi-select-sortable" id="pagelocations">
                        <?php
                        if (!empty($locationPages)) {
                            foreach ($locationPages as $location) {
                                if ($location) {
                                    $locationpage = \Page::getByID($location->getCollectionID());
                                    if ($locationpage) {
                                        echo '<li class="list-group-item">' . $locationpage->getCollectionName() . ' <a><i class="pull-right fa fa-minus-circle"></i></a> <input type="hidden" name="cID[]" value="' . $location->getCollectionID() . '" /></li>';
                                    }
                                }
                            }
                        }
                        ?>
                    </ul>

                    <script type="text/javascript">
                        $(function() {
                            $('#pagelocations').sortable({axis: 'y'});
                        });
                    </script>

                    <div class="page_picker">
                        <?= $ps->selectPage('noneselection'); ?>
                    </div>
                </div>

                <?= $form->label('',t("In product groups")); ?>
                <div class="ccm-search-field-content ccm-search-field-content-select2">
                    <select multiple="multiple" name="pProductGroups[]" class="existing-select2 select2-select" style="width: 100%"
                            placeholder="<?= (empty($productgroups) ? t('No Product Groups Available') :  t('Select Product Groups')); ?>">
                        <?php
                            if (!empty($productgroups)) {
                                if (!is_array($pgroups)) {
                                    $pgroups = array();
                                }
                                foreach ($productgroups as $pgkey=>$pglabel) { ?>
                            <option value="<?= $pgkey;?>" <?= (in_array($pgkey, $pgroups) ? 'selected="selected"' : ''); ?>>  <?= $pglabel; ?></option>
                        <?php   }
                            } ?>
                    </select>
                </div>


                <script>
                    $(document).ready(function(){
                        $('.existing-select2').select2();

                        $('#pagelocations').on('click', 'a', function(){
                            $(this).parent().remove();
                        });

                        Concrete.event.bind('ConcreteSitemap', function(e, instance) {
                            var instance = instance;

                            Concrete.event.bind('SitemapSelectPage', function(e, data) {
                                if (data.instance == instance) {
                                    Concrete.event.unbind(e);

                                    //var existing = $('#pagelocations input[value=' + + ']').size();
                                    if($('#pagelocations input[value=' + data.cID + ']').size() == 0) {
                                        $('#pagelocations').append('<li class="list-group-item">' + data.title + '<a><i class="pull-right fa fa-minus-circle"></i></a> <input type="hidden" name="cID[]" value="' + data.cID + '" /></li>');
                                    }

                                    $('.page_picker > div').hide();

                                    setTimeout(function() {
                                           $('#product-categories a[data-page-selector-action=clear]').trigger( "click" );
                                        $('.page_picker > div').show();
                                    }, 1000);
                                }
                            });
                        });

                    });
                </script>

                <style>
                    .picker_hidden {
                        display: none;
                    }
                </style>
            </div><!-- #product-categories -->


            <div class="col-sm-9 store-pane" id="product-shipping">

                <div class="form-group">
                    <?= $form->label("pShippable", t("Product is Shippable"));?>
                    <?= $form->select("pShippable",array('1'=>t('Yes'),'0'=>t('No')), ($product->isShippable() ? '1' : '0'));?>
                </div>
                <div class="row">
                    <div class="col-xs-6">
                        <div class="form-group">
                            <?= $form->label("pWeight", t("Weight"));?>
                            <div class="input-group" >
                                <?php $weight = $product->getWeight(); ?>
                                <?= $form->text('pWeight',$weight?$weight:'0')?>
                                <div class="input-group-addon"><?= Config::get('community_store.weightUnit')?></div>
                            </div>
                        </div>

                        <div class="form-group">
                            <?= $form->label("pNumberItems", t("Number Of Items"));?>
                                <?= $form->number('pNumberItems',$product->getNumberItems(), array('min'=>0, 'step'=>1))?>
                        </div>
                        <div class="form-group">
                            <?= $form->label("pSeperateShip", t("Product can be packaged with other items"));?>
                            <?= $form->select("pSeperateShip",array('0'=>t('Yes'),'1'=>t('No, must be shipped as seperate package')), ($product->getSeperateShip() ? '1' : '0'));?>
                        </div>
                    </div>
                    <div class="col-xs-6">
                        <div class="form-group">
                            <div class="form-group">
                                <?= $form->label("pLength", t("Length"));?>
                                <div class="input-group" >
                                    <?php $length = $product->getLength(); ?>
                                    <?= $form->text('pLength',$length?$length:'0')?>
                                    <div class="input-group-addon"><?= Config::get('community_store.sizeUnit')?></div>
                                </div>
                            </div>
                            <div class="form-group">
                                <?= $form->label("pWidth", t("Width"));?>
                                <div class="input-group" >
                                    <?php $width = $product->getWidth(); ?>
                                    <?= $form->text('pWidth',$width?$width:'0')?>
                                    <div class="input-group-addon"><?= Config::get('community_store.sizeUnit')?></div>
                                </div>
                            </div>
                            <div class="form-group">
                                <?= $form->label("pHeight", t("Height"));?>
                                <div class="input-group">
                                    <?php $height = $product->getHeight(); ?>
                                    <?= $form->text('pHeight',$height?$height:'0')?>
                                    <div class="input-group-addon"><?= Config::get('community_store.sizeUnit')?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row <?= Config::get('community_store.multiplePackages') ? '' : 'hidden'; ?>">
                    <div class="col-xs-12">
                        <div class="form-group">
                            <?= $form->label("pPackageData", t("Or, Package(s) Data"));?>
                            <?= $form->textarea('pPackageData',$product->getPackageData(), array('rows'=>4, 'placeholder'=>t('%s LENGTHxWIDTHxHEIGHT', strtoupper(Config::get('community_store.weightUnit')))))?>
                            <span class="help-block">
                                <?= t('Values entered will override individual set weights and sizes'); ?>
                                <br />
                                <?= t('Enter packages on new lines, using the format:');?>
                                <br />
                                <?= t('%s LENGTHxWIDTHxHEIGHT', strtoupper(Config::get('community_store.weightUnit'))); ?>
                                <br />
                                <?= t('E.g. 10 4x6x8'); ?>
                            </span>
                        </div>
                    </div>
                </div>

            </div><!-- #product-shipping -->

            <div class="col-sm-9 store-pane" id="product-images">

                <div class="form-group">
                    <?= $form->label('pfID',t("Primary Product Image")); ?>
                    <?php $pfID = $product->getImageID(); ?>
                    <?= $al->image('ccm-image', 'pfID', t('Choose Image'), $pfID ? File::getByID($pfID):null); ?>
                </div>

                <?= $form->label('',t("Additional Images")); ?>

                <ul class="list-group multi-select-list multi-select-sortable" id="additional-image-list">
                    <?php  foreach ($product->getimagesobjects() as $file) {
                        if ($file) {
                            $thumb = $file->getListingThumbnailImage();
                            if ($thumb) {
                                echo '<li class="list-group-item">' . $thumb . ' ' . $file->getTitle() . '<a><i class="pull-right fa fa-minus-circle"></i></a><input type="hidden" name="pifID[]" value="' . $file->getFileID() . '" /></li>';
                            }
                        }
                    }
                    ?>
                </ul>

                <div href="#" id="launch_additional" data-launch="file-manager" class="ccm-file-selector"><div class="ccm-file-selector-choose-new"><?= t('Choose Images'); ?></div></div>
                <script type="text/javascript">
                    $(function() {
                        $('#launch_additional').on('click', function(e) {
                            e.preventDefault();

                            var options = {
                                multipleSelection: true,
                                filters : [{ field : 'type', type: '<?= \Concrete\Core\File\Type\Type::T_IMAGE; ?>'}]
                            };

                            ConcreteFileManager.launchDialog(function (data) {
                                ConcreteFileManager.getFileDetails(data.fID, function(r) {
                                    for(var i in r.files) {
                                        var file = r.files[i];
                                        $('#additional-image-list').append('<li class="list-group-item">'+ file.resultsThumbnailImg +' ' +  file.title +'<a><i class="pull-right fa fa-minus-circle"></i></a><input type="hidden" name="pifID[]" value="' + file.fID + '" /></li>');
                                    }

                                });
                            },options);
                        });

                        $('#additional-image-list').sortable({axis: 'y'});

                        $('#additional-image-list').on('click', 'a', function(){
                            $(this).parent().remove();
                        });
                    });
                </script>

            </div><!-- #product-images -->


            <div class="col-sm-9 store-pane" id="product-options">

                <?= $form->label('',t("Options")); ?>
                <div id="product-options-container"></div>

                <div class="clearfix">
                    <h4><?= t('Add'); ?></h4>
                    <span class="btn btn-primary" id="btn-add-option-group"><?= t('Option List')?></span>
                    <span class="btn btn-primary" id="btn-add-text"><?= t('Text Field')?></span>
                    <span class="btn btn-primary" id="btn-add-textarea"><?= t('Text Area')?></span>
                    <span class="btn btn-primary" id="btn-add-checkbox"><?= t('Checkbox')?></span>
                    <span class="btn btn-primary" id="btn-add-hidden"><?= t('Hidden Value')?></span>
                </div>

                <!-- THE TEMPLATE WE'LL USE FOR EACH OPTION GROUP -->
                <script type="text/template" id="option-group-template">
                    <div class="panel panel-default option-group clearfix" data-order="<%=sort%>">
                        <div class="panel-heading">

                            <div class="row">
                                <div class="col-xs-6">
                                    <h3 class="panel-title"><i class="fa fa-arrows drag-handle"></i> <%=poLabel%></h3>
                                </div>
                                <div class="col-xs-6 text-right">
                                     <a href="javascript:deleteOptionGroup(<%=sort%>)" class="btn btn-sm btn-delete-item btn-danger"><i data-toggle="tooltip" data-placement="top" title="<?= t('Delete the Option Group')?>" class="fa fa-times"></i> Remove</a>
                                </div>
                            </div>
                        </div>
                        <div class="panel-body">

                            <div class="row">
                                <div class="col-xs-5">
                                    <div class="form-group">
                                        <label for="poName<%=sort%>" ><?= t('Option Name');?></label>
                                        <input type="text" class="form-control" name="poName[]" value="<%=poName%>">
                                    </div>
                                </div>
                                <div class="col-xs-4">
                                    <div class="form-group">
                                        <label><?= t('Option Handle');?></label>
                                        <input type="text" class="form-control" name="poHandle[]" placeholder="<?= t('Optional');?>" value="<%=poHandle%>">
                                    </div>
                                    <% if (poType == 'select') { %>
                                    <div class="form-group">
                                        <label><?= t('Display Type');?></label>
                                        <select class="form-control" name="poDisplayType[]"><option value="select" <% if (poDisplayType == 'select') { %>selected="selected"<% } %>><?= t('Drop-down');?></option><option value="radio" <% if (poDisplayType == 'radio') { %>selected="selected"<% } %>><?= t('Radio Buttons');?></option></select>
                                    </div>
                                    <% } %>
                                </div>

                                <% if (poType == 'select') { %>
                                <div class="col-xs-3">
                                    <div class="form-group">
                                        <label><?= t('In Variations');?></label>
                                        <select class="form-control" name="poIncludeVariations[]"><option value="1" <% if (poIncludeVariations == 1) { %>selected="selected"<% } %>><?= t('Yes');?></option><option value="0" <% if (poIncludeVariations == 0) { %>selected="selected"<% } %>><?= t('No');?></option></select>
                                    </div>
                                </div>
                                <% } else { %>
                                    <input type="hidden" value="0" name="poIncludeVariations[]" />
                                <% } %>
                                <% if (poType != 'select' && poType != 'checkbox') { %>
                                <div class="col-xs-3">
                                    <div class="form-group">
                                        <label><?= t('Required');?></label>
                                        <select class="form-control" name="poRequired[]"><option value="0"><?= t('No');?></option><option value="1" <% if (poRequired == 1) { %>selected="selected"<% } %>><?= t('Yes');?></option></select>
                                    </div>
                                </div>
                                <% } else {  %>
                                    <input type="hidden" value="0" name="poRequired[]" />
                                <% } %>
                            </div>

                            <% if (poType == 'select') { %>
                            <hr />
                            <div data-group="<%=sort%>" class="option-group-item-container"></div>
                            <a href="javascript:addOptionItem(<%=sort%>)" data-group="<%=sort%>" class="btn btn-default"><?= t('Add Option')?></a>
                            <% } %>
                         </div>
                            <input type="hidden" name="poID[]" value="<%=poID%>">
                            <input type="hidden" name="poType[]" value="<%=poType%>">
                            <input type="hidden" name="poSort[]" value="<%=sort%>" class="option-group-sort">
                        </div>

                    </div><!-- .option-group -->
                </script>
                <script type="text/javascript">
                    function indexOptionGroups(){
                        $('#product-options-container .option-group').each(function(i) {
                            $(this).find('.option-group-sort').val(i);
                            $(this).attr("data-order",i);
                            $(this).find('.optGroupID').attr("name","optGroup"+i+"[]");
                        });
                    }


                    function deleteOptionGroup(id){
                        var variationeffect = $(".option-group[data-order='"+id+"'] select[name=poIncludeVariations\\[\\]] option:selected");

                        if (variationeffect && variationeffect.val() == 1) {
                            $('#variationshider').addClass('hidden');
                            $('#changenotice').removeClass('hidden');
                        }

                        $(".option-group[data-order='"+id+"']").remove();

                        indexOptionGroups();

                    }

                    $(function(){


                        $(document).on('change','select[name=poIncludeVariations\\[\\]]', function(){
                            $('#variationshider').addClass('hidden');
                            $('#changenotice').removeClass('hidden');
                            $('#changewarning').removeClass('hidden');
                        });


                        //Make items sortable. If we re-sort them, re-index them.
                        $("#product-options-container").sortable({
                            handle: ".panel-heading",
                            update: function(){
                                indexOptionGroups();
                            }
                        });

                        //Define container and items
                        var optionsContainer = $('#product-options-container');
                        var optionsTemplate = _.template($('#option-group-template').html());

                        //load up existing option groups
                        <?php



                        if($options) {
                        $optionsort = 0;
                            foreach ($options as $option) {

                            $type = $option->getType();
                            $displayType = $option->getDisplayType();
                            $handle = $option->getHandle();
                            $required = $option->getRequired();
                            $includeVariations = $option->getIncludeVariations();

                            $labels = array();
                            $labels['select'] = t('Option List');
                            $labels['text'] = t('Text Input');
                            $labels['textarea'] = t('Text Area Input');
                            $labels['checkbox'] = t('Checkbox');
                            $labels['hidden'] = t('Hidden Value');


                            if (!$type) {
                                $type = 'select';
                            }

                            $label = $labels[$type];

                        ?>
                        optionsContainer.append(optionsTemplate({
                            poName: '<?= h($option->getName()) ?>',
                            poID: '<?= $option->getID()?>',
                            poType: '<?= $type ?>',
                            poDisplayType: '<?= $displayType ?>',
                            poLabel: '<?= $label; ?>',
                            poHandle: '<?= h($handle); ?>',
                            poRequired: '<?= $required ? 1 : 0; ?>',
                            poIncludeVariations: '<?= $includeVariations ? 1 : 0; ?>',
                            sort: '<?= $optionsort ?>'
                        }));
                        <?php

                        $optionsort++;
                            }
                        }
                        ?>

                        //add item
                        $('#btn-add-option-group').click(function(){

                            //Use the template to create a new item.
                            var temp = $(".option-group").length;
                            temp = (temp);
                            optionsContainer.append(optionsTemplate({
                                //vars to pass to the template
                                poName: '',
                                poID: '',
                                poType: 'select',
                                poDisplayType: 'select',
                                poLabel: '<?= $labels['select']; ?>',
                                poHandle: '',
                                poRequired: '',
                                poIncludeVariations: '0',
                                sort: temp
                            }));

                            //Init Index
                            indexOptionGroups();

//                            $('#variationshider').addClass('hidden');
//                            $('#changenotice').removeClass('hidden');
                        });



                        $('#btn-add-text').click(function(){

                            //Use the template to create a new item.
                            var temp = $(".option-group").length;
                            temp = (temp);
                            optionsContainer.append(optionsTemplate({
                                //vars to pass to the template
                                poName: '',
                                poID: '',
                                poType: 'text',
                                poLabel: '<?= $labels['text']; ?>',
                                poHandle: '',
                                poRequired: '',
                                sort: temp
                            }));

                            //Init Index
                            indexOptionGroups();
                        });

                        $('#btn-add-textarea').click(function(){

                            //Use the template to create a new item.
                            var temp = $(".option-group").length;
                            temp = (temp);
                            optionsContainer.append(optionsTemplate({
                                //vars to pass to the template
                                poName: '',
                                poID: '',
                                poType: 'textarea',
                                poLabel: '<?= $labels['textarea']; ?>',
                                poHandle: '',
                                poRequired: '',
                                sort: temp
                            }));

                            //Init Index
                            indexOptionGroups();
                        });

                        $('#btn-add-checkbox').click(function(){

                            //Use the template to create a new item.
                            var temp = $(".option-group").length;
                            temp = (temp);
                            optionsContainer.append(optionsTemplate({
                                //vars to pass to the template
                                poName: '',
                                poID: '',
                                poType: 'checkbox',
                                poLabel: '<?= $labels['checkbox']; ?>',
                                poHandle: '',
                                poRequired: '',
                                sort: temp
                            }));

                            //Init Index
                            indexOptionGroups();
                        });

                        $('#btn-add-hidden').click(function(){

                            //Use the template to create a new item.
                            var temp = $(".option-group").length;
                            temp = (temp);
                            optionsContainer.append(optionsTemplate({
                                //vars to pass to the template
                                poName: '',
                                poID: '',
                                poType: 'hidden',
                                poLabel: '<?= $labels['hidden']; ?>',
                                poHandle: '',
                                poRequired: '',
                                sort: temp
                            }));

                            //Init Index
                            indexOptionGroups();
                        });

                        indexOptionGroups();
                    });

                </script>
                <!-- TEMPLATE FOR EACH OPTION ITEM ---->
                <script type="text/template" id="option-item-template">
                    <div class="option-item clearfix form-horizontal" data-order="<%=sort%>" data-option-group="<%=optGroup%>">
                        <div class="form-group">
                            <div class="col-sm-3 text-right">
                                <label class="grabme"><i class="fa fa-arrows drag-handle pull-left"></i><?= t('Option')?></label>
                            </div>
                            <div class="col-sm-7">
                                <div class="input-group">
                                <input type="text" name="poiName[]" class="form-control" value="<%=poiName%>">
                                    <div class="input-group-addon">
                                        <label>
                                            <input type="hidden" name="poiHidden[]" value="<%=poiHiddenValue%>" />
                                            <input type="checkbox" class="optionHiddenToggle" name="poiHiddenToggle[]" value="1" <%=poiHidden%> /> <?= t('Hide'); ?></label>
                                    </div>
                                </div>
                                <input type="hidden" name="poiID[]" class="form-control" value="<%=poiID%>">
                            </div>
                            <div class="col-sm-2">
                                <a href="javascript:deleteOptionItem(<%=optGroup%>,<%=sort%>)" class="btn btn-danger"><i class="fa fa-times"></i></a>
                            </div>
                        </div>
                        <input type="hidden" name="optGroup<%=optGroup%>[]" class="optGroupID" value="">
                        <input type="hidden" name="poiSort[]" value="<%=sort%>" class="option-item-sort">
                    </div><!-- .option-group -->
                </script>
                <script type="text/javascript">
                    function deleteOptionItem(group,id){
                        $(".option-group[data-order='"+group+"']").find(".option-item[data-order='"+id+"']").remove();

                        $('#variationshider').addClass('hidden');
                        $('#changenotice').removeClass('hidden');
                    }

                    function indexOptionItems(){
                        $('.option-group-item-container').each(function(){
                            $(this).find('.option-item').each(function(i) {
                                $(this).find('.option-item-sort').val(i);
                                $(this).attr("data-order",i);
                            });
                        });

                    }

                    function addOptionItem(group){
                        var optItemsTemplate = _.template($('#option-item-template').html());
                        var optItemsContainer = $(".option-group-item-container[data-group='"+group+"']");

                        //Use the template to create a new item.
                        var temp = $(".option-group-item-container[data-group='"+group+"'] .option-item").length;
                        temp = (temp);
                        optItemsContainer.append(optItemsTemplate({
                            //vars to pass to the template
                            poiName: '',
                            poiID: '',
                            optGroup: group,
                            sort: temp,
                            poiHidden: '',
                            poiHiddenValue: '0'
                        }));

                        //Init Index
                        indexOptionItems();
                        $('#variationshider').addClass('hidden');
                        $('#changenotice').removeClass('hidden');
                    }

                    // add handler for hide checkbox, to adjust hidden value when changed
                    $(document).on('change', '.optionHiddenToggle', function() {
                        $(this).prev().val(($(this).prop('checked') ? '1' : '0'));
                    });

                    $(function(){
                        //Make items sortable. If we re-sort them, re-index them.
                        $(".option-group-item-container").sortable({
                            handle: ".grabme",
                            update: function(){
                                indexOptionItems();
                            }
                        });

                        //define template
                        var optItemsTemplate = _.template($('#option-item-template').html());

                        //load up items
                        <?php
                        if($options) {
                            $count = count($options);
                            for($i=0;$i<$count;$i++){
                                foreach($options[$i]->getOptionItems() as $optionItem){
                                    if($optionItem->getOptionID() == $options[$i]->getID()){

                                    ?>

                        var optItemsContainer = $(".option-group-item-container[data-group='<?= $i?>']");
                        optItemsContainer.append(optItemsTemplate({
                            poiName: '<?= h($optionItem->getName())?>',
                            poiID: '<?= $optionItem->getID()?>',
                            optGroup: <?= $i?>,
                            sort: <?= $optionItem->getSort()?>,
                            poiHidden: <?= ($optionItem->isHidden() ? '\'checked="checked"\'' : '""'); ?>,
                            poiHiddenValue:  '<?= ($optionItem->isHidden() ? '1' : '0'); ?>'

                        }));
                        <?php
                        }//if belongs to group
                    }//foreach opt
                }
            }//if items
            ?>
                        //indexOptionItems();
                    });

                </script>

            <br />
            <div class="form-group">
                <h3><?= t('Variations');?></h3>
                <label class="control-label"><?= $form->checkbox('pVariations', '1', $product->hasVariations() ? '1' : '0')?>
                <?= t('Options have different prices, SKUs or stock levels');?></label>

                <?php if (!$pID) { ?>
                    <p class="alert alert-info hidden" id="variationnotice"><?= t('After creating options add the product to configure product variations.') ?></p>
                <?php } ?>


            </div>

            <?php if (!empty($comboOptions)) { ?>
            <div id="variations" class="<?= ($product->hasVariations() ? '' : 'hidden');?>">

                <p class="alert alert-danger hidden" id="changewarning"><?= t('Warning: Product options have changed that will create different variations - any existing variation data will be lost') ?></p>

                <?php if ($pID) { ?>
                    <p class="alert alert-info hidden" id="changenotice"><?= t('Product options have changed, update the product to configure updated variations') ?></p>
                <?php } ?>

                <div id="variationshider">

                 <?php
                 if ($product->hasVariations()) {
                     $count = 0;

                     foreach ($comboOptions as $combinedOptions) {
                         ?>
                         <div class="panel panel-default">
                             <div class="panel-heading">
                                 <?= t('Options') . ':'; ?>
                                 <?php
                                 $comboIDs = array();

                                 foreach ($combinedOptions as $optionItemID) {
                                     $comboIDs[] = $optionItemID;
                                     sort($comboIDs);
                                     $group = $optionLookup[$optionItemLookup[$optionItemID]->getOptionID()];
                                     echo '<span class="label label-primary">' . ($group ? $group->getName() : '') . ': ' . $optionItemLookup[$optionItemID]->getName() . '</span> ';
                                 }

                                 ?>
                                 <button class="btn btn-xs btn-default pull-right variationdisplaybutton" type="button"
                                         data-toggle="collapse">
                                     <?= t('More options'); ?>
                                 </button>
                             </div>

                             <div class="panel-body">
                                 <input type="hidden" name="option_combo[]" value="<?= implode('_', $comboIDs); ?>"/>

                                 <?php

                                 if (isset($variationLookup[implode('_', $comboIDs)])) {
                                     $variation = $variationLookup[implode('_', $comboIDs)];
                                     $varid = $variation->getID();
                                 } else {
                                     $variation = null;
                                     $varid = '';
                                 } ?>

                                 <div class="row form-group">
                                     <div class="col-md-4">
                                         <?= $form->label("", t("SKU")); ?>
                                     </div>
                                     <div class="col-md-8">
                                         <?= $form->text("pvSKU[" . $varid . "]", $variation ? $variation->getVariationSKU() : '', array('placeholder' => t('Base SKU'))); ?>
                                     </div>
                                 </div>

                                 <div class="row form-group">
                                     <div class="col-md-4">
                                         <?= $form->label("", t("Stock Level")); ?>
                                     </div>
                                     <div class="col-md-8">
                                         <div class="input-group">
                                             <?php
                                             if ($variation) {
                                                 echo $form->number("pvQty[" . $varid . "]", round($variation->getVariationQty(), 3), array(($variation->isUnlimited() ? 'readonly' : '') => ($variation->isUnlimited() ? 'readonly' : ''),'step'=>0.001));
                                             } else {
                                                 echo $form->number("pvQty[" . $varid . "]", '', array('readonly' => 'readonly','step'=>0.001));
                                             }
                                             ?>

                                             <div class="input-group-addon">
                                                 <label><?= $form->checkbox('pvQtyUnlim[' . $varid . ']', '1', $variation ? $variation->isUnlimited() : true) ?> <?= t('Unlimited'); ?></label>
                                             </div>
                                         </div>
                                     </div>
                                 </div>

                                 <div class="row form-group">
                                     <div class="col-md-4">
                                         <?= $form->label("", t("Price")); ?>
                                     </div>
                                     <div class="col-md-8">
                                         <div class="input-group">
                                             <div class="input-group-addon">
                                                 <?= Config::get('community_store.symbol'); ?>
                                             </div>
                                             <?= $form->text("pvPrice[" . $varid . "]", $variation ? $variation->getVariationPrice() : '', array('placeholder' => t('Base Price'))); ?>
                                         </div>
                                     </div>
                                 </div>

                                 <div class="row form-group">
                                     <div class="col-md-4">
                                         <?= $form->label('pfID[]', t("Primary Image")); ?>
                                     </div>
                                     <?php
                                     $pvfID = null;
                                     if ($variation) {
                                         $pvfID = $variation->getVariationImageID();
                                     }
                                     ?>
                                     <div class="col-md-8">
                                         <?= $al->image('ccm-image' . $count++, 'pvfID[' . $varid . ']', t('Choose Image'), $pvfID ? File::getByID($pvfID) : null); ?>
                                     </div>
                                 </div>

                                 <div class="extrafields hidden">

                                     <div class="row form-group">
                                         <div class="col-md-4">
                                             <?= $form->label("pvSalePrice[]", t("Sale Price")); ?>
                                         </div>
                                         <div class="col-md-8">
                                             <div class="input-group">
                                                 <div class="input-group-addon">
                                                     <?= Config::get('community_store.symbol'); ?>
                                                 </div>
                                                 <?= $form->text("pvSalePrice[" . $varid . "]", $variation ? $variation->getVariationSalePrice() : '', array('placeholder' => t('Base Sale Price'))); ?>
                                             </div>
                                         </div>
                                     </div>


                                     <div class="row form-group">
                                         <div class="col-md-4">
                                             <?= $form->label("", t("Weight")); ?>
                                         </div>
                                         <div class="col-md-8">
                                             <div class="input-group">
                                                 <?= $form->text('pvWeight[' . $varid . ']', $variation ? $variation->getVariationWeight() : '', array('placeholder' => t('Base Weight'))) ?>
                                                 <div class="input-group-addon"><?= Config::get('community_store.weightUnit') ?></div>
                                             </div>
                                         </div>
                                     </div>
                                     <div class="row form-group">
                                         <div class="col-md-4">
                                             <?= $form->label("", t("Number of Items")); ?>
                                         </div>
                                         <div class="col-md-8">
                                             <?= $form->text('pvNumberItems[' . $varid . ']', $variation ? $variation->getVariationNumberItems() : '', array('min' => 0, 'step' => 1, 'placeholder' => t('Base Number Of Items'))) ?>
                                         </div>
                                     </div>
                                     <div class="row form-group">
                                         <div class="col-md-4">
                                             <?= $form->label("", t("Length")); ?>
                                         </div>
                                         <div class="col-md-8">
                                             <div class="input-group">
                                                 <?= $form->text('pvLength[' . $varid . ']', $variation ? $variation->getVariationLength() : '', array('placeholder' => t('Base Length'))) ?>
                                                 <div class="input-group-addon"><?= Config::get('community_store.sizeUnit') ?></div>
                                             </div>
                                         </div>
                                     </div>
                                     <div class="row form-group">
                                         <div class="col-md-4">
                                             <?= $form->label("", t("Width")); ?>
                                         </div>

                                         <div class="col-md-8">
                                             <div class="input-group">
                                                 <?= $form->text('pvWidth[' . $varid . ']', $variation ? $variation->getVariationWidth() : '', array('placeholder' => t('Base Width'))) ?>
                                                 <div class="input-group-addon"><?= Config::get('community_store.sizeUnit') ?></div>
                                             </div>
                                         </div>
                                     </div>
                                     <div class="row form-group">
                                         <div class="col-md-4">
                                             <?= $form->label("", t("Height")); ?>
                                         </div>
                                         <div class="col-md-8">
                                             <div class="input-group">
                                                 <?= $form->text('pvHeight[' . $varid . ']', $variation ? $variation->getVariationHeight() : '', array('placeholder' => t('Base Height'))) ?>
                                                 <div class="input-group-addon"><?= Config::get('community_store.sizeUnit') ?></div>
                                             </div>
                                         </div>
                                     </div>

                                     <div class="row <?= Config::get('community_store.multiplePackages') ? '' : 'hidden'; ?>">
                                         <div class="form-group">
                                             <div class="col-md-4">
                                                <?= $form->label('pvPackageData[' . $varid . ']', t("Or, Package(s) Data"));?>
                                             </div>
                                             <div class="col-md-8">
                                                 <?= $form->textarea('pvPackageData[' . $varid . ']',$variation->getVariationPackageData(), array('rows'=>4, 'placeholder'=>t('%s LENGTHxWIDTHxHEIGHT', strtoupper(Config::get('community_store.weightUnit')))))?>
                                             </div>
                                         </div>
                                     </div>
                                 </div>
                             </div>

                         </div>
                     <?php }

                     if (count($comboOptions) >= 50) { ?>
                         <p class="alert alert-warning"><?= t('Maximum variations reached') ?></p>
                     <?php } ?>

                 <?php } else { ?>
                     <p class="alert alert-info"><?= t('Update the product to display variations') ?></p>
                 <?php } ?>
                </div>
                </div>
            <?php } ?>

            </div><!-- #product-options -->

            <div class="col-sm-9 store-pane" id="product-related">

                <?= $form->label("", t("Related Products")); ?>

                <ul class="list-group multi-select-list multi-select-sortable" id="related-products">
                    <?php
                    $relatedProducts = $product->getRelatedProducts();
                    if (!empty($relatedProducts)) {
                        foreach ($relatedProducts as $relatedProduct) {
                            echo '<li class="list-group-item">' . $relatedProduct->getRelatedProduct()->getName() . '<input type="hidden" name="pRelatedProducts[]" value="'.$relatedProduct->getRelatedProduct()->getID().'" /><a><i class="pull-right fa fa-minus-circle"></i></a></li>';
                        }
                    }
                    ?>
                </ul>

                <div class="form-group" id="product-search">
                    <input name="relatedpID" id="product-select"    style="width: 100%" placeholder="<?= t('Search for a Product') ?>" />
                </div>

                <script type="text/javascript">

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
                                callback({});
                            }
                        }).select2('val', []);

                        $('#product-select').on("change", function(e) {
                            var data = $(this).select2('data');
                            $('#related-products').append('<li class="list-group-item">'+ data.text  +'<a><i class="pull-right fa fa-minus-circle"></i> <input type="hidden" name="pRelatedProducts[]" value="' + data.id + '" /></a> </li>');
                            $(this).select2("val", []);
                        });

                        $('#related-products').on('click', 'a', function(){
                            $(this).parent().remove();
                        });

                        $('#related-products').sortable({axis: 'y'});

                    });

                </script>

            </div><!-- #product-related -->

            <div class="col-sm-9 store-pane" id="product-attributes">
                <div class="alert alert-info">
                    <?= t("While you can set and assign attributes, they're are currently only able to be accessed programmatically")?>
                </div>
                <?php

                if (count($attribs) > 0) {
                    foreach($attribs as $ak) {
                        if (is_object($product)) {
                            $caValue = $product->getAttributeValueObject($ak);
                        }
                        ?>
                        <div class="form-group">
                            <?= $ak->render('label');?>
                            <div class="input">
                                <?= $ak->render('composer', $caValue, true)?>
                            </div>
                        </div>
                    <?php  } ?>

                <?php  } else {?>
                    <em><?= t('You haven\'t created product attributes')?></em>

                <?php }?>

            </div>

            <div class="col-sm-9 store-pane" id="product-digital">
                <?php if (Config::get('concrete.permissions.model') != 'simple') { ?>
                    <?php
                    $files = $product->getDownloadFileObjects();
                    for($i=0;$i<1;$i++){
                        $file = $files[$i];
                        ?>
                        <div class="form-group">
                            <?= $form->label("ddfID".$i, t("File to download on purchase"));?>
                            <?= $al->file('ddfID'.$i, 'ddfID[]', t('Choose File'), is_object($file)?$file:null)?>
                        </div>
                    <?php }
                } else { ?>
                    <div class="alert alert-info">
                        <?php
                        $a = '<a href="'.URL::to('/dashboard/system/permissions/advanced').'"><strong>';
                        $aa = '</strong></a>';
                        echo t("In order to have digital downloads, you need to %sturn on advanced permissions%s.",$a,$aa);
                        ?>
                    </div>
                <?php } ?>

                <div class="form-group">
                    <?= $form->checkbox('pCreateUserAccount', '1', $product->createsLogin())?>
                    <?= $form->label('pCreateUserAccount', t('Create user account on purchase'))?>
                    <span class="help-block"><?=  t('When checked, if customer is guest, will create a user account on purchase'); ?></span>
                </div>

                <div class="form-group">
                    <?= $form->label("usergroups", t("On purchase add user to user groups"));?>
                    <div class="ccm-search-field-content ccm-search-field-content-select2">
                        <select multiple="multiple" name="pUserGroups[]" id="groupselect" class="select2-select" style="width: 100%;" placeholder="<?= t('Select user groups');?>">
                            <?php
                            $selectedusergroups = $product->getUserGroupIDs();
                            foreach ($usergroups as $ugkey=>$uglabel) { ?>
                                <option value="<?= $ugkey;?>" <?= (in_array($ugkey, $selectedusergroups) ? 'selected="selected"' : ''); ?>>  <?= $uglabel; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>


                <div class="form-group">
                    <?= $form->checkbox('pAutoCheckout', '1', $product->autoCheckout())?>
                    <?= $form->label('pAutoCheckout', t('Send customer directly to checkout when added to cart'))?>
                </div>

                <div class="form-group">
                    <?= $form->checkbox('pExclusive', '1', $product->isExclusive())?>
                    <?= $form->label('pExclusive', t('Prevent this item from being in the cart with other items'))?>
                </div>


                <script type="text/javascript">
                    $(document).ready(function() {
                        $('.select2-select').select2();
                    });
                </script>


            </div><!-- #product-digital -->

            <div class="col-sm-9 store-pane" id="product-page">

                <?php if($page && !$page->isInTrash()){ ?>
                    <strong><?= t("Detail Page is set to: ")?><a href="<?= $page->getCollectionLink()?>" target="_blank"><?= $page->getCollectionName()?></a></strong>
                    <?= $ps->selectPage('pageCID', $page->getCollectionID()); ?>
                <?php } else { ?>

                    <?php if ($product->getID()) { ?>
                    <div class="alert alert-warning">
                        <?= t("This product is missing a corresponding page in the sitemap")?>
                    </div>
                        <label for="pageCID"><?= t('Associate with an existing page'); ?></label>
                        <?= $ps->selectPage('pageCID', ''); ?>
                    <?php } ?>
                    <br />
                    <label><?= t('Or, create a new product page') ;?></label>

                    <?php if ($productPublishTarget) { ?>
                        <?php if ($pageTemplates && !empty($pageTemplates)) { ?>
                        <div class="form-group">
                            <label><?= t("Page Template")?></label>
                            <?= $form->select('selectPageTemplate',$pageTemplates,$defaultTemplateID);?>
                        </div>

                        <?php if ($product->getID()) { ?>
                            <a data-confirm-message="<?= h(t('Any changes to the product will not be saved. Create product page?'));?>" href="<?= \URL::to('/dashboard/store/products/generate/',$product->getID())?>" class="btn btn-primary" id="btn-generate-page"><?= t("Generate a Product Page")?></a>
                        <?php } ?>
                        <?php } else { ?>
                            <div class="alert alert-warning">
                                <?= t("A Page Type with the handle store_product was not found")?>
                            </div>
                        <?php } ?>
                    <?php } else { ?>
                        <div class="alert alert-warning">
                            <?= t("No page is configured as the parent page for new products")?>
                        </div>
                    <?php } ?>

                <?php } ?>

            </div>

        </div><!-- .row -->

        <div class="ccm-dashboard-form-actions-wrapper">
            <div class="ccm-dashboard-form-actions">
                <a href="<?= \URL::to('/dashboard/store/products/')?>" class="btn btn-default pull-left"><?= t("Cancel / View All Products")?></a>
                <button class="pull-right btn btn-success" disabled="disabled" type="submit" ><?= t('%s Product',$actionType)?></button>
            </div>
        </div>

        <script>
            $(window).load(function(){
                setTimeout(
                    function() {
                       $('.ccm-dashboard-form-actions .btn-success').removeAttr('disabled');
                    }, 500);
            });

            $(function(){
                $('.variationdisplaybutton').click(function(el) {
                   $(this).closest('.panel').find('.extrafields').toggleClass('hidden');
                    el.preventDefault();
                });
            });
        </script>

    </form>



<?php } elseif(in_array($controller->getTask(),$listViews)) { ?>

    <div class="ccm-dashboard-header-buttons">
        <!--<a href="<?= \URL::to('/dashboard/store/products/', 'attributes')?>" class="btn btn-dark"><?= t("Manage Attributes")?></a>-->
        <a href="<?= \URL::to('/dashboard/store/products/', 'groups')?>" class="btn btn-primary"><?= t("Manage Groups")?></a>
        <a href="<?= \URL::to('/dashboard/store/products/', 'add')?>" class="btn btn-primary"><?= t("Add Product")?></a>
    </div>

    <div class="ccm-dashboard-content-full">
        <form role="form" class="form-inline ccm-search-fields">
            <div class="ccm-search-fields-row">
                <?php if($grouplist){
                    $currentFilter = '';
                    ?>
                    <ul id="group-filters" class="nav nav-pills">
                        <li <?= (!$gID ? 'class="active"' : '');?>><a href="<?= \URL::to('/dashboard/store/products/')?>"><?= t('All Groups')?></a></li>

                        <li role="presentation" class="dropdown <?= ($gID ? 'active' : '');?>">
                            <?php
                            if ($gID) {
                                foreach($grouplist as $group) {
                                    if ($gID == $group->getGroupID()) {
                                        $currentFilter = $group->getGroupName();
                                    }
                                }
                            } ?>


                            <a class="dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
                                <?= $currentFilter ? t('Filtering By: %s', $currentFilter) : t('Filter By Product Group'); ?> <span class="caret"></span>
                            </a>

                            <ul class="dropdown-menu">
                                <?php foreach($grouplist as $group){ ?>
                                    <li <?= ($gID == $group->getGroupID() ? 'class="active"' : '');?>><a href="<?= \URL::to('/dashboard/store/products/', $group->getGroupID())?>"><?= $group->getGroupName()?></a></li>
                                <?php } ?>
                            </ul>
                        </li>
                    </ul>
                <?php } ?>
            </div>
            <div class="ccm-search-fields-row ccm-search-fields-submit">
                <div class="form-group">
                    <div class="ccm-search-main-lookup-field">
                        <i class="fa fa-search"></i>
                        <?= $form->search('keywords', $searchRequest['keywords'], array('placeholder' => t('Search by Name or SKU')))?>
                    </div>

                </div>
                <button type="submit" class="btn btn-default"><?= t('Search')?></button>
            </div>

        </form>

        <table class="ccm-search-results-table">
            <thead>
                <tr>
                    <th><a><?= t('Primary Image')?></a></th>
                    <th><a href="<?=  $productList->getSortURL('alpha');?>"><?= t('Product Name')?></a></th>
                    <th><a href="<?=  $productList->getSortURL('active');?>"><?= t('Active')?></a></th>
                    <th><a><?= t('Stock Level')?></a></th>
                    <th><a href="<?=  $productList->getSortURL('price');?>"><?= t('Price')?></a></th>
                    <th><a><?= t('Featured')?></a></th>
                    <th><a><?= t('Groups')?></a></th>
                    <th><a><?= t('Actions')?></a></th>
                </tr>
            </thead>
            <tbody>

            <?php if(count($products)>0) {
                foreach ($products as $product) {
                    ?>
                    <tr>
                        <td><?= $product->getImageThumb();?></td>
                        <td><strong><a href="<?= \URL::to('/dashboard/store/products/edit/', $product->getID())?>"><?=  $product->getName();
                                $sku = $product->getSKU();
                                if ($sku) {
                                    echo ' (' .$sku . ')';
                                }
                                ?>
                                </a></strong></td>
                        <td>
                            <?php
                            if ($product->isActive()) {
                                echo "<span class='label label-success'>" . t('Active') . "</span>";
                            } else {
                                echo "<span class='label label-default'>" . t('Inactive') . "</span>";
                            }
                            ?>
                        </td>
                        <td><?php
                            if ($product->hasVariations()) {
                                echo '<span class="label label-info">' . t('Multiple') . '</span>';
                            } else {
                                echo($product->isUnlimited() ? '<span class="label label-default">' . t('Unlimited') . '</span>' : $product->getQty());
                            }?></td>
                        <td>
                            <?php
                            if ($product->hasVariations()) {
                                echo '<span class="label label-info">' . t('Multiple') . '</span><br />';
                                echo t('Base price') . ': ' . $product->getFormattedPrice();
                            } else {
                                echo $product->getFormattedPrice();
                            } ?></td>
                        <td>
                            <?php
                            if ($product->isFeatured()) {
                                echo "<span class='label label-success'>" . t('Featured') . "</span>";
                            } else {
                                echo "<span class='label label-default'>" . t('Not Featured') . "</span>";
                            }
                            ?>
                        </td>
                        <td>
                            <?php $productgroups = $product->getGroups();
                            foreach($productgroups as $pg) { ?>
                                <span class="label label-primary"><?=  $pg->getGroup()->getGroupName(); ?></span>
                             <?php } ?>

                            <?php if (empty($productgroups)) { ?>
                                <em><?=  t('None');?></em>
                            <?php } ?>
                        </td>
                        <td>
                            <a class="btn btn-default"
                               href="<?= \URL::to('/dashboard/store/products/edit/', $product->getID())?>"><i
                                    class="fa fa-pencil"></i></a>
                        </td>
                    </tr>
                <?php }
            }?>
            </tbody>
        </table>

        <?php if ($paginator->getTotalPages() > 1) { ?>
            <div class="ccm-search-results-pagination">
                <?=  $pagination ?>
            </div>
        <?php } ?>

    </div>

<?php } ?>

<?php if ($controller->getTask() == 'duplicate') { ?>
    <form method="post" action="<?= $view->action('duplicate', $product->getID())?>">
        <?= $token->output('community_store'); ?>
        <div class="form-group">
            <?= $form->label('newName',t("New Product Name")); ?>
            <?= $form->text('newName',$product->getName() . ' ' . t('(Copy)')); ?>
        </div>

        <div class="form-group">
            <?= $form->label('newSKU',t("New Product SKU")); ?>
            <?= $form->text('newSKU',$product->getSKU()); ?>
        </div>

        <input type="submit" class="btn btn-primary" value="<?= t('Duplicate Product');?>">
    </form>

<?php } ?>


<style>
    @media (max-width: 992px) {
        div#ccm-dashboard-content div.ccm-dashboard-content-full {
            margin-left: -20px !important;
            margin-right: -20px !important;
        }
    }
</style>
