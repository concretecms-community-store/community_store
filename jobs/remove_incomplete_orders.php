<?php

namespace Concrete\Package\CommunityStore\Job;

use Concrete\Core\Job\Job;
use Concrete\Core\Support\Facade\Application;
use Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\RemoveIncompleteOrders as RemoveIncompleteOrdersService;

class RemoveIncompleteOrders extends Job
{
    /**
     * {@inheritdoc}
     *
     * @see Job::getJobName()
     */
    public function getJobName()
    {
        return t('Remove Incomplete Orders');
    }

    /**
     * {@inheritdoc}
     *
     * @see Job::getJobDescription()
     */
    public function getJobDescription()
    {
        return t('Remove older incomplete orders (assists with GDPR compliance).');
    }

    /**
     * {@inheritdoc}
     *
     * @see Job::run()
     */
    public function run()
    {
        $app = Application::getFacadeApplication();
        $orderRemover = $app->make(RemoveIncompleteOrdersService::class);

        $num = $orderRemover->removeIncompleteOrders();

        return t2('%s incomplete order has been removed', '%s incomplete orders have been removed', $num);
    }
}
