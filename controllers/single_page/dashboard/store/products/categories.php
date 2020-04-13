<?php
namespace Concrete\Package\CommunityStore\Controller\SinglePage\Dashboard\Store\Products;

use Concrete\Core\Page\Page;
use Concrete\Core\Routing\Redirect;
use Concrete\Core\Page\Controller\DashboardPageController;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductList;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductLocation;

class Categories extends DashboardPageController
{
    public function view()
    {
        $pages = ProductLocation::getLocationPages();
        $this->set('pageTitle', t('Product Categories'));
        $this->set('pages', $pages);
    }

    public function manage($cID)
    {
        $products = new ProductList();

        $page = Page::getByID($cID);

        if (!$page) {
            return Redirect::to('/dashboard/store/products/categories');
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
        if ($this->request->request->all() && $this->token->validate('community_store')) {
            $data = $this->request->request->all();

            $count = 0;

            $productLocations = ProductLocation::getProductsForLocation($cID);

            $productPositions = $data['products'];
            $productPositions = array_flip($productPositions);

            foreach ($productLocations as $productLocation) {
                $productLocation->setCategorySortOrder($productPositions[$productLocation->getProductID()]);
                $productLocation->save();
            }
        }

        $this->flash('success', t('Category Order Updated'));

        return Redirect::to('/dashboard/store/products/categories/manage/' . $cID);
    }
}
