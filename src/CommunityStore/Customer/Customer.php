<?php

namespace Concrete\Package\CommunityStore\Src\CommunityStore\Customer;

use Concrete\Core\Localization\Service\AddressFormat;
use Concrete\Core\Session\SessionValidator;
use Concrete\Core\Support\Facade\Application;
use Concrete\Core\User\User;
use Concrete\Core\User\UserInfoRepository;

class Customer
{
    /**
     * @var \Concrete\Core\User\UserInfo|null
     */
    protected $ui;

    /**
     * @var \Symfony\Component\HttpFoundation\Session\SessionInterface|null
     */
    private $session;

    /**
     * Initialize the instance.
     *
     * @param int|null $uID NULL to retrieve the current customer, the ID of a user otherwise
     */
    public function __construct($uID = null)
    {
        $app = Application::getFacadeApplication();
        if ($uID !== null) {
            $this->ui = $app->make(UserInfoRepository::class)->getByID($uID);
        } else {
            $u = $app->make(User::class);
            if ($u->isRegistered()) {
                $this->ui = $app->make(UserInfoRepository::class)->getByID($u->getUserID());
            }
        }
    }

    /**
     * Return FALSE if this instance is associated to a user, TRUE if it's a guest.
     *
     * @return bool
     */
    public function isGuest()
    {
        return $this->getUserInfo() === null;
    }

    /**
     * Get the UserInfo object associated to this customer, or NULL if it's a guest.
     *
     * @return \Concrete\Core\User\UserInfo|null
     */
    public function getUserInfo()
    {
        return $this->ui;
    }

    /**
     * Get the ID of the user associated to this customer, or 0 if it's a guest.
     *
     * @return int
     */
    public function getUserID()
    {
        $ui = $this->getUserInfo();

        return $ui === null ? 0 : (int) $ui->getUserID();
    }

    /**
     * Get the email address of the associated user, or the guest email address previusly stored in the session.
     *
     * @return string|null
     */
    public function getEmail()
    {
        $ui = $this->getUserInfo();
        if ($ui !== null) {

            // if receipt email is set on user account, use instead
            $receiptEmail = $ui->getAttribute('receipt_email');
            if ($receiptEmail) {
                return $receiptEmail;
            }

            return $ui->getUserEmail();
        }
        $session = $this->getSession(false);

        return $session === null ? null : $session->get('community_email');
    }

    /**
     * Store the customer email address in the session.
     *
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->getSession()->set('community_email', $email);
    }

    /**
     * Get the ID of the last order as stored in the session.
     *
     * @return int|null NULL if no ID was stored
     */
    public function getLastOrderID()
    {
        $session = $this->getSession(false);

        return $session === null ? null : $session->get('community_lastOrderID');
    }

    /**
     * Store the ID of the last order in the session.
     *
     * @param int $id
     */
    public function setLastOrderID($id)
    {
        $this->getSession()->set('community_lastOrderID', $id);
    }

    /**
     * @param string $handle
     * @param mixed $value
     */
    public function setValue($handle, $value)
    {
        $ui = $this->getUserInfo();
        if ($ui === null) {
            $this->getSession()->set('community_' . $handle, $value);
        } else {
            $ui->setAttribute($handle, $value);
        }
    }

    /**
     * @param string $handle
     *
     * @return \Concrete\Core\Entity\Attribute\Value\Value\AbstractValue|\stdClass|mixed|null
     */
    public function getValue($handle)
    {
        $ui = $this->getUserInfo();
        if ($ui === null) {
            $session = $this->getSession(false);
            $val = $session === null ? null : $session->get('community_' . $handle);

            return is_array($val) ? (object) $val : $val;
        }

        return $ui->getAttribute($handle);
    }

    /**
     * @param string $handle
     *
     * @return \Concrete\Core\Entity\Attribute\Value\Value\AbstractValue|mixed|null
     */
    public function getValueArray($handle)
    {
        $ui = $this->getUserInfo();
        if ($ui === null) {
            $session = $this->getSession(false);

            return $session === null ? null : $session->get('community_' . $handle);
        }

        return $ui->getAttribute($handle);
    }

    /**
     * @param string $handle
     *
     * @return string
     */
    public function getAddress($handle)
    {
        $ui = $this->getUserInfo();
        if ($ui === null) {
            $session = $this->getSession(false);
            $addressraw = $session === null ? null : $session->get('community_' . $handle);
            if (is_array($addressraw)) {
                $addressraw = (object) $addressraw;
            }

            return static::formatAddress($addressraw);
        }

        return (string) $ui->getAttribute($handle);
    }

    /**
     * @param string $handle
     * @param string $field
     *
     * @return string
     */
    public function getAddressValue($handle, $field)
    {
        $attributeValue = $this->getValue($handle);

        return static::extractStringAttributeField($attributeValue, $field);
    }

    /**
     * @param \Concrete\Core\Entity\Attribute\Value\Value\AbstractValue|\stdClass|mixed|null $address
     *
     * @return string
     */
    public static function formatAddress($address)
    {

        if (is_object($address)) {
            $array = [];
            foreach ([
                         'address1',
                         'address2',
                         'city',
                         'state_province',
                         'postal_code',
                         'country',
                     ] as $field) {
                $array[$field] = static::extractStringAttributeField($address, $field);
            }
            $address = $array;
        }

        $af = app()->make(AddressFormat::class);
        $af->setOptions(['subdivision_names'=>false]);
        return $af->format($address, 'text');
    }

    /**
     * @param array|\ArrayAccess $address
     *
     * @return string
     * @deprecated Use formatAddress only
     */
    public static function formatAddressArray($address)
    {
        return self::formatAddress($address);
    }

    /**
     * Get the current user session.
     *
     * @param bool $required
     *
     * @return \Symfony\Component\HttpFoundation\Session\SessionInterface|null
     */
    protected function getSession($required = true)
    {
        if ($this->session === null) {
            $app = Application::getFacadeApplication();
            if ($required || $app->make(SessionValidator::class)->hasActiveSession()) {
                $this->session = $app->make('session');
            }
        }

        return $this->session;
    }

    /**
     * @param \Concrete\Core\Entity\Attribute\Value\Value\AbstractValue|\stdClass|mixed|null $attributeValue
     * @param string $field
     *
     * @return string
     */
    protected static function extractStringAttributeField($attributeValue, $field)
    {
        if (!$attributeValue) {
            return '';
        }
        $functionname = 'get' . camel_case($field);
        if (method_exists($attributeValue, $functionname)) {
            return (string) $attributeValue->$functionname();
        }

        return isset($attributeValue->{$field}) ? (string) $attributeValue->{$field} : '';
    }
}
