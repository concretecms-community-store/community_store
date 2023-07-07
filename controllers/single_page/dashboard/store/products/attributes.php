<?php
namespace Concrete\Package\CommunityStore\Controller\SinglePage\Dashboard\Store\Products;

use Concrete\Core\Attribute\Type;
use Concrete\Core\Support\Facade\Url;
use Concrete\Core\Attribute\Key\Category;
use Concrete\Core\Navigation\Item\Item;
use Concrete\Core\Page\Controller\DashboardAttributesPageController;

class Attributes extends DashboardAttributesPageController
{
    protected function getCategoryObject()
    {
        return Category::getByHandle('store_product');
    }

    public function view()
    {
        $this->renderList();
    }

    public function edit($akID = null)
    {
        $key = $this->getCategoryObject()->getController()->getByID($akID);
        if ($key === null) {
            return $this->buildRedirect('/dashboard/store/products/attributes');
        }
        $this->renderEdit($key,
            Url::to('/dashboard/store/products/attributes', 'view')
        );
        if (method_exists($this, 'createBreadcrumb')) {
            $this->setBreadcrumb($breacrumb = $this->getBreadcrumb() ?: $this->createBreadcrumb());
            $breacrumb->add(new Item('#', $key->getAttributeKeyDisplayName('text')));
        }
    }

    public function update($akID = null)
    {
        $this->edit($akID);
        $key = $this->getCategoryObject()->getController()->getByID($akID);
        $this->executeUpdate($key,
            Url::to('/dashboard/store/products/attributes', 'view')
        );
    }

    public function select_type($type = null)
    {
        $type = Type::getByID($type);
        if ($type === null) {
            return $this->buildRedirect('/dashboard/store/products/attributes');
        }
        $this->renderAdd($type,
            Url::to('/dashboard/store/products/attributes', 'view')
        );
        if (method_exists($this, 'createBreadcrumb')) {
            $this->setBreadcrumb($breacrumb = $this->getBreadcrumb() ?: $this->createBreadcrumb());
            $breacrumb->add(new Item('#', t('Add Attribute')));
        }
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
        $this->executeDelete($key,
            Url::to('/dashboard/store/products/attributes', 'view')
        );
    }
}
