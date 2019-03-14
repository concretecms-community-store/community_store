<?php defined('C5_EXECUTE') or die("Access Denied.");

$app = \Concrete\Core\Support\Facade\Application::getFacadeApplication();
$form = $app->make('helper/form');

$groupList = [];
$gl = new Concrete\Core\User\Group\GroupList();
foreach ($gl->getResults() as $group) {
    $groupList[$group->getGroupID()] = $group->getGroupName();
}

?>

<fieldset>
    <legend><?= t('Restrictions'); ?></legend>
<div class="form-group">

    <?= $form->label('groups[]', t('For customers in these groups')); ?>
    <div class="ccm-search-field-content ccm-search-field-content-select2">
        <?php print $form->selectMultiple('groups', $groupList, $key ? $key->getAttributeUserGroups() : array(), array('class' => 'selectize', 'style' => 'width: 100%', 'placeholder' => t('Available for all Groups'))); ?>
    </div>

    <script>
        $(document).ready(function() {
            $('.selectize').selectize();
            $('.selectize').removeClass('form-control');
        });
    </script>

</div>

<div class="form-group">
    <label class="control-label"><?= t('Required');?></label>
        <div class="checkbox">
            <label><?= $form->checkbox('required', '1', $key ? $key->isRequired() : '0')?> <?= t('Required in checkout');?></label>
        </div>
</div>
</fieldset>

