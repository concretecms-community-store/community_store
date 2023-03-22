<?php defined('C5_EXECUTE') or die("Access Denied."); ?>

<?php foreach ($orderChoicesAttList as $ak) { ?>
    <div id="store-att-display-<?= $ak->getAttributeKeyHandle(); ?>">
        <label><?= h($ak->getAttributeKeyDisplayName()) ?></label>
        <p class="store-summary-order-choices-<?= $ak->getAttributeKeyID() ?>">
            <?php $attValue = $order->getAttributeValueObject($ak);
            if ($attValue) { ?>
                <?= str_replace("\r\n", "<br>", $attValue->getValue('displaySanitized', 'display')); ?>
            <?php } ?>
        </p>
    </div>
<?php } ?>
