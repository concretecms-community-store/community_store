<?php defined('C5_EXECUTE') or die("Access Denied."); ?>
	    
	    <div class="ccm-dashboard-header-buttons">
            <a href="<?= View::url('/dashboard/store/settings/shipping')?>" class="btn btn-primary"><i class="fa fa-gift"></i> <?= t("Shipping Methods")?></a>
            <a href="<?= View::url('/dashboard/store/settings/tax')?>" class="btn btn-primary"><i class="fa fa-money"></i> <?= t("Tax Rates")?></a>
        </div>
	    
	    <form method="post" action="<?= $view->action('save')?>">
	        
            <div class="row">
                <div class="col-sm-3">

                    <ul class="nav nav-pills nav-stacked">
                            <li class="active"><a href="#settings-currency" data-pane-toggle ><?= t('Currency')?></a></li>
                            <li><a href="#settings-tax" data-pane-toggle><?= t('Tax')?></a></li>
                            <li><a href="#settings-shipping" data-pane-toggle><?= t('Shipping')?></a></li>
                            <li><a href="#settings-payments" data-pane-toggle><?= t('Payments')?></a></li>
                            <li><a href="#settings-order-statuses" data-pane-toggle><?= t('Order Statuses')?></a></li>
                            <li><a href="#settings-notifications" data-pane-toggle><?= t('Notifications')?></a></li>
                            <li><a href="#settings-checkout" data-pane-toggle><?= t('Checkout')?></a></li>
                        </ul>

                </div>
                
                <div class="col-sm-9 store-pane active" id="settings-currency">
                    <h3><?= t('Currency Settings');?></h3>

                    <div class="form-group">
                        <?= $form->label('symbol',t('Currency Symbol')); ?>
                        <?= $form->text('symbol',Config::get('communitystore.symbol'),array("style"=>"width:80px;"));?>
                    </div>
                    <div class="form-group">
                        <?= $form->label('thousand',t('Thousands Separator %se.g. , or a space%s', "<small>", "</small>")); ?>
                        <?= $form->text('thousand',Config::get('communitystore.thousand'),array("style"=>"width:60px;"));?>
                    </div>
                    <div class="form-group">
                        <?= $form->label('whole',t('Whole Number Separator %se.g. period or a comma%s', "<small>", "</small>")); ?>
                        <?= $form->text('whole',Config::get('communitystore.whole'),array("style"=>"width:60px;")); ?>
                    </div>
            
                </div><!-- #settings-currency -->
                
                <div class="col-sm-9 store-pane" id="settings-tax">
                    <h3><?= t('Tax Settings');?></h3>

                    <div class="form-group">
                        <label for="calculation"><?= t("Are Prices Entered with Tax Included?")?></label>
                        <?= $form->select('calculation',array('add'=>t("No, I will enter product prices EXCLUSIVE of tax"),'extract'=>t("Yes, I will enter product prices INCLUSIVE of tax")),Config::get('communitystore.calculation')); ?>
                    </div>
                    
                </div>
                                                
                <div class="col-sm-9 store-pane" id="settings-shipping">
                
                    <h3><?= t("Shipping Units")?></h3>
                    <div class="row">
                        <div class="col-xs-6">
                            <div class="form-group">
                                <?= $form->label('weightUnit',t('Units for Weight'));?>
                                <?php // do not add other units to this list. these are specific to making calculated shipping work ?>
                                <?= $form->select('weightUnit',array('lb'=>t('lb'),'kg'=>t('kg'),'g'=>t('g')),Config::get('communitystore.weightUnit'));?>
                            </div>
                        </div> 
                        <div class="col-xs-6">
                            <div class="form-group">
                                <?= $form->label('sizeUnit',t('Units for Size'));?>
                                <?php // do not add other units to this list. these are specific to making calculated shipping work ?>
                                <?= $form->select('sizeUnit',array('in'=>t('in'),'cm'=>t('cm'),'mm'=>t('mm')),Config::get('communitystore.sizeUnit'));?>
                            </div>
                        </div>                        
                    </div>
                    
            
                </div><!-- #settings-shipping -->
                    
                <div class="col-sm-9 store-pane" id="settings-payments">
                    <h3><?= t("Payment Methods")?></h3>
                    <?php
                        if($installedPaymentMethods){
                            foreach($installedPaymentMethods as $pm){?>
                            
                            <div class="panel panel-default">
                            
                                <div class="panel-heading"><?= $pm->getPaymentMethodName()?></div>
                                <div class="panel-body">
                                    <div class="form-group paymentMethodEnabled">
                                        <input type="hidden" name="paymentMethodHandle[<?= $pm->getPaymentMethodID()?>]" value="<?= $pm->getPaymentMethodHandle()?>">
                                        <label><?= t("Enabled")?></label>
                                        <?php
                                            echo $form->select("paymentMethodEnabled[".$pm->getPaymentMethodID()."]", array(0=>"No",1=>"Yes"),$pm->isEnabled());
                                        ?>
                                    </div>
                                    <div id="paymentMethodForm-<?= $pm->getPaymentMethodID(); ?>" style="display:<?= $pm->isEnabled() ? 'block':'none'; ?>">
                                        <div class="form-group">
                                            <label><?= t("Display Name (on checkout)")?></label>
                                            <?= $form->text('paymentMethodDisplayName['.$pm->getPaymentMethodID().']',$pm->getPaymentMethodDisplayName()); ?>
                                        </div>
                                        <div class="form-group">
                                            <label><?= t("Sort Order")?></label>
                                            <?= $form->text('paymentMethodSortOrder['.$pm->getPaymentMethodID().']',$pm->getPaymentMethodSortOrder()); ?>
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
                                $this = $(this);
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
                    <h3><?= t("Order Statuses")?></h3>
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
                                        <td><input type="text" name="osName[]" value="<?= $orderStatus->getName(); ?>" placeholder="<?= $orderStatus->getReadableHandle(); ?>" class="form-control ccm-input-text"></td>
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
                        echo t("No Order Statuses are available");
                    }
                    ?>


                </div><!-- #settings-order-statuses -->

                <div class="col-sm-9 store-pane" id="settings-notifications">
                    <h3><?= t('Notification Emails');?></h3>

                    <div class="form-group">
                        <?= $form->label('notificationEmails',t('Send order notification to email %sseparate multiple emails with commas%s', '<small class="text-muted">','</small>')); ?>
                        <?= $form->text('notificationEmails',Config::get('communitystore.notificationemails'), array('placeholder'=>t('Email Address')));?>
                    </div>

                    <h4><?= t('Emails Sent From');?></h4>

                    <div class="row">
                        <div class="col-xs-6">
                            <div class="form-group">
                                <?= $form->label('emailAlert',t('From Email'));?>
                                <?= $form->text('emailAlert',Config::get('communitystore.emailalerts'),array('placeholder'=>t('From Email Address'))); ?>
                            </div>
                        </div>

                        <div class="col-xs-6">
                            <div class="form-group">
                                <?= $form->label('emailAlertName',t('From Name'));?>
                                <?= $form->text('emailAlertName',Config::get('communitystore.emailalertsname'),array('placeholder'=>t('From Name'))); ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- #settings-products -->
                <div class="col-sm-9 store-pane" id="settings-products">
                    <h3><?= t("Order Statuses")?></h3>
                    <div class="form-group">
                        <?= $form->label('productPublishTarget',t('Page to Publish Product Pages Under'));?>
                        <?= $pageSelector->selectPage('productPublishTarget',$productPublishTarget)?>
                    </div>
            
                </div>

                <!-- #settings-customers -->
                <div class="col-sm-9 store-pane" id="settings-checkout">

                    <h3><?= t('Guest checkout');?></h3>
                    <div class="form-group">
                        <?php $guestCheckout =  Config::get('communitystore.guestCheckout');
                        $guestCheckout = ($guestCheckout ? $guestCheckout : 'off');
                        ?>
                        <label><?= $form->radio('guestCheckout','off', $guestCheckout == 'off' || $guestCheckout == '' ); ?> <?php  echo t('Disabled'); ?></label><br />
                        <label><?= $form->radio('guestCheckout','option',$guestCheckout == 'option'); ?> <?php  echo t('Offer as checkout option'); ?></label><br />
                        <label><?= $form->radio('guestCheckout','always', $guestCheckout == 'always'); ?> <?php  echo t('Always (unless login required for products in cart)'); ?></label><br />

                    </div>

                </div>

            </div><!-- .row -->
                
    	    <div class="ccm-dashboard-form-actions-wrapper">
    	        <div class="ccm-dashboard-form-actions">
    	            <button class="pull-right btn btn-success" type="submit" ><?= t('Save Settings')?></button>
    	        </div>
    	    </div>

	    </form>