<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Customer;

use Session;
use User;
use UserInfo;

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
