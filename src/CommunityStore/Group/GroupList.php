<?php

namespace Concrete\Package\CommunityStore\Src\CommunityStore\Group;

use Concrete\Core\Support\Facade\DatabaseORM as dbORM;

class GroupList
{
    public static function getGroupList()
    {
        $em = dbORM::entityManager();
        $queryBuilder = $em->createQueryBuilder();

        return $queryBuilder->select('g')
            ->from('\Concrete\Package\CommunityStore\Src\CommunityStore\Group\Group', 'g')
            ->orderBy('g.groupName')
            ->getQuery()
            ->getResult()
        ;
    }
}
