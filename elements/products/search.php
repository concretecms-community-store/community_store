<?php

defined('C5_EXECUTE') or die("Access Denied.");

use Concrete\Core\Support\Facade\Url;

/**
 * @var string $headerSearchAction
 * @var Concrete\Core\Form\Service\Form $form
 * @var string|null $keywords
 * @var bool|null $featured
 * @var Concrete\Core\User\Group\Group[]|null $groupList
 * @var int|string|null $gID
 */
?>

<div class="ccm-header-search-form ccm-ui" data-header="file-manager">
    <form method="get" class="row row-cols-auto g-0 align-items-center" action="<?php echo $headerSearchAction ?>">
        <div class="ccm-header-search-form-input input-group">
            <?php if ($groupList) {
                $currentFilter = '';
                ?>
                <div  class="dropdown me-2">

                    <?php
                    if ($gID) {
                        foreach ($groupList as $group) {
                            if ($gID == $group->getGroupID()) {
                                $currentFilter = $group->getGroupName();
                            }
                        }
                    } ?>

                    <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuGroups" data-bs-toggle="dropdown" aria-expanded="false">
                        <?= $currentFilter ? t('Product Group: %s', $currentFilter) : t('Product Group'); ?>
                    </button>

                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuGroups">
                        <li><a  class="dropdown-item <?= (!$gID ? 'active' : ''); ?>"href="<?= Url::to('/dashboard/store/products/') ?>"><?= t('All Groups') ?></a></li>
                        <?php foreach ($groupList as $group) { ?>
                            <li ><a class="dropdown-item  <?= ($gID == $group->getGroupID() ? 'active' : ''); ?>" href="<?= Url::to('/dashboard/store/products/', $group->getGroupID()) ?>"><?= $group->getGroupName() ?></a></li>
                        <?php } ?>
                    </ul>

                </div>
            <?php } ?>

            <div class="input-group">
                <input type="search" id="keywords" name="keywords" value="<?= h($keywords); ?>" style="min-width: 220px" placeholder="<?= t('Search by Name or SKU') ?>" class="form-control border-end-0" autocomplete="off">
                <button type="submit" class="input-group-icon">
                    <svg width="16" height="16">
                        <use xlink:href="#icon-search"></use>
                    </svg>
                </button>
                <div class="btn-group dropdown">
                    <button class="px-3 btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <svg width="16" height="16">
                            <use xlink:href="#icon-cog"></use>
                        </svg>
                        <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu" role="menu">
                        <li class="dropdown-item<?= $featured === null ? ' active' : '' ?>">
                            <a class="nav-link" href="#" data-cs-filter-featured=""><?= tc('Products', 'Featured and not featured') ?></a>
                        </li>
                        <li class="dropdown-item<?= $featured === true ? ' active' : '' ?>">
                            <a class="nav-link" href="#" data-cs-filter-featured="1"><?= tc('Products', 'Only featured') ?></a>
                        </li>
                        <li class="dropdown-item<?= $featured === false ? ' active' : '' ?>">
                            <a class="nav-link" href="#" data-cs-filter-featured="0"><?= tc('Products', 'Only not featured') ?></a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <input type="hidden" name="featured" value="<?= $featured === null ? '' : ($featured ? '1' : '0') ?>" />
    </form>
</div>
<script>
$(document).ready(function() {

$('a[data-cs-filter-featured]').on('click', function(e) {
    e.preventDefault();
    var $a = $(this),
        featured = $a.data('cs-filter-featured'),
        $form = $a.closest('form'),
        $featured = $form.find('input[name="featured"]');
    if (typeof featured === 'number') {
        featured = featured.toString();
    }
    if ($featured.val() === featured) {
        return;
    }
    $featured.val(featured);
    $form.submit();
});

});
</script>
