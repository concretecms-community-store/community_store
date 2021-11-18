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

            <input type="search" id="keywords" name="keywords" value="<?= h($keywords); ?>" style="min-width: 220px" placeholder="Search Discounts" class="form-control border-end-0" autocomplete="off">
            <button type="submit" class="input-group-icon">
                <svg width="16" height="16">
                    <use xlink:href="#icon-search"></use>
                </svg>
            </button>
        </div>
    </form>
</div>
