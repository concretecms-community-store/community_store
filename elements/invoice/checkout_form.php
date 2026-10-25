<?php defined('C5_EXECUTE') or die("Access Denied.");
extract($vars);
?>

<p><?= t("An invoice will be sent to you upon ordering")?></p>

<script type="text/javascript">
    document.querySelectorAll('div[data-payment-method-id="<?= $pmID; ?>"] .store-btn-complete-order').forEach(function (button) {
        button.addEventListener('click', function () {
            var form = button.closest('form');
            if (form.checkValidity && !form.checkValidity()) {
                return;
            }
            button.disabled = true;
            button.value = <?= json_encode(t('Processing...')); ?>;
            form.submit();
        });
    });
</script>
