<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Product;

use Concrete\Core\Search\Pagination\Pagination;
use Concrete\Core\Search\ItemList\Database\AttributedItemList;
use Pagerfanta\Adapter\DoctrineDbalAdapter;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product as StoreProduct;
use Concrete\Package\CommunityStore\Src\CommunityStore\Report\ProductReport as StoreProductReport;
use Concrete\Package\CommunityStore\Src\Attribute\Key\StoreProductKey;

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
    public function setGroupSearch($search)
    {
        $this->groupSearch = $search;
    }
    public function setAttributeSearch($search)
    {
        $this->attributeSearch = $search;
    }

    public function setMinPrice($price){
        $this->minPrice = $price;
    }
    public function setMaxPrice($price){
        $this->maxPrice = $price;
    }

    public function setMinWidth($width){
        $this->minWidth = $width;
    }
    public function setMaxWidth($width){
        $this->maxWidth = $width;
    }

    public function setMinHeight($height){
        $this->minHeight = $height;
    }
    public function setMaxHeight($height){
        $this->maxHeight = $height;
    }
    public function setMinLength($length){
        $this->minLength = $length;
    }
    public function setMaxLength($length){
        $this->maxLength = $length;
    }
    public function setAttributeVals($attributes)
    {
        $this->attributeVals = $attributes;
    }
    public function setAttributeRange($attributes)
    {
        $this->attributeRange = $attributes;
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

        $relatedids = array();

        // if we have a true value for related, we don't have an object, meaning it couldn't find a product to look for related products for
        // this means we should return no products
        if ($this->relatedProduct === true) {
            $query->andWhere("1 = 0");
        }  elseif (is_object($this->relatedProduct)) {

            $related = $this->relatedProduct->getRelatedProducts();

            foreach($related as $r) {
                $relatedids[] = $r->getRelatedProductID();
            }

            if (!empty($relatedids)) {
                $query->andWhere('p.pID in ('. implode(',', $relatedids) .')');
            } else {
                $query->andWhere('1 = 0');
            }
        } elseif (is_array($this->cIDs) && !empty($this->cIDs)) {
            $query->innerJoin('p', 'CommunityStoreProductLocations', 'l', 'p.pID = l.pID and l.cID in (' .  implode(',', $this->cIDs). ')');
        }

        switch ($this->sortBy) {
            case "alpha":
                $query->orderBy('pName', $this->getSortByDirection());
                break;
            case "alpha_asc":
                $query->orderBy('pName', 'asc');
                break;
            case "alpha_desc":
                $query->orderBy('pName', 'desc');
                break;
            case "price":
                $query->orderBy('pPrice', $this->getSortByDirection());
                break;
            case "price_asc":
                $query->orderBy('pPrice', 'asc');
                break;
            case "price_desc":
                $query->orderBy('pPrice', 'desc');
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
            case "related":
                if (!empty($relatedids)) {
                    $query->addOrderBy('FIELD (pID, '. implode(',', $relatedids) .')');
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

        $query->groupBy('p.pID');

        //for price range filter
        if($this->minPrice){
          $query->andWhere('pPrice >= ? OR pSalePrice >= ?')->setParameter($paramcount++,$this->minPrice)->setParameter($paramcount++,$this->minPrice);
        }
        if($this->maxPrice){
          $query->andWhere('pPrice <= ? OR pSalePrice <= ?')->setParameter($paramcount++,$this->maxPrice)->setParameter($paramcount++,$this->maxPrice);
        }
        //for width filter
        if($this->minWidth){
          $query->andWhere('pWidth >= ?')->setParameter($paramcount++,$this->minWidth);
        }
        if($this->maxWidth){
          $query->andWhere('pWidth <= ?')->setParameter($paramcount++,$this->maxWidth);
        }
        //for height filter
        if($this->minHeight){
          $query->andWhere('pHeight >= ?')->setParameter($paramcount++,$this->minHeight);
        }
        if($this->maxHeight){
          $query->andWhere('pHeight <= ?')->setParameter($paramcount++,$this->maxHeight);
        }

        //for length filter
        if($this->minLength){
          $query->andWhere('pLength >= ?')->setParameter($paramcount++,$this->minLength);
        }
        if($this->maxLength){
          $query->andWhere('pLength <= ?')->setParameter($paramcount++,$this->maxLength);
        }



        if ($this->search) {
            $query->andWhere('pName like ? OR pDesc like ? OR pSKU like ?')->setParameter($paramcount++, '%'. $this->search. '%')->setParameter($paramcount++, '%'. $this->search. '%')->setParameter($paramcount++, '%'. $this->search. '%');
        }
        if($this->groupSearch){
          //search through groupNames
            $query->leftJoin('p', 'CommunityStoreProductGroups', 'pg', 'p.pID = pg.pID');
            $query->leftJoin('pg', 'CommunityStoreGroups', 'g', 'pg.gID = g.gID');
            $query->orWhere('g.groupName like ?')->setParameter($paramcount++, '%'. $this->groupSearch. '%');
        }
        //attributeVals filter
        if (!empty($this->attributeVals)) {
          $aks = array();
          $avs = array();
          foreach($this->attributeVals as $ak => $av){
            $aks[] = $ak;
            foreach($av as $avID){
              $avs[] = $avID;
            }
          }
          $query->leftJoin('p', 'CommunityStoreProductAttributeValues', 'av', 'p.pID = av.pID');
          $query->andWhere('av.akID in('. implode(',', $aks) .') and av.avID in('. implode(',', $avs).')');

        }

        $validPIDs = array();
        if($this->attributeSearch){
            //search attributes
            $searchPIDs = StoreProductKey::filterAttributeValuesByKeyword($this->attributeSearch);
            if(!empty($searchPIDs)){
              foreach($searchPIDs as $pid){
                array_push($validPIDs, $pid);
              }
            }
        }

        if($this->attributeRange){
            //search attributeRange
            $rangePIDs = array();
            $temp = array();
            foreach($this->attributeRange as $akID => $vals){
              $validRangePIDs = StoreProductKey::filterAttributeValuesByMinMax($akID, $vals['min'],$vals['max']);
              if(!empty($validRangePIDs)){
                array_push($temp,$validRangePIDs);
              }
            }
            $rangePIDs = call_user_func_array('array_intersect',$temp);
            if(!empty($validPIDs)) $validPIDs = array_intersect($validPIDs, $rangePIDs);
        }
        
        if(!empty($validPIDs)){
          if($this->attributeSearch && $this->attributeRange){
            $query->andWhere('p.pID in ('. implode(',', $validPIDs).')');
          }else if($this->attributeSearch){
            $query->orWhere('p.pID in ('. implode(',', $validPIDs).')');
          }else if($this->attributeRange){
            $query->andWhere('p.pID in ('. implode(',', $rangePIDs).')');
          }
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
