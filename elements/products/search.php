<?php

defined('C5_EXECUTE') or die("Access Denied.");

use Concrete\Core\Form\Service\Form;
use Concrete\Core\Support\Facade\Url;

/** @var string $headerSearchAction */
/** @var Form $form */
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

            <input type="search" id="keywords" name="keywords" value="<?= h($keywords); ?>" style="min-width: 220px" placeholder="<?= t('Search by Name or SKU') ?>" class="form-control border-end-0" autocomplete="off">
            <button type="submit" class="input-group-icon">
                <svg width="16" height="16">
                    <use xlink:href="#icon-search"></use>
                </svg>
            </button>
        </div>
    </form>
</div>
