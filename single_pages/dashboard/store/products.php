<?php
defined('C5_EXECUTE') or die("Access Denied.");

use \Concrete\Core\Page\Page;
use \Concrete\Core\Support\Facade\Url;
use \Concrete\Core\Support\Facade\Config;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product;

$app = \Concrete\Core\Support\Facade\Application::getFacadeApplication();
$listViews = ['view', 'updated', 'removed', 'success'];
$addViews = ['add', 'edit', 'save'];
$attributeViews = ['attributes', 'attributeadded', 'attributeremoved'];
$ps = $app->make('helper/form/page_selector');
$dh = $app->make('helper/date');

$version = $app->make('config')->get('concrete.version');
$badgeClass = ' badge ';
if (version_compare($version, '9.0', '<')) {
    $badgeClass = '';
}

?>

<?php if (in_array($controller->getAction(), $addViews)) { //if adding or editing a product
    if (!is_object($product)) {
        $product = new Product();
        $product->setIsUnlimited(true);
        $product->setIsTaxable(true);

        if (!$productDefaultShippingNo) {
            $product->setIsShippable(true);
        }

        if ($productDefaultActive) {
            $product->setIsActive(true);
        }

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

            <form class="pull-right float-end" method="post" id="delete" action="<?= Url::to('/dashboard/store/products/delete/', $pID) ?>">
                <?= $token->output('community_store'); ?>&nbsp;
                <button class="btn btn-danger"><?= t('Delete Product') ?></button>
            </form>

            <form class="pull-right float-end" method="get" id="duplicate" action="<?= Url::to('/dashboard/store/products/duplicate/', $pID) ?>">
                &nbsp;&nbsp;<button class="btn btn-default btn-secondary"><i class="fa fa-copy"></i> <?= t('Duplicate Product') ?></button>
            </form>

            <?php if ($page && !$page->isInTrash()) { ?>
                <div class="pull-right float-end">
                    <a class="btn btn-primary" target="_blank" href="<?= $page->getCollectionLink() ?>" target="_blank"><?= t('View Product Page'); ?></a>
                </div>
            <?php } ?>

            <script type="text/javascript">
                $(document).ready(function () {
                    $('#delete').submit(function () {
                        return confirm('<?=  t("Are you sure you want to delete this product?"); ?>');
                    });
                });
            </script>
        </div>
    <?php } ?>

    <form method="post">
        <?= $token->output('community_store'); ?>
        <input type="hidden" name="pID" value="<?= $product->getID() ?>"/>

        <div class="row">
            <div class="col-sm-3">
                <ul class="nav nav-pills nav-stacked flex-column">
                    <li class="nav-item active"><a class="nav-link text-primary" href="#product-overview" data-pane-toggle><?= t('Overview') ?></a></li>
                    <li class="nav-item"><a class="nav-link text-primary" href="#product-descriptions" data-pane-toggle><?= t('Descriptions and Manufacturer') ?></a></li>
                    <li class="nav-item"><a class="nav-link text-primary" href="#product-images" data-pane-toggle><?= t('Images') ?></a></li>
                    <li class="nav-item"><a class="nav-link text-primary" href="#product-categories" data-pane-toggle><?= t('Categories and Groups') ?></a></li>
                    <li class="nav-item"><a class="nav-link text-primary" href="#product-shipping" data-pane-toggle><?= t('Shipping') ?></a></li>
                    <li class="nav-item"><a class="nav-link text-primary" href="#product-options" data-pane-toggle><?= t('Options and Variants') ?></a></li>
                    <li class="nav-item"><a class="nav-link text-primary" href="#product-related" data-pane-toggle><?= t('Related Products') ?></a></li>
                    <li class="nav-item"><a class="nav-link text-primary" href="#product-attributes" data-pane-toggle><?= t('Attributes') ?></a></li>
                    <li class="nav-item"><a class="nav-link text-primary" href="#product-digital" data-pane-toggle><?= t("Downloads and User Groups") ?></a></li>
                    <li class="nav-item"><a class="nav-link text-primary" href="#product-checkout" data-pane-toggle><?= t("Checkout Options") ?></a></li>
                    <li class="nav-item"><a class="nav-link text-primary" href="#product-page" data-pane-toggle><?= t('Detail Page') ?></a></li>
                </ul>
            </div>
            <div class="col-sm-9">
                <div id="product-header">

                    <div class="row">
                        <div class="col-md-8 col">
                            <div class="form-group">
                                <?= $form->label("pName", t('Product Name')); ?>
                                <?= $form->text("pName", $product->getName(), ['required' => 'required']); ?>
                            </div>
                        </div>
                        <div class="col-md-4 col">
                            <div class="form-group">
                                <?= $form->label("pSKU", t('Code / SKU')); ?>
                                <?= $form->text("pSKU", $product->getSKU(), ['maxlength' => 100]); ?>
                            </div>
                        </div>

                    </div>
                    <hr/>

                </div>

                <div class="store-pane active" id="product-overview">
                    <div class="row">
                        <div class="col-lg-3">
                            <div class="form-group">
                                <?= $form->label("pActive", t('Active')); ?>
                                <?= $form->select("pActive", ['1' => t('Active'), '0' => t('Inactive')], $product->isActive() ? '1' : '0'); ?>
                            </div>
                        </div>
                        <div class="col-lg-5">
                        <div class="form-group">
                            <?= $form->label("pQty", t('Stock Level')); ?>
                            <?php $qty = $product->getStockLevel(); ?>
                            <div class="input-group">
                                <?= $form->number("pQty", $qty !== '' ? round($qty, 3) : '999', [($product->isUnlimited(true) ? 'disabled' : '') => ($product->isUnlimited(true) ? 'disabled' : ''), 'step' => 0.001]); ?>
                                <div class="input-group-addon input-group-text">
                                    <div class="form-check-inline">
                                    <?= $form->checkbox('pQtyUnlim', '1', $product->isUnlimited(true),  ['class'=>'form-check-input']) ?>
                                    <?= $form->label('pQtyUnlim', t('Unlimited'), ['class'=>'form-label form-check-label']) ?>
                                    </div>
                                </div>

                                <script>
                                    $(document).ready(function () {
                                        $('#pQtyUnlim').change(function () {
                                            $('#pQty').prop('disabled', this.checked);
                                            $('#backorders').toggle();
                                        });

                                        $('#pAllowDecimalQty').change(function () {
                                            $('#quantitystepscontainer').toggleClass('hidden d-none');
                                        });

                                        $('#pQuantityPrice').change(function () {
                                            $('#tieredoptionscontainer').toggleClass('hidden d-none');
                                            $('#tieredoptionsnote').toggleClass('hidden d-none');
                                        });

                                        $('#pNoQty').change(function () {
                                            if ($(this).val() == '1') {
                                                $('#quantityoptions').addClass('hidden d-none');
                                                $('#quantitystepscontainer').addClass('hidden d-none');
                                            } else {
                                                $('#quantityoptions').removeClass('hidden d-none');

                                                if ($('#pAllowDecimalQty').val() == 1) {
                                                    $('#quantitystepscontainer').removeClass('hidden d-none');
                                                } else {
                                                    $('#quantitystepscontainer').addClass('hidden d-none');
                                                }
                                            }

                                        });

                                        $('#pVariations').change(function () {
                                            if ($(this).prop('checked')) {
                                                $('#variations,#variationnotice').removeClass('hidden d-none');
                                            } else {
                                                $('#variations,#variationnotice').addClass('hidden d-none');
                                            }
                                        });

                                        $('input[name^="pvQtyUnlim"]').change(function () {
                                            $(this).closest('.input-group').find('.ccm-input-number').prop('readonly', this.checked);
                                        });

                                        <?php if ($controller->getAction() == 'add') { ?>
                                        $('#pName').focus();
                                        <?php } ?>

                                    });
                                </script>
                            </div>

                        </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-group" id="backorders" <?= ($product->isUnlimited(true) ? 'style="display: none"' : ''); ?>>
                                <?= $form->label("pBackOrder", t('Allow Back Orders')); ?>
                                <?= $form->select("pBackOrder", ['1' => t('Yes'), '0' => t('No')], $product->allowBackOrders() ? '1' : '0'); ?>
                            </div>
                        </div>
                    </div>

                    <div class="<?= ($hideStockAvailabilityDates ? 'hidden d-none' : ''); ?>">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <?= $form->label("pDateAvailableStart", t('Stock Available From')); ?>
                                    <?= $app->make('helper/form/date_time')->datetime('pDateAvailableStart', $product->getDateAvailableStart()); ?>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <?= $form->label("pDateAvailableEnd", t('Stock Available Until')); ?>
                                    <?= $app->make('helper/form/date_time')->datetime('pDateAvailableEnd', $product->getDateAvailableEnd()); ?>
                                </div>
                            </div>
                        </div>
                        <hr />
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <?php
                                $priceclass = 'nonpriceentry';
                                $defaultpriceclass = 'priceentry';
                                if ($product->allowCustomerPrice()) {
                                    $priceclass .= ' hidden d-none';
                                } else {
                                    $defaultpriceclass .= ' hidden d-none';
                                }
                                ?>
                                <?= $form->label("pPrice", t("Price"), ['class' => $priceclass]); ?>
                                <?= $form->label("pPrice", t("Default Price"), ['class' => $defaultpriceclass]); ?>
                                <div class="input-group">
                                    <div class="input-group-addon input-group-text">
                                        <?= Config::get('community_store.symbol'); ?>
                                    </div>
                                    <?php $price = $product->getBasePrice(); ?>
                                    <?= $form->number("pPrice", $price, ['step'=>'0.01', 'placeholder' => ($product->allowCustomerPrice() ? t('No Price Set') : '')]); ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 <?= ($hideWholesalePrice ? 'hidden d-none' : ''); ?>">
                            <div class="form-group nonpriceentry <?= ($product->allowCustomerPrice() ? 'hidden' : '');?> ">
                                <?= $form->label("pWholesalePrice", t('Wholesale Price'), array('class'=>$priceclass));?>
                                <div class="input-group">
                                    <div class="input-group-addon input-group-text">
                                        <?= Config::get('community_store.symbol');?>
                                    </div>
                                    <?php $wholesalePrice = $product->getWholesalePriceValue(); ?>
                                    <?= $form->number("pWholesalePrice", $wholesalePrice, ['step'=>'0.01', 'placeholder'=>t('No Wholesale Price Set')]);?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 <?= ($hideCostPrice ? 'hidden d-none' : ''); ?>">
                            <div class="form-group">
                                <?= $form->label("pCostPrice", t('Cost Price'), array('class'=>$priceclass));?>
                                <div class="input-group">
                                    <div class="input-group-addon input-group-text">
                                        <?= Config::get('community_store.symbol');?>
                                    </div>
                                    <?php $costPrice = $product->getCostPrice(); ?>
                                    <?= $form->number("pCostPrice", $costPrice, ['step'=>'0.01', 'placeholder'=>t('No Cost Price Set')]);?>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="row <?= ($hideCustomerPriceEntry ? 'hidden d-none' : ''); ?>">
                        <div class="col-md-6">
                            <div class="form-group">
                                <?= $form->checkbox('pCustomerPrice', '1', $product->allowCustomerPrice()) ?>
                                <?= $form->label('pCustomerPrice', t('Allow customer to enter price')) ?>
                            </div>
                        </div>
                    </div>



                    <div class="row <?= ($hideSalePrice ? 'hidden d-none' : ''); ?>">

                        <div class="col-md-6">
                            <div class="form-group nonpriceentry <?= ($product->allowCustomerPrice() ? 'hidden d-none' : ''); ?>">
                                <?= $form->label("pSalePrice", t('Sale Price'), ['class' => $priceclass]); ?>
                                <div class="input-group">
                                    <div class="input-group-addon input-group-text">
                                        <?= Config::get('community_store.symbol'); ?>
                                    </div>
                                    <?php $salePrice = $product->getSalePriceValue(); ?>
                                    <?= $form->number("pSalePrice", $salePrice, ['step'=>'0.01', 'placeholder' => t('No Sale Price Set')]); ?>
                                </div>
                                <span class="help-block <?=($salePrice ? 'hidden d-none' : ''); ?>" id="saleNote"><?= t('Enter a value to set start and end dates for the sale'); ?></span>

                                <script>
                                    $(document).ready(function () {
                                        $('#pSalePrice').keyup(function(){
                                            var saleDates = $('.saleDates');
                                            var saleNote = $('#saleNote');

                                            if ($(this).val()) {
                                                saleDates.removeClass('hidden d-none');
                                                saleNote.addClass('hidden d-none')
                                            } else {
                                                saleDates.addClass('hidden d-none');
                                                saleNote.removeClass('hidden d-none');
                                            }
                                        });

                                        $('#pCustomerPrice').change(function () {
                                            if ($(this).prop('checked')) {
                                                $('.priceentry').removeClass('hidden d-none');
                                                $('.nonpriceentry').addClass('hidden d-none');
                                            } else {
                                                $('.priceentry').addClass('hidden d-none');
                                                $('.nonpriceentry').removeClass('hidden d-none');
                                            }
                                        });
                                    });
                                </script>

                            </div>
                        </div>

                    </div>
                    <div class="row saleDates <?=($salePrice ? '' : 'hidden d-none'); ?>">
                        <div class="col-md-12">

                            <div class="form-group nonpriceentry <?= ($product->allowCustomerPrice() ? 'hidden d-none' : ''); ?>">
                                <?= $form->label("pSaleStart", t('Sale Start')); ?>
                                <?= $app->make('helper/form/date_time')->datetime('pSaleStart', $product->getSaleStart()); ?>
                            </div>

                        </div>

                        <div class="col-md-12">

                            <div class="form-group nonpriceentry <?= ($product->allowCustomerPrice() ? 'hidden d-none' : ''); ?>">
                                <?= $form->label("pSaleEnd", t('Sale End')); ?>
                                <?= $app->make('helper/form/date_time')->datetime('pSaleEnd', $product->getSaleEnd()); ?>
                            </div>
                            <style>
                                #ui-datepicker-div {
                                    z-index: 100 !important;
                                }
                            </style>
                        </div>

                    </div>
                    <div class="row priceentry <?= ($product->allowCustomerPrice() ? '' : 'hidden d-none'); ?>">
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= $form->label("pPriceMinimum", t('Minimum Price')); ?>
                                <div class="input-group">
                                    <div class="input-group-addon input-group-text">
                                        <?= Config::get('community_store.symbol'); ?>
                                    </div>
                                    <?php $minimumPrice = $product->getPriceMinimum(); ?>
                                    <?= $form->number("pPriceMinimum", $minimumPrice, ['step'=>'0.01', 'placeholder' => t('No Minimum Price')]); ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= $form->label("pPriceMaximum", t('Maximum Price')); ?>
                                <div class="input-group">
                                    <div class="input-group-addon input-group-text">
                                        <?= Config::get('community_store.symbol'); ?>
                                    </div>
                                    <?php $maximumPrice = $product->getPriceMaximum(); ?>
                                    <?= $form->number("pPriceMaximum", $maximumPrice, ['step'=>'0.01', 'placeholder' => t('No Maximum Price')]); ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                            <?= $form->label('pPriceSuggestions', t('Price Suggestions')) ?>
                            <?= $form->text('pPriceSuggestions', $product->getPriceSuggestions(), ['placeholder' => t('e.g. 10,20,30')]) ?>
                            </div>
                        </div>
                    </div>

                    <div class="row nonpriceentry <?= ($product->allowCustomerPrice() ? 'hidden d-none' : ''); ?> <?= ($hideQuantityBasedPricing ? 'hidden d-none' : ''); ?>">

                        <div class="col-md-12">
                            <div class="form-group">
                                <?= $form->checkbox('pQuantityPrice', '1', $product->hasQuantityPrice()) ?>
                                <?= $form->label('pQuantityPrice', t('Quantity based pricing')) ?>
                                <span id="tieredoptionsnote" class="help-block <?= $product->hasQuantityPrice() ? '' : 'hidden d-none' ?>"><?= t('Note: quantity based pricing is not overridden by a sale price'); ?></span>
                            </div>
                        </div>

                        <div id="tieredoptionscontainer" class="col-md-12  <?= $product->hasQuantityPrice() ? '' : 'hidden d-none' ?>">

                            <div id="tierscontainer">
                                <div class="row">
                                    <div class="col-md-3"><strong><?= t('From'); ?></strong></div>
                                    <div class="col-md-3"><strong><?= t('To'); ?></strong></div>
                                    <div class="col-md-3"><strong><?= t('Price'); ?></strong></div>
                                    <th class="col-md-3"></th>
                                </div>
                            </div>

                            <p><a href="javascript:addtier()" class="btn btn-secondary btn-default"><?= t('Add Tier'); ?></a></p>


                            <script type="text/template" id="price-tier-template">
                                <div class="row" data-order="<%=sort%>">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <input type="text" name="ptFrom[]" class="form-control ccm-input-text" value="<%=from%>">
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <input type="text" name="ptTo[]" class="form-control ccm-input-text" value="<%=to%>">
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="form-group">
                                            <div class="input-group">
                                                <div class="input-group-addon input-group-text">
                                                    <?= Config::get('community_store.symbol'); ?>
                                                </div>
                                                <input type="number" name="ptPrice[]" class="form-control ccm-input-text" step="0.01" value="<%=price%>">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-1">
                                        <a href="javascript:deletetier(<%=sort%>)" class="btn btn-sm btn-danger remove"><i class="fa fa-times"></i></a>
                                    </div>
                                </div>
                            </script>

                            <script type="text/javascript">
                                $(function () {

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

                                    if ($('#tierscontainer .row').length == 1) {
                                        addtier();
                                    }

                                });

                                function deletetier(id) {
                                    $("#tierscontainer .row[data-order='" + id + "']").remove();

                                    if ($('#tierscontainer .row').length == 1) {
                                        addtier();
                                    }
                                }

                                function addtier() {
                                    var tiersContainer = $('#tierscontainer');
                                    var tierTemplate = _.template($('#price-tier-template').html());

                                    tiersContainer.append(tierTemplate({
                                        from: '',
                                        to: '',
                                        price: '',
                                        sort: $('#tierscontainer .row').length
                                    }));

                                }
                            </script>

                        </div>

                    </div>

                    <hr />
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <?= $form->label("pTaxable", t('Taxable')); ?>
                                <?= $form->select("pTaxable", ['1' => t('Yes'), '0' => t('No')], $product->isTaxable() ? '1' : '0'); ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <?= $form->label("pTaxClass", t('Tax Class')); ?>
                                <?= $form->select("pTaxClass", $taxClasses, $product->getTaxClassID()); ?>
                            </div>
                        </div>
                    </div>
                    <hr />

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <?= $form->label("pNoQty", t('Offer quantity selection')); ?>
                                <?= $form->select("pNoQty", ['0' => t('Yes'), '1' => t('No, only allow one of this product in a cart')], !$product->allowQuantity()); ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <?= $form->label("pQtyLabel", t('Quantity Label')); ?>
                                <?= $form->text("pQtyLabel", $product->getQtyLabel(), ['placeholder' => 'e.g. cm', 'maxlength'=>100]); ?>
                            </div>
                        </div>

                    </div>


                    <div class="row <?= !$product->allowQuantity() ? 'hidden d-none' : ''; ?>" id="quantityoptions">
                        <div class="col-lg-4">
                            <div class="form-group">
                                <?= $form->label("pAllowDecimalQty", t('Allow Decimal Quantities')); ?>
                                <?= $form->select("pAllowDecimalQty", ['0' => t('No, whole number quantities only'), '1' => t('Yes')], $product->getAllowDecimalQty()); ?>
                            </div>
                        </div>
                        <div class="col-lg-4 <?= ($product->getAllowDecimalQty() ? '' : 'hidden d-none'); ?>" id="quantitystepscontainer">
                            <div class="form-group">
                                <?= $form->label("pQtySteps", t('Quantity Steps')); ?>
                                <?= $form->number("pQtySteps", $product->getQtySteps() > 0 ? round($product->getQtySteps(), 4) : '', ['min' => 0, 'step' => 0.001, 'placeholder' => 'e.g. 0.1']); ?>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-group">
                                <?= $form->label("pMaxQty", t('Maximum Quantity In Cart')); ?>
                                <?= $form->number("pMaxQty", $product->getMaxQty(), ['min' => 0, 'step' => 0.01]); ?>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-6">
                            <div class="form-group">
                                <?php $outOfStockMessage = $product->getOutOfStockMessage(); ?>
                                <?= $form->label("pOutOfStockMessage", t('Out Of Stock Message')); ?>
                                <?= $form->text("pOutOfStockMessage", $product->getOutOfStockMessage(), ['placeholder'=>t('Out of Stock'), 'maxlength'=>200]); ?>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-group">
                                <?= $form->label("pAddToCartText", t('Add To Cart Button Text')); ?>
                                <?= $form->text("pAddToCartText", $product->getAddToCartText(), ['placeholder'=>t('Add to Cart'), 'maxlength'=>120]); ?>
                            </div>
                        </div>
                    </div>


                    <hr />

                    <?php if ($controller->getAction() == 'edit') { ?>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <?= $form->label("pDateAdded", t('Date Added')); ?>
                                    <?= $app->make('helper/form/date_time')->datetime('pDateAdded', $product->getDateAdded()); ?>
                                </div>
                                <style>
                                    #ui-datepicker-div {
                                        z-index: 100 !important;
                                    }
                                </style>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <?= $form->label("pDateUpdated", t('Last Updated')); ?>
                                    <div class="form-control-static">
                                        <?= $dh->formatDateTime($product->getDateUpdated()); ?>
                                    </div>
                                </div>
                            </div>
                        </div>



                    <?php } ?>

                </div><!-- #product-overview -->

                <div class="store-pane" id="product-descriptions">

                    <div class="form-group">
                        <?= $form->label("pDesc", t('Short Description')); ?><br>
                        <?php
                        $editor = $app->make('editor');
                        $editor->getPluginManager()->deselect(array('autogrow'));
                        echo $editor->outputStandardEditor('pDesc', $product->getDesc());


                        ?>
                    </div>

                    <div class="form-group">
                        <?= $form->label("pDesc", t('Product Details (Long Description)')); ?><br>
                        <?php
                        $editor = $app->make('editor');
                        $editor->getPluginManager()->deselect(array('autogrow'));
                        echo $editor->outputStandardEditor('pDetail', $product->getDetail());
                        ?>
                    </div>

                    <div class="form-group">
                        <?= $form->label("pManufacturer", t('Brand / Manufacturer')); ?>
                        <?= $form->select('pManufacturer', $manufacturers, $product->getManufacturer() ? $product->getManufacturer()->getID() : '',  ['class' => 'selectize']); ?>
                    </div>



                    <div class="form-group">
                        <?= $form->label("pBarcode", t('Barcode')); ?>
                        <?= $form->text("pBarcode", $product->getBarcode()); ?>
                    </div>


                </div><!-- #product-descriptions -->

                <div class="store-pane" id="product-images">

                    <div class="form-group">
                        <?= $form->label('pfID', t('Primary Product Image')); ?>
                        <?php $pfID = $product->getImageID(); ?>
                        <?= $al->image('ccm-image', 'pfID', t('Choose Image'), $pfID ? File::getByID($pfID) : null); ?>
                    </div>

                    <?= $form->label('', t('Additional Images')); ?>

                    <ul class="list-group multi-select-list multi-select-sortable" id="additional-image-list">
                        <?php foreach ($product->getimagesobjects() as $file) {
                            if ($file) {
                                $thumb = $file->getListingThumbnailImage();
                                if ($thumb) {
                                    echo '<li class="list-group-item">' . $thumb . ' ' . $file->getTitle() . '<a><i class="float-end pull-right fa fa-minus-circle"></i></a><input type="hidden" name="pifID[]" value="' . $file->getFileID() . '" /></li>';
                                }
                            }
                        }
                        ?>
                    </ul>

                    <div href="#" id="launch_additional" data-launch="file-manager" class="ccm-file-selector">
                        <div class="ccm-file-selector-choose-new btn btn-secondary"><?= t('Choose Images'); ?></div>
                    </div>
                    <script type="text/javascript">
                        $(function () {
                            $('#launch_additional').on('click', function (e) {
                                e.preventDefault();

                                var options = {
                                    filters: [{field: 'type', type: '<?= \Concrete\Core\File\Type\Type::T_IMAGE; ?>'}]
                                };

                                ConcreteFileManager.launchDialog(function (data) {
                                    ConcreteFileManager.getFileDetails(data.fID, function (r) {
                                        for (var i in r.files) {
                                            var file = r.files[i];
                                            $('#additional-image-list').append('<li class="list-group-item">' + file.resultsThumbnailImg + ' ' + file.title + '<a><i class="float-end pull-right fa fa-minus-circle"></i></a><input type="hidden" name="pifID[]" value="' + file.fID + '" /></li>');
                                        }

                                    });
                                }, options);
                            });

                            $('#additional-image-list').sortable({axis: 'y'});

                            $('#additional-image-list').on('click', 'a', function () {
                                $(this).parent().remove();
                            });
                        });
                    </script>

                </div><!-- #product-images -->

                <div class="store-pane" id="product-categories">
                    <?= $form->label('', t("Categorized under pages")); ?>

                    <div class="form-group" id="page_pickers">

                        <ul class="list-group multi-select-list multi-select-sortable" id="pagelocations">
                            <?php
                            if (!empty($locationPages)) {
                                foreach ($locationPages as $location) {
                                    if ($location) {
                                        $locationpage = Page::getByID($location->getCollectionID());
                                        if ($locationpage) {
                                            echo '<li class="list-group-item">' . $locationpage->getCollectionName() . ' <a><i class="float-end pull-right fa fa-minus-circle"></i></a> <input type="hidden" name="cID[]" value="' . $location->getCollectionID() . '" /></li>';
                                        }
                                    }
                                }
                            }
                            ?>
                        </ul>

                        <script type="text/javascript">
                            $(function () {
                                $('#pagelocations').sortable({axis: 'y'});
                            });
                        </script>

                        <div class="page_picker">
                            <?= $ps->selectPage('noneselection'); ?>
                        </div>
                    </div>

                    <?= $form->label('', t('In product groups')); ?>
                    <div class="ccm-search-field-content ccm-search-field-content-select2">
                        <select multiple="multiple" name="pProductGroups[]" class="existing-select2 select2-select" style="width: 100%"
                                placeholder="<?= (empty($productgroups) ? t('No Product Groups Available') : t('Select Product Groups')); ?>">
                            <?php
                            if (!empty($productgroups)) {
                                if (!is_array($pgroups)) {
                                    $pgroups = [];
                                }
                                foreach ($productgroups as $pgkey => $pglabel) { ?>
                                    <option value="<?= $pgkey; ?>" <?= (in_array($pgkey, $pgroups) ? 'selected="selected"' : ''); ?>>  <?= $pglabel; ?></option>
                                <?php }
                            } ?>
                        </select>
                    </div>

                    <br />
                    <div class="form-group">
                        <?= $form->label("pFeatured", t('Featured Product')); ?>
                        <?= $form->select("pFeatured", ['0' => t('No'), '1' => t('Yes')], $product->isFeatured() ? '1' : '0'); ?>
                    </div>


                    <script>
                        $(document).ready(function () {
                            $('.existing-select2').select2();

                            $('#pagelocations').on('click', 'a', function () {
                                $(this).parent().remove();
                            });

                            Concrete.event.bind('ConcreteSitemap', function (e, instance) {
                                var instance = instance;

                                Concrete.event.bind('SitemapSelectPage', function (e, data) {
                                    if (data.instance == instance) {
                                        Concrete.event.unbind(e);


                                        if ($('#pagelocations input[value=' + data.cID + ']').length == 0) {
                                            $('#pagelocations').append('<li class="list-group-item">' + data.title + '<a><i class="float-end pull-right fa fa-minus-circle"></i></a> <input type="hidden" name="cID[]" value="' + data.cID + '" /></li>');
                                        }

                                        $('.page_picker > div').hide();

                                        setTimeout(function () {
                                            $('#product-categories a[data-page-selector-action=clear]').trigger("click");
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

                <div class="store-pane" id="product-shipping">

                    <div class="form-group">
                        <?= $form->label("pShippable", t('Product is Shippable')); ?>
                        <?= $form->select("pShippable", ['1' => t('Yes'), '0' => t('No')], ($product->isShippable() ? '1' : '0')); ?>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <?= $form->label("pWeight", t('Weight')); ?>
                                <div class="input-group">
                                    <?php $weight = $product->getWeight(); ?>
                                    <?= $form->number('pWeight', $weight ? $weight : '0', ['step'=>'0.01']) ?>
                                    <div class="input-group-addon input-group-text"><?= Config::get('community_store.weightUnit') ?></div>
                                </div>
                            </div>

                            <div class="form-group">
                                <?= $form->label("pNumberItems", t('Number Of Items')); ?>
                                <?= $form->number('pNumberItems', $product->getNumberItems(), ['min' => 0, 'step' => 1]) ?>
                            </div>
                            <div class="form-group">
                                <?= $form->label("pSeperateShip", t('Product can be packaged with other items')); ?>
                                <?= $form->select("pSeperateShip", ['0' => t('Yes'), '1' => t('No, must be shipped as seperate package')], ($product->getSeperateShip() ? '1' : '0')); ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="form-group">
                                    <?= $form->label("pLength", t('Length')); ?>
                                    <div class="input-group">
                                        <?php $length = $product->getLength(); ?>
                                        <?= $form->number('pLength', $length ? $length : '0', ['step'=>'0.01','min'=>0]) ?>
                                        <div class="input-group-addon input-group-text"><?= Config::get('community_store.sizeUnit') ?></div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <?= $form->label("pWidth", t('Width')); ?>
                                    <div class="input-group">
                                        <?php $width = $product->getWidth(); ?>
                                        <?= $form->number('pWidth', $width ? $width : '0', ['step'=>'0.01','min'=>0]) ?>
                                        <div class="input-group-addon input-group-text"><?= Config::get('community_store.sizeUnit') ?></div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <?= $form->label("pHeight", t('Height')); ?>
                                    <div class="input-group">
                                        <?php $height = $product->getHeight(); ?>
                                        <?= $form->number('pHeight', $height ? $height : '0', ['step'=>'0.01','min'=>0]) ?>
                                        <div class="input-group-addon input-group-text"><?= Config::get('community_store.sizeUnit') ?></div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <?= $form->label("pStackedHeight", t('Stacked Height')); ?>
                                    <div class="input-group">
                                        <?php $height = $product->getStackedHeight(); ?>
                                        <?= $form->number('pStackedHeight', $height ? $height : '0', ['step'=>'0.01','min'=>0]) ?>
                                        <div class="input-group-addon input-group-text"><?= Config::get('community_store.sizeUnit') ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row <?= Config::get('community_store.multiplePackages') ? '' : 'hidden d-none'; ?>">
                        <div class="col-md-12">
                            <div class="form-group">
                                <?= $form->label("pPackageData", t('Or, Package(s) Data')); ?>
                                <?= $form->textarea('pPackageData', $product->getPackageData(), ['rows' => 4, 'placeholder' => t('%s LENGTHxWIDTHxHEIGHT', strtoupper(Config::get('community_store.weightUnit')))]) ?>
                                <span class="help-block">
                                    <?= t('Values entered will override individual set weights and sizes'); ?>
                                    <br/>
                                    <?= t('Enter packages on new lines, using the format:'); ?>
                                    <br/>
                                    <?= t('%s LENGTHxWIDTHxHEIGHT', strtoupper(Config::get('community_store.weightUnit'))); ?>
                                    <br/>
                                    <?= t('E.g. 10 4x6x8'); ?>
                                </span>
                            </div>
                        </div>
                    </div>

                </div><!-- #product-shipping -->

                <div class="store-pane" id="product-options">

                    <?= $form->label('', t("Options")); ?>
                    <div id="product-options-container"></div>

                    <div class="clearfix mt-3">
                        <h4><?= t('Add'); ?></h4>
                        <span class="btn btn-sm btn-primary" id="btn-add-option-group"><?= t('Option List') ?></span>
                        <span class="btn btn-sm btn-primary" id="btn-add-text"><?= t('Text Input') ?></span>
                        <span class="btn btn-sm btn-primary" id="btn-add-textarea"><?= t('Text Area') ?></span>
                        <span class="btn btn-sm btn-primary" id="btn-add-checkbox"><?= t('Checkbox') ?></span>
                        <span class="btn btn-sm btn-primary" id="btn-add-hidden"><?= t('Hidden Value') ?></span>
                        <span class="btn btn-sm btn-primary" id="btn-add-static"><?= t('Static HTML') ?></span>
                    </div>

                    <!-- THE TEMPLATE WE'LL USE FOR EACH OPTION GROUP -->
                    <script type="text/template" id="option-group-template">
                        <div class="card panel panel-default option-group clearfix" data-order="<%=sort%>">
                            <div class="panel-heading card-title">

                                <div class="row">
                                    <div class="col-md-6">
                                        <h3 class="panel-title"><i class="fa fa-arrows-alt drag-handle mr-2"></i> <%=poLabel%></h3>
                                    </div>
                                    <div class="col-md-6 text-right">
                                        <a href="javascript:deleteOptionGroup(<%=sort%>)" class="btn btn-sm btn-delete-item btn-danger float-end"><i data-toggle="tooltip" data-placement="top" title="<?= t('Delete the Option Group') ?>" class="fa fa-times"></i> Remove</a>
                                    </div>
                                </div>
                            </div>
                            <div class="panel-body card-body">

                                <div class="row">
                                    <% if (poType == 'static') { %>
                                    <input type="hidden" class="form-control" name="poName[]" value="<%=poType%>">
                    <input type="hidden" class="form-control" name="poHandle[]" value="<%=poHandle%>">
                                    <% } %>

                                    <% if (poType != 'static') { %>
                                    <div class="col-md-5">
                                        <div class="form-group">
                                            <label class="control-label" for="poName<%=sort%>"><?= t('Option Name'); ?></label>
                                            <input type="text" class="form-control" name="poName[]" value="<%=poName%>">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="control-label"><?= t('Option Handle'); ?></label>
                                            <input type="text" class="form-control" name="poHandle[]" placeholder="<?= t('Optional'); ?>" value="<%=poHandle%>">
                                        </div>

                                    </div>
                                    <% } %>


                                    <% if (poType == 'select') { %>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label  class="control-label"><?= t('In Variants'); ?></label>
                                            <select class="form-control" name="poIncludeVariations[]">
                                                <option value="1"
                                                <% if (poIncludeVariations == 1) { %>selected="selected"<% } %>><?= t('Yes'); ?></option>
                                                <option value="0"
                                                <% if (poIncludeVariations == 0) { %>selected="selected"<% } %>><?= t('No'); ?></option></select>
                                        </div>
                                    </div>
                                    <% } else { %>
                                    <input type="hidden" value="0" name="poIncludeVariations[]"/>
                                    <% } %>
                                    <% if (poType != 'checkbox' && poType != 'static') { %>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label class="control-label"><?= t('Required'); ?></label>
                                            <select class="form-control" name="poRequired[]">
                                                <option value="0"><?= t('No'); ?></option>
                                                <option value="1"
                                                <% if (poRequired == 1) { %>selected="selected"<% } %>><?= t('Yes'); ?></option></select>
                                        </div>
                                    </div>
                                    <% } else { %>
                                    <input type="hidden" value="0" name="poRequired[]"/>
                                    <% } %>
                                </div>
                                <% if (poType != 'hidden'  && poType != 'static') { %>
                                <div class="row">
                                    <div class="col-md-9">
                                        <div class="form-group">
                                            <label class="control-label"><?= t('Option Details');?></label>
                                            <textarea rows="1" placeholder="<?= t('Optional - help text for an option'); ?>" class="form-control" name="poDetails[]"><%=poDetails%></textarea>
                                        </div>
                                    </div>

                                    <% if (poType == 'select') { %>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label class="control-label"><?= t('Display Type'); ?></label>
                                            <select class="form-control" name="poDisplayType[]">
                                                <option value="select"
                                                <% if (poDisplayType == 'select') { %>selected="selected"<% } %>><?= t('Drop-down'); ?></option>
                                                <option value="radio"
                                                <% if (poDisplayType == 'radio') { %>selected="selected"<% } %>><?= t('Radio Buttons'); ?></option></select>
                                        </div>
                                    </div>
                                    <% } else { %>
                                    <input type="hidden" value="" name="poDisplayType[]"/>
                                    <% } %>

                                </div>
                                <% } %>

                                <% if (poType == 'static') { %>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label class="control-label"><?= t('Static HTML');?></label>
                                            <textarea rows="3" class="form-control" name="poDetails[]"><%= poDetails.replace('~~~',"\n")%></textarea>
                                        </div>
                                    </div>
                                </div>
                                <% } %>

                                <% if (poType == 'select') { %>
                                <hr/>
                                <div data-group="<%=sort%>" class="option-group-item-container"></div>
                                <p><a href="javascript:addOptionItem(<%=sort%>)" data-group="<%=sort%>" class="btn btn-default btn-secondary"><?= t('Add Option') ?></a></p>
                                <% } %>
                            </div>
                            <input type="hidden" name="poID[]" value="<%=poID%>">
                            <input type="hidden" name="poType[]" value="<%=poType%>">
                            <input type="hidden" name="poSort[]" value="<%=sort%>" class="option-group-sort">
                        </div>

                        </div><!-- .option-group -->
                    </script>
                    <script type="text/javascript">
                        function indexOptionGroups() {
                            $('#product-options-container .option-group').each(function (i) {
                                $(this).find('.option-group-sort').val(i);
                                $(this).attr("data-order", i);
                                $(this).find('.optGroupID').attr("name", "optGroup" + i + "[]");
                            });
                        }


                        function deleteOptionGroup(id) {
                            var variationeffect = $(".option-group[data-order='" + id + "'] select[name=poIncludeVariations\\[\\]] option:selected");

                            if (variationeffect && variationeffect.val() == 1) {
                                $('#variationshider').addClass('hidden d-none');
                                $('#changenotice').removeClass('hidden d-none');
                            }

                            $(".option-group[data-order='" + id + "']").remove();

                            indexOptionGroups();
                        }

                        $(function () {


                            $(document).on('change', 'select[name=poIncludeVariations\\[\\]]', function () {
                                $('#variationshider').addClass('hidden d-none');
                                $('#changenotice').removeClass('hidden d-none');
                                $('#changewarning').removeClass('hidden d-none');
                            });


                            //Make items sortable. If we re-sort them, re-index them.
                            $("#product-options-container").sortable({
                                handle: ".panel-heading",
                                axis: 'y',
                                update: function () {
                                    indexOptionGroups();
                                }
                            });

                            //Define container and items
                            var optionsContainer = $('#product-options-container');
                            var optionsTemplate = _.template($('#option-group-template').html());

                            //load up existing option groups
                            <?php

                            $labels = [];
                            $labels['select'] = t('Option List');
                            $labels['text'] = t('Text Input');
                            $labels['textarea'] = t('Text Area Input');
                            $labels['checkbox'] = t('Checkbox');
                            $labels['hidden'] = t('Hidden Value');
                            $labels['static'] = t('Static Text');


                            if($options) {
                            $optionsort = 0;
                            foreach ($options as $option) {

                            $type = $option->getType();
                            $displayType = $option->getDisplayType();
                            $handle = $option->getHandle();
                            $details = $option->getDetails();
                            $required = $option->getRequired();
                            $includeVariations = $option->getIncludeVariations();


                            if (!$type) {
                                $type = 'select';
                            }

                            $label = $labels[$type];

                            ?>
                            optionsContainer.append(optionsTemplate({
                                poName: <?= json_encode($option->getName()) ?>,
                                poID: <?= json_encode($option->getID()) ?>,
                                poType: '<?= $type ?>',
                                poDisplayType: '<?= $displayType ?>',
                                poLabel: <?= json_encode($label); ?>,
                                poHandle: <?= json_encode($handle); ?>,
                                poDetails: <?= json_encode(str_replace(["\r\n", "\r", "\n"], "~~~", h($details))); ?>,
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
                            $('#btn-add-option-group').click(function () {

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
                                    poDetails: '',
                                    poRequired: '',
                                    poIncludeVariations: '0',
                                    sort: temp
                                }));

                                //Init Index
                                indexOptionGroups();

    //                            $('#variationshider').addClass('hidden');
    //                            $('#changenotice').removeClass('hidden');
                            });


                            $('#btn-add-text').click(function () {

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
                                    poDetails: '',
                                    poRequired: '',
                                    sort: temp
                                }));

                                //Init Index
                                indexOptionGroups();
                            });

                            $('#btn-add-textarea').click(function () {

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
                                    poDetails: '',
                                    poRequired: '',
                                    sort: temp
                                }));

                                //Init Index
                                indexOptionGroups();
                            });

                            $('#btn-add-checkbox').click(function () {

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
                                    poDetails: '',
                                    poRequired: '',
                                    sort: temp
                                }));

                                //Init Index
                                indexOptionGroups();
                            });

                            $('#btn-add-hidden').click(function () {

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
                                    poDetails: '',
                                    poRequired: '',
                                    sort: temp
                                }));

                                //Init Index
                                indexOptionGroups();
                            });

                            $('#btn-add-static').click(function () {

                                //Use the template to create a new item.
                                var temp = $(".option-group").length;
                                temp = (temp);
                                optionsContainer.append(optionsTemplate({
                                    //vars to pass to the template
                                    poName: '',
                                    poID: '',
                                    poType: 'static',
                                    poLabel: '<?= $labels['static']; ?>',
                                    poHandle: '',
                                    poDetails: '',
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
                            <div class="form-group row">
                                <div class="col-sm-2 text-right">
                                    <label class="control-label grabme"><i class="fa fa-arrows-alt drag-handle pull-left float-start"></i> <?= t('Option') ?></label>
                                </div>
                                <div class="col-sm-9">
                                    <div class="input-group">
                                        <input type="text" name="poiName[]" class="form-control input-sm" value="<%=poiName%>">
                                        <div class="input-group-addon input-group-text input-sm">
                                            <div class="form-check-inline">
                                                <input type="hidden" name="poiHidden[]" value="<%=poiHiddenValue%>"/>
                                                <label class="form-label form-check-label"><input type="checkbox" class="optionHiddenToggle form-check-input" name="poiHiddenToggle[]" value="1" <%=poiHidden%> />
                                                    <?= t('Hide'); ?>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <br class="smallbreak">
                                    <input type="text" placeholder="<?= t('Selector Display Label - Optional');?>" name="poiSelectorName[]" class="form-control input-sm" value="<%=poiSelectorName%>">
                                    <br class="smallbreak">

                                    <div class="row">
                                        <div class="col-sm-6">
                                        <div class="input-group">
                                            <div class="input-group-addon input-group-text input-sm">
                                                <?= Config::get('community_store.symbol'); ?>
                                            </div>
                                            <input type="number" step="0.01" placeholder="<?= t('Price Adjustment');?>" name="poiPriceAdjust[]" class="form-control input-sm" value="<%=poiPriceAdjust%>">
                                        </div>
                                        </div>
                                        <div class="col-sm-6">
                                        <div class="input-group">
                                            <input type="number" step="0.01" placeholder="<?= t('Weight Adjustment');?>" name="poiWeightAdjust[]" class="form-control input-sm" value="<%=poiWeightAdjust%>">
                                            <div class="input-group-addon input-group-text input-sm"><?= Config::get('community_store.weightUnit') ?></div>
                                        </div>
                                        </div>
                                    </div>

                                    <input type="hidden" name="poiID[]" class="form-control" value="<%=poiID%>">
                                </div>
                                <div class="col-sm-1">
                                    <a href="javascript:deleteOptionItem(<%=optGroup%>,<%=sort%>)" class="btn btn-danger btn-sm"><i class="fa fa-times"></i></a>
                                </div>
                            </div>
                            <input type="hidden" name="optGroup<%=optGroup%>[]" class="optGroupID" value="">
                            <input type="hidden" name="poiSort[]" value="<%=sort%>" class="option-item-sort">
                        </div><!-- .option-group -->
                    </script>
                    <script type="text/javascript">
                        function deleteOptionItem(group, id) {
                            $(".option-group[data-order='" + group + "']").find(".option-item[data-order='" + id + "']").remove();

                            $('#variationshider').addClass('hidden d-none');
                            $('#changenotice').removeClass('hidden d-none');
                        }

                        function indexOptionItems() {
                            $('.option-group-item-container').each(function () {
                                $(this).find('.option-item').each(function (i) {
                                    $(this).find('.option-item-sort').val(i);
                                    $(this).attr("data-order", i);
                                });
                            });

                        }

                        function addOptionItem(group) {
                            var optItemsTemplate = _.template($('#option-item-template').html());
                            var optItemsContainer = $(".option-group-item-container[data-group='" + group + "']");

                            //Use the template to create a new item.
                            var temp = $(".option-group-item-container[data-group='" + group + "'] .option-item").length;
                            temp = (temp);
                            optItemsContainer.append(optItemsTemplate({
                                //vars to pass to the template
                                poiName: '',
                                poiSelectorName: '',
                                poiPriceAdjust: '',
                                poiWeightAdjust: '',
                                poiID: '',
                                optGroup: group,
                                sort: temp,
                                poiHidden: '',
                                poiHiddenValue: '0'
                            }));

                            //Init Index
                            indexOptionItems();
                            $('#variationshider').addClass('hidden d-none');
                            $('#changenotice').removeClass('hidden d-none');
                        }

                        // add handler for hide checkbox, to adjust hidden value when changed
                        $(document).on('change', '.optionHiddenToggle', function () {
                            $(this).prev().val(($(this).prop('checked') ? '1' : '0'));
                        });

                        $(function () {
                            //Make items sortable. If we re-sort them, re-index them.
                            $(".option-group-item-container").sortable({
                                handle: ".grabme",
                                update: function () {
                                    indexOptionItems();
                                }
                            });

                            //define template
                            var optItemsTemplate = _.template($('#option-item-template').html());

                            //load up items
                            <?php
                            if($options) {
                            $count = count($options);
                            for($i = 0;$i < $count;$i++){
                            foreach($options[$i]->getOptionItems() as $optionItem){
                            if($optionItem->getOptionID() == $options[$i]->getID()){

                            ?>

                            var optItemsContainer = $(".option-group-item-container[data-group='<?= $i?>']");
                            optItemsContainer.append(optItemsTemplate({
                                poiName: '<?= h($optionItem->getName())?>',
                                poiSelectorName: '<?= h($optionItem->getSelectorName())?>',
                                poiPriceAdjust: '<?=  $optionItem->getPriceAdjustment() != 0 ? $optionItem->getPriceAdjustment() : ''; ?>',
                                poiWeightAdjust: '<?= $optionItem->getWeightAdjustment() != 0  ? $optionItem->getWeightAdjustment() : ''?>',
                                poiID: '<?= $optionItem->getID()?>',
                                optGroup: <?= $i?>,
                                sort: <?= $optionItem->getSort()?>,
                                poiHidden: <?= ($optionItem->isHidden() ? '\'checked="checked"\'' : '""'); ?>,
                                poiHiddenValue: '<?= ($optionItem->isHidden() ? '1' : '0'); ?>'

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

                    <br/>
                    <div class="form-group">
                        <h4><?= t('Variants'); ?></h4>
                        <label class="control-label"><?= $form->checkbox('pVariations', '1', $product->hasVariations() ? '1' : '0') ?>
                            <?= t('Options have different prices, SKUs or stock levels'); ?></label>

                        <?php if (!$pID) { ?>
                            <p class="alert alert-info hidden d-none" id="variationnotice"><?= t('After creating options add the product to configure product variations.') ?></p>
                        <?php } ?>


                    </div>

                    <?php if (!empty($comboOptions)) { ?>
                        <div id="variations" class="<?= ($product->hasVariations() ? '' : 'hidden d-none'); ?>">

                            <p class="alert alert-danger hidden d-none" id="changewarning"><?= t('Warning: Product options have changed that will create different variations - any existing variation data will be lost') ?></p>

                            <?php if ($pID) { ?>
                                <p class="alert alert-info hidden d-none" id="changenotice"><?= t('Product options have changed, update the product to configure updated variations') ?></p>
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
                                                $comboIDs = [];

                                                foreach ($combinedOptions as $optionItemID) {
                                                    $comboIDs[] = $optionItemID;
                                                    sort($comboIDs);
                                                    $group = $optionLookup[$optionItemLookup[$optionItemID]->getOptionID()];
                                                    echo '<span class="label label-primary">' . ($group ? $group->getName() : '') . ': ' . $optionItemLookup[$optionItemID]->getName() . '</span> ';
                                                }

                                                ?>
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


                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <?= $form->label("", t('Code / SKU')); ?>
                                                            <?= $form->text("pvSKU[" . $varid . "]", $variation ? $variation->getVariationSKU() : '', ['placeholder' => t('Base SKU')]); ?>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <?= $form->label("", t('Barcode')); ?>
                                                            <?= $form->text("pvBarcode[" . $varid . "]", $variation ? $variation->getVariationBarcode() : '', ['placeholder' => t('Barcode')]); ?>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <?= $form->label("", t('Stock Level')); ?>
                                                            <div class="input-group">
                                                                <?php
                                                                if ($variation) {
                                                                    echo $form->number("pvQty[" . $varid . "]", round($variation->getVariationQty(), 3), [($variation->isUnlimited(true) ? 'readonly' : '') => ($variation->isUnlimited(true) ? 'readonly' : ''), 'step' => 0.001]);
                                                                } else {
                                                                    echo $form->number("pvQty[" . $varid . "]", '', ['readonly' => 'readonly', 'step' => 0.001]);
                                                                }
                                                                ?>

                                                                <div class="input-group-addon input-group-text">
                                                                    <label class="control-label"><?= $form->checkbox('pvQtyUnlim[' . $varid . ']', '1', $variation ? $variation->isUnlimited(true) : true) ?> <?= t('Unlimited'); ?></label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <?= $form->label('pfID[]', t('Primary Image')); ?>
                                                        <?php
                                                        $pvfID = null;
                                                        $thumb = '';
                                                        $title = '';
                                                        if ($variation) {
                                                            $pvfID = $variation->getVariationImageID();

                                                            if ($pvfID) {
                                                                $file = File::getByID($pvfID);

                                                                if ($file) {
                                                                    $thumb = $file->getThumbnailURL('file_manager_listing');
                                                                    $title = $file->getTitle();
                                                                }
                                                            }
                                                        }
                                                        ?>

                                                      <div>
                                                        <img title="<?= h($title); ?>" id="pvfIDImage-<?= $varid; ?>" src="<?= $thumb; ?>" style="height: 40px" class="<?= (!$thumb ? 'hidden d-none' : ''); ?>" />
                                                        <button class="btn btn-primary btn-selectfile" data-pvfid="<?= $varid; ?>" ><?= t('Choose Image'); ?></button>
                                                        <button class="btn btn-danger btn-clearfile <?= (!$thumb ? 'hidden d-none' : ''); ?>"
                                                                data-pvfid="<?= $varid; ?>" ><i class="fa fa-times"></i></button>
                                                        <input type="hidden" id="pvfID-<?= $varid; ?>" name="pvfID[<?= $varid; ?>]" value="<?= $pvfID; ?>" />
                                                      </div>

                                                        <?php // $al->image('ccm-image' . $count++, 'pvfID[' . $varid . ']', t('Choose Image'), $pvfID ? File::getByID($pvfID) : null); ?>
                                                    </div>
                                                </div>


                                                <div class="row <?= ($hideVariationPrices ? 'hidden d-none' : ''); ?>">
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <?= $form->label("", t('Price')); ?>
                                                            <div class="input-group">
                                                                <div class="input-group-addon input-group-text">
                                                                    <?= Config::get('community_store.symbol'); ?>
                                                                </div>
                                                                <?= $form->number("pvPrice[" . $varid . "]", $variation ? $variation->getVariationPrice() : '', ['step'=>'0.01', 'placeholder' => t('Base Price')]); ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <?= $form->label("", t('Wholesale Price')); ?>
                                                            <div class="input-group">
                                                                <div class="input-group-addon input-group-text">
                                                                    <?= Config::get('community_store.symbol'); ?>
                                                                </div>
                                                                <?= $form->number("pvWholesalePrice[" . $varid . "]", $variation ? $variation->getVariationWholesalePrice() : '', ['step'=>'0.01', 'placeholder' => t('Wholesale Price')]); ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <?= $form->label("", t('Cost Price')); ?>
                                                            <div class="input-group">
                                                                <div class="input-group-addon input-group-text">
                                                                    <?= Config::get('community_store.symbol'); ?>
                                                                </div>
                                                                <?= $form->number("pvCostPrice[" . $varid . "]", $variation ? $variation->getVariationCostPrice() : '', ['step'=>'0.01', 'placeholder' => t('Cost Price')]); ?>
                                                            </div>
                                                        </div>
                                                    </div>

                                                </div>

                                                <div class="row <?= ($hideVariationPrices ? 'hidden d-none' : ''); ?>">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <?= $form->label("pvSalePrice[]", t('Sale Price')); ?>

                                                            <div class="input-group">
                                                                <div class="input-group-addon input-group-text">
                                                                    <?= Config::get('community_store.symbol'); ?>
                                                                </div>
                                                                <?= $form->number("pvSalePrice[" . $varid . "]", $variation ? $variation->getVariationSalePrice() : '', ['step'=>'0.01', 'placeholder' => t('Base Sale Price')]); ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">

                                                    </div>
                                                </div>

                                                <div class="row <?= ($hideVariationShippingFields ? 'hidden d-none' : ''); ?>">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <?= $form->label("", t('Weight')); ?>
                                                            <div class="input-group">
                                                                <?= $form->number('pvWeight[' . $varid . ']', $variation ? $variation->getVariationWeight() : '', ['step'=>'0.01', 'min'=>0, 'placeholder' => t('Base Weight')]) ?>
                                                                <div class="input-group-addon input-group-text"><?= Config::get('community_store.weightUnit') ?></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <?= $form->label("", t('Length')); ?>
                                                            <div class="input-group">
                                                                <?= $form->number('pvLength[' . $varid . ']', $variation ? $variation->getVariationLength() : '', ['step'=>'0.01', 'min'=>0, 'placeholder' => t('Base Length')]) ?>
                                                                <div class="input-group-addon input-group-text"><?= Config::get('community_store.sizeUnit') ?></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row <?= ($hideVariationShippingFields ? 'hidden d-none' : ''); ?>">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <?= $form->label("", t('Number of Items')); ?>
                                                            <?= $form->number('pvNumberItems[' . $varid . ']', $variation ? $variation->getVariationNumberItems() : '', ['min'=>0, 'step' => 1, 'placeholder' => t('Base Number Of Items')]) ?>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <?= $form->label("", t('Width')); ?>
                                                            <div class="input-group">
                                                                <?= $form->number('pvWidth[' . $varid . ']', $variation ? $variation->getVariationWidth() : '', ['step'=>'0.01','min'=>'0','placeholder' => t('Base Width')]) ?>
                                                                <div class="input-group-addon input-group-text"><?= Config::get('community_store.sizeUnit') ?></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row <?= ($hideVariationShippingFields ? 'hidden d-none' : ''); ?>">
                                                    <div class="col-md-6">

                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <?= $form->label("", t('Height')); ?>
                                                            <div class="input-group">
                                                                <?= $form->number('pvHeight[' . $varid . ']', $variation ? $variation->getVariationHeight() : '', ['step'=>'0.01', 'min'=>0, 'placeholder' => t('Base Height')]) ?>
                                                                <div class="input-group-addon input-group-text"><?= Config::get('community_store.sizeUnit') ?></div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                </div>

                                                <div class="row <?= ($hideVariationShippingFields ? 'hidden d-none' : ''); ?>">

                                                    <div class="col-md-12 <?= Config::get('community_store.multiplePackages') ? '' : 'hidden d-none'; ?>">
                                                        <div class="form-group">

                                                            <?= $form->label('pvPackageData[' . $varid . ']', t("Or, Package(s) Data")); ?>
                                                            <?= $form->textarea('pvPackageData[' . $varid . ']', $variation ? $variation->getVariationPackageData() : '', ['rows' => 4, 'placeholder' => t('%s LENGTHxWIDTHxHEIGHT', strtoupper(Config::get('community_store.weightUnit')))]) ?>

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

                <div class="store-pane" id="product-related">

                    <?= $form->label("", t("Related Products")); ?>

                    <ul class="list-group multi-select-list multi-select-sortable" id="related-products">
                        <?php
                        $relatedProducts = $product->getRelatedProducts();
                        if (!empty($relatedProducts)) {
                            foreach ($relatedProducts as $relatedProduct) {
                                echo '<li class="list-group-item">' . $relatedProduct->getRelatedProduct()->getName() . '<input type="hidden" name="pRelatedProducts[]" value="' . $relatedProduct->getRelatedProduct()->getID() . '" /><a><i class="float-end pull-right fa fa-minus-circle"></i></a></li>';
                            }
                        }
                        ?>
                    </ul>

                    <div class="form-group" id="product-search">
                        <input name="relatedpID" id="product-select" style="width: 100%" placeholder="<?= t('Search for a Product') ?>"/>
                    </div>

                    <script type="text/javascript">

                        $(function () {
                            $("#product-select").select2({
                                ajax: {
                                    url: "<?= Url::to('/productfinder')?>",
                                    dataType: 'json',
                                    quietMillis: 250,
                                    data: function (term, page) {
                                        return {
                                            q: term // search term
                                        };
                                    },
                                    results: function (data) {
                                        var results = [];
                                        $.each(data, function (index, item) {
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
                                initSelection: function (element, callback) {
                                    callback({});
                                }
                            }).select2('val', []);

                            $('#product-select').on("change", function (e) {
                                var data = $(this).select2('data');
                                $('#related-products').append('<li class="list-group-item">' + data.text + '<a><i class="float-end pull-right fa fa-minus-circle"></i> <input type="hidden" name="pRelatedProducts[]" value="' + data.id + '" /></a> </li>');
                                $(this).select2("val", []);
                            });

                            $('#related-products').on('click', 'a', function () {
                                $(this).parent().remove();
                            });

                            $('#related-products').sortable({axis: 'y'});

                        });

                    </script>

                </div><!-- #product-related -->

                <div class="store-pane" id="product-attributes">

                    <?php
                    $hasKeys = false;
                    $sets = $productAttributeCategory->getController()->getSetManager()->getAttributeSets();

                    foreach ($sets as $set) {
                        echo '<h4>' . $set->getAttributeSetDisplayName() . '</h4>';
                        foreach ($set->getAttributeKeys() as $key => $ak) {
                            $hasKeys = true;

                            if (is_object($product)) {
                                $caValue = $product->getAttributeValueObject($ak);
                            }
                            ?>
                            <div class="form-group">
                                <?= $ak->render('label'); ?>
                                <div class="input">
                                    <?= $ak->render(new \Concrete\Core\Attribute\Context\DashboardFormContext(), $caValue, true) ?>
                                </div>
                            </div>
                        <?php  }
                    }

                    $attributeKeys = $productAttributeCategory->getController()->getSetManager()->getUnassignedAttributeKeys();
                    if (count($attributeKeys) > 0) {
                        if (count($sets) > 0) {
                            echo '<h4>' . t('Other') . '</h4>';
                        }

                        foreach ($attributeKeys as $key => $ak) {
                            $hasKeys = true;

                            if (is_object($product)) {
                                $caValue = $product->getAttributeValueObject($ak);
                            }
                            ?>
                            <div class="form-group">
                                <?= $ak->render('label'); ?>
                                <div class="input">
                                    <?= $ak->render(new \Concrete\Core\Attribute\Context\DashboardFormContext(), $caValue, true) ?>
                                </div>
                            </div>
                     <?php   }
                    }

                    if (!$hasKeys) { ?>
                        <p><?= t('No product attributes defined') ?></p>
                    <?php } ?>

                </div>

                <div class="store-pane" id="product-digital">
                    <?php
                    $files = $product->getDownloadFileObjects();
                    for ($i = 0; $i < 1; $i++) {
                        $file = $files[$i];
                        ?>
                        <div class="form-group">
                            <?= $form->label("ddfID" . $i, t("File to download on purchase")); ?>
                            <?= $al->file('ddfID' . $i, 'ddfID[]', t('Choose File'), is_object($file) ? $file : null) ?>
                        </div>
                    <?php } ?>
                    <div class="form-group">
                        <?= $form->checkbox('pCreateUserAccount', '1', $product->createsLogin()) ?>
                        <?= $form->label('pCreateUserAccount', t('Create user account on purchase')) ?>
                        <span class="help-block"><?= t('When checked, if customer is guest, will create a user account on purchase'); ?></span>
                    </div>

                    <div class="form-group">
                        <?= $form->label("usergroups", t("On purchase add user to user groups")); ?>
                        <div class="ccm-search-field-content ccm-search-field-content-select2">
                            <select multiple="multiple" name="pUserGroups[]" id="groupselect" class="select2-select" style="width: 100%;" placeholder="<?= t('Select user groups'); ?>">
                                <?php
                                $selectedusergroups = $product->getUserGroupIDs();
                                foreach ($usergroups as $ugkey => $uglabel) { ?>
                                    <option value="<?= $ugkey; ?>" <?= (in_array($ugkey, $selectedusergroups) ? 'selected="selected"' : ''); ?>>  <?= $uglabel; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>


                    <script type="text/javascript">
                        $(document).ready(function () {
                            $('.select2-select').select2();
                        });
                    </script>


                </div><!-- #product-digital -->

                <div class="store-pane" id="product-checkout">
                    <div class="form-group">
                        <?= $form->checkbox('pAutoCheckout', '1', $product->autoCheckout()) ?>
                        <?= $form->label('pAutoCheckout', t('Send customer directly to checkout when added to cart')) ?>
                    </div>


                    <div class="form-group">
                        <?= $form->label('pOrderCompleteCID', t('Order Complete Destination')); ?>
                        <?= $ps->selectPage('pOrderCompleteCID', $product->getOrderCompleteCID()); ?>
                    </div>

                    <div class="form-group">
                        <?= $form->checkbox('pExclusive', '1', $product->isExclusive()) ?>
                        <?= $form->label('pExclusive', t('Prevent this item from being in the cart with other items')) ?>
                    </div>

                    <div class="form-group">
                        <?= $form->label('pNotificationEmails', t('If order contains this product also send order notification to email(s)')); ?>
                        <?= $form->text('pNotificationEmails', $product->getNotificationEmails(), ['placeholder' => t('Email Address')]); ?>
                        <span class="help-block"><?= t('separate multiple emails with commas'); ?></span>
                    </div>

                </div><!-- #checkout-digital -->

                <div class="store-pane" id="product-page">

                    <?php if ($page && !$page->isInTrash()) { ?>
                        <p><strong><?= t("Detail Page is set to: ") ?><a href="<?= $page->getCollectionLink() ?>" target="_blank"><?= $page->getCollectionName() ?></a></strong></p>
                        <?= $ps->selectPage('pageCID', $page->getCollectionID()); ?>
                    <?php } else { ?>

                        <?php if ($product->getID()) { ?>
                            <div class="alert alert-warning">
                                <?= t("This product is missing a corresponding page in the sitemap") ?>
                            </div>
                            <label for="pageCID"><?= t('Associate with an existing page'); ?></label>
                            <?= $ps->selectPage('pageCID', ''); ?>
                            <br/>
                            <label class="control-label"><?= t('Or, create a new product page'); ?></label><br /><br />
                        <?php } else { ?>
                            <div class="form-group">
                                <?= $form->checkbox('createPage', '1',  true) ?>
                                <?= $form->label('createPage', t('Create Product Page In Sitemap')) ?>
                            </div>
                        <?php } ?>

                        <?php if ($productPublishTarget) { ?>
                            <?php if ($pageTemplates && !empty($pageTemplates)) { ?>
                                <div class="form-group">
                                    <label class="control-label"><?= t("Page Template") ?></label>
                                    <?= $form->select('selectPageTemplate', $pageTemplates, $defaultTemplateID); ?>
                                </div>

                                <?php if ($product->getID()) { ?>
                                    <a data-confirm-message="<?= h(t('Any changes to the product will not be saved. Create product page?')); ?>" href="<?= Url::to('/dashboard/store/products/generate/', $product->getID()) ?>" class="btn btn-primary" id="btn-generate-page"><?= t("Generate a Product Page") ?></a>
                                <?php } ?>
                            <?php } else { ?>
                                <div class="alert alert-warning">
                                    <?= t("A Page Type with the handle store_product was not found") ?>
                                </div>
                            <?php } ?>
                        <?php } else { ?>
                            <div class="alert alert-warning">
                                <?= t("No page is configured as the parent page for new products") ?>
                            </div>
                        <?php } ?>

                    <?php } ?>

                </div>
            </div>
        </div><!-- .row -->

        <div class="ccm-dashboard-form-actions-wrapper">
            <div class="ccm-dashboard-form-actions">
                <a href="<?= Url::to('/dashboard/store/products/'. ($groupSearch ? $groupSearch : '') . ($keywordsSearch ? '?keywords='.urlencode($keywordsSearch) : '')) ?>" class="btn btn-default btn-secondary pull-left float-start"><?= t("Cancel / View All Products") ?></a>
                <button class="float-end pull-right btn btn-primary" disabled="disabled" type="submit"><?= t('%s Product', $actionType) ?></button>
            </div>
        </div>

        <script>
            $(window).on('load', function(){
                setTimeout(
                    function () {
                        $('.ccm-dashboard-form-actions .btn-primary').removeAttr('disabled');
                    }, 500);
            });

            $(function () {
                $('.variationdisplaybutton').click(function (el) {
                    $(this).closest('.panel').find('.extrafields').toggleClass('hidden d-none');
                    el.preventDefault();
                });
            });
        </script>

    </form>


<?php } elseif (in_array($controller->getAction(), $listViews)) { ?>

    <div class="ccm-dashboard-header-buttons">
        <!--<a href="<?= Url::to('/dashboard/store/products/', 'attributes') ?>" class="btn btn-dark"><?= t("Manage Attributes") ?></a>-->
        <a href="<?= Url::to('/dashboard/store/products/', 'groups') ?>" class="btn btn-primary"><?= t("Manage Groups") ?></a>
        <a href="<?= Url::to('/dashboard/store/products/', 'add') ?>" class="btn btn-primary"><?= t("Add Product") ?></a>
    </div>

    <div class="cccm-dashboard-content-inner">

        <?php
        $version = $app->make('config')->get('concrete.version');
        if (version_compare($version, '9.0', '<')) { ?>

        <form role="form" class="form-inline">
            <div class="row">
                <div class="ccm-search-fields-submit col-sm-12 col-md-6">
                    <?= $form->search('keywords', $searchRequest['keywords'], ['placeholder' => t('Search by Name or SKU'), 'style'=>"min-width: 220px"]) ?>
                    <button class="btn btn-info" type="submit"><i class="fa fa-search"></i></button>
                </div>

                <div class="col-sm-12 col-md-6">
                    <?php if ($grouplist) {
                        $currentFilter = '';
                        ?>
                        <ul id="group-filters" class="nav nav-pills">

                            <li role="presentation" class="dropdown <?= ($gID ? 'active' : ''); ?> nav-item">
                                <?php
                                if ($gID) {
                                    foreach ($grouplist as $group) {
                                        if ($gID == $group->getGroupID()) {
                                            $currentFilter = $group->getGroupName();
                                        }
                                    }
                                } ?>

                                <a class="dropdown-toggle nav-link" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
                                    <?= $currentFilter ? t('Product Group: %s', $currentFilter) : t('Product Group'); ?> <span class="caret"></span>
                                </a>

                                <ul class="dropdown-menu">
                                    <li class="nav-item <?= (!$gID ? 'active' : ''); ?>"><a class="nav-link" href="<?= Url::to('/dashboard/store/products/') ?>"><?= t('All Groups') ?></a></li>
                                    <?php foreach ($grouplist as $group) { ?>
                                        <li class="nav-item <?= ($gID == $group->getGroupID() ? 'active' : ''); ?>"><a class="nav-link" href="<?= Url::to('/dashboard/store/products/', $group->getGroupID()) ?>"><?= $group->getGroupName() ?></a></li>
                                    <?php } ?>
                                </ul>
                            </li>
                        </ul><br />
                    <?php } ?>
                </div>
            </div>
        </form>
        <?php } ?>

        <div class="ccm-dashboard-content-full">
        <table class="ccm-search-results-table">
            <thead>
            <tr>
                <th><a><?= t('Primary Image') ?></a></th>
                <th><a href="<?= $productList->getSortURL('alpha'); ?>"><?= t('Product Name') ?></a></th>
                <th><a href="<?= $productList->getSortURL('active'); ?>"><?= t('Active') ?></a></th>
                <th><a><?= t('Stock Level') ?></a></th>
                <th><a href="<?= $productList->getSortURL('price'); ?>"><?= t('Price') ?></a></th>
                <th><a><?= t('Featured') ?></a></th>
                <th><a><?= t('Groups') ?></a></th>
                <th><a><?= t('Actions') ?></a></th>
            </tr>
            </thead>
            <tbody>

            <?php if (count($products) > 0) {
                foreach ($products as $product) {
                    ?>
                    <tr>
                        <td><?= $product->getImageThumb(); ?></td>
                        <td><strong><a href="<?= Url::to('/dashboard/store/products/edit/', $product->getID()) ?>"><?= $product->getName();
                                    $sku = $product->getSKU();
                                    if ($sku) {
                                        echo ' (' . $sku . ')';
                                    }
                                    ?>
                                </a></strong></td>
                        <td>
                            <?php
                            if ($product->isActive()) {
                                echo "<span class='label label-success ". $badgeClass ." bg-success'>" . t('Active') . "</span>";
                            } else {
                                echo "<span class='label label-default ". $badgeClass ." bg-success'>" . t('Inactive') . "</span>";
                            }
                            ?>

                        </td>
                        <td><?php
                            if ($product->hasVariations()) {
                                echo '<span class="label label-info">' . t('Multiple') . '</span>';
                            } else {
                                echo($product->isUnlimited(true) ? '<span class="label label-default">' . t('Unlimited') . '</span>' : $product->getQty());
                            } ?>

                            <?php
                            $startDate = $product->getDateAvailableStart();
                            $endDate = $product->getDateAvailableEnd();

                            if ($startDate || $endDate) {
                                echo '<br /><span class="label label-warning">' . t('Stock time limited') . '</span>';
                            }
                            ?>

                        </td>
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
                                echo "<span class='label label-success ". $badgeClass ." bg-success'>" . t('Featured') . "</span>";
                            } else {
                                echo "<span class='label label-default ". $badgeClass ." bg-success'>" . t('Not Featured') . "</span>";
                            }
                            ?>
                        </td>
                        <td>
                            <?php $productgroups = $product->getGroups();
                            foreach ($productgroups as $pg) { ?>
                                <span class="label label-primary <?= $badgeClass; ?> badge-primary"><?= $pg->getGroup()->getGroupName(); ?></span>
                            <?php } ?>

                            <?php if (empty($productgroups)) { ?>
                                <em><?= t('None'); ?></em>
                            <?php } ?>
                        </td>
                        <td>
                            <div class="btn-group" style="width:100px">
                                <a class="btn btn-sm btn-primary" title="<?= t('Manage'); ?>"
                                   href="<?= Url::to('/dashboard/store/products/edit/', $product->getID()) ?>"><i class="fa fa-pencil-alt fa-pencil"></i></a>

                                        <button type="button" class="btn btn-primary btn-sm dropdown-toggle" data-bs-toggle="dropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <span class="caret"></span>
                                        </button>
                                        <ul class="dropdown-menu" >
                                            <?php
                                            $page = $product->getProductPage();
                                            if ($page && !$page->isInTrash()) { ?>
                                                <li><a class="nav-link"  target="_blank" href="<?= $page->getCollectionLink() ?>"><?= t('View Product Page') ?></a></li>
                                            <?php } else { ?>
                                                <li><a class="nav-link"  style="pointer-events: none; cursor: none" disabled href=""><?= t('No product page'); ?></a></li>
                                            <?php } ?>
                                            <?php if ($multilingualEnabled) { ?>
                                            <li><a class="nav-link"  target="_blank" href="<?= Url::to('/dashboard/store/multilingual/products/translate/' . $product->getID()) ?>"><?= t("Translate") ?></a></li>
                                            <?php } ?>
                                        </ul>

                            </div>
                        </td>
                    </tr>
                <?php }
            } ?>
            </tbody>
        </table>
        </div>

        <?php if ($paginator->getTotalPages() > 1) { ?>
            <div class="ccm-search-results-pagination">
                <?= $pagination ?>
            </div>
        <?php } ?>

    </div>

<?php } ?>

<?php if ($controller->getAction() == 'duplicate') { ?>
    <form method="post" action="<?= $view->action('duplicate', $product->getID()) ?>">
        <?= $token->output('community_store'); ?>
        <div class="form-group">
            <?= $form->label('newName', t("New Product Name")); ?>
            <?= $form->text('newName', $product->getName() . ' ' . t('(Copy)')); ?>
        </div>

        <div class="form-group">
            <?= $form->label('newSKU', t("New Product SKU")); ?>
            <?= $form->text('newSKU', $product->getSKU()); ?>
        </div>

        <input type="submit" class="btn btn-primary" value="<?= t('Duplicate Product'); ?>">
    </form>

<?php } ?>


<style>
    @media (max-width: 992px) {
        div#ccm-dashboard-content div.ccm-dashboard-content-full {
            margin-left: -20px !important;
            margin-right: -20px !important;
        }
    }

    .smallbreak {
       height: 10px;
        display: block;
        content: '';
    }
</style>

<script>

    $(document).ready(function() {
       $('.btn-selectfile').click(function(e){
           e.preventDefault();
           var pvfid  = $(this).data('pvfid');
           var button = $(this);

           ConcreteFileManager.launchDialog(function (data) {
               ConcreteFileManager.getFileDetails(data.fID, function (r) {
                       let template = document.createElement('template');
                       template.innerHTML = r.files[0].resultsThumbnailImg;

                       $('#pvfID-' + pvfid).val(data['fID']);
                       $('#pvfIDImage-' + pvfid).prop('src',template.content.firstChild.src ).removeClass('hidden d-none').prop('title', r.files[0].title);
                       button.next().removeClass('hidden d-none');
               });
           }, {
             filters:
                 [{"field":"type","type":1}]
             }

           );
       }) ;

       $('.btn-clearfile').click(function(e){
            e.preventDefault();
            var pvfid  = $(this).data('pvfid');

            $('#pvfID-' + pvfid).val('');
            $('#pvfIDImage-' + pvfid).prop('src','').addClass('hidden d-none');
            $(this).addClass('hidden d-none');
        });
    });

</script>

<style>
    #ccm-dashboard-content-regular .nav-pills.nav-stacked .active a {
        font-weight: bold
    }
</style>
