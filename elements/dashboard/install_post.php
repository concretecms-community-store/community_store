<?php defined('C5_EXECUTE') or die('Access Denied.');
use Concrete\Core\Support\Facade\Url;
?>


<p><?= t('Community Store is now installed.'); ?></p>

<p>
    <a class="btn btn-primary text-white" href="<?php echo Url::to('/dashboard/store/settings'); ?>">
        <?= t('Open Community Store Settings'); ?>
    </a>
</p>

<p>
    <a class="text-primary" target="_blank" href="https://concretecms-community-store.github.io/community_store/">
        <?= t('View Community Store Documentation'); ?> <i class="fa fa-external-link-alt"></i>
    </a>
</p>

<p>
    <a class="text-primary" target="_blank" href="https://github.com/concretecms-community-store">
        <?= t('View Community Store Github for additional add-ons such as payment gateways and shipping methods'); ?> <i class="fa fa-external-link-alt"></i>
    </a>
</p>
