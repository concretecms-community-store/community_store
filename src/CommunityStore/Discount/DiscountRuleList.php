<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Discount;

use Pagerfanta\Adapter\DoctrineDbalAdapter;
use Concrete\Core\Search\Pagination\Pagination;
use Concrete\Core\Search\ItemList\Database\ItemList;
use Concrete\Package\CommunityStore\Src\CommunityStore\Discount\DiscountRule;

class DiscountRuleList extends ItemList
{
    protected $sortBy = 'alpha';
    protected $search = '';

    public function setGroupID($gID)
    {
        $this->gID = $gID;
    }

    public function setSortBy($sort)
    {
        $this->sortBy = $sort;
    }

    public function createQuery()
    {
        $this->query
            ->select('r.drID')
            ->from('CommunityStoreDiscountRules', 'r');
    }

    public function setSearch($search)
    {
        $this->search = $search;
    }

    public function finalizeQuery(\Doctrine\DBAL\Query\QueryBuilder $query)
    {
        $paramcount = 0;

        if (isset($this->gID) && ($this->gID > 0)) {
            $query->where('gID = ?')->setParameter($paramcount++, $this->gID);
        }
        switch ($this->sortBy) {
            case "alpha":
                $query->orderBy('drName', 'ASC');
                break;
        }

        if ($this->search) {
            $query->andWhere('drName like ?')->setParameter($paramcount++, '%' . $this->search . '%')
                ->orWhere('drDisplay like ?')->setParameter($paramcount++, '%' . $this->search . '%');

            $query->leftJoin('r', 'CommunityStoreDiscountCodes', 'rc', 'rc.drID = r.drID')
                ->orWhere('dcCode like ?')->setParameter($paramcount++, '%' . $this->search . '%');

            $query->groupBy('r.drID');

        }

        $query->andWhere('drDeleted is NULL');

        return $query;
    }

    public function getResult($queryRow)
    {
        $dr = DiscountRule::getByID($queryRow['drID']);

        if ($dr) {
            $dr->retrieveStatistics();
        }

        return $dr;
    }

    protected function createPaginationObject()
    {
        $adapter = new DoctrineDbalAdapter($this->deliverQueryObject(), function ($query) {
            $query->resetQueryParts(['groupBy', 'orderBy'])->select('count(distinct r.drID)')->setMaxResults(1);
        });
        $pagination = new Pagination($this, $adapter);

        return $pagination;
    }

    public function getTotalResults()
    {
        $query = $this->deliverQueryObject();

        return $query->resetQueryParts(['groupBy', 'orderBy'])->select('count(distinct r.drID)')->setMaxResults(1)->execute()->fetchColumn();
    }
}
