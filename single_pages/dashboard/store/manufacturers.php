<?php defined('C5_EXECUTE') or die("Access Denied.");

use \Concrete\Core\Support\Facade\Url;?>

<?php
if ($controller->getAction() == 'add' ||
    $controller->getAction() == 'edit' ||
    $controller->getAction() == 'submit') {
    ?>

    <form method="post" action="<?= $view->action('submit') ?>">
        <?php echo $token->output('submit') ?>
        <?php echo $form->hidden('mID', $mID) ?>


        <div class="row">
            <div class="col-xs-12">
                <div class="form-group">
                    <?= $form->label("name", t("Name")); ?>
                    <?= $form->text("name", $name); ?>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xs-12">
                <div class="form-group">
                    <?= $form->label("description", t("Description")); ?>
                    <?= $form->text("description", $description); ?>
                </div>
            </div>
        </div>

        <div class="ccm-dashboard-form-actions-wrapper">
            <div class="ccm-dashboard-form-actions">
                <a href="<?php echo URL::to('/dashboard/store/manufacturers') ?>"
                   class="btn btn-default pull-left"><?= t('Cancel') ?></a>
                <?php if (isset($mID)) { ?>
                    <?php echo $form->submit('save', t('Update'), array('class' => 'btn btn-primary pull-right')) ?>
                <?php } else { ?>
                    <?php echo $form->submit('add', t('Add'), array('class' => 'btn btn-primary pull-right')) ?>
                <?php } ?>
            </div>
        </div>
    </form>

<?php } else { ?>

    <?php if (count($entries)) { ?>
        <div data-search-element="results">
            <div class="table-responsive">
                <table class="ccm-search-results-table">
                    <thead>
                    <tr>
                        <th><a><?= t('Name')?></a></th>
                        <th><a><?= t('Description')?></a></th>
                        <th class="text-right"><a><?= t('Actions')?></a></th>

                    </tr>
                    </thead>
                    <tbody>

                    <?php foreach ($entries as $e) {
                        ?>

                        <tr id="tID_<?php echo($e->getMID()) ?>">
                            <td>
                                    <?php echo h($e->getName()) ?>
                            </td>
                            </a>
                            <td>
                                <?php echo h($e->getDescription()) ?>
                            </td>

                            <td class="text-right">
                                <a href="<?=Url::to('/dashboard/store/manufacturers/edit',$e->getMID())?>" class="btn btn-default"><?= t("Edit")?></a>
                                <a href="<?=Url::to('/dashboard/store/manufacturers/delete',$e->getMID())?>" class="btn btn-danger"><?= t("Delete")?></a>
                            </td>

                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="ccm-search-results-pagination">
            <?php print $pagination; ?>
        </div>


    <?php } else { ?>

            <br/><p class="alert alert-info"><?= t('No Manufacturers Found'); ?></p>


    <?php } ?>
    <div class="ccm-dashboard-header-buttons">
        <a href="<?= \URL::to('/dashboard/store/manufacturers/', 'add') ?>"
           class="btn btn-primary"><?= t("Add Manufacturer") ?></a>
    </div>
<?php }
?>
