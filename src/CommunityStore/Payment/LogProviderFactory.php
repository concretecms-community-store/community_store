<?php

declare(strict_types=1);

namespace Concrete\Package\CommunityStore\Src\CommunityStore\Payment;

use RuntimeException;

defined('C5_EXECUTE') or die('Access Denied.');

class LogProviderFactory
{
    /**
     * @var \Concrete\Package\CommunityStore\Src\CommunityStore\Payment\LogProvider[]
     */
    private array $registeredProviders = [];

    public function hasRegisteredProviders(): bool
    {
        return $this->registeredProviders !== [];
    }

    /**
     * @return $this
     */
    public function registerProvider(LogProvider $provider): self
    {
        $handle = $provider->getHandle();
        if (isset($this->registeredProviders[$handle])) {
            throw new RuntimeException(t('Duplicated log provider handle: %s', $handle));
        }
        $this->registeredProviders[$handle] = $provider;

        return $this;
    }

    /**
     * @return \Concrete\Package\CommunityStore\Src\CommunityStore\Payment\LogProvider[]
     */
    public function getRegisteredProviders(): array
    {
        return array_values($this->registeredProviders);
    }
}
