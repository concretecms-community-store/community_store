<?php
$form = Core::make('helper/form'); 
?>

<div class="form-group">
    <?php echo $form->label('groups[]', t('For customers in these groups')); ?>
    <div class="ccm-search-field-content ccm-search-field-content-select2">
        <select multiple="multiple" name="groups[]" class="existing-select2 select2-select" style="width: 100%" placeholder="<?= t('Available for all Groups'); ?>">
            <?php
                if (!empty($groups)) {
                    if (!is_array($pgroups)) {
                        $pgroups = array();
                    }
                    foreach ($groups as $group) { ?>
                        <option value="<?php echo $group->getGroupID(); ?>" <?php echo (in_array($group->getGroupID(), $pgroups) ? 'selected="selected"' : ''); ?>><?php echo $group->getGroupName(); ?></option>
            <?php   }
                } ?>
        </select>
    </div>


    <script>
        $(document).ready(function(){
            $('.existing-select2').select2();

            Concrete.event.bind('ConcreteSitemap', function(e, instance) {
                var instance = instance;
                Concrete.event.bind('SitemapSelectPage', function(e, data) {
                    if (data.instance == instance) {
                        Concrete.event.unbind(e);

                        if ($('.page_picker :input[value="0"]').length == $('.picker_hidden :input[value="0"]').length) {
                            $('#page_pickers .picker_hidden').first().removeClass('picker_hidden');
                        }


                    }
                });
            });

        });
    </script>

    <style>
        .picker_hidden {
            display: none;
        }
    </style>
</div>
