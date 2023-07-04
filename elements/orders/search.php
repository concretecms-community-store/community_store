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

            <?php
            $keywordsparam = '';
                if ( $keywords) {
                    $keywordsparam = '?keywords=' . urlencode($keywords);
                }

            ?>

            <?php if ($paymentMethods) {
                $statusString = '';

                $statusString = '';
                foreach ($paymentMethods as $paym) {
                    if ($paymentMethod == $paym->getID()) {
                        $statusString = $paym->getName();
                    }
                }

                ?>
                <div  class="dropdown me-2">

                    <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuPaymentMethods" data-bs-toggle="dropdown" aria-expanded="false">
                        <?= $statusString ? t($statusString) : t('Pay Method'); ?>
                    </button>

                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuPaymentMethods">
                        <li><a  class="dropdown-item <?= (!$paymentMethod ? 'active' : ''); ?>"href="<?= \URL::to('/dashboard/store/orders/'  . $status .'/all/' . $paymentStatus . $keywordsparam)?>"><?= t('All Payment Methods') ?></a></li>
                        <?php foreach ($paymentMethods as $pm) { ?>
                            <li ><a class="dropdown-item  <?= ($paymentMethod == $pm->getID()  ? 'active' : ''); ?>" href="<?= \URL::to('/dashboard/store/orders/' . $status . '/' . $pm->getID() . '/' . $paymentStatus . $keywordsparam)?>"><?= t($pm->getName());?></a></li>
                        <?php } ?>

                    </ul>

                </div>
            <?php } ?>

            <?php if ($paymentStatuses) {
                $statusString = '';
                foreach ($paymentStatuses as $handle=>$label) {
                    if ($paymentStatus ==$handle) {
                        $statusString = $label;
                    }
                }

                ?>
                <div  class="dropdown me-2">


                    <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuPaymentStatuses" data-bs-toggle="dropdown" aria-expanded="false">
                        <?= $statusString ? t( $statusString) : t('Status'); ?>
                    </button>

                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuPaymentStatuses">
                        <li><a  class="dropdown-item <?= (!$paymentStatus ? 'active' : ''); ?>" href="<?= \URL::to('/dashboard/store/orders/'  . $status .'/' . $paymentMethod . '/all' .$keywordsparam )?>"><?= t('All Payment Statuses') ?></a></li>
                        <?php foreach ($paymentStatuses as $handle=>$label) { ?>
                            <li ><a class="dropdown-item  <?= ($paymentStatus == $handle ? 'active' : ''); ?>" href="<?= \URL::to('/dashboard/store/orders/' . $status .'/' . $paymentMethod . '/' . $handle .$keywordsparam)?>"><?= $label ?></a></li>
                        <?php } ?>
                    </ul>

                </div>
            <?php } ?>

            <?php if ($fulfilmentStatuses) {

                $statusFilter = '';
                foreach ($fulfilmentStatuses as $s) {
                    if ($status == $s->getHandle()) {
                        $statusString = $s->getName();
                    }
                }
                ?>

                <div  class="dropdown me-2">

                    <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuFulfilmentStatuses" data-bs-toggle="dropdown" aria-expanded="false">
                        <?= $statusString ? t($statusString) : t('Fulfilment'); ?>
                    </button>

                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuFulfilmentStatuses">
                        <li><a  class="dropdown-item <?= (!$status ? 'active' : ''); ?>"href="<?= \URL::to('/dashboard/store/orders/all/' . $paymentMethod . '/' . $paymentStatus . $keywordsparam)?>"><?= t('All Fulfilment Statuses') ?></a></li>
                        <?php foreach ($fulfilmentStatuses as $statusoption) { ?>
                            <li ><a class="dropdown-item  <?= ($status == $statusoption->getHandle() ? 'active' : ''); ?>" href="<?= \URL::to('/dashboard/store/orders/' . $statusoption->getHandle() . '/' . $paymentMethod . '/' . $paymentStatus . $keywordsparam)?>"><?= t($statusoption->getName());?></a></li>
                        <?php } ?>
                    </ul>

                </div>
            <?php } ?>

            <input type="search" id="keywords" name="keywords" value="<?= h($keywords); ?>" style="min-width: 100px" placeholder="<?= t('Search Orders') ?>" class="form-control border-end-0" autocomplete="off">
            <button type="submit" class="input-group-icon">
                <svg width="16" height="16">
                    <use xlink:href="#icon-search"></use>
                </svg>
            </button>
        </div>
    </form>
</div>

