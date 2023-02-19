<?php

namespace Concrete\Package\CommunityStore\Src\CommunityStore\Customer;

use Concrete\Core\Support\Facade\Application;
use Concrete\Core\Support\Facade\Session;
use Concrete\Core\User\User;
use Concrete\Core\User\UserInfoRepository;

class Customer
{
    protected $ui;

    public function __construct($uID = null)
    {
        $app = Application::getFacadeApplication();
        $u = new User();

        if ($uID !== null) {
            $this->ui = $app->make(UserInfoRepository::class)->getByID($uID);
        } elseif ($u->isRegistered()) {
            $this->ui = $app->make(UserInfoRepository::class)->getByID($u->getUserID());
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

    public function getAddress($handle)
    {
        if ($this->isGuest()) {
            $addressraw = Session::get('community_' . $handle);

            if (is_array($addressraw)) {
                $addressraw = (object) $addressraw;
            }

            return self::formatAddress($addressraw);
        }

        return (string) $this->ui->getAttribute($handle);
    }

    public function getValue($handle)
    {
        if ($this->isGuest()) {
            $val = Session::get('community_' . $handle);

            if (is_array($val)) {
                return (object) $val;
            }

            return $val;
        }

         return $this->ui->getAttribute($handle);
    }

    public function getAddressValue($handle, $valuename)
    {
        $att = $this->getValue($handle);

        return $this->returnAttributeValue($att, $valuename);
    }

    public function getValueArray($handle)
    {
        if ($this->isGuest()) {
            return Session::get('community_' . $handle);
        }

        return $this->ui->getAttribute($handle);
    }

    public function isGuest()
    {
        return $this->ui === null;
    }

    public function getUserID()
    {
        if ($this->isGuest()) {
            return 0;
        }

        return $this->ui->getUserID();
    }

    public function getEmail()
    {
        if ($this->isGuest()) {
            return Session::get('community_email');
        }

        return $this->ui->getUserEmail();
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
        $app = Application::getFacadeApplication();

        $ret = '';
        $address1 = self::returnAttributeValue($address, 'address1');
        $address2 = self::returnAttributeValue($address, 'address2');
        $city = self::returnAttributeValue($address, 'city');
        $state_province = self::returnAttributeValue($address, 'state_province');
        $postal_code = self::returnAttributeValue($address, 'postal_code');
        $country = self::returnAttributeValue($address, 'country');

        if ($address1) {
            $ret .= $address1 . "\n";
        }
        if ($address2) {
            $ret .= $address2 . "\n";
        }
        if ($city) {
            $ret .= $city;
        }
        if ($state_province) {
            $ret .= ', ';
        }
        if ($state_province) {
            $val = $app->make('helper/lists/states_provinces')->getStateProvinceName($state_province, $country);
            if ($val == '') {
                $ret .= $state_province;
            } else {
                $ret .= $val;
            }
        }
        if ($postal_code) {
            $ret .= ' ' . $postal_code;
        }
        if ($city || $state_province || $postal_code) {
            $ret .= "\n";
        }
        if ($country) {
            $ret .= $app->make('helper/lists/countries')->getCountryName($country);
        }

        return $ret;
    }

    public static function formatAddressArray($address)
    {
        $app = Application::getFacadeApplication();

        $ret = '';
        $address1 = $address['address1'];
        $address2 = $address['address2'];
        $city = $address['city'];
        $state_province = $address['state_province'];
        $postal_code = $address['postal_code'];
        $country = $address['country'];

        if ($address1) {
            $ret .= $address1;
        }
        if ($address2) {
            $ret .= ', ' . $address2;
        }

        $ret .= "\n";

        if ($city) {
            $ret .= $city;
        }
        if ($state_province) {
            $ret .= ', ';
        }
        if ($state_province) {
            $val = $app->make('helper/lists/states_provinces')->getStateProvinceName($state_province, $country);
            if ($val == '') {
                $ret .= $state_province;
            } else {
                $ret .= $val;
            }
        }
        if ($postal_code) {
            $ret .= ' ' . $postal_code;
        }
        if ($city || $state_province || $postal_code) {
            $ret .= "\n";
        }
        if ($country) {
            $ret .= $app->make('helper/lists/countries')->getCountryName($country);
        }

        return $ret;
    }

    private static function returnAttributeValue($att, $valuename)
    {
        $valueCamel = camel_case($valuename);

        if ($att) {
            if (method_exists($att, 'get' . $valueCamel)) {
                $functionname = 'get' . $valueCamel;

                return $att->$functionname();
            }

                return $att->$valuename;
        }

        return '';
    }
}
