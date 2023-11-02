<?php

namespace Concrete\Package\CommunityStore\Job;

use Concrete\Core\Job\Job;
use Concrete\Core\Support\Facade\Application;
use Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\AutoUpdaterQuantitiesFromVariations;

class AutoUpdateQuantitiesFromVariations extends Job
{
    /**
     * {@inheritdoc}
     *
     * @see Job::getJobName()
     */
    public function getJobName()
    {
        return t('Automatic Product Quantity Updater');
    }

    /**
     * {@inheritdoc}
     *
     * @see Job::getJobDescription()
     */
    public function getJobDescription()
    {
        return t('Update the product quantities from variations.');
    }

    /**
     * {@inheritdoc}
     *
     * @see Job::run()
     */
    public function run()
    {
        $app = Application::getFacadeApplication();
        $updater = $app->make(AutoUpdaterQuantitiesFromVariations::class);
        if (!$updater->isEnabled()) {
            return t('Quantities for products with variations is configured to be performed manually.');
        }
        $num = $updater->updateAll();

        return t2('%s product has been updated', '%s products have been updated', $num);
    }
}
