<?php

namespace Concrete\Package\CommunityStore\Controller\SinglePage\Dashboard\Store\Products;

use Concrete\Core\Attribute\Key\Category;
use Concrete\Core\Attribute\Type;
use Concrete\Core\Page\Controller\DashboardAttributesPageController;
use Concrete\Core\Support\Facade\Url;

class Attributes extends DashboardAttributesPageController
{
    public function view()
    {
        $this->renderList();
    }

    public function edit($akID = null)
    {
        $key = $this->getCategoryObject()->getController()->getByID($akID);
        $this->renderEdit(
            $key,
            Url::to('/dashboard/store/products/attributes', 'view')
        );
    }

    public function update($akID = null)
    {
        $this->edit($akID);
        $key = $this->getCategoryObject()->getController()->getByID($akID);
        $this->executeUpdate(
            $key,
            Url::to('/dashboard/store/products/attributes', 'view')
        );
    }

    public function select_type($type = null)
    {
        $type = Type::getByID($type);
        $this->renderAdd(
            $type,
            Url::to('/dashboard/store/products/attributes', 'view')
        );
    }

    public function add($type = null)
    {
        $this->select_type($type);
        $type = Type::getByID($type);
        $this->executeAdd($type, Url::to('/dashboard/store/products/attributes', 'view'));
    }

    public function delete($akID = null)
    {
        $key = $this->getCategoryObject()->getController()->getByID($akID);
        $this->executeDelete(
            $key,
            Url::to('/dashboard/store/products/attributes', 'view')
        );
    }

    protected function getCategoryObject()
    {
        return Category::getByHandle('store_product');
    }
}
