<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Product;

use Concrete\Core\Search\Pagination\Pagination;
use Concrete\Core\Search\ItemList\Database\AttributedItemList;
use Pagerfanta\Adapter\DoctrineDbalAdapter;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product as StoreProduct;
use Concrete\Package\CommunityStore\Src\CommunityStore\Report\ProductReport as StoreProductReport;

class ProductList extends AttributedItemList
{
    protected $gIDs = array();
    protected $groupMatchAny = false;
    protected $sortBy = "alpha";
    protected $sortByDirection = "desc";
    protected $featuredOnly = false;
    protected $saleOnly = false;
    protected $activeOnly = true;
    protected $cIDs = array();
    protected $relatedProduct = false;

    public function setGroupID($gID)
    {
        $this->gIDs = array($gID);
    }

    public function setGroupIDs($groupIDs)
    {
        $this->gIDs = array_merge($this->gIDs, $groupIDs);
    }

    public function setSortBy($sort)
    {
        $this->sortBy = $sort;
    }

    public function setSortByDirection($dir)
    {
        $this->sortByDirection = $dir;
    }

    public function getSortByDirection() {
        return $this->sortByDirection;
    }

    public function setCID($cID)
    {
        $this->cIDs[] = $cID;
    }

    public function setCIDs($cIDs)
    {
        $this->cIDs = array_merge($this->cIDs, array_values($cIDs));
    }

    public function setGroupMatchAny($match)
    {
        $this->groupMatchAny = (bool) $match;
    }

    public function setFeaturedOnly($bool)
    {
        $this->featuredOnly = $bool;
    }
    public function setSaleOnly($bool)
    {
        $this->saleOnly = $bool;
    }
    public function setActiveOnly($bool)
    {
        $this->activeOnly = $bool;
    }
    public function setShowOutOfStock($bool)
    {
        $this->showOutOfStock = $bool;
    }
    public function setRelatedProduct($product)
    {
        $this->relatedProduct = $product;
    }

    protected function getAttributeKeyClassName()
    {
        return '\\Concrete\\Package\\CommunityStore\\Src\\Attribute\\Key\\StoreProductKey';
    }

    public function createQuery()
    {
        $this->query
        ->select('p.pID')
        ->from('CommunityStoreProducts', 'p');
    }

    public function setSearch($search)
    {
        $this->search = $search;
    }

    public function finalizeQuery(\Doctrine\DBAL\Query\QueryBuilder $query)
    {
        $paramcount = 0;

        if (!empty($this->gIDs)) {
            $validgids = array();

            foreach ($this->gIDs as $gID) {
                if ($gID > 0) {
                    $validgids[] = $gID;
                }
            }

            if (!empty($validgids)) {
                $query->innerJoin('p', 'CommunityStoreProductGroups', 'g', 'p.pID = g.pID and g.gID in (' . implode(',', $validgids) . ')');

                if (!$this->groupMatchAny) {
                    $query->having('count(g.gID) = '  . count($validgids));
                }
            }
        }

        switch ($this->sortBy) {
            case "alpha":
                $query->orderBy('pName', $this->getSortByDirection());
                break;
            case "price":
                $query->orderBy('pPrice', $this->getSortByDirection());
                break;
            case "active":
                $query->orderBy('pActive', $this->getSortByDirection());
                break;
            case "date":
                $query->orderBy('pDateAdded', $this->getSortByDirection());
                break;
            case "popular":
                $pr = new StoreProductReport();
                $pr->sortByPopularity();
                $products = $pr->getProducts();
                $pIDs = array();
                foreach ($products as $product) {
                    $pIDs[] = $product['pID'];
                }
                foreach ($pIDs as $pID) {
                    $query->addOrderBy("pID = ?", 'DESC')->setParameter($paramcount++, $pID);
                }
                break;
        }
        if ($this->featuredOnly) {
            $query->andWhere("pFeatured = 1");
        }
        if ($this->saleOnly) {
            $query->andWhere("pSalePrice is not null");
        }
        if (!$this->showOutOfStock) {
            $query->andWhere("pQty > 0 OR pQtyUnlim = 1");
        }
        if ($this->activeOnly) {
            $query->andWhere("pActive = 1");
        }

        // if we have a true value for related, we don't have an object, meaning it couldn't find a product to look for related products for
        // this means we should return no products
        if ($this->relatedProduct === true) {
            $query->andWhere("1 = 0");
        }  elseif (is_object($this->relatedProduct)) {
            $relatedids = array();
            $related = $this->relatedProduct->getRelatedProducts();

            foreach($related as $r) {
                $relatedids[] = $r->getRelatedProductID();
            }

            if (!empty($relatedids)) {
                $query->andWhere('pID in ('. implode(',', $relatedids) .')');
            } else {
                $query->andWhere('1 = 0');
            }
        } elseif (is_array($this->cIDs) && !empty($this->cIDs)) {
            $query->innerJoin('p', 'CommunityStoreProductLocations', 'l', 'p.pID = l.pID and l.cID in (' .  implode(',', $this->cIDs). ')');
        }

        $query->groupBy('p.pID');

        if ($this->search) {
            $query->andWhere('pName like ?')->setParameter($paramcount++, '%'. $this->search. '%');
        }

        return $query;
    }

    public function getResult($queryRow)
    {
        return StoreProduct::getByID($queryRow['pID']);
    }

    protected function createPaginationObject()
    {
        $adapter = new DoctrineDbalAdapter($this->deliverQueryObject(), function ($query) {
            $query->select('count(distinct p.pID) c ');
            $query->groupBy('null');
            $query->having('1 = 1');
        });
        $pagination = new Pagination($this, $adapter);

        return $pagination;
    }

    public function getTotalResults()
    {
        $query = $this->deliverQueryObject();

        return $query->select('count(distinct p.pID)')->setMaxResults(1)->execute()->fetchColumn();
    }
}
