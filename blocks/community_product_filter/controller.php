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
        $result = $db->query("SELECT akID, `order`, `type`, matchingType, invalidHiding, label FROM btCommunityStoreProductFilterAttributes where bID = ? order by `order` asc", [$this->bID])->fetchAll();

        return $result;
    }

    public function view()
    {
        $request = \Request::getInstance();

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

        $selectedAttributeList = $this->getAttributes();


        $attrList = array();
        $optionList = array();
        $attrFilterTypes = array();

        $count = 0;

        foreach($selectedAttributeList as $attr) {
            if ($attr['type'] == 'attr') {
                $attributeKey = $productCategory->getByID($attr['akID']);

                if ($attributeKey) {
                    $attrList[] = $attributeKey;
                    $handle = $attributeKey->getAttributeKeyHandle();
                    $selectedAttributeList[$count]['handle'] = $handle;
                    $attrFilterTypes[$handle] = $attr;
                }
            }
            $count++;
        }

        foreach($attrList as $attitem) {
            $handle = $attitem->getAttributeKeyHandle();
            $attrLookup[$handle] = $attitem;

            $params = $request->get($handle);

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

        $unfilteredIDs = $products->getResultIDs();

        $attributemapping = array();

        $fieldnames = array_keys($attrLookup);
        $fieldnamesak = array_map(function ($str) { return 'ak_' . $str; }, $fieldnames);

        $db = \Database::connection();
        if (!empty($unfilteredIDs) && !empty($fieldnamesak)) {
            $attributedata = $db->fetchAll('SELECT ' . implode(',', $fieldnamesak) . ' FROM CommunityStoreProductSearchIndexAttributes WHERE pID in (' . implode(',', $unfilteredIDs) . ')');

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

                    $attributedata = array();

                    if ($afterfilterids) {
                        $attributedata = $db->fetchAll('SELECT * FROM CommunityStoreProductSearchIndexAttributes WHERE pID in (' . implode(',', $afterfilterids) . ')');
                    }

                    foreach($attributemapping as $handle=> $values) {
                        foreach($values as $k2=>$val2) {

                            // if we only have one filter in place, don't reset the counts for that set of options
                            //if (! (count($selectedarray) == 1 && isset($selectedarray[$handle])  && $attrFilterTypes[$handle]['matchingType'] == 'or' ) ) {
                                $attributemapping[$handle][$k2] = 0;
                            //}
                        }
                    }

                    foreach ($attributedata as $atdata) {
                        foreach ($atdata as $handle => $data) {

                            if ($handle != 'pID') {
                                $items = explode("\n", trim($data));
                                $handle = substr($handle, 3);

                                foreach ($items as $item) {
                                    $item = trim($item);
                                    if ($item) {
                                        // if we only have one filter in place, don't reset the counts for that set of options
                                       // if (!(count($selectedarray) == 1 && isset($selectedarray[$handle]) && $attrFilterTypes[$handle]['matchingType'] == 'or') && $attrLookup[$handle] ) {
                                            $attributemapping[$handle][$item] += 1;
                                        //}
                                    }
                                }
                            }
                        }
                    }

                }
            }
        }

        // final loop to create a selection list that includes non-attribute options (like price)
        $finalData = array();

        $hasprice = false;



        foreach($selectedAttributeList as $att) {
            $handle = $att['handle'];

            if ($att['type'] == 'attr') {
                if (isset($attributemapping[$handle])) {
                    $finalData[$handle] = array('type'=>'attr', 'data'=>$attributemapping[$handle], 'label'=>$att['label']);
                }
            } else {
                $finalData[$att['type']] = array('type'=>$att['type'], 'data'=>false, 'label'=>$att['label']);

                if ($att['type'] == 'price') {
                    $hasprice = true;
                }
            }
        }


        $minPriceSelected = '';
        $maxPriceSelected = '';
        $minPrice = '';
        $maxPrice = '';


        if ($hasprice) {
            $minmax = $db->fetchAll('SELECT MIN(pPrice) as min_price, MAX(pPrice) as max_price 
                                            FROM CommunityStoreProducts 
                                            WHERE pID in (' . implode(',', $unfilteredIDs) . ')
                                            AND pPrice > 0
                                            ');

            $minPrice = $minmax[0]['min_price'];
            $maxPrice = $minmax[0]['max_price'];

            $minPriceSelected = $minPrice;
            $maxPriceSelected = $maxPrice;

            $priceparam = $request->get('price');

            $this->set('priceFiltering', (bool)$priceparam);


            if ($priceparam) {
                $price = explode('-', $priceparam);
                if (count($price) > 1) {
                    $minPriceSelected = max($price[0],$minPriceSelected) ;
                    $maxPriceSelected = min($price[1], $maxPriceSelected);
                } else {
                    $maxPriceSelected = min($price[0], $maxPriceSelected);
                }
            }
        }

        $this->set('filterData', $finalData);
        $this->set('selectedAttributes', $selectedarray);
        $this->set('minPriceSelected', $minPriceSelected);
        $this->set('maxPriceSelected', $maxPriceSelected);
        $this->set('minPrice', $minPrice);
        $this->set('maxPrice', $maxPrice);
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
        $args['displayClear'] = isset($args['displayClear']) ? 1 : 0;
        $args['showTotals'] = isset($args['showTotals']) ? 1 : 0;
        $args['jumpAnchor'] = isset($args['jumpAnchor']) ? 1 : 0;

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
        $labels = $args['labels'];
        $types = $args['types'];

        //insert attribute selection
        $count = 0;
        if (!empty($attributes)) {
            foreach ($attributes as $attributesid) {
                $vals = [$this->bID, (int)$attributesid, $count, $types[$count] , $matchingTypes[$count], $invalidHidings[$count],  $labels[$count]];
                $db->query("INSERT INTO btCommunityStoreProductFilterAttributes (bID, akID,`order`, `type`, matchingType,invalidHiding, label) VALUES (?,?,?,?,?,?,?)", $vals);
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
