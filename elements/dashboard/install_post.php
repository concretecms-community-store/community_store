<?php

use Concrete\Core\Package\Package;
use Concrete\Core\Support\Facade\Application;
use Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Installer;

defined('C5_EXECUTE') or die('Access Denied.');

$application = Application::getFacadeApplication();

/** @var Installer $installer */
$installer = $application->make(Installer::class);
$installer->installOrderAttributes(Package::getByHandle('community_store'));
?>

<p><?= t('Community Store is now installed.'); ?></p>

<p>
    <a class="btn btn-primary" href="<?php echo Url::to('/dashboard/store/settings'); ?>">
        <?= t('Open Community Store Settings'); ?>
    </a>
</p>

<p>
    <a class="text-primary" target="_blank" href="https://concrete5-community-store.github.io/community_store/">
        <?= t('View Community Store Documentation'); ?> <i class="fa fa-external-link-alt"></i>
    </a>
</p>

<p>
    <a class="text-primary" target="_blank" href="https://github.com/concrete5-community-store">
        <?= t('View Community Store Github for additional add-ons such as payment gateways and shipping methods'); ?> <i class="fa fa-external-link-alt"></i>
    </a>
</p>
