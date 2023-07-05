<?php

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @var bool $editMode
 * @var bool $show
 * @var string $message
 * @var string $cssClass
 */

if ($show) {
    ?>
    <div<?= $cssClass === '' ? '' : (' class="' . h($cssClass) . '"') ?>>
        <?= $message ?>
    </div>
    <?php
    return;
}
if (!$editMode) {
    return;
}
/** @var Concrete\Core\Localization\Localization $localization */
$localization->pushActiveContext(Localization::CONTEXT_UI);
?>
<div class="ccm-edit-mode-disabled-item">
    <?= t('When the sales will be suspended, site visitors will see the following message:') ?>
    <div<?= $cssClass === '' ? '' : (' class="' . h($cssClass) . '"') ?>>
        <?= $message ?>
    </div>
</div>
<?php
$localization->popActiveContext();
