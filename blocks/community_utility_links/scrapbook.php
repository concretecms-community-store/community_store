<?php defined('C5_EXECUTE') or die("Access Denied.");

use Concrete\Core\Support\Facade\Application;

$app = Application::getFacadeApplication();
$bt = $controller->getBlockObject()->getBlockTypeObject();
$ci = $app->make('helper/concrete/urls');
$csm = $app->make('cs/helper/multilingual');

$options = [
    'showCartItems' => t('Show Number Of Items In Cart'),
    'showCartTotal' => t('Show Cart Total'),
    'showCheckout' => t('Show Checkout Link'),
    'popUpCart' => t('Display Cart In Popup'),
    'showGreeting' => t('Show Greeting'),
    'showSignIn' => t('Show Sign-In Link')
];
?>
<p style="padding-top: 5px;">
    <img style="vertical-align: baseline; max-width: 16px; margin-right: 5px; display: inline-block;" src="<?= $ci->getBlockTypeIconURL($bt) ?>" /> <strong><?= t($cartLabel); ?></strong>
</p>
<?php
$list = '';
foreach ($options as $option => $text) {
    $option = ${$option};
    if (isset($option) && $option) {
        $list .= '<li><em>' . $text . '</em></li>';
    }
}

if (!empty($list)) {
    echo '<ul style="font-size: 90%">';
    echo $list;
    echo '</ul>';
}
