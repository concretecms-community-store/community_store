<?php

namespace Concrete\Package\CommunityStore\Controller\SinglePage\Dashboard\Store\Products;

use \Concrete\Core\Page\Controller\DashboardPageController;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductLocation as StoreProductLocation;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductList as StoreProductList;


class Categories extends DashboardPageController
{
    public function view() {
        $pages = StoreProductLocation::getLocationPages();
        $this->set('pageTitle', t('Categories'));
        $this->set('pages', $pages);
    }

    public function manage($cID) {
        $products = new StoreProductList();

        $page = \Page::getByID($cID);

        if (!$page) {
            $this->redirect('/dashboard/store/products/categories/');
        }

        //$products->setSortBy($this->sortOrder);

        $products->setCID($cID);
        $this->set('products', $products->getResults());
        $this->set('page', $page);
        $this->set('pageTitle', t('Manage Category: ', $page->getCollectionName()));

    }


}
