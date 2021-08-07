<?php defined('C5_EXECUTE') or die('Access Denied.');
use Concrete\Core\Support\Facade\Url;

$pkg = app()->make('Concrete\Core\Package\PackageService')->getByHandle('community_store');
$pkg->installStore($pkg);

?>


<p><?= t('Community Store is now installed.'); ?></p>

<p>
    <a class="btn btn-primary" href="<?php echo Url::to('/dashboard/store/settings'); ?>">
        <?= t('Open Community Store Settings'); ?>
    </a>
</p>

<p>
    <a class="btn btn-primary" target="_blank" href="https://concrete5-community-store.github.io/community_store/">
        <?= t('View Community Store Documentation'); ?>
    </a>
</p>

<p>
    <a class="btn btn-primary" target="_blank" href="https://github.com/concrete5-community-store">
        <?= t('View Community Store Github for additional add-ons such as payment gateways and shipping methods'); ?>
    </a>
</p>
