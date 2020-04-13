<?php

namespace Concrete\Package\CommunityStore\Src\CommunityStore\Manufacturer;

use Pagerfanta\Adapter\DoctrineDbalAdapter;
use Concrete\Core\Search\Pagination\Pagination;
use Concrete\Core\Search\ItemList\Database\ItemList;
use Concrete\Core\Support\Facade\DatabaseORM as dbORM;
use Concrete\Package\CommunityStore\Src\CommunityStore\Manufacturer\Manufacturer;

class ManufacturerList extends ItemList
{

    /**
     * Create base query
     */
    public function createQuery()
    {
        $this->query->select('m.mID')
            ->from('CommunityStoreManufacturer', 'm');
    }

    public function getTotalResults()
    {
        $query = $this->deliverQueryObject();
        return $query->select('count(m.mID)')
            ->execute()
            ->fetchColumn();
    }
    /**
     * Gets the pagination object for the query.
     * @return Pagination
     */
    protected function createPaginationObject()
    {
        $adapter = new DoctrineDbalAdapter($this->deliverQueryObject(), function ($query) {
            $query->resetQueryParts(['groupBy', 'orderBy'])->select('count(distinct m.mID)')->setMaxResults(1);
        });
        $pagination = new Pagination($this, $adapter);
        return $pagination;
    }

    public function getResult($queryRow)
    {
        $ai = Manufacturer::getByID($queryRow['mID']);
        return $ai;
    }

    public static function getManufacturerList()
    {
        $em = dbORM::entityManager();
        $queryBuilder = $em->createQueryBuilder();

        return $queryBuilder->select('m')
            ->from('\Concrete\Package\CommunityStore\Src\CommunityStore\Manufacturer\Manufacturer', 'm')
            ->orderBy('m.mName')
            ->getQuery()
            ->getResult();
    }

}
