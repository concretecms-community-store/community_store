<?php
defined('C5_EXECUTE') or die("Access Denied.");

use Concrete\Core\Form\Context\ContextInterface;
use \Concrete\Core\Support\Facade\Url;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Price;

$app = \Concrete\Core\Support\Facade\Application::getFacadeApplication();
$csm = $app->make('cs/helper/multilingual');
?>
<div class="store-checkout-page">
<?php if ($controller->getAction() == "view" || $controller->getAction() == "failed") { ?>

    <h1><?= t("Checkout") ?></h1>

    <?php
    $a = new Area('Checkout Info');
    $a->display();
    ?>

    <div class="store-checkout-form-row row">

        <div class="store-checkout-form-shell col-md-8 clearfix mb-4">

            <?php if (isset($paymentErrors) && $paymentErrors) { ?>
                <p class="alert alert-danger text-center"><strong><?= h($paymentErrors); ?></strong></p>
            <?php } ?>

            <?php
            if ($customer->isGuest() && ($requiresLogin || $guestCheckout == 'off' || ($guestCheckout == 'option' && !$guest))) {
                ?>
                <div class="store-checkout-form-group store-active-form-group" id="store-checkout-form-group-signin">

                    <?php
                    if ($guestCheckout == 'option' && !$requiresLogin) {
                        $introTitle = t("Sign in, Register or Checkout as Guest");
                    } else {
                        $introTitle = t("Sign in or Register");
                    }
                    ?>

                    <h2><?= $introTitle ?></h2>

                    <div class="store-checkout-form-group-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <p><?= t("In order to proceed, you'll need to either register, or sign in with your existing account.") ?></p>
                                <a class="btn btn-default btn-secondary" href="<?= Url::to('/login') ?>"><?= t("Sign In") ?></a>
                                <?php if (Config::get('concrete.user.registration.enabled')) { ?>
                                    <a class="btn btn-default btn-secondary"
                                       href="<?= Url::to('/register') ?>"><?= t("Register") ?></a>
                                <?php } ?>
                            </div>
                            <?php if ($guestCheckout == 'option' && !$requiresLogin) { ?>
                                <div class="col-md-6">
                                    <p><?= t("Or optionally, you may choose to checkout as a guest.") ?></p>
                                    <a class="btn btn-default btn-secondary"
                                       href="<?= Url::to($langpath . '/checkout/1') ?>"><?= t("Checkout as Guest") ?></a>
                                </div>
                            <?php } ?>
                        </div>
                    </div>

                </div>
            <?php } else { ?>
                <form class="store-checkout-form-group store-active-form-group " id="store-checkout-form-group-billing" action="">
                    <?= $token->output('community_store'); ?>
                    <div class="store-checkout-form-group-body">

                        <?php if ($customer->isGuest()) { ?>
                            <h2><?= t("Customer Information") ?></h2>
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="store-email"><?= t("Email") ?></label>
                                        <?= $form->email('store-email', $customer->getEmail(), ['required'=>'required','placeholder'=>t('Email')]); ?>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>


                        <h2><?= t("Billing Details") ?></h2>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="store-checkout-billing-first-name"><?= t('First Name') ?></label>
                                    <?= $form->text('store-checkout-billing-first-name', $customer->getValue('billing_first_name'), array('required' => 'required', 'placeholder'=>t('First Name'))); ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="store-checkout-billing-last-name"><?= t("Last Name") ?></label>
                                    <?= $form->text('store-checkout-billing-last-name', $customer->getValue('billing_last_name'), array('required'=>'required', 'placeholder'=>t('Last Name'))); ?>
                                </div>
                            </div>
                        </div>
                        <?php if ($companyField == 'required' || $companyField == 'optional') {
                            if ($companyField == 'required') {
                                $companyOptions = array('required'=>'required','placeholder'=>t('Company'));
                            } else {
                                $companyOptions = array('placeholder'=>t('Company (optional)'));
                            }
                        ?>
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <div class="form-group">
                                    <label for="store-checkout-billing-company"><?= t("Company") ?></label>
                                    <?= $form->text('store-checkout-billing-company', $customer->getValue('billing_company'), $companyOptions); ?>
                                </div>
                            </div>
                        </div>
                        <?php } ?>
                        <div class="row">
                            <div class="col-md-12">
                                <label for="store-checkout-billing-address-1"><?= t("Street Address") ?></label>
                            </div>
                            <div class="col-md-7 mb-3">
                                <div class="form-group">
                                    <?= $form->text('store-checkout-billing-address-1', $customer->getAddressValue('billing_address', 'address1'), array('required'=>'required','placeholder'=>t('Street Address'))); ?>
                                </div>
                            </div>
                            <div class="col-md-5 mb-3">
                                <div class="form-group">
                                    <?= $form->text('store-checkout-billing-address-2', $customer->getAddressValue('billing_address', 'address2'), array('placeholder'=>t('Apartment, unit, etc (optional)'))); ?>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <div class="form-group">
                                    <label for="store-checkout-billing-city"><?= t("City") ?></label>
                                    <?= $form->text('store-checkout-billing-city', $customer->getAddressValue('billing_address', 'city'), array('required'=>'required','placeholder'=>t('City'))); ?>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <div class="form-group">
                                    <label for="store-checkout-billing-country"><?= t("Country") ?></label>
                                    <?php $country = $customer->getAddressValue('billing_address', 'country') ?>
                                    <?= $form->select('store-checkout-billing-country', $billingCountries, $country && array_key_exists($country, $billingCountries) ? $country : ($defaultBillingCountry ? $defaultBillingCountry : 'US'), ["onchange" => "communityStore.updateBillingStates()", 'class'=>'form-control form-select'] ); ?>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                               <div class="form-group">
                                    <label for="store-checkout-billing-state"><?= t("State / Province") ?></label>
                                    <?php $billingState = $customer->getAddressValue('billing_address', 'state_province'); ?>
                                    <?= $form->select('store-checkout-billing-state', $states, $billingState ? $billingState : "", ['class'=>'form-control form-select']); ?>
                                </div>
                                <input type="hidden" id="store-checkout-saved-billing-state" value="<?= $billingState ?>">
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="form-group">
                                    <label for="store-checkout-billing-zip"><?= t("Postal Code") ?></label>
                                    <?= $form->text('store-checkout-billing-zip', $customer->getAddressValue('billing_address', 'postal_code'), array('required'=>'required', 'placeholder'=>t('Postal Code'))); ?>
                                </div>
                            </div>

                        </div>
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <div class="form-group">
                                    <label for="store-checkout-billing-phone"><?= t("Phone Number") ?></label>
                                    <?= $form->telephone('store-checkout-billing-phone', $customer->getValue('billing_phone'), array('required'=>'required','placeholder'=>t('Phone Number'))); ?>
                                </div>
                            </div>
                        </div>

                        <?php if($orderNotesEnabled) { ?>
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <div class="form-group">
                                    <label for="store-checkout-notes"><?= t("Order notes") ?></label>
                                    <?= $form->textarea('store-checkout-notes', nl2br(h($notes)), ['placeholder'=>t('Order notes')]); ?>
                                </div>
                            </div>
                        </div>
                        <?php } ?>

                        <div class="row">
                            <?php if ($shippingEnabled) { ?>
                            <div class="store-copy-billing-container col-md-12 mb-3 text-right text-end">
                                <div class="form-group">
                                <label>
                                    <input type="checkbox" id="store-copy-billing" <?= ($useForShippingDefault) ? 'checked' : '' ?>/>
                                    <?= t("Use these details for shipping") ?>
                                </label>
                                </div>
                            </div>
                            <?php } ?>
                        </div>

                        <?php if ($orderChoicesEnabled) { ?>
                            <div id="store-checkout-form-group-other-attributes" data-no-value="<?= t('No');?>" data-yes-value="<?= t('Yes');?>" class="store-checkout-form-group ">

                                <div class="">
                                    <?php foreach ($orderChoicesAttList as $ak) { ?>
                                        <div class="row" data-akid="<?= $ak->getAttributeKeyID()?>">
                                            <div class="col-md-12  mb-3">
                                                <div class="form-group" id="store-att-<?= $ak->getAttributeKeyHandle(); ?>">
                                                    <label><?= $csm->t($ak->getAttributeKeyDisplayName(), 'orderAttributeName', null, $ak->getAttributeKeyID()); ?></label><br />
                                                     <?php
                                                     $cv = $ak->getControlView(new \Concrete\Core\Attribute\Context\FrontendFormContext());
                                                     if ($order) {
                                                         $cv->setValue($order->getAttributeValueObject($ak));
                                                     }
                                                     ob_start();
                                                     $fieldoutput1 = $cv->renderControl();
                                                     $fieldoutput2 = ob_get_contents();
                                                     ob_end_clean();
                                                     $fieldoutput = $fieldoutput1 === null ? $fieldoutput2 : $fieldoutput1;
                                                     if ($ak->isRequired()) {
                                                         $fieldoutput = str_replace('<input', '<input required ', $fieldoutput);
                                                         $fieldoutput = str_replace('<select', '<select required ', $fieldoutput);
                                                     }
                                                     echo $fieldoutput;
                                                     ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php } ?>
                                </div>

                            </div>
                        <?php } ?>

                        <div class="store-checkout-form-group-buttons mb-3 clearfix">
                            <input type="submit" class="store-btn-next-pane btn btn-primary pull-right float-end" value="<?= t("Next") ?>">
                        </div>

                    </div>

                    <div class="store-checkout-form-group-summary panel card panel-default mb-3">
                        <div class="panel-heading card-header">
                            <?= t('Billing Details'); ?>
                        </div>
                        <div class="row panel-body card-body">
                            <div class="col-sm-6">
                                <label><?= t('Email'); ?></label>
                                <p class="store-summary-email"><?= $customer->getEmail(); ?></p>

                                <label><?= t('Name'); ?></label>
                                <p class="store-summary-name"><?= $customer->getValue('billing_first_name') . ' ' . $customer->getValue('billing_last_name'); ?></p>

                                <?php if ($companyField == 'required' || $companyField == 'optional') { ?>
                                    <label><?= t('Company'); ?></label>
                                    <p class="store-summary-company"><?= $customer->getValue('billing_company'); ?></p>
                                <?php } ?>
                            </div>
                            <div class="col-sm-6">
                                <label><?= t('Address'); ?></label>
                                <p class="store-summary-address"><?= nl2br($customer->getAddress('billing_address')); ?></p>

                                <label><?= t('Phone'); ?></label>
                                <p class="store-summary-phone"><?= $customer->getValue('billing_phone'); ?></p>
                            </div>

                           <?php if($orderNotesEnabled) { ?>
                            <div class="col-sm-12" id="store-check-notes-container">
                              <label><?= t('Order notes'); ?></label>
                                <p class="store-summary-notes"></p>
                            </div>
                            <?php } ?>

                            <?php if ($orderChoicesEnabled) { ?>

                                <div class="col-sm-12" id="store-attribute-values">

                                </div>

                            <?php } ?>

                        </div>
                    </div>



                </form>
                <?php if ($shippingEnabled) { ?>
                    <form class="store-checkout-form-group " id="store-checkout-form-group-shipping">
                        <?= $token->output('community_store'); ?>
                        <div class="store-checkout-form-group-body">
                            <h2><?= t("Shipping Address") ?></h2>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label for="store-checkout-shipping-first-name"><?= t("First Name") ?></label>
                                        <?= $form->text('store-checkout-shipping-first-name', $customer->getValue("shipping_first_name"), ['required'=>'required', 'placeholder'=>t('First Name')]); ?>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-group">
                                        <label for="store-checkout-shipping-last-name"><?= t("Last Name") ?></label>
                                        <?= $form->text('store-checkout-shipping-last-name', $customer->getValue("shipping_last_name"), ['required'=>'required', 'placeholder'=>t('Last Name')]); ?>
                                    </div>
                                </div>
                            </div>
                            <?php if ($companyField == 'required' || $companyField == 'optional') { ?>
                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="store-checkout-shipping-company"><?= t("Company") ?></label>
                                            <?= $form->text('store-checkout-shipping-company', $customer->getValue('shipping_company'), $companyOptions); ?>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                            <div class="row">
                                <div class="col-md-12">
                                    <label for="store-checkout-shipping-address-1"><?= t("Address") ?></label>
                                </div>
                                <div class="col-md-7 mb-3" >
                                    <div class="form-group">
                                        <?= $form->text('store-checkout-shipping-address-1', $customer->getAddressValue('shipping_address', 'address1'), ['required'=>'required','placeholder'=>t('Street Address')]); ?>
                                    </div>
                                </div>
                                <div class="col-md-5 mb-3">
                                    <div class="form-group">
                                        <?= $form->text('store-checkout-shipping-address-2', $customer->getAddressValue('shipping_address', 'address2'), ['placeholder'=>t('Apartment, unit, etc (optional)')]); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <div class="form-group">
                                        <label for="store-checkout-shipping-city"><?= t("City") ?></label>
                                        <?= $form->text('store-checkout-shipping-city', $customer->getAddressValue('shipping_address', 'city'), ['required'=>'required', 'placeholder'=>t('City')]); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <div class="form-group">
                                        <label for="store-checkout-shipping-country"><?= t("Country") ?></label>
                                        <?php $country = $customer->getAddressValue('shipping_address', 'country'); ?>
                                        <?= $form->select('store-checkout-shipping-country', $shippingCountries, $country && array_key_exists($country, $shippingCountries)  ? $country : ($defaultShippingCountry ? $defaultShippingCountry : 'US'), ["onchange" => "communityStore.updateShippingStates()", 'class'=>'form-control form-select']); ?>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="form-group">
                                        <label for="store-checkout-shipping-state"><?= t("State / Province") ?></label>
                                        <?php $shippingState = $customer->getAddressValue('shipping_address', 'state_province'); ?>
                                        <?= $form->select('store-checkout-shipping-state', $states, $shippingState ? $shippingState : "", ['class'=>'form-control form-select']); ?>
                                    </div>
                                    <input type="hidden" id="store-checkout-saved-shipping-state" value="<?= $shippingState ?>">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="form-group">
                                        <label for="store-checkout-shipping-zip"><?= t("Postal Code") ?></label>
                                            <?= $form->text('store-checkout-shipping-zip', $customer->getAddressValue('shipping_address', 'postal_code'), ['required'=>'required', 'placeholder'=>t('Postal Code')]); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="store-checkout-form-group-buttons mb-3 mt-3">
                                <a href="#" class="store-btn-previous-pane btn btn-default btn-secondary"><?= t("Previous") ?></a>
                                <input type="submit" class="store-btn-next-pane btn btn-primary pull-right float-end" value="<?= t("Next") ?>">
                            </div>
                        </div>

                        <div class="store-checkout-form-group-summary panel card panel-default mb-3">
                            <div class="panel-heading card-header">
                                    <?= t('Shipping Address'); ?>
                            </div>
                            <div class="row panel-body card-body">
                                <div class="col-sm-6">
                                    <label><?= t('Name'); ?></label>
                                    <p class="store-summary-name"><?= $customer->getValue("billing_first_name") . ' ' . $customer->getValue("billing_last_name"); ?></p>

                                    <?php if ($companyField == 'required' || $companyField == 'optional') { ?>
                                        <label><?= t('Company'); ?></label>
                                        <p class="store-summary-company"><?= $customer->getValue('shipping_company'); ?></p>
                                    <?php } ?>
                                </div>

                                <div class="col-sm-6">
                                    <label><?= t('Address'); ?></label>

                                    <p class="store-summary-address"><?= nl2br($customer->getAddress('shipping_address')); ?></p>
                                </div>
                            </div>
                        </div>
                    </form>

                    <form class="store-checkout-form-group " id="store-checkout-form-group-shipping-method" <?= ($autoSkipSingleShipping) ? 'data-autoskip="true"' : ''  ?>>
                        <?= $token->output('community_store'); ?>
                        <div class="store-checkout-form-group-body">
                            <h2><?= t("Shipping") ?></h2>

                            <div id="store-checkout-shipping-method-options" class="mb-1 mt-3" data-error-message="<?= h(t('Please select a shipping method'));?>">
                            </div>

                            <?php if (Config::get('community_store.deliveryInstructions')) { ?>
                            <div class="store-checkout-form-delivery-instructions form-group">
                                <label><?= t('Delivery Instructions'); ?></label>
                                <?= $form->textarea('store-checkout-shipping-instructions', h($shippingInstructions)); ?>
                            </div>
                            <?php } ?>

                            <div class="store-checkout-form-group-buttons mb-3 mt-3">
                                <a href="#" class="store-btn-previous-pane btn btn-default btn-secondary"><?= t("Previous") ?></a>
                                <input type="submit" class="store-btn-next-pane btn btn-primary pull-right float-end" value="<?= t("Next") ?>">
                            </div>

                        </div>

                        <div class="store-checkout-form-group-summary panel card panel-default mb-3">
                            <div class="panel-heading card-header">
                                <?= t('Shipping'); ?>
                            </div>
                            <div class="row panel-body card-body">
                                <div class="col-md-6">
                                    <div class="summary-shipping-method">
                                        <?= $activeShippingLabel; ?> - <?= $shippingTotal > 0 ? Price::format($shippingTotal) : t('No Charge');?>
                                    </div>
                                    <p class="summary-shipping-instructions">
                                        <?= h($shippingInstructions); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </form>
                <?php } ?>

                <?php if (Config::get('community_store.vat_number')) {
                    $ak = \Concrete\Package\CommunityStore\Attribute\OrderKey::getByHandle('vat_number');
                    $vatRequired = '';
                    if ($ak) {
                        if ($ak->isRequired()) {
                            $vatRequired = 'required';
                        }
                    }
                    ?>
                    <form class="store-checkout-form-group " id="store-checkout-form-group-vat">
                        <?= $token->output('community_store'); ?>
                        <div class="store-checkout-form-group-body">
                            <h2><?= t("VAT Number") ?></h2>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="store-checkout-shipping-vat-number"><?= $vatRequired ? t("VAT Number") : t("VAT Number (if applicable)") ?></label>
                                        <?= $form->text('store-checkout-shipping-vat-number', $customer->getValue('vat_number', 'vat_number'), ['placeholder'=>t('VAT Number'), $vatRequired=>$vatRequired]); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="store-checkout-form-group-buttons mb-3 mt-3">
                                <a href="#" class="store-btn-previous-pane btn btn-default btn-secondary"><?= t("Previous") ?></a>
                                <input type="submit" class="store-btn-next-pane btn btn-primary pull-right float-end" value="<?= t("Next") ?>">
                            </div>
                        </div>

                        <div class="store-checkout-form-group-summary panel panel-default mb-3">
                        <div class="panel-heading">
                            <?=t('VAT Number'); ?>
                        </div>
                        <div class="row panel-body">
                            <div class="col-md-6">
                                <label><?=t('Applied VAT Number'); ?></label>
                                <p class="store-summary-vat-number" data-vat-blank="<?=t('Not entered'); ?>"><?= $customer->getValue('vat_number'); ?></p>
                            </div>
                        </div>
                    </div>
                    </form>
                <?php } ?>

                <form class="store-checkout-form-group " id="store-checkout-form-group-payment" method="post"
                      action="<?= Url::to($langpath.'/checkout/submit'. ($guest ? '/1' : '')) ?>">
                    <?= $token->output('community_store'); ?>
                    <div class="store-checkout-form-group-body">
                        <h2><?= t("Payment") ?></h2>

                        <div id="store-checkout-payment-method-options" class="mb-1 mt-3"></div>

                    </div>
                </form>


            <?php } ?>

        </div>
        <!-- .checkout-form-shell -->

        <div class="store-checkout-cart-view col-md-4">
            <div class="store-checkout-cart-contents">
                <h2><?= t("Your Cart") ?></h2>

                <?php

                if (file_exists(DIR_BASE . '/application/elements/cart_list.php')) {
                    View::element('cart_list', array('cart' => $cart));
                } else {
                    View::element('cart_list', array('cart' => $cart), 'community_store');
                }
                ?>

                <ul class="store-checkout-totals-line-items list-group mb-3">

                    <?php if (!empty($discounts)) { ?>
                        <li class="store-line-item store-discounts list-group-item">
                            <strong><?= (count($discounts) == 1 ? t('Discount Applied') : t('Discounts Applied')); ?>:</strong>
                            <?php
                            $discountstrings = array();
                            foreach ($discounts as $discount) {
                                $discountstrings[] = h( $csm->t($discount->getDisplay(), 'discountRuleDisplayName', null, $discount->getID()));
                            }
                            echo implode(', ', $discountstrings);
                            ?>
                        </li>
                    <?php } ?>
                    <?php if ($discountsWithCodesExist) { ?>
                        <li class="list-group-item">
                            <?php if (isset($codesuccess) && $codesuccess) { ?>
                                <p><?= t('Discount has been applied');?></p>
                            <?php } ?>

                            <?php if (isset($codeerror)  && $codeerror) { ?>
                                <p><?= t('Invalid code');?></p>
                            <?php } ?>

                            <a href="<?= Url::to($langpath . '/cart'); ?>" id="store-enter-discount-trigger"><?= t('Enter discount code'); ?></a>

                            <form method="post" action="" class="form-inline store-checkout-code-form d-flex" style="display: none !important;">
                                <?= $token->output('community_store'); ?>
                                <input type="text" class="form-control me-3" name="code" placeholder="<?= t('Enter code'); ?>" />
                                <input type="hidden" name="action" value="code" />
                                <button type="submit" class="btn btn-default btn-secondary btn-cart-discount-apply"><?= t('Apply');?></button>
                            </form>

                             <script type="text/javascript">
                                $(function () {
                                    $("#store-enter-discount-trigger").click(function(e){
                                        $('.store-checkout-code-form').show().find('.form-control').focus();
                                        $(this).remove();
                                        e.preventDefault();
                                    });
                                });
                            </script>

                        </li>

                    <?php } ?>
                   </ul>



                <ul class="store-checkout-totals-line-items list-group mb-3">
                    <li class="store-line-item store-sub-total list-group-item">
                        <strong><?= t("Items Subtotal") ?>:</strong> <span class="store-sub-total-amount"><?= Price::format($subtotal); ?></span>
                        <?php if (isset($calculation) && $calculation == 'extract') {
                            echo '<small class="text-muted">' . t("inc. taxes") . "</small>";
                        } ?>
                    </li>

                 <?php if ($shippingEnabled) { ?>

                        <li class="store-line-item store-shipping list-group-item"><strong><?= t("Shipping") ?>:</strong> <span
                                id="shipping-total" data-no-charge-label="<?=t('No Charge');?>" data-unknown-label="<?=t('to be determined');?>"><?= $shippingtotal !== false ? ($shippingtotal > 0 ? Price::format($shippingtotal) : t('No Charge')) : t('to be determined'); ?></span></li>

                 <?php } ?>
                 </ul>


                <ul class="store-checkout-totals-line-items list-group mb-3 <?= $taxtotal > 0 ? '' : 'd-none hidden' ;?>" id="store-taxes">
                        <?php foreach ($taxes as $tax) {
                            if ($tax['taxamount'] > 0) {
                                $taxlabel = $csm->t($tax['name'] , 'taxRateName', null, $tax['id']);
                                ?>
                                <li class="store-line-item store-tax-item list-group-item">
                                <strong><?= ($taxlabel ? $taxlabel : t("Tax")) ?>:</strong> <span class="tax-amount"><?= Price::format($tax['taxamount']); ?></span>
                                </li>
                            <?php }
                        } ?>
                </ul>



                <ul class="store-checkout-totals-line-items list-group">
                    <li class="store-line-item store-grand-total list-group-item"><strong><?= t("Total") ?>:</strong> <span
                            class="store-total-amount" data-total-cents="<?= Price::formatInNumberOfCents($total); ?>"><?= Price::format($total) ?></span></li>
                </ul>
            </div>
        </div>

    </div>

<?php } elseif ($controller->getAction() == "external") { ?>
    <form id="store-checkout-redirect-form" action="<?= $action ?>" method="post">
        <?php
        $pm->renderRedirectForm(); ?>
        <br />
        <p><input type="submit" class="btn btn-primary" value="<?= t('Click here if you are not automatically redirected') ?>"></p>
    </form>
    <script type="text/javascript">
        $(function () {
            $("#store-checkout-redirect-form").submit();
        });
    </script>
    <style>
        .store-utility-links {
            display: none;
        }
    </style>
<?php } ?>
</div>
