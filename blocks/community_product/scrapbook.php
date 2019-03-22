<?php defined('C5_EXECUTE') or die("Access Denied.");

use Concrete\Core\Support\Facade\Application;

$app = Application::getFacadeApplication();
$bt = $controller->getBlockObject()->getBlockTypeObject();
$ci = $app->make('helper/concrete/urls');
$csm = $app->make('cs/helper/multilingual');
?>
<p style="padding-top: 5px;">
    <img style="vertical-align: baseline; max-width: 16px; margin-right: 5px; display: inline-block;" src="<?= $ci->getBlockTypeIconURL($bt) ?>" /> <strong><?= $csm->t($product->getName(), 'productName', $product->getID()); ?></strong>
</p>
<div class="cs-product-scrapbook-wrapper">
    <?= $product->getImageThumb(); ?>
    <ul style="font-size: 90%">
        <li>
            <?= t('sku%s', ':&nbsp;') . '<em>' . $product->getSKU() . '</em>'; ?>
        </li>
        <li>
            <?= t('Price%s', ':&nbsp;') . '<em>' . $product->getFormattedPrice() . '</em>'; ?>
        </li>
    </ul>
</div>
<style>
.cs-product-scrapbook-wrapper>img {
    float: left;
}
.cs-product-scrapbook-wrapper>img + ul {
    float: left;
}
</style>