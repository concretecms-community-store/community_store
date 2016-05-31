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
            return self::formatAddress($addressraw);
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

    public function getAddressValue($handle, $valuename) {
        $att = $this->getValue($handle);

        $valuename = camel_case($valuename);

        if (method_exists($att, 'get' .$valuename)) {
            $functionname = 'get'.$valuename;
            return $att->$functionname();
        } else {
            return $att->$valuename;
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

    // 5.7 compatibility function
    public static function formatAddress($address)
    {
        $ret = '';
        if ($address->address1) {
            $ret .= $address->address1 . "\n";
        }
        if ($address->address2) {
            $ret .= $address->address2 . "\n";
        }
        if ($address->city) {
            $ret .= $address->city;
        }
        if ($address->city && $address->state_province) {
            $ret .= ", ";
        }
        if ($address->state_province) {

            $val = \Core::make('helper/lists/states_provinces')->getStateProvinceName($address->state_province, $address->country);
            if ($val == '') {
                $ret .= $address->state_province;
            } else {
                $ret .= $val;
            }
        }
        if ($address->postal_code) {
            $ret .= " " . $address->postal_code;
        }
        if ($address->city || $address->state_province || $address->postal_code) {
            $ret .= "\n";
        }
        if ($address->country) {
            $ret .= \Core::make('helper/lists/countries')->getCountryName($address->country);
        }
        return $ret;
    }
}
