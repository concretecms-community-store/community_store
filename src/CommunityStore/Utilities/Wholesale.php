<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Utilities;

use Concrete\Core\Support\Facade\Config;
use Concrete\Core\User\Group\Group;
use Concrete\Core\User\User;

class Wholesale {

    public static function isUserWholesale(){
        $wholesaleCustomerGroupID = Config::get('community_store.wholesaleCustomerGroup');

        if ($wholesaleCustomerGroupID) {
            $wholesaleCustomerGroup = Group::getByID($wholesaleCustomerGroupID);

            if ($wholesaleCustomerGroup && is_object($wholesaleCustomerGroup)) {
                $user = app(User::class);
                return $user->inGroup($wholesaleCustomerGroup);
            }
        }

        return false;
    }
}
