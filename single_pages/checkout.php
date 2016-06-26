<?php
defined('C5_EXECUTE') or die(_("Access Denied."));
use \Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Price as StorePrice;
use \Concrete\Package\CommunityStore\Src\Attribute\Key\StoreOrderKey as StoreOrderKey;

?>
<?php if ($controller->getTask() == "view" || $controller->getTask() == "failed") { ?>

    <h1><?= t("Checkout") ?></h1>

    <?php
    $a = new Area('Checkout Info');
    $a->display();
    ?>

    <div class="row">

        <div class="store-checkout-form-shell col-md-8">

            <?php
            if ($customer->isGuest() && ($requiresLogin || $guestCheckout == 'off' || ($guestCheckout == 'option' && $_GET['guest'] != '1'))) {
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
                        <div class="row">
                            <div class="col-md-6">
                                <p><?= t("In order to proceed, you'll need to either register, or sign in with your existing account.") ?></p>
                                <a class="btn btn-default" href="<?= \URL::to('/login') ?>"><?= t("Sign In") ?></a>
                                <?php if (Config::get('concrete.user.registration.enabled')) { ?>
                                    <a class="btn btn-default"
                                       href="<?= \URL::to('/register') ?>"><?= t("Register") ?></a>
                                <?php } ?>
                            </div>
                            <?php if ($guestCheckout == 'option' && !$requiresLogin) { ?>
                                <div class="col-md-6">
                                    <p><?= t("Or optionally, you may choose to checkout as a guest.") ?></p>
                                    <a class="btn btn-default"
                                       href="<?= \URL::to('/checkout/?guest=1') ?>"><?= t("Checkout as Guest") ?></a>
                                </div>
                            <?php } ?>
                        </div>
                    </div>

                </div>
            <?php } else { ?>
                <form class="store-checkout-form-group store-active-form-group <?= isset($paymentErrors) ? 'store-checkout-form-group-complete' : '';?>" id="store-checkout-form-group-billing" action="">
                    <div class="store-checkout-form-group-body">
                        <h2><?= t("Billing Address") ?></h2>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="store-checkout-billing-first-name"><?= t('First Name') ?></label>
                                    <?= $form->text('store-checkout-billing-first-name', $customer->getValue('billing_first_name'), array('required' => 'required', 'placeholder'=>t('First Name'))); ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="store-checkout-billing-last-name"><?= t("Last Name") ?></label>
                                    <?= $form->text('store-checkout-billing-last-name', $customer->getValue('billing_last_name'), array('required'=>'required', 'placeholder'=>t('Last Name'))); ?>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <?php if ($customer->isGuest()) { ?>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="store-email"><?= t("Email") ?></label>
                                        <?= $form->email('store-email', $customer->getEmail(), array('required'=>'required','placeholder'=>t('Email'))); ?>
                                    </div>
                                </div>
                            <?php } ?>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="store-checkout-billing-phone"><?= t("Phone Number") ?></label>
                                    <?= $form->telephone('store-checkout-billing-phone', $customer->getValue('billing_phone'), array('required'=>'required','placeholder'=>t('Phone Number'))); ?>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="store-checkout-billing-address-1"><?= t("Address Line 1") ?></label>
                                    <?= $form->text('store-checkout-billing-address-1', $customer->getAddressValue('billing_address', 'address1'), array('required'=>'required','placeholder'=>t('Address Line 1'))); ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="store-checkout-billing-address-1"><?= t("Address Line 2") ?></label>
                                    <?= $form->text('store-checkout-billing-address-2', $customer->getAddressValue('billing_address', 'address2'), array('placeholder'=>t('Address Line 2'))); ?>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="store-checkout-billing-city"><?= t("City") ?></label>
                                    <?= $form->text('store-checkout-billing-city', $customer->getAddressValue('billing_address', 'city'), array('required'=>'required','placeholder'=>t('City'))); ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="store-checkout-billing-state"><?= t("State / Province") ?></label>
                                    <?php $billingState = $customer->getAddressValue('billing_address', 'state_province'); ?>
                                    <?= $form->select('store-checkout-billing-state', $states, $billingState ? $billingState : ""); ?>
                                </div>
                                <input type="hidden" id="store-checkout-saved-billing-state" value="<?= $billingState ?>">
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="store-checkout-billing-zip"><?= t("Postal Code") ?></label>
                                    <?= $form->text('store-checkout-billing-zip', $customer->getAddressValue('billing_address', 'postal_code'), array('required'=>'required', 'placeholder'=>t('Postal Code'))); ?>
                                </div>
                            </div>
                             <div class="col-md-6">
                                <div class="form-group">
                                    <label for="store-checkout-billing-country"><?= t("Country") ?></label>
                                    <?php $country = $customer->getAddressValue('billing_address', 'country') ?>
                                    <?= $form->select('store-checkout-billing-country', $billingCountries, $country ? $country : ($defaultBillingCountry ? $defaultBillingCountry : 'US'), array("onchange" => "communityStore.updateBillingStates()")); ?>
                                </div>
                            </div>

                            <?php if ($shippingEnabled) { ?>
                            <div class="col-md-12 store-copy-billing-container">
                                <label>
                                    <input type="checkbox" id="store-copy-billing" />
                                    <?= t("Use these details for shipping") ?>
                                </label>
                            </div>
                            <?php } ?>
                        </div>

                        <?php if ($orderChoicesEnabled) { ?>
                            <div id="store-checkout-form-group-other-attributes" class="store-checkout-form-group <?= isset($paymentErrors) ? 'store-checkout-form-group-complete' : '';?>">

                                <div class="">
                                    <h2><?= t("Other Choices") ?></h2>
                                    <?php foreach ($orderChoicesAttList as $ak) { ?>
                                        <div class="row" data-akid="<?= $ak->getAttributeKeyID()?>">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label><?= $ak->getAttributeKeyDisplayName(); ?></label>
                                                     <?php $ak->getAttributeType()->render('form', $ak); ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php } ?>
                                </div>

                            </div>
                        <?php } ?>

                        <div class="store-checkout-form-group-buttons">
                            <input type="submit" class="store-btn-next-pane btn btn-default pull-right" value="<?= t("Next") ?>">
                        </div>

                    </div>

                    <div class="store-checkout-form-group-summary <?= isset($paymentErrors) ? 'store-checkout-form-group-complete' : '';?> panel panel-default ">
                        <div class="panel-heading">
                            <?= t('Billing Address'); ?>
                        </div>
                        <div class="row panel-body">
                            <div class="col-sm-6">
                                <label><?= t('Name'); ?></label>

                                <p class="store-summary-name"><?= $customer->getValue('billing_first_name') . ' ' . $customer->getValue('billing_last_name'); ?></p>

                                <label><?= t('Email'); ?></label>

                                <p class="store-summary-email"><?= $customer->getEmail(); ?></p>

                                <label><?= t('Phone'); ?></label>

                                <p class="store-summary-phone"><?= $customer->getValue('billing_phone'); ?></p>
                            </div>

                            <div class="col-sm-6">
                                <label><?= t('Address'); ?></label>
                                <p class="store-summary-address"><?= nl2br($customer->getAddress('billing_address')); ?></p>
                            </div>
                        </div>
                    </div>

                    <?php if ($orderChoicesEnabled) { ?>
                        <div class="store-checkout-form-group-summary <?= isset($paymentErrors) ? 'store-checkout-form-group-complete' : '';?> panel panel-default ">
                            <div class="panel-heading">
                                <?= t('Other Choices'); ?>
                            </div>
                            <div class="row panel-body">
                                <div class="col-md-12">
                                    <?php foreach ($orderChoicesAttList as $ak) { ?>
                                        <label><?= $ak->getAttributeKeyDisplayName()?></label>
                                        <p class="store-summary-order-choices-<?= $ak->getAttributeKeyID()?>"></p>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                    <?php } ?>

                </form>
                <?php if ($shippingEnabled) { ?>
                    <form class="store-checkout-form-group <?= isset($paymentErrors) ? 'store-checkout-form-group-complete' : '';?>" id="store-checkout-form-group-shipping">

                        <div class="store-checkout-form-group-body">
                            <h2><?= t("Shipping Address") ?></h2>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="store-checkout-shipping-first-name"><?= t("First Name") ?></label>
                                        <?= $form->text('store-checkout-shipping-first-name', $customer->getValue("shipping_first_name"), array('required'=>'required', 'placeholder'=>t('First Name'))); ?>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="store-checkout-shipping-last-name"><?= t("Last Name") ?></label>
                                        <?= $form->text('store-checkout-shipping-last-name', $customer->getValue("shipping_last_name"), array('required'=>'required', 'placeholder'=>t('Last Name'))); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="store-checkout-shipping-address-1"><?= t("Address Line 1") ?></label>
                                        <?= $form->text('store-checkout-shipping-address-1', $customer->getAddressValue('shipping_address', 'address1'), array('required'=>'required','placeholder'=>t('Address Line 1'))); ?>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="store-checkout-shipping-address-1"><?= t("Address Line 2") ?></label>
                                        <?= $form->text('store-checkout-shipping-address-2', $customer->getAddressValue('shipping_address', 'address2'), array('placeholder'=>t('Address Line 2'))); ?>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="store-checkout-shipping-city"><?= t("City") ?></label>
                                        <?= $form->text('store-checkout-shipping-city', $customer->getAddressValue('shipping_address', 'city'), array('required'=>'required', 'placeholder'=>t('City'))); ?>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="store-checkout-shipping-state"><?= t("State / Province") ?></label>
                                        <?php $shippingState = $customer->getAddressValue('shipping_address', 'state_province'); ?>
                                        <?= $form->select('store-checkout-shipping-state', $states, $shippingState ? $shippingState : ""); ?>
                                    </div>
                                    <input type="hidden" id="store-checkout-saved-shipping-state" value="<?= $shippingState ?>">
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="store-checkout-shipping-zip"><?= t("Postal Code") ?></label>
                                            <?= $form->text('store-checkout-shipping-zip', $customer->getAddressValue('shipping_address', 'postal_code'), array('required'=>'required', 'placeholder'=>t('Postal Code'))); ?>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="store-checkout-shipping-country"><?= t("Country") ?></label>
                                        <?php $country = $customer->getAddressValue('shipping_address', 'country'); ?>
                                        <?= $form->select('store-checkout-shipping-country', $shippingCountries, $country ? $country : ($defaultShippingCountry ? $defaultShippingCountry : 'US'), array("onchange" => "communityStore.updateShippingStates()")); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="store-checkout-form-group-buttons">
                                <a href="#" class="store-btn-previous-pane btn btn-default"><?= t("Previous") ?></a>
                                <input type="submit" class="store-btn-next-pane btn btn-default pull-right" value="<?= t("Next") ?>">
                            </div>
                        </div>

                        <div class="store-checkout-form-group-summary panel panel-default">
                            <div class="panel-heading">
                                    <?= t('Shipping Address'); ?>
                            </div>
                            <div class="row panel-body">
                                <div class="col-sm-6">
                                    <label><?= t('Name'); ?></label>
                                    <p class="store-summary-name"><?= $customer->getValue("billing_first_name") . ' ' . $customer->getValue("billing_last_name"); ?></p>
                                </div>

                                <div class="col-sm-6">
                                    <label><?= t('Address'); ?></label>

                                    <p class="store-summary-address"><?= nl2br($customer->getAddress('shipping_address')); ?></p>
                                </div>
                            </div>
                        </div>
                    </form>

                    <form class="store-checkout-form-group <?= isset($paymentErrors) ? 'store-checkout-form-group-complete' : '';?>" id="store-checkout-form-group-shipping-method">

                        <div class="store-checkout-form-group-body">
                            <h2><?= t("Shipping") ?></h2>

                            <div id="store-checkout-shipping-method-options" data-error-message="<?= h(t('Please select a shipping method'));?>">
                            </div>

                            <?php if (Config::get('community_store.deliveryInstructions')) { ?>
                            <div class="store-checkout-form-delivery-instructions form-group">
                                <label><?= t('Delivery Instructions'); ?></label>
                                <?= $form->textarea('store-checkout-shipping-instructions', h($shippingInstructions)); ?>
                            </div>
                            <?php } ?>

                            <div class="store-checkout-form-group-buttons">
                                <a href="#" class="store-btn-previous-pane btn btn-default"><?= t("Previous") ?></a>
                                <input type="submit" class="store-btn-next-pane btn btn-default pull-right" value="<?= t("Next") ?>">
                            </div>

                        </div>

                        <div class="store-checkout-form-group-summary panel panel-default">
                            <div class="panel-heading">
                                <?= t('Shipping'); ?>
                            </div>
                            <div class="row panel-body">
                                <div class="col-md-6">
                                    <div class="summary-shipping-method">
                                        <?= $activeShippingLabel; ?> - <?= $shippingTotal > 0 ? StorePrice::format($shippingTotal) : t('No Charge');?>
                                    </div>
                                    <p class="summary-shipping-instructions">
                                        <?= h($shippingInstructions); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </form>
                <?php } ?>

                <form class="store-checkout-form-group " id="store-checkout-form-group-payment" method="post"
                      action="<?= \URL::to('/checkout/submit') ?>">

                    <div class="store-checkout-form-group-body">
                        <h2><?= t("Payment") ?></h2>
                        <?php
                        if ($enabledPaymentMethods) {
                            ?>
                            <div id="store-checkout-payment-method-options"
                                 class="<?= count($enabledPaymentMethods) == 1 ? "hidden" : ""; ?>">
                                <?php
                                $i = 1;
                                foreach ($enabledPaymentMethods as $pm):
                                    if (!isset($lastPaymentMethodHandle) && $i == 1 || $lastPaymentMethodHandle == $pm->getHandle()) {
                                        $props = array('data-payment-method-id' => $pm->getID(), 'checked' => 'checked');
                                    } else {
                                        $props = array('data-payment-method-id' => $pm->getID());
                                    }
                                    ?>
                                    <div class='radio'>
                                        <label>
                                            <?= $form->radio('payment-method', $pm->getHandle(), false, $props) ?>
                                            <?= $pm->getDisplayName() ?>
                                        </label>
                                    </div>
                                    <?php
                                    $i++;
                                endforeach; ?>
                            </div>

                            <?php
                            foreach ($enabledPaymentMethods as $pm) {
                                echo '<div class="store-payment-method-container hidden" data-payment-method-id="' . $pm->getID() . '">';
                                 if ($pm->getHandle() == $lastPaymentMethodHandle) { ?>
                                <div class="store-payment-errors alert alert-danger <?php if ($controller->getTask() == 'view') {
                                echo "hidden";
                            } ?>"><?= $paymentErrors ?></div>
                                <?php }


                                $pm->renderCheckoutForm();
                                ?>
                                <div class="store-checkout-form-group-buttons">
                                 <a href="#" class="store-btn-previous-pane btn btn-default"><?= t("Previous") ?></a>
                                <input type="submit" class="store-btn-complete-order btn btn-default pull-right" value="<?= $pm->getButtonLabel()? $pm->getButtonLabel() : t("Complete Order") ?>">

                                </div></div>

                            <?php    }
                        } else {  //if payment methods
                            ?>
                            <p class="alert alert-warning"><?= t('There are currently no payment methods available to process your order.'); ?></p>
                        <?php } ?>



                    </div>

                </form>

            <?php } ?>

        </div>
        <!-- .checkout-form-shell -->

        <div class="store-checkout-cart-view col-md-4">
            <div class="store-checkout-cart-contents">
                <h2><?= t("Your Cart") ?></h2>

                <?php

                if (\Illuminate\Filesystem\Filesystem::exists(DIR_BASE . '/application/elements/cart_list.php')) {
                    View::element('cart_list', array('cart' => $cart));
                } else {
                    View::element('cart_list', array('cart' => $cart), 'community_store');
                }
                ?>

                <ul class="store-checkout-totals-line-items list-group">
                    <li class="store-line-item store-sub-total list-group-item">
                        <strong><?= t("Items Subtotal") ?>:</strong> <span class="store-sub-total-amount"><?= StorePrice::format($subtotal); ?></span>
                        <?php if ($calculation == 'extract') {
                            echo '<small class="text-muted">' . t("inc. taxes") . "</small>";
                        } ?>
                    </li>

                    <?php
                    if ($taxtotal > 0) {
                        foreach ($taxes as $tax) {
                            if ($tax['taxamount'] > 0) { ?>
                                <li class="store-line-item store-tax-item list-group-item"><strong><?= ($tax['name'] ? $tax['name'] : t("Tax")) ?>
                                        :</strong> <span class="tax-amount"><?= StorePrice::format($tax['taxamount']); ?></span>
                                </li>
                            <?php }
                        }
                    }
                    ?>


                    <?php if ($shippingEnabled) { ?>
                        <li class="store-line-item store-shipping list-group-item"><strong><?= t("Shipping") ?>:</strong> <span
                                id="shipping-total" data-no-charge-label="<?=t('No Charge');?>" data-unknown-label="<?=t('to be determined');?>"><?= $shippingtotal !== false ? ($shippingtotal > 0 ? StorePrice::format($shippingtotal) : t('No Charge')) : t('to be determined'); ?></span></li>
                    <?php } ?>
                    <?php if (!empty($discounts)) { ?>
                        <li class="store-line-item store-discounts list-group-item">
                            <strong><?= (count($discounts) == 1 ? t('Discount Applied') : t('Discounts Applied')); ?>
                                :</strong>
                            <?php
                            $discountstrings = array();
                            foreach ($discounts as $discount) {
                                $discountstrings[] = h($discount->getDisplay());
                            }
                            echo implode(', ', $discountstrings);
                            ?>
                        </li>
                    <?php } ?>
                    <?php if ($discountsWithCodesExist) { ?>
                        <li class="list-group-item">
                            <?php if ($codesuccess) { ?>
                                <p><?= t('Discount has been applied');?></p>
                            <?php } ?>

                            <?php if ($codeerror) { ?>
                                <p><?= t('Invalid code');?></p>
                            <?php } ?>

                            <a href="<?= \URL::to('/cart'); ?>" id="store-enter-discount-trigger"><?= t('Enter discount code'); ?></a>

                            <form method="post" action="" class="form-inline store-checkout-code-form">
                                <input type="text" class="form-control" name="code" placeholder="<?= t('Enter code'); ?>" />
                                <input type="hidden" name="action" value="code" />
                                <button type="submit" class="btn btn-default btn-cart-discount-apply"><?= t('Apply');?></button>
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
                    <li class="store-line-item store-grand-total list-group-item"><strong><?= t("Grand Total") ?>:</strong> <span
                            class="store-total-amount" data-total-cents="<?= StorePrice::formatInNumberOfCents($total); ?>"><?= StorePrice::format($total) ?></span></li>
                </ul>
            </div>
        </div>

    </div>

<?php } elseif ($controller->getTask() == "external") { ?>
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
