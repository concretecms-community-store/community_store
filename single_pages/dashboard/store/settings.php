<?php defined('C5_EXECUTE') or die("Access Denied."); ?>

	    <div class="ccm-dashboard-header-buttons">
            <a href="<?= \URL::to('/dashboard/store/settings/shipping')?>" class="btn btn-primary"><i class="fa fa-gift"></i> <?= t("Shipping Methods")?></a>
            <a href="<?= \URL::to('/dashboard/store/settings/tax')?>" class="btn btn-primary"><i class="fa fa-money"></i> <?= t("Tax Rates")?></a>
        </div>

	    <form method="post" action="<?= $view->action('save')?>">

            <div class="row">
                <div class="col-sm-3">

                    <ul class="nav nav-pills nav-stacked">
                            <li class="active"><a href="#settings-currency" data-pane-toggle ><?= t('Currency')?></a></li>
                            <li><a href="#settings-tax" data-pane-toggle><?= t('Tax')?></a></li>
                            <li><a href="#settings-shipping" data-pane-toggle><?= t('Shipping')?></a></li>
                            <li><a href="#settings-payments" data-pane-toggle><?= t('Payments')?></a></li>
                            <li><a href="#settings-order-statuses" data-pane-toggle><?= t('Fulfilment Statuses')?></a></li>
                            <li><a href="#settings-notifications" data-pane-toggle><?= t('Notifications and Receipts')?></a></li>
                            <li><a href="#settings-products" data-pane-toggle><?= t('Products')?></a></li>
                            <li><a href="#settings-checkout" data-pane-toggle><?= t('Cart and Checkout')?></a></li>
                        </ul>

                </div>

                <div class="col-sm-9 store-pane active" id="settings-currency">
                    <h3><?= t('Currency Settings');?></h3>
                    <div class="row">
                        <div class="form-group col-md-6">
                            <?= $form->label('symbol',t('Currency Symbol')); ?>
                            <?= $form->text('symbol',Config::get('community_store.symbol'));?>
                        </div>

                        <div class="form-group col-md-6">
                            <?= $form->label('currency',t('Currency Code')); ?>
                            <?= $form->text('currency',Config::get('community_store.currency'));?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-6">
                            <?= $form->label('thousand',t('Thousands Separator')); ?>
                            <?= $form->text('thousand',Config::get('community_store.thousand'));?>
                            <span class="help-block"><?= t('e.g. , or a space'); ?></span>
                        </div>
                        <div class="form-group col-md-6">
                            <?= $form->label('whole',t('Whole Number Separator')); ?>
                            <?= $form->text('whole',Config::get('community_store.whole')); ?>
                            <span class="help-block"><?= t('e.g. period or a comma'); ?></span>
                        </div>
                    </div>

                </div><!-- #settings-currency -->

                <div class="col-sm-9 store-pane" id="settings-tax">
                    <h3><?= t('Tax Settings');?></h3>

                    <div class="form-group">
                        <label for="calculation"><?= t("Are Prices Entered with Tax Included?")?></label>
                        <?= $form->select('calculation',array('add'=>t("No, I will enter product prices EXCLUSIVE of tax"),'extract'=>t("Yes, I will enter product prices INCLUSIVE of tax")),Config::get('community_store.calculation')); ?>
                    </div>

                    <div class="form-group">
                        <label for="vat_number"><?= t("Enable EU VAT Number Options?")?></label>
                        <?= $form->select('vat_number',array('0'=>t("No, I don't need this"),'1'=>t("Yes, enable VAT Number options")),Config::get('community_store.vat_number')); ?>
                    </div>

                </div>

                <div class="col-sm-9 store-pane" id="settings-shipping">

                    <h3><?= t("Shipping Units")?></h3>
                    <div class="row">
                        <div class="col-xs-6">
                            <div class="form-group">
                                <?= $form->label('weightUnit',t('Units for Weight'));?>
                                <?php // do not add other units to this list. these are specific to making calculated shipping work ?>
                                <?= $form->select('weightUnit',array('oz'=>t('oz'),'lb'=>t('lb'),'kg'=>t('kg'),'g'=>t('g')),Config::get('community_store.weightUnit'));?>
                            </div>
                        </div>
                        <div class="col-xs-6">
                            <div class="form-group">
                                <?= $form->label('sizeUnit',t('Units for Size'));?>
                                <?php // do not add other units to this list. these are specific to making calculated shipping work ?>
                                <?= $form->select('sizeUnit',array('in'=>t('in'),'cm'=>t('cm'),'mm'=>t('mm')),Config::get('community_store.sizeUnit'));?>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-xs-12">
                            <label><?= $form->checkbox('deliveryInstructions', '1',Config::get('community_store.deliveryInstructions') ? '1' : '0')?>
                                <?= t('Include Delivery Instructions field in checkout');?></label>
                        </div>
                    </div>


                </div><!-- #settings-shipping -->

                <div class="col-sm-9 store-pane" id="settings-payments">
                    <h3><?= t("Payment Methods")?></h3>
                    <?php
                        if($installedPaymentMethods){
                            foreach($installedPaymentMethods as $pm){?>

                            <div class="panel panel-default">

                                <div class="panel-heading"><?= t($pm->getName())?></div>
                                <div class="panel-body">
                                    <div class="form-group paymentMethodEnabled">
                                        <input type="hidden" name="paymentMethodHandle[<?= $pm->getID()?>]" value="<?= $pm->getHandle()?>">
                                        <label><?= t("Enabled")?></label>
                                        <?php
                                            echo $form->select("paymentMethodEnabled[".$pm->getID()."]", array(0=>t("No"),1=>t("Yes")),$pm->isEnabled());
                                        ?>
                                    </div>
                                    <div id="paymentMethodForm-<?= $pm->getID(); ?>" style="display:<?= $pm->isEnabled() ? 'block':'none'; ?>">
                                        <div class="row">
                                            <div class="form-group col-sm-6">
                                                <label><?= t("Display Name (on checkout)")?></label>
                                                <?= $form->text('paymentMethodDisplayName['.$pm->getID().']',$pm->getDisplayName()); ?>
                                            </div>
                                            <div class="form-group col-sm-6">
                                                <label><?= t("Button Label")?></label>
                                                <?= $form->text('paymentMethodButtonLabel['.$pm->getID().']',$pm->getButtonLabel(), array('placeholder'=>t('Optional'))); ?>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label><?= t("Sort Order")?></label>
                                            <?= $form->text('paymentMethodSortOrder['.$pm->getID().']',$pm->getSortOrder()); ?>
                                        </div>
                                        <?php
                                            $pm->renderDashboardForm();
                                        ?>
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
                    <h3><?= t("Fulfilment Statuses")?></h3>
                    <?php
                    if(count($orderStatuses)>0){ ?>
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
                                <?php foreach($orderStatuses as $orderStatus){?>
                                    <tr>
                                        <td class="sorthandle"><input type="hidden" name="osID[]" value="<?= $orderStatus->getID(); ?>"><i class="fa fa-arrows-v"></i></td>
                                        <td><input type="text" name="osName[]" value="<?= t($orderStatus->getName()); ?>" placeholder="<?= $orderStatus->getReadableHandle(); ?>" class="form-control ccm-input-text"></td>
                                        <td><input type="radio" name="osIsStartingStatus" value="<?= $orderStatus->getID(); ?>" <?= $orderStatus->isStartingStatus() ? 'checked':''; ?>></td>
                                        <td style="display:none;"><input type="checkbox" name="osInformSite[]" value="1" <?= $orderStatus->getInformSite() ? 'checked':''; ?> class="form-control"></td>
                                        <td style="display:none;"><input type="checkbox" name="osInformCustomer[]" value="1" <?= $orderStatus->getInformCustomer() ? 'checked':''; ?> class="form-control"></td>
                                    </tr>
                                <?php } ?>
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
                    <h3><?= t('Notification Emails');?></h3>

                    <div class="form-group">
                        <?= $form->label('notificationEmails',t('Send order notification to email %sseparate multiple emails with commas%s', '<small class="text-muted">','</small>')); ?>
                        <?= $form->text('notificationEmails',Config::get('community_store.notificationemails'), array('placeholder'=>t('Email Address')));?>
                    </div>

                    <h4><?= t('Emails Sent From');?></h4>

                    <div class="row">
                        <div class="col-xs-6">
                            <div class="form-group">
                                <?= $form->label('emailAlert',t('From Email'));?>
                                <?= $form->text('emailAlert',Config::get('community_store.emailalerts'),array('placeholder'=>t('From Email Address'))); ?>
                            </div>
                        </div>

                        <div class="col-xs-6">
                            <div class="form-group">
                                <?= $form->label('emailAlertName',t('From Name'));?>
                                <?= $form->text('emailAlertName',Config::get('community_store.emailalertsname'),array('placeholder'=>t('From Name'))); ?>
                            </div>
                        </div>
                    </div>

                    <h3><?= t('Receipt Emails');?></h3>

                    <div class="form-group">
                        <label><?= t("Receipt Email Header Content")?></label>
                        <?php $editor = \Core::make('editor');
                        echo $editor->outputStandardEditor('receiptHeader', Config::get('community_store.receiptHeader'));?>
                    </div>

                    <div class="form-group">
                        <label><?= t("Receipt Email Footer Content")?></label>
                        <?php $editor = \Core::make('editor');
                        echo $editor->outputStandardEditor('receiptFooter', Config::get('community_store.receiptFooter'));?>
                    </div>


                </div>

                <!-- #settings-products -->
                <div class="col-sm-9 store-pane" id="settings-products">
                    <h3><?= t("Products")?></h3>
                    <div class="form-group">
                        <?= $form->label('productPublishTarget',t('Page to Publish Product Pages Under'));?>
                        <?= $pageSelector->selectPage('productPublishTarget',$productPublishTarget)?>
                    </div>

                </div>

                <!-- #settings-customers -->
                <div class="col-sm-9 store-pane" id="settings-checkout">
                    <h3><?= t('Cart and Ordering');?></h3>
                    <div class="form-group">
                        <?php $shoppingDisabled =  Config::get('community_store.shoppingDisabled');
                        ?>
                        <label><?= $form->radio('shoppingDisabled',' ', ($shoppingDisabled == '') ); ?> <?php  echo t('Enabled'); ?></label><br />
                        <label><?= $form->radio('shoppingDisabled','all',$shoppingDisabled == 'all'); ?> <?php  echo t('Disabled (Catalog Mode)'); ?></label><br />
                    </div>



                    <h3><?= t('Guest checkout');?></h3>
                    <div class="form-group">
                        <?php $guestCheckout =  Config::get('community_store.guestCheckout');
                        $guestCheckout = ($guestCheckout ? $guestCheckout : 'off');
                        ?>
                        <label><?= $form->radio('guestCheckout','always', $guestCheckout == 'always'); ?> <?php  echo t('Always (unless login required for products in cart)'); ?></label><br />
                        <label><?= $form->radio('guestCheckout','option',$guestCheckout == 'option'); ?> <?php  echo t('Offer as checkout option'); ?></label><br />
                        <label><?= $form->radio('guestCheckout','off', $guestCheckout == 'off' || $guestCheckout == '' ); ?> <?php  echo t('Disabled'); ?></label><br />

                    </div>

                </div>

            </div><!-- .row -->

    	    <div class="ccm-dashboard-form-actions-wrapper">
    	        <div class="ccm-dashboard-form-actions">
    	            <button class="pull-right btn btn-success" type="submit" ><?= t('Save Settings')?></button>
    	        </div>
    	    </div>

	    </form>
