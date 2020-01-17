<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Utilities;

use \Concrete\Core\User\Group\Group;
use \Concrete\Core\Support\Facade\Config;

class Wholesale {

    public static function isUserWholesale(){
        $user = new \User();

        $wholesaleCustomerGroupID = Config::get('community_store.wholesaleCustomerGroup');

        if ($wholesaleCustomerGroupID) {
            $wholesaleCustomerGroup = Group::getByID($wholesaleCustomerGroupID);

            if ($wholesaleCustomerGroup && is_object($wholesaleCustomerGroup)) {
                return $user->inGroup($wholesaleCustomerGroup);
            }
        }

        return false;
    }
}
