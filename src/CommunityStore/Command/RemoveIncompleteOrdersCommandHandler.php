<?php

namespace Concrete\Package\CommunityStore\Src\CommunityStore\Command;

use Concrete\Core\Foundation\Command\Command;
use Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\RemoveIncompleteOrders;

class RemoveIncompleteOrdersCommandHandler extends Command
{
    /**
     * @var \Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\AutoUpdaterQuantitiesFromVariations
     */
    private $service;

    public function __construct(RemoveIncompleteOrders $service)
    {
        $this->service = $service;
    }

    public function __invoke(RemoveIncompleteOrdersCommand $command)
    {
        $days = $command->getDays();
        $this->service->removeIncompleteOrders($days);
    }
}
