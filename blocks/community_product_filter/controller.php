<?php
namespace Concrete\Package\CommunityStore\Block\CommunityProductFilter;

use Concrete\Core\Block\BlockController;
use Concrete\Package\CommunityStore\Entity\Attribute\Key\StoreProductKey;
use Core;
use Config;
use Page;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product as StoreProduct;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductList as StoreProductList;
use Concrete\Package\CommunityStore\Src\CommunityStore\Group\GroupList as StoreGroupList;

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

        $productCategory = $this->app->make('Concrete\Package\CommunityStore\Attribute\Category\ProductCategory');
        $attrList = $productCategory->getList();

        $this->set('attributes', $attrList);
    }

    public function edit()
    {
        $this->requireAsset('css', 'select2');
        $this->requireAsset('javascript', 'select2');
        $this->getGroupList();
        $this->set('groupfilters', $this->getGroupFilters());

        $productCategory = $this->app->make('Concrete\Package\CommunityStore\Attribute\Category\ProductCategory');
        $attrList = $productCategory->getList();

        $this->set('attributes', $attrList);

        $this->set('selectedAttributes', $this->getAttributes());

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

    public function getAttributes()
    {
        $app = \Concrete\Core\Support\Facade\Application::getFacadeApplication();
        $db = $app->make('database')->connection();
        $result = $db->query("SELECT akID, `order`, matchingType, invalidHiding FROM btCommunityStoreProductFilterAttributes where bID = ? order by `order` asc", [$this->bID])->fetchAll();

        return $result;
    }

    public function view()
    {
        $productCategory = $this->app->make('Concrete\Package\CommunityStore\Attribute\Category\ProductCategory');
        $attrList = $productCategory->getList();
        $attrLookup = array();
        $selectedarray = array();

        if ($this->filterSource == 'auto') {
            $page = \Page::getCurrentPage();
            $blocks = $page->getBlocks();
            $block = null;
            $groupfilters = $this->getGroupFilters();
            foreach ($blocks as $block) {
                if ($block->getBlockTypeHandle() == 'community_product_list') {
                    $blockcontroller = $block->getController();
                    $this->filter = $blockcontroller->filter;
                    $this->filterCID = $blockcontroller->filterCID;
                    $this->relatedPID = $blockcontroller->relatedPID;
                    $this->showFeatured = $blockcontroller->showFeatured;
                    $this->showSale = $blockcontroller->showSale;
                    $this->showOutOfStock = $blockcontroller->showOutOfStock;
                    $this->groupMatchAny = $blockcontroller->groupMatchAny;
                    $groupfilters = $blockcontroller->getGroupFilters();

                    break;
                }
            }
        }

        $selecteAttributeList = $this->getAttributes();

        $attrList = array();
        $attrFilterTypes = array();

        foreach($selecteAttributeList as $attr) {
            $attributeKey = $productCategory->getByID($attr['akID']);

            if ($attributeKey) {
                $attrList[] = $attributeKey;
                $attrFilterTypes[ $attributeKey->getAttributeKeyHandle()] = $attr;
            }
        }

        foreach($attrList as $attitem) {
            $handle = $attitem->getAttributeKeyHandle();
            $attrLookup[$handle] = $attitem;

            $params = $_GET[$handle];

            if (!is_array($params)) {
                $params = str_replace(';', '|', $params);
                $params = explode('|', $params);
            }

            $params = array_filter($params);

            if (isset($attrLookup[$handle]) && !empty($params)) {
                $selectedarray[$handle] = $params;
            }
        }


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

        $products->setGroupIDs($groupfilters);
        $products->setFeaturedOnly($this->showFeatured);
        $products->setSaleOnly($this->showSale);
        $products->setShowOutOfStock($this->showOutOfStock);
        $products->setGroupMatchAny($this->groupMatchAny);

        $values = $products->getResultIDs();
        $attributemapping = array();

        $fieldnames = array_keys($attrLookup);
        $fieldnames = array_map(function ($str) { return 'ak_' . $str; }, $fieldnames);

        if (!empty($values) && !empty($fieldnames)) {
            $db = \Database::connection();


            $attributedata = $db->fetchAll('SELECT ' . implode(',', $fieldnames) . ' FROM CommunityStoreProductSearchIndexAttributes WHERE pID in (' . implode(',', $values) . ')');

            if (!empty($attributedata)) {
                foreach ($attributedata as $atdata) {
                    foreach ($atdata as $handle => $data) {

                        if ($handle != 'pID') {
                            $items = explode("\n", trim($data));
                            $handle = substr($handle, 3);

                            foreach ($items as $item) {
                                $item = trim($item);
                                if ($item && isset($attrLookup[$handle]) && isset($attrLookup[$handle])) {
                                    $attributemapping[$handle][$item] += 1;
                                }
                            }
                        }
                    }
                }

                foreach ($attributemapping as $attrhandle => $values) {
                    ksort($attributemapping[$attrhandle]);
                }
            }


            if (!empty($attributemapping)) {
                //  second pass to work out what attribute values are actually available
                $request = \Request::getInstance();

                if ($request->getQueryString()) {
                    $products->processUrlFilters($request);
                    $hasfilters = true;
                }

                if ($hasfilters) {
                    $afterfilterids = $products->getResultIDs();

                    if ($afterfilterids) {
                        $attributedata = $db->fetchAll('SELECT * FROM CommunityStoreProductSearchIndexAttributes WHERE pID in (' . implode(',', $afterfilterids) . ')');

                        foreach($attributemapping as $key=> $values) {
                            foreach($values as $k2=>$val2) {
                                // if we only have one filter in place, don't reset the counts for that set of options
                                if (! (count($selectedarray) == 1 && isset($selectedarray[$key])) ) {
                                    $attributemapping[$key][$k2] = 0;
                                }
                            }
                        }

                        foreach ($attributedata as $atdata) {
                            foreach ($atdata as $handle => $data) {

                                if ($handle != 'pID') {
                                    $items = explode("\n", trim($data));
                                    $handle = substr($handle, 3);

                                    foreach ($items as $item) {
                                        $item = trim($item);
                                        // if we only have one filter in place, don't reset the counts for that set of options
                                        if (! (count($selectedarray) == 1 && isset($selectedarray[$handle])) && $attrLookup[$handle]) {
                                            $attributemapping[$handle][$item] += 1;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }



        $this->set('filterData', $attributemapping);
        $this->set('selectedAttributes', $selectedarray);
        $this->set('attributes', $attrLookup);
        $this->set('attrFilterTypes', $attrFilterTypes);
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

        $vals = [$this->bID];
        $db->query("DELETE FROM btCommunityStoreProductFilterAttributes where bID = ?", $vals);

        $attributes = $args['attributes'];
        $matchingTypes = $args['matchingType'];
        $invalidHidings = $args['invalidHiding'];

        //insert attribute selection
        $count = 0;
        if (!empty($attributes)) {
            foreach ($attributes as $attributesid) {
                $vals = [$this->bID, (int)$attributesid, $count, $matchingTypes[$count], $invalidHidings[$count]];
                $db->query("INSERT INTO btCommunityStoreProductFilterAttributes (bID, akID,`order`,matchingType,invalidHiding) VALUES (?,?,?,?,?)", $vals);
                $count++;
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
