<?php
namespace Concrete\Package\CommunityStore\Block\CommunityProductFilter;

use Concrete\Core\Block\BlockController;
use Core;
use Config;
use Page;
use Concrete\Core\Search\Pagination\PaginationFactory;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product as StoreProduct;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductList as StoreProductList;
use Concrete\Package\CommunityStore\Src\CommunityStore\Group\GroupList as StoreGroupList;
use Concrete\Package\CommunityStore\Src\CommunityStore\Discount\DiscountRule as StoreDiscountRule;

class Controller extends BlockController
{
    protected $btTable = 'btCommunityStoreProductFilter';
    protected $btInterfaceWidth = "800";
    protected $btWrapperClass = 'ccm-ui';
    protected $btInterfaceHeight = "600";
    protected $btDefaultSet = 'community_store';
    protected $attFilters = [];

    public function getBlockTypeDescription()
    {
        return t("Add a Product List Filter for Community Store");
    }

    public function getBlockTypeName()
    {
        return t("Product List Filter");
    }

    public function add()
    {
        $this->requireAsset('css', 'select2');
        $this->requireAsset('javascript', 'select2');
        $this->getGroupList();
        $this->set('groupfilters', []);
    }

    public function edit()
    {
        $this->requireAsset('css', 'select2');
        $this->requireAsset('javascript', 'select2');
        $this->getGroupList();
        $this->set('groupfilters', $this->getGroupFilters());

        if ($this->relatedPID) {
            $relatedProduct = StoreProduct::getByID($this->relatedPID);
            $this->set('relatedProduct', $relatedProduct);
        }
    }

    public function getGroupFilters()
    {
        $app = \Concrete\Core\Support\Facade\Application::getFacadeApplication();
        $db = $app->make('database')->connection();
        $result = $db->query("SELECT gID FROM btCommunityStoreProductListGroups where bID = ?", [$this->bID]);

        $list = [];

        if ($result) {
            foreach ($result as $g) {
                $list[] = $g['gID'];
            }
        }

        return $list;
    }

    public function getGroupList()
    {
        $grouplist = StoreGroupList::getGroupList();
        $this->set("grouplist", $grouplist);
    }

    public function view()
    {
        $products = new StoreProductList();

        if ('current' == $this->filter || 'current_children' == $this->filter) {
            $page = Page::getCurrentPage();
            $products->setCID($page->getCollectionID());

            if ('current_children' == $this->filter) {
                $products->setCIDs($page->getCollectionChildrenArray());
            }
        }

        if ('page' == $this->filter || 'page_children' == $this->filter) {
            if ($this->filterCID) {
                $products->setCID($this->filterCID);

                if ('page_children' == $this->filter) {
                    $targetpage = Page::getByID($this->filterCID);
                    if ($targetpage) {
                        $products->setCIDs($targetpage->getCollectionChildrenArray());
                    }
                }
            }
        }

        if ('related' == $this->filter || 'related_product' == $this->filter) {
            if ('related' == $this->filter) {
                $cID = Page::getCurrentPage()->getCollectionID();
                $product = StoreProduct::getByCollectionID($cID);
            } else {
                $product = StoreProduct::getByID($this->relatedPID);
            }

            if (is_object($product)) {
                $products->setRelatedProduct($product);
            } else {
                $products->setRelatedProduct(true);
            }
        }

        if ('random' == $this->filter) {
            $products->setSortBy('random');
        }

        if ('random_daily' == $this->filter) {
            $products->setSortBy('random');
            $products->setRandomSeed(date('z'));
        }


        $products->setGroupIDs($this->getGroupFilters());
        $products->setFeaturedOnly($this->showFeatured);
        $products->setSaleOnly($this->showSale);
        $products->setShowOutOfStock($this->showOutOfStock);
        $products->setGroupMatchAny($this->groupMatchAny);

        if (!empty($this->attFilters)) {
            $products->setAttributeFilters($this->attFilters);
        }

        $request = \Request::getInstance();

        if ($request->getQueryString()) {
            $products->processUrlFilters($request);
        }

        $factory = new PaginationFactory(\Request::createFromGlobals());
        $paginator = $factory->createPaginationObject($products, PaginationFactory::PERMISSIONED_PAGINATION_STYLE_PAGER);

        $pagination = $paginator->renderDefaultView();
        $products = $paginator->getCurrentPageResults();


        $this->set('products', $products);

        $productCategory = $this->app->make('Concrete\Package\CommunityStore\Attribute\Category\ProductCategory');

        $attrList = $productCategory->getList();
        $this->set('attribs', $attrList);
    }

    public function registerViewAssets($outputContent = '')
    {
        $this->requireAsset('javascript', 'jquery');
        $js = \Concrete\Package\CommunityStore\Controller::returnHeaderJS();
        $this->addFooterItem($js);
        $this->requireAsset('javascript', 'community-store');
        $this->requireAsset('css', 'community-store');
    }

    public function save($args)
    {
        $args['showOutOfStock'] = isset($args['showOutOfStock']) ? 1 : 0;
        $args['showFeatured'] = isset($args['showFeatured']) ? 1 : 0;
        $args['showSale'] = isset($args['showSale']) ? 1 : 0;
        $args['relatedPID'] = isset($args['relatedPID']) ? (int) $args['relatedPID'] : 0;

        if ('related_product' != $args['filter']) {
            $args['relatedPID'] = 0;
        }

        $filtergroups = $args['filtergroups'];
        unset($args['filtergroups']);

        $app = \Concrete\Core\Support\Facade\Application::getFacadeApplication();
        $db = $app->make('database')->connection();
        $vals = [$this->bID];
        $db->query("DELETE FROM btCommunityStoreProductListGroups where bID = ?", $vals);

        //insert  groups
        if (!empty($filtergroups)) {
            foreach ($filtergroups as $gID) {
                $vals = [$this->bID, (int) $gID];
                $db->query("INSERT INTO btCommunityStoreProductListGroups (bID,gID) VALUES (?,?)", $vals);
            }
        }

        parent::save($args);
    }

    public function validate($args)
    {
        $e = Core::make("helper/validation/error");
        $nh = Core::make("helper/number");

        if (('page' == $args['filter'] || 'page_children' == $args['filter']) && $args['filterCID'] <= 0) {
            $e->add(t('A page must be selected'));
        }
        return $e;
    }
}
