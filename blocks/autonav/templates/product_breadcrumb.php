<?php defined('C5_EXECUTE') or die("Access Denied.");

$c = Page::getCurrentPage();
$product = \Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product::getByCollectionID($c->getCollectionID());

if ($product) {
    $locations = $product->getLocationPages();

    if ($locations[0]) {
        $first_location = $locations[0];
        $controller->cID = $first_location->getCollectionID();

        $navItems = $controller->getNavItems(true); // Ignore exclude from nav

        if (count($navItems) > 0) {
            echo '<nav role="navigation" aria-label="breadcrumb">'; //opens the top-level menu
            echo '<ol class="breadcrumb">';

            foreach ($navItems as $ni) {
                if ($ni->isCurrent) {
                    echo '<li class="active">' . $ni->name . '</li>';
                } else {
                    echo '<li><a href="' . $ni->url . '" target="' . $ni->target . '">' . $ni->name . '</a></li>';
                }
            }

            echo '</ol>';
            echo '</nav>'; //closes the top-level menu
        } elseif (is_object($c) && $c->isEditMode()) {
            ?>
            <div class="ccm-edit-mode-disabled-item"><?php echo t('Empty Auto-Nav Block.'); ?></div>
        <?php
        }
    }
}
