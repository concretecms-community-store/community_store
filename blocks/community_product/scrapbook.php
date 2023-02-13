<?php defined('C5_EXECUTE') or die("Access Denied.");

use Concrete\Core\Support\Facade\Application;

$app = Application::getFacadeApplication();
$bt = $controller->getBlockObject()->getBlockTypeObject();
$ci = $app->make('helper/concrete/urls');
$csm = $app->make('cs/helper/multilingual');
?>


<p style="padding-top: 5px;">
    <img style="vertical-align: baseline; max-width: 16px; margin-right: 5px; display: inline-block;" src="<?= $ci->getBlockTypeIconURL($bt) ?>"/>
    <strong>
        <?php if ($productLocation == 'page') { ?>
            <?= t('Showing product associated with the page'); ?>
        <?php } elseif ($product) { ?>

            <?= $csm->t($product->getName(), 'productName', $product->getID()); ?>
            <?php $sku = $product->getSKU();
            if ($sku) { ?>
                (<?= $sku; ?>)
            <?php } ?>
        <?php } else { ?>
            <?= t('Product not found'); ?>
        <?php } ?>
    </strong>
</p>
<div class="cs-product-scrapbook-wrapper">
    <?php if ($product && $productLocation != 'page') { ?>

        <?= $product->getImageThumb(); ?>
        <ul style="font-size: 90%">
            <?php if (!$product->isActive()) { ?>
                <li><em><?= t('Inactive'); ?></em></li>
            <?php } ?>

            <?php if ($product->allowCustomerPrice()) { ?>
                <li><em><?= t('Allow customer to enter price'); ?></em></li>
            <?php } ?>

            <li><em>
                    <?php
                    $salePrice = $product->getSalePrice();
                    if (isset($salePrice) && "" != $salePrice) {
                        $formattedSalePrice = $product->getFormattedSalePrice();
                        $formattedOriginalPrice = $product->getFormattedOriginalPrice();
                        echo '<span style="white-space: nowrap">', t(/* i18n: %s is the on-sale price */'On Sale: %s', $formattedSalePrice), '</span>';
                        echo ' ' . tc(/* i18n: before we have the on-sale price, after the original price */'OnSale', 'was') . ' ';
                        echo '<span style="text-decoration: line-through">' . $formattedOriginalPrice . '</span>';
                    } else {
                        $formattedPrice = $product->getFormattedPrice();
                        echo '<span style="white-space: nowrap">', t('Price: ', $formattedPrice), '</span>';
                    } ?>
                </em></li>

            <?php if ($product->isFeatured()) { ?>
                <li><em><?= t('Featured Product'); ?></li>
            <?php } ?>

        </ul>
    <?php } ?>
</div>
<style>
    .cs-product-scrapbook-wrapper > img {
        float: left;
    }

    .cs-product-scrapbook-wrapper > img + ul {
        float: left;
        width: calc(100% - 60px)
    }
    
</style>