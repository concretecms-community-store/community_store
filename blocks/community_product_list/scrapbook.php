<?php defined('C5_EXECUTE') or die("Access Denied.");

use Concrete\Core\Support\Facade\Application;

$app = Application::getFacadeApplication();
$bt = $controller->getBlockObject()->getBlockTypeObject();
$ci = $app->make('helper/concrete/urls');
$csm = $app->make('cs/helper/multilingual');
?>
<p style="padding-top: 5px;">
    <img style="vertical-align: baseline; max-width: 16px; margin-right: 5px; display: inline-block;" src="<?= $ci->getBlockTypeIconURL($bt) ?>" /> <strong><?= t2('%s product listed', '%s products listed', count($products)); ?></strong>
</p>
<?php
if ($products && count($products)) {
    echo '<ul style="font-size: 90%">';
    $productsToDisplay = min(count($products), 3);

    foreach($products as $index => $product) {
        if ($index > $productsToDisplay - 1) {
            break;
        }
        echo '<li><em>';
        echo $csm->t($product->getName(), 'productName', $product->getID());
        echo '</em></li>';
    }

    if ($productsToDisplay < count($products)) {
        echo '<li><em>&hellip;</em></li>';
        echo '<li><em>';
        echo t2('%s more product in the list', '%s more products in the list', count($products) - $productsToDisplay);
        echo '</em></li>';
    }
    echo '</ul>';
} else {
    echo '<ul style="font-size: 90%">';
    echo '<li><em>';
    echo t('There are no products in this list');
    echo '</em></li>';
    echo '</ul>';
}
?>