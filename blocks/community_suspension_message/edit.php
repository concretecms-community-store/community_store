<?php

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @var Concrete\Core\Form\Service\Form $form
 * @var Concrete\Core\Editor\EditorInterface $editor
 * @var string $cssClass
 * @var bool $useCustomMessage
 * @var string $customMessage
 */

$editor->getPluginManager()->deselect('autogrow');

?>
<div class="form-group">
    <?= $form->label('cssClass', t('CSS Class')) ?>
    <?= $form->text('cssClass', $cssClass, ['maxlength' => '255', 'spellcheck' => 'false']) ?>
</div>

<div class="form-check">
    <label>
        <?= $form->checkbox('useCustomMessage', '1', $useCustomMessage) ?>
        <?= t('Display a custom message') ?>
    </label>
</div>

<div class="form-group useCustomMessage-yes"<?= $useCustomMessage ? '' : ' style="display:none"' ?>>
    <?= $form->label('customMessage', t('Custom Message')) ?>
    <?= $editor->outputBlockEditModeEditor('customMessage', $customMessage) ?>
</div>

<script>
(function () {
    var useCustomMessage = document.getElementById('useCustomMessage');
    var update = function () {
        document.querySelectorAll('.useCustomMessage-yes').forEach(function (element) {
            element.style.display = useCustomMessage.checked ? '' : 'none';
        });
    };
    useCustomMessage.addEventListener('change', update);
    update();
})();
</script>