<?php

namespace Concrete\Package\CommunityStore\Src\CommunityStore\Utilities;

use Concrete\Core\Config\Repository\Repository;
use Concrete\Core\Editor\LinkAbstractor;
use Concrete\Core\Localization\Service\Date;
use DateTime;
use DateTimeInterface;

class SalesSuspension
{
    /**
     * @var \Concrete\Core\Config\Repository\Repository
     */
    protected $config;

    /**
     * @var \Concrete\Core\Localization\Service\Date
     */
    protected $dateService;

    public function __construct(Repository $config, Date $dateService)
    {
        $this->config = $config;
        $this->dateService = $dateService;
    }

    /**
     * Check if sales are permanently disabled ("Catalog Mode").
     *
     * @return bool
     */
    public function salesPermanentlyDisabled()
    {
        return $this->isSuspended() && $this->getSuspendedFrom() === null && $this->getSuspendedTo() === null;
    }

    /**
     * Check if the sales are currently suspended, considering both the "sales suspended" flag and the suspension dates.
     *
     * @return bool
     */
    public function salesCurrentlySuspended()
    {
        if (!$this->isSuspended()) {
            return false;
        }
        $now = new DateTime();
        $from = $this->getSuspendedFrom();
        if ($from !== null && $from >= $now) {
            return false;
        }
        $to = $this->getSuspendedTo();
        if ($to !== null && $to < $now) {
            return false;
        }

        return true;
    }

    /**
     * Enable/disable the "sales suspended" flag.
     *
     * @param bool|mixed $value
     *
     * @return $this
     */
    public function setSuspended($value)
    {
        $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
        $this->config->set('community_store.salesSuspension.suspend', $value);
        $this->config->save('community_store.salesSuspension.suspend', $value);

        return $this;
    }

    /**
     * Get the value of the "sales suspended" flag.
     *
     * @return bool
     */
    public function isSuspended()
    {
        return (bool) $this->config->get('community_store.salesSuspension.suspend');
    }

    /**
     * @return \DateTime|null
     */
    public function getSuspendedFrom()
    {
        return static::unserializeDateTime($this->config->get('community_store.salesSuspension.from'));
    }

    /**
     * Set the date/time of the beginning of the sales suspension.
     *
     * @param \DateTimeInterface $value NULL means "since the dawn of time"
     *
     * @return $this
     */
    public function setSuspendedFrom(DateTimeInterface $value = null)
    {
        $serialized = static::serializeDateTime($value);
        $this->config->set('community_store.salesSuspension.from', $serialized);
        $this->config->save('community_store.salesSuspension.from', $serialized);

        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getSuspendedTo()
    {
        return static::unserializeDateTime($this->config->get('community_store.salesSuspension.to'));
    }

    /**
     * Set the date/time of the end of the sales suspension.
     *
     * @param \DateTimeInterface $value NULL means "forever"
     *
     * @return $this
     */
    public function setSuspendedTo(DateTimeInterface $value = null)
    {
        $serialized = static::serializeDateTime($value);
        $this->config->set('community_store.salesSuspension.to', $serialized);
        $this->config->save('community_store.salesSuspension.to', $serialized);

        return $this;
    }

    public function getSuspensionMessage($editMode = false)
    {
        $message = (string) $this->config->get('community_store.salesSuspension.message', '');
        if ($editMode) {
            return LinkAbstractor::translateFromEditMode($message);
        }
        if ($message === '') {
            $to = $this->getSuspendedTo();

            return $to === null ? tc(/* i18n: sale here means the act of selling */ 'Selling', 'Sales are currently suspended.') : tc(/* i18n: sale here means the act of selling */ 'Selling', 'Sales are currently suspended until %s.', $this->dateService->formatDateTime($to, true));
        }

        return LinkAbstractor::translateFrom($message);
    }

    /**
     * @param string|mixed $value
     *
     * @return $this
     */
    public function setSuspensionMessage($value)
    {
        $value = is_string($value) ? LinkAbstractor::translateTo(trim($value)) : '';
        $this->config->set('community_store.salesSuspension.message', $value);
        $this->config->save('community_store.salesSuspension.message', $value);

        return $this;
    }

    /**
     * @return string
     */
    protected static function serializeDateTime(DateTimeInterface $value = null)
    {
        return $value === null ? '' : $value->format(DateTime::ISO8601);
    }

    /**
     * @param string|mixed $value
     *
     * @return \DateTime|null
     */
    protected static function unserializeDateTime($value)
    {
        if (!$value) {
            return null;
        }
        $dateTime = date_create_immutable($value);
        if (!$dateTime) {
            return null;
        }
        // Let's create a new date/time instance, in order to avoid changes in time zone definitions.
        $result = new DateTime();
        $result->setTimestamp($dateTime->getTimestamp());

        return $result;
    }
}
