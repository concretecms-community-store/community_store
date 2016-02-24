<?php
defined('C5_EXECUTE') or die(_("Access Denied."));
use \Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Price as StorePrice;

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
                                <a class="btn btn-default" href="<?= View::url('/login') ?>"><?= t("Sign In") ?></a>
                                <?php if (Config::get('concrete.user.registration.enabled')) { ?>
                                    <a class="btn btn-default"
                                       href="<?= View::url('/register') ?>"><?= t("Register") ?></a>
                                <?php } ?>
                            </div>
                            <?php if ($guestCheckout == 'option' && !$requiresLogin) { ?>
                                <div class="col-md-6">
                                    <p><?= t("Or optionally, you may choose to checkout as a guest.") ?></p>
                                    <a class="btn btn-default"
                                       href="<?= View::url('/checkout/?guest=1') ?>"><?= t("Checkout as Guest") ?></a>
                                </div>
                            <?php } ?>
                        </div>
                    </div>

                </div>
            <?php } else { ?>
                <form class="store-checkout-form-group store-active-form-group" id="store-checkout-form-group-billing" action="">
                    <div class="store-checkout-form-group-body">
                        <h2><?= t("Billing Address") ?></h2>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="store-checkout-billing-first-name"><?= t("First Name") ?></label>
                                    <?= $form->text('store-checkout-billing-first-name', $customer->getValue("billing_first_name"), array("required" => "required")); ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="store-checkout-billing-last-name"><?= t("Last Name") ?></label>
                                    <?= $form->text('store-checkout-billing-last-name', $customer->getValue("billing_last_name"), array("required" => "required")); ?>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <?php if ($customer->isGuest()) { ?>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="store-email"><?= t("Email") ?></label>
                                        <?= $form->email('store-email', $customer->getEmail(), array("required" => "required")); ?>
                                    </div>
                                </div>
                            <?php } ?>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="store-checkout-billing-phone"><?= t("Phone") ?></label>
                                    <?= $form->telephone('store-checkout-billing-phone', $customer->getValue("billing_phone"), array("required" => "required")); ?>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="store-checkout-billing-address-1"><?= t("Address 1") ?></label>
                                    <?= $form->text('store-checkout-billing-address-1', $customer->getValue("billing_address")->address1, array("required" => "required")); ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="store-checkout-billing-address-1"><?= t("Address 2") ?></label>
                                    <?= $form->text('store-checkout-billing-address-2', $customer->getValue("billing_address")->address2); ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="store-checkout-billing-country"><?= t("Country") ?></label>
                                    <?php $country = $customer->getValue("billing_address")->country; ?>
                                    <?= $form->select('store-checkout-billing-country', $billingCountries, $country ? $country : ($defaultBillingCountry ? $defaultBillingCountry : 'US'), array("onchange" => "communityStore.updateBillingStates()")); ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="store-checkout-billing-city"><?= t("City") ?></label>
                                    <?= $form->text('store-checkout-billing-city', $customer->getValue("billing_address")->city, array("required" => "required")); ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="store-checkout-billing-state"><?= t("State") ?></label>
                                    <?php $billingState = $customer->getValue("billing_address")->state_province; ?>
                                    <?= $form->select('store-checkout-billing-state', $states, $billingState ? $billingState : ""); ?>
                                    <input type="hidden" id="store-checkout-saved-billing-state" value="<?= $billingState ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="store-checkout-billing-zip"><?= t("Postal Code") ?></label>
                                    <?= $form->text('store-checkout-billing-zip', $customer->getValue("billing_address")->postal_code, array("required" => "required")); ?>
                                </div>
                            </div>
                        </div>
                        <div class="store-checkout-form-group-buttons">
                            <input type="submit" class="store-btn-next-pane btn btn-default" value="<?= t("Next") ?>">
                        </div>

                    </div>

                    <div class="store-checkout-form-group-summary panel panel-default">
                        <div class="panel-heading">
                            <?= t('Billing Address'); ?>
                        </div>
                        <div class="row panel-body">
                            <div class="col-sm-6">
                                <label><?= t('Name'); ?></label>

                                <p class="store-summary-name"></p>

                                <label><?= t('Email'); ?></label>

                                <p class="store-summary-email"></p>

                                <label><?= t('Phone'); ?></label>

                                <p class="store-summary-phone"></p>
                            </div>

                            <div class="col-sm-6">
                                <label><?= t('Address'); ?></label>

                                <p class="store-summary-address"></p>
                            </div>
                        </div>
                    </div>

                </form>
                <?php if ($shippingEnabled) { ?>
                    <form class="store-checkout-form-group" id="store-checkout-form-group-shipping">

                        <div class="store-checkout-form-group-body">
                            <h2><?= t("Shipping Address") ?></h2>
                            <p>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" id="ckbx-copy-billing">
                                        <?= t("Same as Billing Address") ?>
                                    </label>
                                </div>
                            </p>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="store-checkout-shipping-first-name"><?= t("First Name") ?></label>
                                        <?= $form->text('store-checkout-shipping-first-name', $customer->getValue("shipping_first_name"), array("required" => "required")); ?>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="store-checkout-shipping-last-name"><?= t("Last Name") ?></label>
                                        <?= $form->text('store-checkout-shipping-last-name', $customer->getValue("shipping_last_name"), array("required" => "required")); ?>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="store-checkout-shipping-address-1"><?= t("Address 1") ?></label>
                                        <?= $form->text('store-checkout-shipping-address-1', $customer->getValue("shipping_address")->address1, array("required" => "required")); ?>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="store-checkout-shipping-address-1"><?= t("Address 2") ?></label>
                                        <?= $form->text('store-checkout-shipping-address-2', $customer->getValue("shipping_address")->address2); ?>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="store-checkout-shipping-country"><?= t("Country") ?></label>
                                        <?php $country = $customer->getValue("shipping_address")->country; ?>
                                        <?= $form->select('store-checkout-shipping-country', $shippingCountries, $country ? $country : ($defaultShippingCountry ? $defaultShippingCountry : 'US'), array("onchange" => "communityStore.updateShippingStates()")); ?>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="store-checkout-shipping-city"><?= t("City") ?></label>
                                        <?= $form->text('store-checkout-shipping-city', $customer->getValue("shipping_address")->city, array("required" => "required")); ?>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="store-checkout-shipping-state"><?= t("State") ?></label>
                                        <?php $shippingState = $customer->getValue("shipping_address")->state_province; ?>
                                        <?= $form->select('store-checkout-shipping-state', $states, $shippingState ? $shippingState : ""); ?>
                                        <input type="hidden" id="store-checkout-saved-shipping-state"
                                               value="<?= $shippingState ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="store-checkout-shipping-zip"><?= t("Postal Code") ?></label>
                                        <?= $form->text('store-checkout-shipping-zip', $customer->getValue("shipping_address")->postal_code, array("required" => "required")); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="store-checkout-form-group-buttons">
                                <a href="#" class="store-btn-previous-pane btn btn-default"><?= t("Previous") ?></a>
                                <input type="submit" class="store-btn-next-pane btn btn-default" value="<?= t("Next") ?>">
                            </div>
                        </div>

                        <div class="store-checkout-form-group-summary panel panel-default">
                            <div class="panel-heading">
                                    <?= t('Shipping Address'); ?>
                            </div>
                            <div class="row panel-body">
                                <div class="col-sm-6">
                                    <label><?= t('Name'); ?></label>

                                    <p class="store-summary-name"></p>
                                </div>

                                <div class="col-sm-6">
                                    <label><?= t('Address'); ?></label>

                                    <p class="store-summary-address"></p>
                                </div>
                            </div>
                        </div>
                    </form>

                    <form class="store-checkout-form-group" id="store-checkout-form-group-shipping-method">


                        <div class="store-checkout-form-group-body">
                            <h2><?= t("Shipping") ?></h2>

                            <div id="store-checkout-shipping-method-options">

                                <?php
                                /* shipping options are loaded in via ajax,
                                 * since we dont know which shipping methods are available
                                 * until after the shipping address fields are filled out.
                                 */
                                ?>

                            </div>

                            <div class="store-checkout-form-group-buttons">
                                <a href="#" class="store-btn-previous-pane btn btn-default"><?= t("Previous") ?></a>
                                <input type="submit" class="store-btn-next-pane btn btn-default" value="<?= t("Next") ?>">
                            </div>

                        </div>

                        <div class="store-checkout-form-group-summary panel panel-default">
                            <div class="panel-heading">
                                <?= t('Shipping'); ?>
                            </div>
                            <div class="row panel-body">
                                <div class="col-md-6">
                                    <p class="summary-shipping-method"></p>
                                </div>
                            </div>
                        </div>
                    </form>
                <?php } ?>

                <form class="store-checkout-form-group" id="store-checkout-form-group-payment" method="post"
                      action="<?= View::url('/checkout/submit') ?>">

                    <div class="store-checkout-form-group-body">
                        <h2><?= t("Payment") ?></h2>
                        <?php
                        if ($enabledPaymentMethods) {
                            ?>
                            <div class="container-fluid">
                                <div id="store-checkout-payment-method-options"
                                     class="<?= count($enabledPaymentMethods) == 1 ? "hidden" : ""; ?>">
                                    <?php
                                    $i = 1;
                                    foreach ($enabledPaymentMethods as $pm):
                                        if ($i == 1) {
                                            $props = array('data-payment-method-id' => $pm->getPaymentMethodID(), 'checked' => 'checked');
                                        } else {
                                            $props = array('data-payment-method-id' => $pm->getPaymentMethodID());
                                        }
                                        ?>
                                        <div class='radio'>
                                            <label>
                                                <?= $form->radio('payment-method', $pm->getPaymentMethodHandle(), false, $props) ?>
                                                <?= $pm->getPaymentMethodDisplayName() ?>
                                            </label>
                                        </div>
                                        <?php
                                        $i++;
                                    endforeach; ?>
                                </div>
                            </div>
                            <div class="store-payment-errors alert alert-danger <?php if ($controller->getTask() == 'view') {
                                echo "hidden";
                            } ?>"><?= $paymentErrors ?></div>
                            <?php
                            foreach ($enabledPaymentMethods as $pm) {
                                echo "<div class=\"store-payment-method-container hidden\" data-payment-method-id=\"{$pm->getPaymentMethodID()}\">";
                                $pm->renderCheckoutForm();
                                echo "</div>";
                            }
                        } else {  //if payment methods
                            ?>
                            <p class="alert alert-warning"><?= t('There are currently no payment methods available to process your order.'); ?></p>
                        <?php } ?>

                        <div class="store-checkout-form-group-buttons">
                            <a href="#" class="store-btn-previous-pane btn btn-default"><?= t("Previous") ?></a>

                            <?php if ($enabledPaymentMethods) { ?>
                                <input type="submit" class="store-btn-complete-order btn btn-default"
                                       value="<?= t("Complete Order") ?>">
                            <?php } ?>
                        </div>

                    </div>

                </form>

            <?php } ?>

        </div>
        <!-- .checkout-form-shell -->

        <div class="store-checkout-cart-view col-md-4">
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
                    <strong><?= t("Items Subtotal") ?>:</strong> <?= StorePrice::format($subtotal); ?>
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
                            id="shipping-total"><?= StorePrice::format($shippingtotal); ?></span></li>
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
                <?php if ($discountsWithCodesExist && !$hasCode) { ?>
                    <li class="list-group-item"><a href="<?= View::url('/cart'); ?>"><?= t('Enter discount code'); ?></a></li>
                <?php } ?>
                <li class="store-line-item store-grand-total list-group-item"><strong><?= t("Grand Total") ?>:</strong> <span
                        class="store-total-amount" data-total-cents="<?= StorePrice::formatInNumberOfCents($total); ?>"><?= StorePrice::format($total) ?></span></li>
            </ul>

        </div>

    </div>

<?php } elseif ($controller->getTask() == "external") { ?>
    <form id="store-checkout-redirect-form" action="<?= $action ?>" method="post">
        <?php
        $pm->renderRedirectForm(); ?>
        <input type="submit" class="btn btn-primary" value="<?= t('Click Here if You\'re not Redirected') ?>">
    </form>
    <script type="text/javascript">
        $(function () {
            $("#store-checkout-redirect-form").submit();
        });
    </script>
<?php } ?>
