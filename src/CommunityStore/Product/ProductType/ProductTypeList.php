<?php

namespace Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductType;

use Pagerfanta\Adapter\DoctrineDbalAdapter;
use Concrete\Core\Search\Pagination\Pagination;
use Concrete\Core\Search\ItemList\Database\ItemList;
use Concrete\Core\Support\Facade\DatabaseORM as dbORM;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductType\ProductType;

class ProductTypeList extends ItemList
{

    /**
     * Create base query
     */
    public function createQuery()
    {
        $this->query->select('pt.ptID')
            ->from('CommunityStoreProductTypes', 'pt');
    }

    public function getTotalResults()
    {
        $query = $this->deliverQueryObject();
        return $query->select('count(pt.ptID)')
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
            $query->resetQueryParts(['groupBy', 'orderBy'])->select('count(distinct pt.ptID)')->setMaxResults(1);
        });
        $pagination = new Pagination($this, $adapter);
        return $pagination;
    }

    public function getResult($queryRow)
    {
        $ai = ProductType::getByID($queryRow['ptID']);
        return $ai;
    }

    public static function getProductTypeList()
    {
        $em = dbORM::entityManager();
        $queryBuilder = $em->createQueryBuilder();

        return $queryBuilder->select('t')
            ->from('\Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductType\ProductType', 't')
            ->orderBy('t.ptName')
            ->getQuery()
            ->getResult();

    }

}
