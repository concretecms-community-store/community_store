<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Utilities;

use \Concrete\Core\User\Group\Group;

class Wholesale {

    public static function isUserWholesale(){
        $user = new \User();
        $wholesale = Group::getByName('Wholesale Customer');
        return $user->inGroup($wholesale);
    }
}