<?php

namespace Concrete\Package\CommunityStore\Src\CommunityStore\Command;

use Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\AutoUpdaterQuantitiesFromVariations;
use Concrete\Core\Error\UserMessageException;

class AutoUpdateQuantitiesFromVariationsCommandHandler
{
    /**
     * @var \Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\AutoUpdaterQuantitiesFromVariations
     */
    private $service;

    public function __construct(AutoUpdaterQuantitiesFromVariations $service)
    {
        $this->service = $service;
    }

    public function __invoke(AutoUpdateQuantitiesFromVariationsCommand $command)
    {
        if (!$command->isForce() && !$this->service->isEnabled()) {
            throw new UserMessageException(t('Quantities for products with variations is configured to be performed manually.'));
        }
        $this->service->updateAll();
    }
}