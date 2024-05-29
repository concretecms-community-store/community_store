<?php

declare(strict_types=1);

namespace Concrete\Package\CommunityStore\Src\CommunityStore\Payment;

use DateTimeInterface;

defined('C5_EXECUTE') or die('Access Denied.');

interface LogProvider
{
    /**
     * Get the handle that uniquely identifies this log provider.
     */
    public function getHandle(): string;

    /**
     * Get the display name of this log provider.
     */
    public function getName(): string;

    /**
     * Find the log entries in the specified date/time range.
     *
     * @return \Concrete\Package\CommunityStore\Src\CommunityStore\Payment\LogEntry[]
     */
    public function findByDate(DateTimeInterface $fromInclusive, DateTimeInterface $toExclusive): array;

    /**
     * Find the log entries associated to a specific order.
     *
     * @return \Concrete\Package\CommunityStore\Src\CommunityStore\Payment\LogEntry[]
     */
    public function findByOrderID(int $orderID): array;
}
