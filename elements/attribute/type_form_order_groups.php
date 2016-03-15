<?php
$form = Core::make('helper/form');
?>

<div class="form-group">

    <?= $form->label('oaGroups[]', t('For customers in these groups')); ?>
    <div class="ccm-search-field-content ccm-search-field-content-select2">
        <?php print $form->selectMultiple('oaGroups', $groupList, $oaGroups, array('class' => 'existing-select2', 'style' => 'width: 100%', 'placeholder' => t('Available for all Groups'))); ?>
    </div>

    <script>
        $(document).ready(function() {
            $('.existing-select2').select2();
        });
    </script>

</div>
