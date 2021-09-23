<?php

namespace Concrete\Package\CommunityStore\Block\CommunityProductList;

use Concrete\Core\Page\Page;
use Concrete\Core\Http\Request;
use Concrete\Core\Block\BlockController;
use Concrete\Core\Support\Facade\Config;
use Concrete\Core\Localization\Localization;
use Concrete\Core\Multilingual\Page\Section\Section;
use Concrete\Core\Search\Pagination\PaginationFactory;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product;
use Concrete\Package\CommunityStore\Src\CommunityStore\Group\GroupList;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductList;
use Concrete\Package\CommunityStore\Src\CommunityStore\Discount\DiscountRule;
use Concrete\Package\CommunityStore\Src\CommunityStore\Manufacturer\ManufacturerList;

class Controller extends BlockController
{
    protected $btTable = 'btCommunityStoreProductList';
    protected $btInterfaceWidth = "840";
    protected $btWrapperClass = 'ccm-ui';
    protected $btInterfaceHeight = "600";
    protected $btDefaultSet = 'community_store';
    protected $attFilters = [];

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
        $this->set('groupfilters', []);
        $this->set('manufacturersList', ManufacturerList::getManufacturerList());

    }

    public function getGroupList()
    {
        $grouplist = GroupList::getGroupList();
        $this->set("grouplist", $grouplist);
    }

    public function edit()
    {
        $this->requireAsset('css', 'select2');
        $this->requireAsset('javascript', 'select2');
        $this->getGroupList();
        $this->set('groupfilters', $this->getGroupFilters());
        $this->set('manufacturersList', ManufacturerList::getManufacturerList());
        if ($this->relatedPID) {
            $relatedProduct = Product::getByID($this->relatedPID);
            $this->set('relatedProduct', $relatedProduct);
        }
    }

    public function getGroupFilters()
    {
        // $app = Application::getFacadeApplication();
        $db = $this->app->make('database')->connection();
        $result = $db->query("SELECT gID FROM btCommunityStoreProductListGroups where bID = ?", [$this->bID]);

        $list = [];

        if ($result) {
            foreach ($result as $g) {
                $list[] = $g['gID'];
            }
        }

        return $list;
    }

    public function action_filterby($atthandle1 = '', $attvalue1 = '', $atthandle2 = '', $attvalue2 = '', $atthandle3 = '', $attvalue3 = '')
    {
        for ($i = 1; $i < 4; ++$i) {
            $attitle = 'atthandle' . $i;
            $atvalue = 'attvalue' . $i;
            if ($$attitle) {
                $this->attFilters[$$attitle] = $$atvalue;
            }
        }

        $this->view();
    }

    public function view()
    {
        // $app = Application::getFacadeApplication();
        $request = $this->app->make(Request::class);

        $products = new ProductList();

        // checks in case sort order was inadvertantly set to an option that doesn't work with the current filter
        if ('category' == $this->sortOrder && !('current' == $this->filter || 'page' == $this->filter)) {
            $this->sortOrder = 'alpha';
        }

        if ('related' == $this->sortOrder && !('related' == $this->filter || 'related_product' == $this->filter)) {
            $this->sortOrder = 'related';
        }

        $usersort = $request->query->get('sort' . $this->bID);

        if ($usersort && '0' != $usersort) {
            $products->setSortBy($usersort);
            $this->set('usersort', $usersort);
        } else {
            $products->setSortBy($this->sortOrder);
            $this->set('usersort', '');
        }

        if ($this->sortOrder == 'alpha' || $this->sortOrder == 'sku') {
            $products->setSortByDirection('asc');
        }

        if ($this->filter == 'current' || $this->filter == 'current_children') {
            $page = Page::getCurrentPage();
            $pageID = $page->getCollectionID();

            $site = $this->app->make('site')->getSite();
            if ($site) {
                $locale = $site->getDefaultLocale();

                if ($locale) {
                    $relatedPageID = Section::getRelatedCollectionIDForLocale($pageID, $locale->getLocale());
                    if ($relatedPageID) {
                        $pageID = $relatedPageID;
                        $page = Page::getByID($pageID);
                    }
                }
            }

            $products->setCID($pageID);

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
            if ('related' == $this->filter) {
                $cID = Page::getCurrentPage()->getCollectionID();
                $product = Product::getByCollectionID($cID);
            } else {
                $product = Product::getByID($this->relatedPID);
            }

            if (is_object($product)) {
                $products->setRelatedProduct($product);
            } else {
                $products->setRelatedProduct(true);
            }
        }

        if ($this->filter == 'random') {
            $products->setSortBy('random');
        }

        if ($this->filter == 'random_daily') {
            $products->setSortBy('random');
            $products->setRandomSeed(date('z'));
        }

        $products->setItemsPerPage($this->maxProducts > 0 ? $this->maxProducts : 1000);
        $products->setGroupIDs($this->getGroupFilters());
        $products->setFeaturedOnly($this->showFeatured);
        $products->setSaleOnly($this->showSale);
        $products->setShowOutOfStock($this->showOutOfStock);

        if ($this->groupMatchAny === '-1') {
            $products->setGroupNoMatchAny(true);
        } else {
            $products->setGroupMatchAny($this->groupMatchAny);
        }

        $products->setManufacturer($this->filterManufacturer);

        if (!empty($this->attFilters)) {
            $products->setAttributeFilters($this->attFilters);
        }

        if ($request->getQueryString() && $this->enableExternalFiltering) {
            $products->processUrlFilters($request);
        }

        $factory = new PaginationFactory(Request::createFromGlobals());
        $paginator = $factory->createPaginationObject($products, PaginationFactory::PERMISSIONED_PAGINATION_STYLE_PAGER);

        $pagination = $paginator->renderDefaultView();
        $products = $paginator->getCurrentPageResults();

        $automaticdiscounts = DiscountRule::findAutomaticDiscounts();

        foreach ($products as $key => $product) {
            if (!empty($automaticdiscounts)) {
                $products[$key]->addDiscountRules($automaticdiscounts);
            }
        }

        $this->set('products', $products);
        $this->set('pagination', $pagination);
        $this->set('paginator', $paginator);

        //load some helpers
        $this->set('ih', $this->app->make('helper/image'));
        $this->set('th', $this->app->make('helper/text'));

        if (Config::get('community_store.shoppingDisabled') == 'all') {
            $this->set('showAddToCart', false);
        }

        $this->set('token', $this->app->make('token'));

        $c = Page::getCurrentPage();
        $al = Section::getBySectionOfSite($c);
        $langpath = '';

        if ($al !== null) {
            $langpath = $al->getCollectionHandle();
        }

        $this->set('langpath', $langpath);
        $this->set('app', $this->app);
        $this->set('locale', Localization::activeLocale());

        $this->set('isWholesale', \Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Wholesale::isUserWholesale());
    }

    public function registerViewAssets($outputContent = '')
    {
        $this->requireAsset('javascript', 'jquery');
        $js = \Concrete\Package\CommunityStore\Controller::returnHeaderJS();
        $this->addFooterItem($js);
        $this->requireAsset('javascript', 'sysend');
        $this->requireAsset('javascript', 'community-store');
        $this->requireAsset('css', 'community-store');
    }

    public function save($args)
    {
        $args['showOutOfStock'] = isset($args['showOutOfStock']) ? (int)$args['showOutOfStock'] : 0;
        $args['showDescription'] = isset($args['showDescription']) ? (int)$args['showDescription'] : 0;
        $args['showQuickViewLink'] = isset($args['showQuickViewLink']) ? (int)$args['showQuickViewLink'] : 0;
        $args['showPageLink'] = isset($args['showPageLink']) ? (int)$args['showPageLink'] : 0;
        $args['showSortOption'] = isset($args['showSortOption']) ? (int)$args['showSortOption'] : 0;
        $args['showName'] = isset($args['showName']) ? (int)$args['showName'] : 0;
        $args['showPrice'] = isset($args['showPrice']) ? (int)$args['showPrice'] : 0;
        $args['showQuantity'] = isset($args['showQuantity']) ? (int)$args['showQuantity'] : 0;
        $args['showAddToCart'] = isset($args['showAddToCart']) ? (int)$args['showAddToCart'] : 0;
        $args['showLink'] = isset($args['showLink']) ? (int)$args['showLink'] : 0;
        $args['showButton'] = isset($args['showButton']) ? (int)$args['showButton'] : 0;
        $args['truncateEnabled'] = isset($args['truncateEnabled']) ? (int)$args['truncateEnabled'] : 0;
        $args['showPagination'] = isset($args['showPagination']) ? (int)$args['showPagination'] : 0;
        $args['enableExternalFiltering'] = isset($args['enableExternalFiltering']) ? 1 : 0;
        $args['showFeatured'] = isset($args['showFeatured']) ? (int)$args['showFeatured'] : 0;
        $args['showSale'] = isset($args['showSale']) ? (int)$args['showSale'] : 0;
        $args['maxProducts'] = (isset($args['maxProducts']) && $args['maxProducts'] > 0) ? $args['maxProducts'] : 0;
        $args['relatedPID'] = isset($args['relatedPID']) ? (int)$args['relatedPID'] : 0;

        if ('related_product' != $args['filter']) {
            $args['relatedPID'] = 0;
        }

        $filtergroups = $args['filtergroups'];
        unset($args['filtergroups']);

        // $app = Application::getFacadeApplication();
        $db = $this->app->make('database')->connection();
        $vals = [$this->bID];
        $db->query("DELETE FROM btCommunityStoreProductListGroups where bID = ?", $vals);

        //insert  groups
        if (!empty($filtergroups)) {
            foreach ($filtergroups as $gID) {
                $vals = [$this->bID, (int)$gID];
                $db->query("INSERT INTO btCommunityStoreProductListGroups (bID,gID) VALUES (?,?)", $vals);
            }
        }

        parent::save($args);
    }

    public function duplicate($newBID) {
        $db = $this->app->make('database')->connection();
        $ni = parent::duplicate($newBID);
        $db->query("INSERT INTO btCommunityStoreProductListGroups (bID, gID) select ?, gID from btCommunityStoreProductListGroups where  bID = ?", [$ni->bID, $this->bID]);
    }

    public function delete()
    {
        $db = $this->app->make('database')->connection();
        $db->executeQuery('DELETE FROM btCommunityStoreProductListGroups WHERE bID = ?', [$this->bID]);
        parent::delete();
    }

    public function validate($args)
    {
        $e = $this->app->make("helper/validation/error");
        $nh = $this->app->make("helper/number");

        if (($args['filter']  == 'page' || $args['filter'] == 'page_children') && $args['filterCID'] <= 0) {
            $e->add(t('A page must be selected'));
        }

        if ($args['maxProducts'] && !$nh->isInteger($args['maxProducts'])) {
            $e->add(t('Number of Products must be a whole number'));
        }

        return $e;
    }
}
