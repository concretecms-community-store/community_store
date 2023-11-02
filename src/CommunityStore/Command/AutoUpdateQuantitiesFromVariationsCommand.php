<?php

namespace Concrete\Package\CommunityStore\Src\CommunityStore\Command;

use Concrete\Core\Foundation\Command\Command;

class AutoUpdateQuantitiesFromVariationsCommand extends Command
{
    private $force = false;

    /**
     * @return bool
     */
    public function isForce()
    {
        return $this->force;
    }

    /**
     * @return $this
     */
    public function setForce(bool $value): self
    {
        $this->force = $value;

        return $this;
    }
}