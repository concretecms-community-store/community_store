<?php
namespace Concrete\Package\CommunityStore\Block\CommunityProductList;

use Concrete\Core\Block\BlockController;
use Core;
use Config;
use Page;
use Database;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product as StoreProduct;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductList as StoreProductList;
use Concrete\Package\CommunityStore\Src\CommunityStore\Group\GroupList as StoreGroupList;

class Controller extends BlockController
{
    protected $btTable = 'btCommunityStoreProductList';
    protected $btInterfaceWidth = "800";
    protected $btWrapperClass = 'ccm-ui';
    protected $btInterfaceHeight = "600";
    protected $btDefaultSet = 'community_store';

    public function getBlockTypeDescription()
    {
        return t("Add a Product List for Community Store");
    }

    public function getBlockTypeName()
    {
        return t("Product List");
    }
    public function add()
    {
        $this->requireAsset('css', 'select2');
        $this->requireAsset('javascript', 'select2');
        $this->getGroupList();
        $this->set('groupfilters', array());
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
        $result = $db->query("SELECT gID FROM btCommunityStoreProductListGroups where bID = ?", array($this->bID));

        $list = array();

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
        $products->setSortBy($this->sortOrder);

        if ($this->sortOrder == 'alpha') {
            $products->setSortByDirection('asc');
        }

        if ($this->filter == 'current' || $this->filter == 'current_children') {
            $page = Page::getCurrentPage();
            $products->setCID($page->getCollectionID());

            if ($this->filter == 'current_children') {
                $products->setCIDs($page->getCollectionChildrenArray());
            }
        }

        if ($this->filter == 'page' || $this->filter == 'page_children') {
            if ($this->filterCID) {
                $products->setCID($this->filterCID);

                if ($this->filter == 'page_children') {
                    $targetpage = Page::getByID($this->filterCID);
                    if ($targetpage) {
                        $products->setCIDs($targetpage->getCollectionChildrenArray());
                    }
                }
            }
        }

        if ($this->filter == 'related' || $this->filter == 'related_product') {

            if ($this->filter == 'related') {
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


        $products->setItemsPerPage($this->maxProducts > 0 ? $this->maxProducts : 1000);
        $products->setGroupIDs($this->getGroupFilters());
        $products->setFeaturedOnly($this->showFeatured);
        $products->setSaleOnly($this->showSale);
        $products->setShowOutOfStock($this->showOutOfStock);
        $products->setGroupMatchAny($this->groupMatchAny);
        $paginator = $products->getPagination();
        $pagination = $paginator->renderDefaultView();
        $products = $paginator->getCurrentPageResults();

        foreach ($products as $product) {
            $product->setInitialVariation();
        }

        $this->set('products', $products);
        $this->set('pagination', $pagination);
        $this->set('paginator', $paginator);

        //load some helpers
        $this->set('ih', Core::make('helper/image'));
        $this->set('th', Core::make('helper/text'));

        if (Config::get('community_store.shoppingDisabled') == 'all') {
            $this->set('showAddToCart', false);
        }
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
        $args['showDescription'] = isset($args['showDescription']) ? 1 : 0;
        $args['showQuickViewLink'] = isset($args['showQuickViewLink']) ? 1 : 0;
        $args['showPageLink'] = isset($args['showPageLink']) ? 1 : 0;
        $args['showName'] = isset($args['showName']) ? 1 : 0;
        $args['showPrice'] = isset($args['showPrice']) ? 1 : 0;
        $args['showAddToCart'] = isset($args['showAddToCart']) ? 1 : 0;
        $args['showLink'] = isset($args['showLink']) ? 1 : 0;
        $args['showButton'] = isset($args['showButton']) ? 1 : 0;
        $args['truncateEnabled'] = isset($args['truncateEnabled']) ? 1 : 0;
        $args['showPagination'] = isset($args['showPagination']) ? 1 : 0;
        $args['showFeatured'] = isset($args['showFeatured']) ? 1 : 0;
        $args['showSale'] = isset($args['showSale']) ? 1 : 0;
        $args['maxProducts'] = (isset($args['maxProducts']) && $args['maxProducts'] > 0) ? $args['maxProducts'] : 0;
        $args['relatedPID'] = isset($args['relatedPID']) ? (int)$args['relatedPID'] : 0;

        if ($args['filter'] != 'related_product') {
            $args['relatedPID'] = 0;
        }

        $filtergroups = $args['filtergroups'];
        unset($args['filtergroups']);

        $app = \Concrete\Core\Support\Facade\Application::getFacadeApplication();
        $db = $app->make('database')->connection();
        $vals = array($this->bID);
        $db->query("DELETE FROM btCommunityStoreProductListGroups where bID = ?", $vals);

        //insert  groups
        if (!empty($filtergroups)) {
            foreach ($filtergroups as $gID) {
                $vals = array($this->bID, (int) $gID);
                $db->query("INSERT INTO btCommunityStoreProductListGroups (bID,gID) VALUES (?,?)", $vals);
            }
        }

        parent::save($args);
    }
    public function validate($args)
    {
        $e = Core::make("helper/validation/error");
        $nh = Core::make("helper/number");

        if (($args['filter'] == 'page' || $args['filter'] == 'page_children') && $args['filterCID'] <= 0) {
            $e->add(t('A page must be selected'));
        }

        if ($args['maxProducts'] && !$nh->isInteger($args['maxProducts'])) {
            $e->add(t('Number of Products must be a whole number'));
        }

        return $e;
    }
}
