<?php defined('C5_EXECUTE') or die("Access Denied.");
use Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Image;

?>

	    <div class="ccm-dashboard-header-buttons">
            <a href="<?php echo \URL::to('/dashboard/store/settings/shipping'); ?>" class="btn btn-primary"><i class="fa fa-truck fa-flip-horizontal"></i> <?php echo t("Shipping Methods"); ?></a>
            <a href="<?php echo \URL::to('/dashboard/store/settings/tax'); ?>" class="btn btn-primary"><i class="fa fa-money"></i> <?php echo t("Tax Rates"); ?></a>
        </div>

	    <form method="post" action="<?php echo $view->action('save'); ?>">
            <?php echo $token->output('community_store'); ?>

            <div class="row">
                <div class="col-sm-3">

                    <ul class="nav nav-pills nav-stacked">
                            <li class="active"><a href="#settings-currency" data-pane-toggle ><?php echo t('Currency'); ?></a></li>
                            <li><a href="#settings-tax" data-pane-toggle><?php echo t('Tax'); ?></a></li>
                            <li><a href="#settings-shipping" data-pane-toggle><?php echo t('Shipping'); ?></a></li>
                            <li><a href="#settings-payments" data-pane-toggle><?php echo t('Payments'); ?></a></li>
                            <li><a href="#settings-order-statuses" data-pane-toggle><?php echo t('Fulfilment Statuses'); ?></a></li>
                            <li><a href="#settings-notifications" data-pane-toggle><?php echo t('Notifications and Receipts'); ?></a></li>
                            <li><a href="#settings-products" data-pane-toggle><?php echo t('Products'); ?></a></li>
                            <li><a href="#settings-product-images" data-pane-toggle><?php echo t('Product Images'); ?></a></li>
                            <li><a href="#settings-checkout" data-pane-toggle><?php echo t('Cart and Checkout'); ?></a></li>
                            <li><a href="#settings-orders" data-pane-toggle><?php echo t('Orders'); ?></a></li>
                        </ul>

                </div>

                <div class="col-sm-9 store-pane active" id="settings-currency">
                    <h3><?php echo t('Currency Settings'); ?></h3>
                    <div class="row">
                        <div class="form-group col-md-6">
                            <?php echo $form->label('symbol', t('Currency Symbol')); ?>
                            <?php echo $form->text('symbol', Config::get('community_store.symbol')); ?>
                        </div>

                        <div class="form-group col-md-6">
                            <?php echo $form->label('currency', t('Currency Code')); ?>
                            <?php echo $form->text('currency', Config::get('community_store.currency')); ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-6">
                            <?php echo $form->label('thousand', t('Thousands Separator')); ?>
                            <?php echo $form->text('thousand', Config::get('community_store.thousand')); ?>
                            <span class="help-block"><?php echo t('e.g. , or a space'); ?></span>
                        </div>
                        <div class="form-group col-md-6">
                            <?php echo $form->label('whole', t('Whole Number Separator')); ?>
                            <?php echo $form->text('whole', Config::get('community_store.whole')); ?>
                            <span class="help-block"><?php echo t('e.g. period or a comma'); ?></span>
                        </div>
                    </div>

                </div><!-- #settings-currency -->

                <div class="col-sm-9 store-pane" id="settings-tax">
                    <h3><?php echo t('Tax Settings'); ?></h3>

                    <div class="form-group">
                        <label for="calculation"><?php echo t("Are Prices Entered with Tax Included?"); ?></label>
                        <?php echo $form->select('calculation', ['add' => t("No, I will enter product prices EXCLUSIVE of tax"), 'extract' => t("Yes, I will enter product prices INCLUSIVE of tax")], Config::get('community_store.calculation')); ?>
                    </div>

                    <div class="form-group">
                        <label for="vat_number"><?php echo t("Enable EU VAT Number Options?"); ?></label>
                        <?php echo $form->select('vat_number', ['0' => t("No, I don't need this"), '1' => t("Yes, enable VAT Number options")], Config::get('community_store.vat_number')); ?>
                    </div>

                </div>

                <div class="col-sm-9 store-pane" id="settings-shipping">

                    <h3><?php echo t("Shipping Units"); ?></h3>
                    <div class="row">
                        <div class="col-xs-6">
                            <div class="form-group">
                                <?php echo $form->label('weightUnit', t('Units for Weight')); ?>
                                <?php // do not add other units to this list. these are specific to making calculated shipping work?>
                                <?php echo $form->select('weightUnit', ['oz' => t('oz'), 'lb' => t('lb'), 'kg' => t('kg'), 'g' => t('g')], Config::get('community_store.weightUnit')); ?>
                            </div>
                        </div>
                        <div class="col-xs-6">
                            <div class="form-group">
                                <?php echo $form->label('sizeUnit', t('Units for Size')); ?>
                                <?php // do not add other units to this list. these are specific to making calculated shipping work?>
                                <?php echo $form->select('sizeUnit', ['in' => t('in'), 'cm' => t('cm'), 'mm' => t('mm')], Config::get('community_store.sizeUnit')); ?>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-xs-12">
                            <label><?php echo $form->checkbox('deliveryInstructions', '1', Config::get('community_store.deliveryInstructions') ? '1' : '0'); ?>
                                <?php echo t('Include Delivery Instructions field in checkout'); ?></label>
                        </div>
                    </div>

                    <h3><?php echo t("Multiple Packages Support"); ?></h3>
                    <div class="row">
                        <div class="col-xs-12">
                            <label><?php echo $form->checkbox('multiplePackages', '1', Config::get('community_store.multiplePackages') ? '1' : '0'); ?>
                                <?php echo t('Enable Package(s) Data fields'); ?></label>
                            <span class="help-block">Allows multiple packages to be defined per product configuration, to be used by advanced shipping methods</span>
                        </div>
                    </div>



                </div><!-- #settings-shipping -->

                <div class="col-sm-9 store-pane" id="settings-payments">
                    <h3><?php echo t("Payment Methods"); ?></h3>
                    <?php
                        if ($installedPaymentMethods) {
                            foreach ($installedPaymentMethods as $pm) {
                                ?>

                            <div class="panel panel-default">

                                <div class="panel-heading"><?php echo t($pm->getName()); ?></div>
                                <div class="panel-body">
                                    <div class="form-group paymentMethodEnabled">
                                        <input type="hidden" name="paymentMethodHandle[<?php echo $pm->getID(); ?>]" value="<?php echo $pm->getHandle(); ?>">
                                        <label><?php echo t("Enabled"); ?></label>
                                        <?php
                                            echo $form->select("paymentMethodEnabled[" . $pm->getID() . "]", [0 => t("No"), 1 => t("Yes")], $pm->isEnabled()); ?>
                                    </div>
                                    <div id="paymentMethodForm-<?php echo $pm->getID(); ?>" style="display:<?php echo $pm->isEnabled() ? 'block' : 'none'; ?>">
                                        <div class="row">
                                            <div class="form-group col-sm-6">
                                                <label><?php echo t("Display Name (on checkout)"); ?></label>
                                                <?php echo $form->text('paymentMethodDisplayName[' . $pm->getID() . ']', $pm->getDisplayName()); ?>
                                            </div>
                                            <div class="form-group col-sm-6">
                                                <label><?php echo t("Button Label"); ?></label>
                                                <?php echo $form->text('paymentMethodButtonLabel[' . $pm->getID() . ']', $pm->getButtonLabel(), ['placeholder' => t('Optional')]); ?>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label><?php echo t("Sort Order"); ?></label>
                                            <?php echo $form->text('paymentMethodSortOrder[' . $pm->getID() . ']', $pm->getSortOrder()); ?>
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
                        $(function(){
                            $('.paymentMethodEnabled SELECT').on('change',function(){
                                var $this = $(this);
                                if ($this.val()==1) {
                                    $this.parent().next().slideDown();
                                } else {
                                    $this.parent().next().slideUp();
                                }
                            });
                        });
                    </script>
                </div><!-- #settings-payments -->

                <div class="col-sm-9 store-pane" id="settings-order-statuses">
                    <h3><?php echo t("Fulfilment Statuses"); ?></h3>
                    <?php
                    if (count($orderStatuses) > 0) {
                        ?>
                        <div class="panel panel-default">

                            <table class="table" id="orderStatusTable">
                                <thead>
                                <tr>
                                    <th rowspan="1">&nbsp;</th>
                                    <th rowspan="1"><?php echo t('Display Name'); ?></th>
                                    <th rowspan="1"><?php echo t('Default Status'); ?></th>
                                    <th colspan="2" style="display:none;"><?php echo t('Send Change Notifications to...'); ?></th>
                                </tr>
                                <tr style="display:none;">
                                    <th><?php echo t('Site'); ?></th>
                                    <th><?php echo t('Customer'); ?></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($orderStatuses as $orderStatus) {
                            ?>
                                    <tr>
                                        <td class="sorthandle"><input type="hidden" name="osID[]" value="<?php echo $orderStatus->getID(); ?>"><i class="fa fa-arrows-v"></i></td>
                                        <td><input type="text" name="osName[]" value="<?php echo t($orderStatus->getName()); ?>" placeholder="<?php echo $orderStatus->getReadableHandle(); ?>" class="form-control ccm-input-text"></td>
                                        <td><input type="radio" name="osIsStartingStatus" value="<?php echo $orderStatus->getID(); ?>" <?php echo $orderStatus->isStartingStatus() ? 'checked' : ''; ?>></td>
                                        <td style="display:none;"><input type="checkbox" name="osInformSite[]" value="1" <?php echo $orderStatus->getInformSite() ? 'checked' : ''; ?> class="form-control"></td>
                                        <td style="display:none;"><input type="checkbox" name="osInformCustomer[]" value="1" <?php echo $orderStatus->getInformCustomer() ? 'checked' : ''; ?> class="form-control"></td>
                                    </tr>
                                <?php
                        } ?>
                                </tbody>
                            </table>
                            <script>
                                $(function(){
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
                    <h3><?php echo t('Notification Emails'); ?></h3>

                    <div class="form-group">
                        <?php echo $form->label('notificationEmails', t('Send order notification to email')); ?>
                        <?php echo $form->text('notificationEmails', Config::get('community_store.notificationemails'), ['placeholder' => t('Email Address')]); ?>
                        <span class="help-block"><?php echo t('separate multiple emails with commas'); ?></span>
                    </div>

                    <h4><?php echo t('Emails Sent From'); ?></h4>

                    <div class="row">
                        <div class="col-xs-6">
                            <div class="form-group">
                                <?php echo $form->label('emailAlert', t('From Email')); ?>
                                <?php echo $form->text('emailAlert', Config::get('community_store.emailalerts'), ['placeholder' => t('From Email Address')]); ?>
                            </div>
                        </div>

                        <div class="col-xs-6">
                            <div class="form-group">
                                <?php echo $form->label('emailAlertName', t('From Name')); ?>
                                <?php echo $form->text('emailAlertName', Config::get('community_store.emailalertsname'), ['placeholder' => t('From Name')]); ?>
                            </div>
                        </div>
                    </div>

                    <h3><?php echo t('Receipt Emails'); ?></h3>

                    <div class="form-group">
                        <label><?php echo t("Receipt Email Header Content"); ?></label>
                        <?php $editor = \Core::make('editor');
                        echo $editor->outputStandardEditor('receiptHeader', Config::get('community_store.receiptHeader')); ?>
                    </div>

                    <div class="form-group">
                        <label><?php echo t("Receipt Email Footer Content"); ?></label>
                        <?php $editor = \Core::make('editor');
                        echo $editor->outputStandardEditor('receiptFooter', Config::get('community_store.receiptFooter')); ?>
                    </div>


                </div>

                <!-- #settings-products -->
                <div class="col-sm-9 store-pane" id="settings-products">
                    <h3><?php echo t("Products"); ?></h3>
                    <div class="form-group">
                        <?php echo $form->label('productPublishTarget', t('Page to Publish Product Pages Under')); ?>
                        <?php echo $pageSelector->selectPage('productPublishTarget', $productPublishTarget); ?>
                    </div>
                </div>

                <!-- #settings-product-images -->
                <div class="col-sm-9 store-pane" id="settings-product-images">
                    <h3><?php echo t("Product Images"); ?></h3>

                    <div class="row">
                        <h4 class="col-md-12"><?php echo t("Product Thumbnail Types"); ?></h4>
                        <div class="form-group col-md-12">
                            <?php echo $form->label('defaultSingleProductThumbType', t('Single Product Thumbnail Type')); ?>
                            <?php echo $form->select('defaultSingleProductThumbType', $thumbnailTypes, Config::get('community_store.defaultSingleProductThumbType')); ?>
                        </div>

                        <div class="form-group col-md-12">
                            <?php echo $form->label('defaultProductListThumbType', t('Product List Thumbnail Type')); ?>
                            <?php echo $form->select('defaultProductListThumbType', $thumbnailTypes, Config::get('community_store.defaultProductListThumbType')); ?>
                        </div>

                        <div class="form-group col-md-12">
                            <?php echo $form->label('defaultProductModalThumbType', t('Product Modal Thumbnail Type')); ?>
                            <?php echo $form->select('defaultProductModalThumbType', $thumbnailTypes, Config::get('community_store.defaultProductModalThumbType')); ?>
                        </div>
                    </div>

                    <div class="row">
                        <h4 class="col-md-12"><?php echo t("Single Product - Legacy Thumbnail Generator"); ?></h4>
                        <div class="form-group col-md-4">
                            <?php echo $form->label('defaultSingleProductImageWidth', t('Image Width')); ?>
                            <div class="input-group">
                                <?php echo $form->number('defaultSingleProductImageWidth', Config::get('community_store.defaultSingleProductImageWidth') ?: Image::DEFAULT_SINGLE_PRODUCT_IMG_WIDTH, ['min' => '0', 'step' => '1']); ?>
                                <div class="input-group-addon">px</div>
                            </div>
                            <div class="help-block">
                                <?php echo t("Default value: %s", Image::DEFAULT_SINGLE_PRODUCT_IMG_WIDTH); ?>
                            </div>
                        </div>

                        <div class="form-group col-md-4">
                            <?php echo $form->label('defaultSingleProductImageHeight', t('Image Height')); ?>
                            <div class="input-group">
                                <?php echo $form->number('defaultSingleProductImageHeight', Config::get('community_store.defaultSingleProductImageHeight') ?: Image::DEFAULT_SINGLE_PRODUCT_IMG_HEIGHT, ['min' => '0', 'step' => '1']); ?>
                                <div class="input-group-addon">px</div>
                            </div>
                            <div class="help-block">
                                <?php echo t("Default value: %s", Image::DEFAULT_SINGLE_PRODUCT_IMG_HEIGHT); ?>
                            </div>
                        </div>

                        <div class="form-group col-md-4">
                            <?php echo $form->label('defaultSingleProductCrop', t('Image cropping')); ?>
                            <?php echo $form->select('defaultSingleProductCrop', ['0' => t("Scale proportionally"), '1' => t("Scale and crop")], Config::get('community_store.defaultSingleProductCrop')); ?>
                        </div>
                    </div>

                    <div class="row">
                        <h4 class="col-md-12"><?php echo t("Product List - Legacy Thumbnail Generator"); ?></h4>
                        <div class="form-group col-md-4">
                            <?php echo $form->label('defaultProductListImageWidth', t('Image Width')); ?>
                            <div class="input-group">
                                <?php echo $form->number('defaultProductListImageWidth', Config::get('community_store.defaultProductListImageWidth') ?: Image::DEFAULT_PRODUCT_LIST_IMG_WIDTH, ['min' => '0', 'step' => '1']); ?>
                                <div class="input-group-addon">px</div>
                            </div>
                            <div class="help-block">
                                <?php echo t("Default value: %s", Image::DEFAULT_PRODUCT_LIST_IMG_WIDTH); ?>
                            </div>
                        </div>

                        <div class="form-group col-md-4">
                            <?php echo $form->label('defaultProductListImageHeight', t('Image Height')); ?>
                            <div class="input-group">
                                <?php echo $form->number('defaultProductListImageHeight', Config::get('community_store.defaultProductListImageHeight') ?: Image::DEFAULT_PRODUCT_LIST_IMG_HEIGHT, ['min' => '0', 'step' => '1']); ?>
                                <div class="input-group-addon">px</div>
                            </div>
                            <div class="help-block">
                                <?php echo t("Default value: %s", Image::DEFAULT_PRODUCT_LIST_IMG_HEIGHT); ?>
                            </div>
                        </div>

                        <div class="form-group col-md-4">
                            <?php echo $form->label('defaultProductListCrop', t('Image cropping')); ?>
                            <?php echo $form->select('defaultProductListCrop', ['0' => t("Scale proportionally"), '1' => t("Scale and crop")], Config::get('community_store.defaultProductListCrop')); ?>
                        </div>
                    </div>

                    <div class="row">
                        <h4 class="col-md-12"><?php echo t("Product Modal - Legacy Thumbnail Generator"); ?></h4>
                        <div class="form-group col-md-4">
                            <?php echo $form->label('defaultProductModalImageWidth', t('Image Width')); ?>
                            <div class="input-group">
                                <?php echo $form->number('defaultProductModalImageWidth', Config::get('community_store.defaultProductModalImageWidth') ?: Image::DEFAULT_PRODUCT_MODAL_IMG_WIDTH, ['min' => '0', 'step' => '1']); ?>
                                <div class="input-group-addon">px</div>
                            </div>
                            <div class="help-block">
                                <?php echo t("Default value: %s", Image::DEFAULT_PRODUCT_MODAL_IMG_WIDTH); ?>
                            </div>
                        </div>

                        <div class="form-group col-md-4">
                            <?php echo $form->label('defaultProductModalImageHeight', t('Image Height')); ?>
                            <div class="input-group">
                                <?php echo $form->number('defaultProductModalImageHeight', Config::get('community_store.defaultProductModalImageHeight') ?: Image::DEFAULT_PRODUCT_MODAL_IMG_HEIGHT, ['min' => '0', 'step' => '1']); ?>
                                <div class="input-group-addon">px</div>
                            </div>
                            <div class="help-block">
                                <?php echo t("Default value: %s", Image::DEFAULT_PRODUCT_MODAL_IMG_HEIGHT); ?>
                            </div>
                        </div>

                        <div class="form-group col-md-4">
                            <?php echo $form->label('defaultProductModalCrop', t('Image cropping')); ?>
                            <?php echo $form->select('defaultProductModalCrop', ['0' => t("Scale proportionally"), '1' => t("Scale and crop")], Config::get('community_store.defaultProductModalCrop')); ?>
                        </div>
                    </div>
                </div>

                <!-- #settings-customers -->
                <div class="col-sm-9 store-pane" id="settings-checkout">
                    <h3><?php echo t('Cart and Checkout'); ?></h3>
                    <div class="form-group">
                        <?php $shoppingDisabled = Config::get('community_store.shoppingDisabled');
                        ?>
                        <label><?php echo $form->radio('shoppingDisabled', ' ', ('' == $shoppingDisabled)); ?> <?php  echo t('Enabled'); ?></label><br />
                        <label><?php echo $form->radio('shoppingDisabled', 'all', 'all' == $shoppingDisabled); ?> <?php  echo t('Disabled (Catalog Mode)'); ?></label><br />
                    </div>

                    <h3><?php echo t('Guest Checkout'); ?></h3>
                    <div class="form-group">
                        <?php $guestCheckout = Config::get('community_store.guestCheckout');
                        $guestCheckout = ($guestCheckout ? $guestCheckout : 'off');
                        ?>
                        <label><?php echo $form->radio('guestCheckout', 'always', 'always' == $guestCheckout); ?> <?php  echo t('Always (unless login required for products in cart)'); ?></label><br />
                        <label><?php echo $form->radio('guestCheckout', 'option', 'option' == $guestCheckout); ?> <?php  echo t('Offer as checkout option'); ?></label><br />
                        <label><?php echo $form->radio('guestCheckout', 'off', 'off' == $guestCheckout || '' == $guestCheckout); ?> <?php  echo t('Disabled'); ?></label><br />
                    </div>

                    <h3><?php echo t('Address Auto-Complete'); ?></h3>
                    <div class="form-group">
                        <?php echo $form->label('placesAPIKey', t('Address Auto-Complete API Key (Google Places)')); ?>
                        <?php echo $form->text('placesAPIKey', Config::get('community_store.placesAPIKey')); ?>
                    </div>

                    <h3><?php echo t('Company Name'); ?></h3>
                    <div class="form-group">
                        <?php $companyField = Config::get('community_store.companyField');
                        $companyField = ($companyField ? $companyField : 'off');
                        ?>
                        <label><?php echo $form->radio('companyField', 'off', 'off' == $companyField || '' == $companyField); ?> <?php  echo t('Hidden'); ?></label><br />
                        <label><?php echo $form->radio('companyField', 'optional', 'optional' == $companyField); ?> <?php  echo t('Optional'); ?></label><br />
                        <label><?php echo $form->radio('companyField', 'required', 'required' == $companyField); ?> <?php  echo t('Required'); ?></label><br />
                    </div>

                    <h3><?php echo t('Billing Details'); ?></h3>

                    <div class="row">
                        <div class="col-xs-12">
                            <label><?php echo $form->checkbox('noBillingSave', '1', Config::get('community_store.noBillingSave') ? '1' : '0'); ?>
                                <?php echo t('Do not save billing details to user on order'); ?></label>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-xs-12">
                            <div class="ccm-search-field-content ccm-search-field-content-select2">
                                <?php echo t('For users in groups'); ?> <?php echo $form->selectMultiple('noBillingSaveGroups', $groupList, explode(',', Config::get('community_store.noBillingSaveGroups')), ['class' => 'existing-select2', 'style' => 'width: 100%', 'placeholder' => t('All Users/Groups')]); ?>
                            </div>
                        </div>
                    </div>


                    <h3><?php echo t('Shipping Details'); ?></h3>
                    <div class="row">
                        <div class="col-xs-12">
                            <label><?php echo $form->checkbox('noShippingSave', '1', Config::get('community_store.noShippingSave') ? '1' : '0'); ?>
                                <?php echo t('Do not save shipping details to user on order'); ?></label>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-xs-12">
                            <div class="ccm-search-field-content ccm-search-field-content-select2">
                                <?php echo t('For users in groups'); ?> <?php echo $form->selectMultiple('noShippingSaveGroups', $groupList, explode(',', Config::get('community_store.noShippingSaveGroups')), ['class' => 'existing-select2', 'style' => 'width: 100%', 'placeholder' => t('All Users/Groups')]); ?>
                            </div>
                        </div>
                    </div>



                    <script>
                        $(document).ready(function() {
                            $('.existing-select2').select2();
                            $('.select2-container').removeClass('form-control');
                        });
                    </script>

                    <h3><?php echo t('Digital Download Expiry'); ?></h3>
                    <div class="form-group">
                        <?php echo $form->label('download_expiry_hours', t('Number of hours before digital download links expiry')); ?>
                        <div class="input-group">
                        <?php echo $form->number('download_expiry_hours', Config::get('community_store.download_expiry_hours'), ['placeholder' => '48']); ?>
                        <div class="input-group-addon"><?php echo t('hours'); ?></div>
                        </div>
                    </div>


                </div>

                <!-- #settings-orders -->
                <div class="col-sm-9 store-pane" id="settings-orders">
                    <h3><?php echo t('Orders'); ?></h3>
                    <div class="form-group">
                        <label><?php echo $form->checkbox('showUnpaidExternalPaymentOrders', '1', Config::get('community_store.showUnpaidExternalPaymentOrders') ? '1' : '0'); ?>
                            <?php echo t('Unhide orders with incomplete payments (i.e. cancelled Paypal transactions)'); ?></label></div>

                </div>

            </div><!-- .row -->

    	    <div class="ccm-dashboard-form-actions-wrapper">
    	        <div class="ccm-dashboard-form-actions">
    	            <button class="pull-right btn btn-success" type="submit" ><?php echo t('Save Settings'); ?></button>
    	        </div>
    	    </div>

	    </form>
