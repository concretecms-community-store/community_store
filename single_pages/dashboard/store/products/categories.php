<?php
defined('C5_EXECUTE') or die(_("Access Denied."));
?>


<?php if ($controller->getTask() == 'view') { ?>
    <p><?php echo t('The following pages have been used to categorise products:'); ?></p>

    <?php foreach ($pages as $key => $pageinfo) { ?>
        <p>
            <a href="<?= \URL::to('/dashboard/store/products/categories/manage/', $pageinfo['page']->getCollectionID()) ?>"><?= $key; ?></a>
            - <?= t2('%d product', '%d products', $pageinfo['productCount'], $pageinfo['productCount']); ?></p>
    <?php } ?>
<?php } ?>


<?php if ($controller->getTask() == 'manage') { ?>
    <form method="post" action="<?= $view->action('save', $cID)?>">
    <p><?php echo t('Products within category'); ?></p>

    <ul class="list-group" id="product-list">
    <?php foreach($products as $product) { ?>
        <li class="list-group-item"><i class="fa fa-arrows drag-handle pull-right"></i> <?= $product->getName(); ?>
        <input type="hidden" name="products[]" value="<?= $product->getID(); ?>" />
        </li>
    <?php } ?>
    </ul>


<script type="text/javascript">
    $(function() {
        $('#product-list').sortable({axis: 'y'});
    });
</script>

<div class="ccm-dashboard-form-actions-wrapper">
    <div class="ccm-dashboard-form-actions">
        <a href="<?= \URL::to('/dashboard/store/products/')?>" class="btn btn-default pull-left"><?= t("Cancel")?></a>
        <button class="pull-right btn btn-success"  type="submit" ><?= t('Save Category Product Order')?></button>
    </div>
</div>
    </form>

<?php } ?>