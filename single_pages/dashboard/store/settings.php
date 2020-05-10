<?php defined('C5_EXECUTE') or die("Access Denied.");

use Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Image;
use \Concrete\Core\Support\Facade\Url;
use \Concrete\Core\Support\Facade\Config;

$app = \Concrete\Core\Support\Facade\Application::getFacadeApplication();
?>

<div class="ccm-dashboard-header-buttons">
    <a href="<?= Url::to('/dashboard/store/settings/shipping'); ?>" class="btn btn-primary"><i class="fa fa-truck fa-flip-horizontal"></i> <?= t("Shipping Methods"); ?></a>
    <a href="<?= Url::to('/dashboard/store/settings/tax'); ?>" class="btn btn-primary"><i class="fa fa-money"></i> <?= t("Tax Rates"); ?></a>
</div>

<form method="post">
    <?= $token->output('community_store'); ?>

    <div class="row">
        <div class="col-sm-3">

            <ul class="nav nav-pills nav-stacked">
                <li class="active"><a href="#settings-currency" data-pane-toggle><?= t('Currency'); ?></a></li>
                <li><a href="#settings-tax" data-pane-toggle><?= t('Tax'); ?></a></li>
                <li><a href="#settings-shipping" data-pane-toggle><?= t('Shipping'); ?></a></li>
                <li><a href="#settings-payments" data-pane-toggle><?= t('Payments'); ?></a></li>
                <li><a href="#settings-order-statuses" data-pane-toggle><?= t('Fulfilment Statuses'); ?></a></li>
                <li><a href="#settings-notifications" data-pane-toggle><?= t('Notifications and Receipts'); ?></a></li>
                <li><a href="#settings-customers" data-pane-toggle><?= t('Customers'); ?></a></li>
                <li><a href="#settings-products" data-pane-toggle><?= t('Products'); ?></a></li>
                <li><a href="#settings-product-images" data-pane-toggle><?= t('Product Images'); ?></a></li>
                <li><a href="#settings-digital-downloads" data-pane-toggle><?= t('Digital Downloads'); ?></a></li>
                <li><a href="#settings-checkout" data-pane-toggle><?= t('Cart and Checkout'); ?></a></li>
                <li><a href="#settings-orders" data-pane-toggle><?= t('Orders'); ?></a></li>
            </ul>

        </div>

        <div class="col-sm-9 store-pane active" id="settings-currency">
            <h3><?= t('Currency Settings'); ?></h3>

            <div class="row">
                    <div class="form-group col-md-4">
                        <?= $form->label('currency', t('Currency')); ?>

                        <?php
                        $currencies = [''=> '-- '. t('Unspecified') . ' --'];
                        $currencyList = array_merge($currencies, $currencyList);
                        ?>

                        <?= $form->select('currency', $currencyList, Config::get('community_store.currency')); ?>
                    </div>
            </div>

            <div id="extra-currency" class="row <?= (Config::get('community_store.currency') ? 'hidden' : ''); ?>">
                <div class="form-group col-md-4">
                    <?= $form->label('symbol', t('Currency Symbol')); ?>
                    <?= $form->text('symbol', Config::get('community_store.symbol')); ?>
                </div>
                <div class="form-group col-md-4">
                    <?= $form->label('thousand', t('Thousands Separator')); ?>
                    <?= $form->text('thousand', Config::get('community_store.thousand')); ?>
                    <span class="help-block"><?= t('e.g. , or a space'); ?></span>
                </div>
                <div class="form-group col-md-4">
                    <?= $form->label('whole', t('Whole Number Separator')); ?>
                    <?= $form->text('whole', Config::get('community_store.whole')); ?>
                    <span class="help-block"><?= t('e.g. period or a comma'); ?></span>
                </div>
            </div>

            <script>
                $(function() {
                    $('#currency').change(function(){
                        if ($(this).val()) {
                            $('#extra-currency').addClass('hidden');
                        } else {
                            $('#extra-currency').removeClass('hidden');
                        }
                    });

                });
            </script>

        </div><!-- #settings-currency -->

        <div class="col-sm-9 store-pane" id="settings-tax">
            <h3><?= t('Tax Settings'); ?></h3>

            <div class="form-group">
                <?= $form->label('calculation', t('Are Prices Entered with Tax Included?')); ?>
                <?= $form->select('calculation', ['add' => t("No, I will enter product prices EXCLUSIVE of tax"), 'extract' => t("Yes, I will enter product prices INCLUSIVE of tax")], Config::get('community_store.calculation')); ?>
            </div>

            <div class="form-group">
                <?= $form->label('vat_number', t('Enable EU VAT Number Options?')); ?>
                <?= $form->select('vat_number', ['0' => t("No, I don't need this"), '1' => t("Yes, enable VAT Number options")], Config::get('community_store.vat_number')); ?>
            </div>

        </div>

        <div class="col-sm-9 store-pane" id="settings-shipping">

            <h3><?= t("Shipping Units"); ?></h3>
            <div class="row">
                <div class="col-xs-6">
                    <div class="form-group">
                        <?= $form->label('weightUnit', t('Units for Weight')); ?>
                        <?php  ?>
                        <?= $form->select('weightUnit', ['oz' => t('oz'), 'lb' => t('lb'), 'kg' => t('kg'), 'g' => t('g')], Config::get('community_store.weightUnit')); ?>
                    </div>
                </div>
                <div class="col-xs-6">
                    <div class="form-group">
                        <?= $form->label('sizeUnit', t('Units for Size')); ?>
                        <?php  ?>
                        <?= $form->select('sizeUnit', ['in' => t('in'), 'cm' => t('cm'), 'mm' => t('mm')], Config::get('community_store.sizeUnit')); ?>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-xs-12">
                    <label><?= $form->checkbox('deliveryInstructions', '1', Config::get('community_store.deliveryInstructions') ? '1' : '0'); ?>
                        <?= t('Include Delivery Instructions field in checkout'); ?></label>
                </div>
            </div>

            <h3><?= t("Multiple Packages Support"); ?></h3>
            <div class="row">
                <div class="col-xs-12">
                    <label><?= $form->checkbox('multiplePackages', '1', Config::get('community_store.multiplePackages') ? '1' : '0'); ?>
                        <?= t('Enable Package(s) Data fields'); ?></label>
                    <span class="help-block"> <?= t('Allows multiple packages to be defined per product configuration, to be used by advanced shipping methods'); ?></span>
                </div>
            </div>


        </div><!-- #settings-shipping -->

        <div class="col-sm-9 store-pane" id="settings-payments">
            <h3><?= t("Payment Methods"); ?></h3>
            <?php
            if ($installedPaymentMethods) {
                foreach ($installedPaymentMethods as $pm) {
                    ?>

            <div class="panel panel-default">

                <div class="panel-heading"><?= t($pm->getName()); ?></div>

                <div class="panel-body">

                    <div class="row">
                        <div class="col-md-9">
                            <div class="form-group paymentMethodEnabled">
                                <input type="hidden" name="paymentMethodHandle[<?= $pm->getID(); ?>]" value="<?= $pm->getHandle(); ?>">
                                <?= $form->label("paymentMethodEnabled[" . $pm->getID() . "]", t("Enabled"));?>
                                <?php
                                echo $form->select("paymentMethodEnabled[" . $pm->getID() . "]", [0 => t("No"), 1 => t("Yes")], $pm->isEnabled()); ?>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <?= $form->label("paymentMethodSortOrder[" . $pm->getID() . "]", t("Sort Order"));?>
                                <?= $form->number('paymentMethodSortOrder[' . $pm->getID() . ']', $pm->getSortOrder()); ?>
                            </div>
                        </div>

                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <?= $form->label("paymentMethodUserGroups[<?= $pm->getID(); ?>]", t("Available To User Groups"));?>
                                <div class="ccm-search-field-content ccm-search-field-content-select2">
                                    <select multiple="multiple" name="paymentMethodUserGroups[<?= $pm->getID(); ?>][]" id="groupselect" class="selectize" style="width: 100%;" placeholder="<?= t('All User Groups');?>">
                                        <?php
                                        foreach ($allGroupList as $ugkey=>$uglabel) { ?>
                                            <option value="<?= $ugkey;?>" <?= (in_array($ugkey, $pm->getUserGroups()) ? 'selected="selected"' : ''); ?>>  <?= $uglabel; ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <?= $form->label("paymentMethodExcludedUserGroups[<?= $pm->getID(); ?>]", t("Exclude From User Groups"));?>
                                <div class="ccm-search-field-content ccm-search-field-content-select2">
                                    <select multiple="multiple" name="paymentMethodExcludedUserGroups[<?= $pm->getID(); ?>][]" id="groupselect" class="selectize" style="width: 100%;" placeholder="<?= t('None');?>">
                                        <?php
                                        foreach ($allGroupList as $ugkey=>$uglabel) { ?>
                                            <option value="<?= $ugkey;?>" <?= (in_array($ugkey,  $pm->getExcludedUserGroups()) ? 'selected="selected"' : ''); ?>>  <?= $uglabel; ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div id="paymentMethodForm-<?= $pm->getID(); ?>" style="display:<?= $pm->isEnabled() ? 'block' : 'none'; ?>">
                        <div class="row">
                            <div class="form-group col-sm-6">
                                <?= $form->label("paymentMethodDisplayName[" . $pm->getID() . "]", t("Display Name (on checkout)"));?>
                                <?= $form->text('paymentMethodDisplayName[' . $pm->getID() . ']', $pm->getDisplayName()); ?>
                            </div>
                            <div class="form-group col-sm-6">
                                <?= $form->label("paymentMethodButtonLabel[" . $pm->getID() . "]", t("Button Label"));?>
                                <?= $form->text('paymentMethodButtonLabel[' . $pm->getID() . ']', $pm->getButtonLabel(), ['placeholder' => t('Optional')]); ?>
                            </div>
                        </div>
                        <?php
                        $pm->renderDashboardForm(); ?>
                    </div>

                </div>

            </div>

            <?php

        }
    } else {
        echo t("No Payment Methods are Installed");
    }
    ?>

            <script>
                $(function() {
                    $('.paymentMethodEnabled SELECT').on('change', function() {
                        var $this = $(this);
                        if ($this.val() == 1) {
                            $this.parent().next().slideDown();
                        } else {
                            $this.parent().next().slideUp();
                        }
                    });
                });
            </script>
        </div><!-- #settings-payments -->

        <div class="col-sm-9 store-pane" id="settings-order-statuses">
            <h3><?= t("Fulfilment Statuses"); ?></h3>
            <?php
            if (count($orderStatuses) > 0) {
                ?>
            <div class="panel panel-default">

                <table class="table" id="orderStatusTable">
                    <thead>
                        <tr>
                            <th rowspan="1">&nbsp;</th>
                            <th rowspan="1"><?= t('Display Name'); ?></th>
                            <th rowspan="1"><?= t('Default Status'); ?></th>
                            <th colspan="2" style="display:none;"><?= t('Send Change Notifications to...'); ?></th>
                        </tr>
                        <tr style="display:none;">
                            <th><?= t('Site'); ?></th>
                            <th><?= t('Customer'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orderStatuses as $orderStatus) {
                            ?>
                        <tr>
                            <td class="sorthandle"><input type="hidden" name="osID[]" value="<?= $orderStatus->getID(); ?>"><i class="fa fa-arrows-v"></i></td>
                            <td><input type="text" name="osName[]" value="<?= t($orderStatus->getName()); ?>" placeholder="<?= $orderStatus->getReadableHandle(); ?>" class="form-control ccm-input-text"></td>
                            <td><input type="radio" name="osIsStartingStatus" value="<?= $orderStatus->getID(); ?>" <?= $orderStatus->isStartingStatus() ? 'checked' : ''; ?>></td>
                            <td style="display:none;"><input type="checkbox" name="osInformSite[]" value="1" <?= $orderStatus->getInformSite() ? 'checked' : ''; ?> class="form-control"></td>
                            <td style="display:none;"><input type="checkbox" name="osInformCustomer[]" value="1" <?= $orderStatus->getInformCustomer() ? 'checked' : ''; ?> class="form-control"></td>
                        </tr>
                        <?php

                    } ?>
                    </tbody>
                </table>
                <script>
                    $(function() {
                        $('#orderStatusTable TBODY').sortable({
                            cursor: 'move',
                            opacity: 0.5,
                            handle: '.sorthandle'
                        });

                    });
                </script>

            </div>

            <?php

        } else {
            echo t("No Fulfilment Statuses are available");
        }
        ?>


        </div><!-- #settings-order-statuses -->

        <div class="col-sm-9 store-pane" id="settings-notifications">
            <h3><?= t('Notification Emails'); ?></h3>

            <div class="form-group">
                <?= $form->label('notificationEmails', t('Send order notification to email')); ?>
                <?= $form->text('notificationEmails', Config::get('community_store.notificationemails'), ['placeholder' => t('Email Address')]); ?>
                <span class="help-block"><?= t('separate multiple emails with commas'); ?></span>
            </div>

            <h4><?= t('Emails Sent From'); ?></h4>

            <div class="row">
                <div class="col-xs-6">
                    <div class="form-group">
                        <?= $form->label('emailAlert', t('From Email')); ?>
                        <?= $form->email('emailAlert', Config::get('community_store.emailalerts'), ['placeholder' => t('From Email Address')]); ?>
                    </div>
                </div>

                <div class="col-xs-6">
                    <div class="form-group">
                        <?= $form->label('emailAlertName', t('From Name')); ?>
                        <?= $form->text('emailAlertName', Config::get('community_store.emailalertsname'), ['placeholder' => t('From Name')]); ?>
                    </div>
                </div>
            </div>

            <h3><?= t('Receipt Emails'); ?></h3>

            <div class="form-group">
                <?= $form->label('receiptHeader', t('Receipt Email Header Content')); ?>
                <?php $editor = $app->make('editor');
                echo $editor->outputStandardEditor('receiptHeader', Config::get('community_store.receiptHeader')); ?>
            </div>

            <div class="form-group">
                <?= $form->label('receiptFooter', t('Receipt Email Footer Content')); ?>
                <?php $editor = $app->make('editor');
                echo $editor->outputStandardEditor('receiptFooter', Config::get('community_store.receiptFooter')); ?>
            </div>


        </div>

        <!-- #settings-customers -->
        <div class="col-sm-9 store-pane" id="settings-customers">
            <h3><?= t("Customers"); ?></h3>
            <div class="row">
                <div class="col-xs-12">
                    <div class="form-group">
                        <?= $form->label('customerGroup', t('Customers User Group')); ?>
                        <?= $form->select('customerGroup', $groupList, $customerGroup, [  'placeholder' => t('Select a Group')]); ?>
                    </div>

                    <div class="form-group">
                        <?= $form->label('wholesaleCustomerGroup', t('Wholesale Customers User Group')); ?>
                        <?= $form->select('wholesaleCustomerGroup', $groupList, $wholesaleCustomerGroup, ['placeholder' => t('Select a Group')]); ?>
                    </div>

                    <div class="alert alert-warning">
                        <?= t("If you change group remember to switch your existing customers over to the new group"); ?>
                    </div>

                </div>
            </div>
        </div>

        <!-- #settings-products -->
        <div class="col-sm-9 store-pane" id="settings-products">
            <h3><?= t("Products"); ?></h3>
            <div class="form-group">
                <?= $form->label('productPublishTarget', t('Page to Publish Product Pages Under')); ?>
                <?= $pageSelector->selectPage('productPublishTarget', $productPublishTarget); ?>
            </div>
        </div>

        <!-- #settings-product-images -->
        <div class="col-sm-9 store-pane" id="settings-product-images">
            <h3><?= t("Product Images"); ?></h3>

            <div class="row">
                <h4 class="col-md-12"><?= t("Product Thumbnail Types"); ?></h4>
                <div class="form-group col-md-12">
                    <?= $form->label('defaultSingleProductThumbType', t('Single Product Thumbnail Type')); ?>
                    <?= $form->select('defaultSingleProductThumbType', $thumbnailTypes, Config::get('community_store.defaultSingleProductThumbType')); ?>
                </div>

                <div class="form-group col-md-12">
                    <?= $form->label('defaultProductListThumbType', t('Product List Thumbnail Type')); ?>
                    <?= $form->select('defaultProductListThumbType', $thumbnailTypes, Config::get('community_store.defaultProductListThumbType')); ?>
                </div>

                <div class="form-group col-md-12">
                    <?= $form->label('defaultProductModalThumbType', t('Product Modal Thumbnail Type')); ?>
                    <?= $form->select('defaultProductModalThumbType', $thumbnailTypes, Config::get('community_store.defaultProductModalThumbType')); ?>
                </div>
            </div>
            <div class="row">
                <div class="form-group col-md-12">
                    <div class="alert alert-info small">
                        <?= t("%sThumbnail types will be used if selected because they offer better performance. %sIf they are not available for any reason, the Legacy Thumbnailer Generator set below will be used as fallback to avoid any disruption. %sReasons thumbnail types can be unavailable are if you don't select one, if it was deleted or if the image displayed doesn't have a thumbnail of the selected type.%s", '<p>', '</p><p>', '</p><p>', '</p>'); ?>
                    </div>
                </div>
            </div>
            <div class="row">
                <h4 class="col-md-12"><?= t("Single Product - Legacy Thumbnail Generator"); ?></h4>
                <div class="form-group col-md-4">
                    <?= $form->label('defaultSingleProductImageWidth', t('Image Width')); ?>
                    <div class="input-group">
                        <?= $form->number('defaultSingleProductImageWidth', Config::get('community_store.defaultSingleProductImageWidth') ?: Image::DEFAULT_SINGLE_PRODUCT_IMG_WIDTH, ['min' => '0', 'step' => '1']); ?>
                        <div class="input-group-addon">px</div>
                    </div>
                    <div class="help-block">
                        <?= t("Default value: %s", Image::DEFAULT_SINGLE_PRODUCT_IMG_WIDTH); ?>
                    </div>
                </div>

                <div class="form-group col-md-4">
                    <?= $form->label('defaultSingleProductImageHeight', t('Image Height')); ?>
                    <div class="input-group">
                        <?= $form->number('defaultSingleProductImageHeight', Config::get('community_store.defaultSingleProductImageHeight') ?: Image::DEFAULT_SINGLE_PRODUCT_IMG_HEIGHT, ['min' => '0', 'step' => '1']); ?>
                        <div class="input-group-addon">px</div>
                    </div>
                    <div class="help-block">
                        <?= t("Default value: %s", Image::DEFAULT_SINGLE_PRODUCT_IMG_HEIGHT); ?>
                    </div>
                </div>

                <div class="form-group col-md-4">
                    <?= $form->label('defaultSingleProductCrop', t('Image cropping')); ?>
                    <?= $form->select('defaultSingleProductCrop', ['0' => t("Scale proportionally"), '1' => t("Scale and crop")], Config::get('community_store.defaultSingleProductCrop')); ?>
                </div>
            </div>

            <div class="row">
                <h4 class="col-md-12"><?= t("Product List - Legacy Thumbnail Generator"); ?></h4>
                <div class="form-group col-md-4">
                    <?= $form->label('defaultProductListImageWidth', t('Image Width')); ?>
                    <div class="input-group">
                        <?= $form->number('defaultProductListImageWidth', Config::get('community_store.defaultProductListImageWidth') ?: Image::DEFAULT_PRODUCT_LIST_IMG_WIDTH, ['min' => '0', 'step' => '1']); ?>
                        <div class="input-group-addon">px</div>
                    </div>
                    <div class="help-block">
                        <?= t("Default value: %s", Image::DEFAULT_PRODUCT_LIST_IMG_WIDTH); ?>
                    </div>
                </div>

                <div class="form-group col-md-4">
                    <?= $form->label('defaultProductListImageHeight', t('Image Height')); ?>
                    <div class="input-group">
                        <?= $form->number('defaultProductListImageHeight', Config::get('community_store.defaultProductListImageHeight') ?: Image::DEFAULT_PRODUCT_LIST_IMG_HEIGHT, ['min' => '0', 'step' => '1']); ?>
                        <div class="input-group-addon">px</div>
                    </div>
                    <div class="help-block">
                        <?= t("Default value: %s", Image::DEFAULT_PRODUCT_LIST_IMG_HEIGHT); ?>
                    </div>
                </div>

                <div class="form-group col-md-4">
                    <?= $form->label('defaultProductListCrop', t('Image cropping')); ?>
                    <?= $form->select('defaultProductListCrop', ['0' => t("Scale proportionally"), '1' => t("Scale and crop")], Config::get('community_store.defaultProductListCrop')); ?>
                </div>
            </div>

            <div class="row">
                <h4 class="col-md-12"><?= t("Product Modal - Legacy Thumbnail Generator"); ?></h4>
                <div class="form-group col-md-4">
                    <?= $form->label('defaultProductModalImageWidth', t('Image Width')); ?>
                    <div class="input-group">
                        <?= $form->number('defaultProductModalImageWidth', Config::get('community_store.defaultProductModalImageWidth') ?: Image::DEFAULT_PRODUCT_MODAL_IMG_WIDTH, ['min' => '0', 'step' => '1']); ?>
                        <div class="input-group-addon">px</div>
                    </div>
                    <div class="help-block">
                        <?= t("Default value: %s", Image::DEFAULT_PRODUCT_MODAL_IMG_WIDTH); ?>
                    </div>
                </div>

                <div class="form-group col-md-4">
                    <?= $form->label('defaultProductModalImageHeight', t('Image Height')); ?>
                    <div class="input-group">
                        <?= $form->number('defaultProductModalImageHeight', Config::get('community_store.defaultProductModalImageHeight') ?: Image::DEFAULT_PRODUCT_MODAL_IMG_HEIGHT, ['min' => '0', 'step' => '1']); ?>
                        <div class="input-group-addon">px</div>
                    </div>
                    <div class="help-block">
                        <?= t("Default value: %s", Image::DEFAULT_PRODUCT_MODAL_IMG_HEIGHT); ?>
                    </div>
                </div>

                <div class="form-group col-md-4">
                    <?= $form->label('defaultProductModalCrop', t('Image cropping')); ?>
                    <?= $form->select('defaultProductModalCrop', ['0' => t("Scale proportionally"), '1' => t("Scale and crop")], Config::get('community_store.defaultProductModalCrop')); ?>
                </div>
            </div>
        </div>

        <!-- #settings-digital-downloads -->
        <div class="col-sm-9 store-pane" id="settings-digital-downloads">
            <h3><?= t("Digital Downloads"); ?></h3>
            <div class="form-group">
                <?= $form->label('digitalDownloadFileSet', t('Digital Downloads File Set')); ?>
                <?= $form->select('digitalDownloadFileSet', $fileSets, $digitalDownloadFileSet,  ['class' => 'selectize']); ?>
                <div class="alert alert-warning">
                    <?= t("If you change file set remember to switch your existing digital downloads over to the new file set"); ?>
                </div>
            </div>


            <h3><?= t('Digital Download Expiry'); ?></h3>
            <div class="form-group">
                <?= $form->label('download_expiry_hours', t('Number of hours before digital download links expiry')); ?>
                <div class="input-group">
                    <?= $form->number('download_expiry_hours', Config::get('community_store.download_expiry_hours'), ['placeholder' => '48']); ?>
                    <div class="input-group-addon"><?= t('hours'); ?></div>
                </div>
            </div>
        </div>

        <!-- #settings-checkout -->
        <div class="col-sm-9 store-pane" id="settings-checkout">
            <h3><?= t('Cart and Checkout'); ?></h3>
            <div class="form-group">
                <?php $shoppingDisabled = Config::get('community_store.shoppingDisabled');
                ?>
                <label><?= $form->radio('shoppingDisabled', ' ', ('' == $shoppingDisabled)); ?> <?php echo t('Enabled'); ?></label><br />
                <label><?= $form->radio('shoppingDisabled', 'all', 'all' == $shoppingDisabled); ?> <?php echo t('Disabled (Catalog Mode)'); ?></label><br />
            </div>

            <h3><?= t('Guest Checkout'); ?></h3>
            <div class="form-group">
                <?php $guestCheckout = Config::get('community_store.guestCheckout');
                $guestCheckout = ($guestCheckout ? $guestCheckout : 'off');
                ?>
                <label><?= $form->radio('guestCheckout', 'always', 'always' == $guestCheckout); ?> <?php echo t('Always (unless login required for products in cart)'); ?></label><br />
                <label><?= $form->radio('guestCheckout', 'option', 'option' == $guestCheckout); ?> <?php echo t('Offer as checkout option'); ?></label><br />
                <label><?= $form->radio('guestCheckout', 'off', 'off' == $guestCheckout || '' == $guestCheckout); ?> <?php echo t('Disabled'); ?></label><br />
            </div>

            <h3><?= t('Address Auto-Complete'); ?></h3>
            <div class="form-group">
                <?= $form->label('placesAPIKey', t('Address Auto-Complete API Key (Google Places)')); ?>
                <?= $form->text('placesAPIKey', Config::get('community_store.placesAPIKey')); ?>
            </div>

            <h3><?= t('Checkout Scroll Offset'); ?></h3>
            <div class="form-group">
                <?= $form->label('checkoutScrollOffset', t('Amount to offset the automatic scroll in the checkout')); ?>
                <div class="input-group">
                <?= $form->number('checkoutScrollOffset', Config::get('community_store.checkout_scroll_offset')); ?>
                    <div class="input-group-addon"><?= t('px');?></div>
                </div>
                <span class="help-block"><?= t('If your theme has a fixed header area in the checkout, enter a height in pixels of this area to offset the automatic scroll amount'); ?></span>
            </div>

            <h3><?= t('Company Name'); ?></h3>
            <div class="form-group">
                <?php $companyField = Config::get('community_store.companyField');
                $companyField = ($companyField ? $companyField : 'off');
                ?>
                <label><?= $form->radio('companyField', 'off', 'off' == $companyField || '' == $companyField); ?> <?php echo t('Hidden'); ?></label><br />
                <label><?= $form->radio('companyField', 'optional', 'optional' == $companyField); ?> <?php echo t('Optional'); ?></label><br />
                <label><?= $form->radio('companyField', 'required', 'required' == $companyField); ?> <?php echo t('Required'); ?></label><br />
            </div>

            <h3><?= t('Billing Details'); ?></h3>

            <div class="row">
                <div class="col-xs-12">
                    <label><?= $form->checkbox('noBillingSave', '1', Config::get('community_store.noBillingSave') ? '1' : '0'); ?>
                        <?= t('Do not save billing details to user on order'); ?></label>
                </div>
            </div>

            <div class="row">
                <div class="col-xs-12">
                    <div class="ccm-search-field-content">
                        <?= $form->label('noBillingSaveGroups', t('For users in groups')); ?>
                        <?= $form->selectMultiple('noBillingSaveGroups', $groupList, explode(',', Config::get('community_store.noBillingSaveGroups')), ['class' => 'selectize', 'style' => 'width: 100%', 'placeholder' => t('All Users/Groups')]); ?>
                    </div>
                </div>
            </div>


            <h3><?= t('Shipping Details'); ?></h3>
            <div class="row">
                <div class="col-xs-12">
                    <label><?= $form->checkbox('noShippingSave', '1', Config::get('community_store.noShippingSave') ? '1' : '0'); ?>
                        <?= t('Do not save shipping details to user on order'); ?></label>
                </div>
            </div>

            <div class="row">
                <div class="col-xs-12">
                    <div class="ccm-search-field-content">
                        <?= $form->label('noShippingSaveGroups', t('For users in groups')); ?>
                        <?= $form->selectMultiple('noShippingSaveGroups', $groupList, explode(',', Config::get('community_store.noShippingSaveGroups')), ['class' => 'selectize', 'style' => 'width: 100%', 'placeholder' => t('All Users/Groups')]); ?>
                    </div>
                </div>
            </div>


            <script>
                $(document).ready(function() {
                    $('.selectize').selectize({
                        plugins: ['remove_button'],
                        selectOnTab: true
                    });
                    $('.selectize').removeClass('form-control');
                });
            </script>

        </div>

        <!-- #settings-orders -->
        <div class="col-sm-9 store-pane" id="settings-orders">
            <h3><?= t('Orders'); ?></h3>
            <div class="form-group">
                <label><?= $form->checkbox('showUnpaidExternalPaymentOrders', '1', Config::get('community_store.showUnpaidExternalPaymentOrders') ? '1' : '0'); ?>
                    <?= t('Unhide orders with incomplete payments (i.e. cancelled Paypal transactions)'); ?></label>
            </div>

            <div class="form-group">
                <?= $form->label('numberOfOrders', t('Number of orders displayed per page on orders dashboard page')); ?>
               <?= $form->select('numberOfOrders', array(20 => 20, 50 => 50, 100 => 100, 500 => 500), Config::get('community_store.numberOfOrders'), array('style' => 'width: 125px;'))?>
            </div>

             <div class="checkbox">
                <label>
                    <?= $form->checkbox('logUserAgent', '1', (bool) Config::get('community_store.logUserAgent')) ?>
                    <?= t('Log User Agent') ?>
                    <span class="small text-muted"><br /><?= t('Log the user agent against the order (this will effect your GDPR compliance).') ?></span>
                </label>
            </div>

        </div>
    </div>

    <div class="ccm-dashboard-form-actions-wrapper">
        <div class="ccm-dashboard-form-actions">
            <button class="pull-right btn btn-primary" type="submit"><?= t('Save'); ?></button>
        </div>
    </div>

</form>
