<?php

namespace Concrete\Package\CommunityStore\Src\CommunityStore\Command;

use Concrete\Core\Foundation\Command\Command;

class RemoveIncompleteOrdersCommand extends Command
{
    private $days = 7;

    /**
     * @return integer
     */
    public function getDays()
    {
        return (int)$this->days;
    }

    /**
     * @return $this
     */
    public function setDays(int $days): self
    {
        $this->days = $days;

        return $this;
    }
}
