<?php defined('C5_EXECUTE') or die("Access Denied.");

use \Concrete\Core\Support\Facade\Url;

$app = \Concrete\Core\Support\Facade\Application::getFacadeApplication();
$th = $app->make('helper/text');
?>

<?php
if ($controller->getAction() == 'add' ||
    $controller->getAction() == 'edit' ||
    $controller->getAction() == 'submit') {
    ?>


    <?php if ($mID > 0) { ?>
        <div class="ccm-dashboard-header-buttons">

            <form class="pull-right" method="post" id="delete" action="<?= Url::to('/dashboard/store/manufacturers/delete/', $mID) ?>">
                <?= $token->output('community_store'); ?>&nbsp;
                <button class="btn btn-danger"><?= t("Delete Manufacturer") ?></button>
            </form>


            <script type="text/javascript">
                $(function () {
                    $('#delete').submit(function () {
                        return confirm('<?=  t("Are you sure you want to delete this manufacturer?"); ?>');
                    });
                });
            </script>
        </div>
    <?php } ?>


    <form method="post" action="<?= $view->action('submit') ?>">
        <?php echo $token->output('submit') ?>
        <?php echo $form->hidden('mID', $manufacturer->getID()) ?>

        <div class="form-group">
            <?= $form->label("name", t("Name")); ?>
            <?= $form->text("name", $manufacturer->getName()); ?>
        </div>

        <div class="form-group">
            <?= $form->label("name", t("Page")); ?>
            <?php $ps = $app->make('helper/form/page_selector'); ?>
            <?= $ps->selectPage('pageCID', $manufacturer->getPageID()); ?>
        </div>

        <div class="form-group">
            <?= $form->label("description", t("Description")); ?>
            <?php
            $editor = $app->make('editor');
            echo $editor->outputStandardEditor('description', $manufacturer->getDescription());
            ?>
        </div>


        <div class="ccm-dashboard-form-actions-wrapper">
            <div class="ccm-dashboard-form-actions">
                <a href="<?php echo URL::to('/dashboard/store/manufacturers') ?>"
                   class="btn btn-default pull-left"><?= t('Cancel') ?></a>
                <?php if (isset($mID)) { ?>
                    <?php echo $form->submit('save', t('Update'), ['class' => 'btn btn-primary pull-right']) ?>
                <?php } else { ?>
                    <?php echo $form->submit('add', t('Add'), ['class' => 'btn btn-primary pull-right']) ?>
                <?php } ?>
            </div>
        </div>
    </form>

<?php } else { ?>

    <?php if (count($manufacturers)) { ?>

        <div class="ccm-dashboard-content-full">
            <table class="ccm-search-results-table">
                <thead>
                <tr>
                    <th><a><?= t('Name') ?></a></th>
                    <th><a><?= t('Description') ?></a></th>
                    <th><a><?= t('Link') ?></a></th>
                    <th><a><?= t('Number of Products') ?></a></th>
                    <th><a><?= t('Actions') ?></a></th>
                </tr>
                </thead>
                <tbody>

                <?php foreach ($manufacturers as $manufacturer) {
                    ?>

                    <tr>
                        <td>
                            <a href="<?= Url::to('/dashboard/store/manufacturers/edit', $manufacturer->getID()) ?>"><?php echo h($manufacturer->getName()) ?></a>
                        </td>

                        <td>
                            <?php echo  $th->wordSafeShortText($manufacturer->getDescription(), 160); ?>
                        </td>
                        <td>
                            <?php $page = $manufacturer->getManufacturerPage() ?>
                            <?php if ($page) { ?>
                                <a href="<?= URL::to($page) ?>"><?= h($page->getCollectionName()); ?></a>
                            <?php } ?>
                        </td>

                        <td>
                            <?php echo count($manufacturer->getProducts()) ?>
                        </td>

                        <td>
                            <a class="btn btn-primary btn-sm" href="<?= Url::to('/dashboard/store/manufacturers/edit', $manufacturer->getID()) ?>"><?= t("Edit") ?></a>
                        </td>

                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>


        <?php if ($paginator->getTotalPages() > 1) { ?>
            <div class="ccm-search-results-pagination">
                <?= $pagination ?>
            </div>
        <?php } ?>

    <?php } else { ?>

        <br/><p class="alert alert-info"><?= t('No Manufacturers Found'); ?></p>


    <?php } ?>
    <div class="ccm-dashboard-header-buttons">
        <a href="<?= \URL::to('/dashboard/store/manufacturers/', 'add') ?>"
           class="btn btn-primary"><?= t("Add Manufacturer") ?></a>
    </div>
<?php }
?>
