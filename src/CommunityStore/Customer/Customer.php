<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Customer;

use Session;
use User;
use UserInfo;
use Concrete\Attribute\Address\Value as AddressAttributeValue;

class Customer
{
    protected $ui;

    public function __construct($uID = null)
    {
        $u = new User();

        if ($u->isLoggedIn()) {
            $this->ui = UserInfo::getByID($u->getUserID());
        } elseif ($uID) {
            $this->ui = UserInfo::getByID($uID);
        } else {
            $this->ui = null;
        }
    }

    public function getUserInfo()
    {
        return $this->ui;
    }

    public function setValue($handle, $value)
    {
        if ($this->isGuest()) {
            Session::set('community_' . $handle, $value);
        } else {
            $this->ui->setAttribute($handle, $value);
        }
    }

    public function getAddress($handle) {

        if ($this->isGuest()) {
            $addressraw = Session::get('community_' .$handle);

            if (is_array($addressraw)) {
                $address = new AddressAttributeValue();

                // use concrete5's built in address class for formatting
                $address->address1 = $addressraw['address1'];
                $address->address2 = $addressraw['address2'];
                $address->city = $addressraw['city'];
                $address->state_province = $addressraw['state_province'];
                $address->postal_code = $addressraw['postal_code'];
                $address->city = $addressraw['city'];
                $address->country = $addressraw['country'];
            }

            return $address . '';
        } else {
            return $this->ui->getAttribute($handle);
        }

    }

    public function getValue($handle)
    {
        if ($this->isGuest()) {
            $val = Session::get('community_' .$handle);

            if (is_array($val)) {
                return (object) $val;
            }

            return $val;
        } else {
            return $this->ui->getAttribute($handle);
        }
    }

    public function getValueArray($handle)
    {
        if ($this->isGuest()) {
            $val = Session::get('community_' .$handle);

            return $val;
        } else {
            return $this->ui->getAttribute($handle);
        }
    }

    public function isGuest()
    {
        return is_null($this->ui);
    }

    public function getUserID()
    {
        if ($this->isGuest()) {
            return 0;
        } else {
            return $this->ui->getUserID();
        }
    }

    public function getEmail()
    {
        if ($this->isGuest()) {
            return Session::get('community_email');
        } else {
            return $this->ui->getUserEmail();
        }
    }

    public function setEmail($email)
    {
        Session::set('community_email', $email);
    }

    public function getLastOrderID()
    {
        return Session::get('community_lastOrderID');
    }

    public function setLastOrderID($id)
    {
        Session::set('community_lastOrderID', $id);
    }
}
