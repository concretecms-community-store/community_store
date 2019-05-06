<?php defined('C5_EXECUTE') or die("Access Denied.");

use Concrete\Core\Support\Facade\Application;

$app = Application::getFacadeApplication();
$bt = $controller->getBlockObject()->getBlockTypeObject();
$ci = $app->make('helper/concrete/urls');
$csm = $app->make('cs/helper/multilingual');
$filters = [
                    'all' => t("All"),
                    'current' => t('products under the current page'),
                    'current_children' => t('products under the current page and child pages'),
                    'page' => t('products under a specified page'),
                    'page_children' => t('products under a specified page and child pages'),
                    'showAddToCartrelated' => t('products related to the product displayed on this page'),
                    'related_product' => t('products related to a specified product'),
];
?>
<p style="padding-top: 5px;">
<?php
    $groupText = false;
    $filterText = false;
    if ($filterSource === 'auto') {
        $header = t("Filters will match the Product List block on the page");
    } elseif ($filterSource === 'manual') {
        $header = t("Filters were set manually");
        $filterOption = $filters[$filter];
        $filterText = t("List %s", $filterOption);
        $gIDs = $controller->getGroupFilters();

        if ($gIDs && count($gIDs) > 1) {
            $anyOrAll = $groupMatchAny ? t("any"): t("all");
            $groupText = t("Products must match %s of %s", $anyOrAll, t2('the %s group selected', 'the %s groups selected', count($gIDs)));
        } elseif ($gIDs && count($gIDs) === 1) {
            $groupText = t("Products must match the selected group");
        }

    }
?>
    <img style="vertical-align: baseline; max-width: 16px; margin-right: 5px; display: inline-block;" src="<?= $ci->getBlockTypeIconURL($bt) ?>" /> <strong><?= $header; ?></strong>
</p>
<?php
    if ($filterText || $groupText) {
        echo '<ul style="font-size: 90%">';
        if ($filterText) {
            echo '<li><em>' . $filterText . '</em></li>';
        }
        if ($groupText) {
            echo '<li><em>' . $groupText . '</em></li>';
        }
        echo '</ul>';
    }
?>