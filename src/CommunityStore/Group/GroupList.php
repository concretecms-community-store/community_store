<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Group;

class GroupList
{
    public static function getGroupList()
    {
        $em = \ORM::entityManager();
        $queryBuilder = $em->createQueryBuilder();

        return $queryBuilder->select('g')
            ->from('\Concrete\Package\CommunityStore\Src\CommunityStore\Group\Group', 'g')
            ->orderBy('g.groupName')
            ->getQuery()
            ->getResult();
    }
}
