<?php

namespace Concrete\Package\CommunityStore\Controller\SinglePage\Dashboard\Store\Products;

use \Concrete\Core\Page\Controller\DashboardPageController;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductLocation as StoreProductLocation;


class Categories extends DashboardPageController
{
    public function view() {


        $pages = StoreProductLocation::getLocationPages();

        $this->set('pageTitle', t('Categories'));
    }


}
