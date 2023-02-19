<?php

namespace Concrete\Package\CommunityStore\Src\CommunityStore\Manufacturer;

use Concrete\Core\Search\ItemList\Database\ItemList;
use Concrete\Core\Search\Pagination\Pagination;
use Concrete\Core\Support\Facade\DatabaseORM as dbORM;
use Pagerfanta\Adapter\DoctrineDbalAdapter;

class ManufacturerList extends ItemList
{
    /**
     * Create base query.
     */
    public function createQuery()
    {
        $this->query->select('m.mID')
            ->from('CommunityStoreManufacturer', 'm')
        ;
    }

    public function getTotalResults()
    {
        $query = $this->deliverQueryObject();

        return $query->select('count(m.mID)')
            ->execute()
            ->fetchColumn()
        ;
    }

    public function getResult($queryRow)
    {
        return Manufacturer::getByID($queryRow['mID']);
    }

    public static function getManufacturerList()
    {
        $em = dbORM::entityManager();
        $queryBuilder = $em->createQueryBuilder();

        return $queryBuilder->select('m')
            ->from('\Concrete\Package\CommunityStore\Src\CommunityStore\Manufacturer\Manufacturer', 'm')
            ->orderBy('m.mName')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Gets the pagination object for the query.
     *
     * @return Pagination
     */
    protected function createPaginationObject()
    {
        $adapter = new DoctrineDbalAdapter($this->deliverQueryObject(), function ($query) {
            $query->resetQueryParts(['groupBy', 'orderBy'])->select('count(distinct m.mID)')->setMaxResults(1);
        });

        return new Pagination($this, $adapter);
    }
}
