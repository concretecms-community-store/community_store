<?php
namespace Concrete\Package\CommunityStore\Controller\SinglePage\Dashboard\Store\Products;

use Concrete\Core\Page\Controller\DashboardPageController;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductLocation as StoreProductLocation;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductList as StoreProductList;

class Categories extends DashboardPageController
{
    public function view()
    {
        $pages = StoreProductLocation::getLocationPages();
        $this->set('pageTitle', t('Product Categories'));
        $this->set('pages', $pages);
    }

    public function manage($cID)
    {
        $products = new StoreProductList();

        $page = \Page::getByID($cID);

        if (!$page) {
            $this->redirect('/dashboard/store/products/categories');
        }

        $products->setSortBy('category');
        $products->setActiveOnly(false);
        $products->setShowOutOfStock(true);

        $products->setCID($cID);
        $this->set('products', $products->getResults());
        $this->set('page', $page);
        $this->set('cID', $cID);
        $this->set('pageTitle', t('Manage Category: ' . $page->getCollectionName()));
    }

    public function save($cID)
    {
        if ($this->post() && $this->token->validate('community_store')) {
            $data = $this->post();

            $count = 0;

            $productLocations = StoreProductLocation::getProductsForLocation($cID);

            $productPositions = $data['products'];
            $productPositions = array_flip($productPositions);

            foreach ($productLocations as $productLocation) {
                $productLocation->setCategorySortOrder($productPositions[$productLocation->getProductID()]);
                $productLocation->save();
            }
        }

        $this->flash('success', t('Category Order Updated'));
        $this->redirect('/dashboard/store/products/categories/manage/' . $cID);
    }
}
