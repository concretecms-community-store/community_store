<?php defined('C5_EXECUTE') or die("Access Denied.");

use Concrete\Core\Support\Facade\Application;

$app = Application::getFacadeApplication();
$bt = $controller->getBlockObject()->getBlockTypeObject();
$ci = $app->make('helper/concrete/urls');
$csm = $app->make('cs/helper/multilingual');
$filters = [
                    'all' => t("List All"),
                    'current' => t('List products under the current page'),
                    'current_children' => t('List products under the current page and child pages'),
                    'page' => t('List products under a specified page'),
                    'page_children' => t('List products under a specified page and child pages'),
                    'showAddToCartrelated' => t('List products related to the product displayed on this page'),
                    'related_product' => t('List products related to a specified product'),
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
        $filterText = $filters[$filter];
        $gIDs = $controller->getGroupFilters();

        if ($gIDs && count($gIDs) > 1) {
            if ($groupMatchAny) {
                $groupText = t2('Products must match any of the %s group selected', 'Products must match any of the %s groups selected', count($gIDs));
            } else {
                $groupText = t2('Products must match all of the %s group selected', 'Products must match all of the %s groups selected', count($gIDs));
            }
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