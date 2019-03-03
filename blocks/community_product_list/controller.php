<?php
namespace Concrete\Package\CommunityStore\Block\CommunityProductList;

use Concrete\Core\Block\BlockController;
use Core;
use Config;
use Page;
use Concrete\Core\Search\Pagination\PaginationFactory;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product as StoreProduct;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductList as StoreProductList;
use Concrete\Package\CommunityStore\Src\CommunityStore\Group\GroupList as StoreGroupList;
use Concrete\Package\CommunityStore\Src\CommunityStore\Discount\DiscountRule as StoreDiscountRule;
use Concrete\Core\Multilingual\Page\Section\Section;
use Concrete\Core\Localization\Localization;

class Controller extends BlockController
{
    protected $btTable = 'btCommunityStoreProductList';
    protected $btInterfaceWidth = "800";
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

        // checks in case sort order was inadvertantly set to an option that doesn't work with the current filter
        if ('category' == $this->sortOrder && !('current' == $this->filter || 'page' == $this->filter)) {
            $this->sortOrder = 'alpha';
        }

        if ('related' == $this->sortOrder && !('related' == $this->filter || 'related_product' == $this->filter)) {
            $this->sortOrder = 'related';
        }

        $usersort = $this->get('sort' . $this->bID);

        if ($usersort && '0' != $usersort) {
            $products->setSortBy($usersort);
            $this->set('usersort', $usersort);
        } else {
            $products->setSortBy($this->sortOrder);
            $this->set('usersort', '');
        }

        if ('alpha' == $this->sortOrder) {
            $products->setSortByDirection('asc');
        }

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

                // if product not found, look for it via multilingual related page
                if (!$product) {
                    $site = $this->app->make('site')->getSite();
                    if ($site) {
                        $locale = $site->getDefaultLocale();

                        if ($locale) {
                            $originalcID = Section::getRelatedCollectionIDForLocale($cID, $locale->getLocale());
                            $product = StoreProduct::getByCollectionID($originalcID);
                        }
                    }
                }
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

        $products->setItemsPerPage($this->maxProducts > 0 ? $this->maxProducts : 1000);
        $products->setGroupIDs($this->getGroupFilters());
        $products->setFeaturedOnly($this->showFeatured);
        $products->setSaleOnly($this->showSale);
        $products->setShowOutOfStock($this->showOutOfStock);
        $products->setGroupMatchAny($this->groupMatchAny);

        if (!empty($this->attFilters)) {
            $products->setAttributeFilters($this->attFilters);
        }

        $request = \Request::getInstance();

        if ($request->getQueryString() && $this->enableExternalFiltering) {
            $products->processUrlFilters($request);
        }

        $factory = new PaginationFactory(\Request::createFromGlobals());
        $paginator = $factory->createPaginationObject($products, PaginationFactory::PERMISSIONED_PAGINATION_STYLE_PAGER);

        $pagination = $paginator->renderDefaultView();
        $products = $paginator->getCurrentPageResults();

        $codediscounts = false;
        $automaticdiscounts = StoreDiscountRule::findAutomaticDiscounts();
        $code = trim(\Session::get('communitystore.code'));

        if ($code) {
            $codediscounts = StoreDiscountRule::findDiscountRuleByCode($code);
        }

        foreach ($products as $key => $product) {
            if (!empty($automaticdiscounts)) {
                $products[$key]->addDiscountRules($automaticdiscounts);
            }

            if (!empty($codediscounts)) {
                $products[$key]->addDiscountRules($codediscounts);
            }
        }

        $this->set('products', $products);
        $this->set('pagination', $pagination);
        $this->set('paginator', $paginator);

        //load some helpers
        $this->set('ih', Core::make('helper/image'));
        $this->set('th', Core::make('helper/text'));

        if ('all' == Config::get('community_store.shoppingDisabled')) {
            $this->set('showAddToCart', false);
        }

        $app = \Concrete\Core\Support\Facade\Application::getFacadeApplication();
        $this->set('token', $app->make('token'));

        $c = \Page::getCurrentPage();
        $al = Section::getBySectionOfSite($c);
        $langpath = '';
        if ($al !== null) {
            $langpath =  $al->getCollectionHandle();
        }
        $this->set('langpath', $langpath);
        $this->set('app', $this->app);
        $this->set('locale', Localization::activeLocale());
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
        $args['showSortOption'] = isset($args['showSortOption']) ? 1 : 0;
        $args['showName'] = isset($args['showName']) ? 1 : 0;
        $args['showPrice'] = isset($args['showPrice']) ? 1 : 0;
        $args['showQuantity'] = isset($args['showQuantity']) ? 1 : 0;
        $args['showAddToCart'] = isset($args['showAddToCart']) ? 1 : 0;
        $args['showLink'] = isset($args['showLink']) ? 1 : 0;
        $args['showButton'] = isset($args['showButton']) ? 1 : 0;
        $args['truncateEnabled'] = isset($args['truncateEnabled']) ? 1 : 0;
        $args['showPagination'] = isset($args['showPagination']) ? 1 : 0;
        $args['enableExternalFiltering'] = isset($args['enableExternalFiltering']) ? 1 : 0;
        $args['showFeatured'] = isset($args['showFeatured']) ? 1 : 0;
        $args['showSale'] = isset($args['showSale']) ? 1 : 0;
        $args['maxProducts'] = (isset($args['maxProducts']) && $args['maxProducts'] > 0) ? $args['maxProducts'] : 0;
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

        if ($args['maxProducts'] && !$nh->isInteger($args['maxProducts'])) {
            $e->add(t('Number of Products must be a whole number'));
        }

        return $e;
    }
}
