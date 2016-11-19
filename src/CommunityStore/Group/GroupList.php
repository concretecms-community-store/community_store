<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Group;

use Database;

class GroupList
{
    public static function getGroupList()
    {
        $em = \ORM::entityManager();
        $queryBuilder = $em->createQueryBuilder();

        return $queryBuilder->select('g')
            ->from('\Concrete\Package\CommunityStore\Src\CommunityStore\Group\Group', 'g')
            ->getQuery()
            ->getResult();
    }
}
