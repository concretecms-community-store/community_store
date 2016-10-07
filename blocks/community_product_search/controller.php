<?php

namespace Concrete\Package\CommunityStore\Block\CommunityProductSearch;

use Database;
use CollectionAttributeKey;
use Concrete\Core\Page\PageList;
use Page;
use Core;
use Request;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductList as StoreProductList;
use Concrete\Block\Search\Controller as SearchBlockController;
class Controller extends SearchBlockController
{

    protected $btDefaultSet = 'community_store';

    public function getBlockTypeDescription()
    {
        return t("Add a search box to your community store site.");
    }

    public function getBlockTypeName()
    {
        return t("Product Search");
    }

    public function do_search()
    {

        $q = Request::request('query');

        $aksearch = false;
        $products = new StoreProductList();

        if (empty(Request::request('query')) && $aksearch == false) {
            return false;
        }

        if (null!==Request::request('query')) {
            $products->setSearch($q);
            $products->setGroupSearch($q);
            $products->setAttributesearch($q);
        }

        $pagination = $products->getPagination();
        $results = $pagination->getCurrentPageResults();

        $this->set('ih', Core::make('helper/image'));
        $this->set('query', $q);
        $this->set('results', $results);
        $this->set('do_search', true);
        $this->set('searchList', $ipl);
        $this->set('pagination', $pagination);
    }

}
