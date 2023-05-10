<?php
$form = app()->make("helper/form");
$csm = app()->make('cs/helper/multilingual');
if ($enabledPaymentMethods) {
    ?>
    <div id="store-checkout-payment-method-options"
         class="mb-3 mt-3 <?= count($enabledPaymentMethods) == 1 ? "hidden" : ""; ?>">
        <?php
        $i = 1;
        foreach ($enabledPaymentMethods as $pm) {
            if (!isset($lastPaymentMethodHandle) && $i == 1 || (isset($lastPaymentMethodHandle) && $lastPaymentMethodHandle == $pm->getHandle())) {
                $props = ['data-payment-method-id' => $pm->getID(), 'checked' => 'checked'];
            } else {
                $props = ['data-payment-method-id' => $pm->getID()];
            }
            ?>
            <div class='radio mb-2'>
                <label>
                    <?= $form->radio('payment-method', $pm->getHandle(), false, $props) ?>
                    <?= $csm->t($pm->getDisplayName(), 'paymentDisplayName', false, $pm->getID()); ?>

                </label>
            </div>
            <?php
            $i++;
        } ?>
    </div>

    <?php
    foreach ($enabledPaymentMethods as $pm) {
        echo '<div class="store-payment-method-container hidden" data-payment-method-id="' . $pm->getID() . '">';
        if (isset($lastPaymentMethodHandle) && $pm->getHandle() == $lastPaymentMethodHandle) { ?>
            <div class="store-payment-errors alert alert-danger <?php if ($controller->getAction() == 'view') {
                echo "hidden d-none";
            } ?>"><?= $paymentErrors ?></div>
        <?php }


        $pm->renderCheckoutForm();
        ?>
        <div class="store-checkout-form-group-buttons mb-3 mt-3">
            <a href="#" class="store-btn-previous-pane btn btn-default btn-secondary"><?= t("Previous") ?></a>
            <input type="submit" class="store-btn-complete-order btn btn-success pull-right float-end text-white" value="<?= $csm->t($pm->getButtonLabel()? $pm->getButtonLabel() : t("Complete Order") , 'paymentButtonLabel', false, $pm->getID()); ?>">
        </div>
        </div>

    <?php    }
} else {  //if payment methods
    ?>
    <p class="alert alert-warning"><?= t('There are currently no payment methods available to process your order.'); ?></p>
<?php } ?>
