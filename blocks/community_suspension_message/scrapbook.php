<?php

use Concrete\Core\Support\Facade\Application;
use Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\SalesSuspension;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @var string $cssClass
 * @var bool|int $useCustomMessage
 * @var string $customMessage
 */

?>
<div class="<?= h($cssClass) ?>">
    <?php
    if ($useCustomMessage && $customMessage) {
        echo $customMessage;
    } else {
        $app = Application::getFacadeApplication();
        echo $app->make(SalesSuspension::class)->getSuspensionMessage();
    }
   ?>
</div>
