<?php
namespace Concrete\Package\CommunityStore\Block\CommunityProductFilter;

use Concrete\Core\Page\Page;
use Concrete\Core\Http\Request;
use Concrete\Core\Block\BlockController;
use Concrete\Core\Support\Facade\Database;
use Concrete\Package\CommunityStore\Attribute\ProductKey;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product;
use Concrete\Package\CommunityStore\Src\CommunityStore\Group\GroupList;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductList;
use Concrete\Package\CommunityStore\Src\CommunityStore\Manufacturer\ManufacturerList;

class Controller extends BlockController
{
    protected $btTable = 'btCommunityStoreProductFilter';
    protected $btInterfaceWidth = "800";
    protected $btWrapperClass = 'ccm-ui';
    protected $btInterfaceHeight = "600";
    protected $btDefaultSet = 'community_store';
    protected $attFilters = [];
    protected $attTypes = ['select', 'text', 'boolean'];
    protected $groupMatchAny = '';
    protected $filterManufacturer = '';

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
        $this->set('manufacturersList', ManufacturerList::getManufacturerList());
        $this->set('attributes', $this->getAvailableAttributes());
        $this->set('app', $this->app);
        $this->set('filterSource', '');
        $this->set('filterManufacturer', false);
        $this->set('filter', '');
        $this->set('filterCID', '');
        $this->set('showFeatured', false);
        $this->set('showSale', false);
        $this->set('showOutOfStock', false);
        $this->set('showTotals', false);
        $this->set('updateType', '');
        $this->set('filterButtonText', '');
        $this->set('displayClear', false);
        $this->set('clearButtonText', '');
        $this->set('jumpAnchor', '');
        $this->set('relatedProduct', false);
        $this->set('relatedPID', false);
    }

    public function edit()
    {
        $this->requireAsset('css', 'select2');
        $this->requireAsset('javascript', 'select2');
        $this->getGroupList();
        $this->set('groupfilters', $this->getGroupFilters());
        $this->set('manufacturersList', ManufacturerList::getManufacturerList());
        $this->set('attributes', $this->getAvailableAttributes());
        $this->set('app', $this->app);

        $this->set('selectedAttributes', $this->getAttributes());

        if ($this->relatedPID) {
            $relatedProduct = Product::getByID($this->relatedPID);
            $this->set('relatedProduct', $relatedProduct);
        } else {
            $this->set('relatedProduct', false);
        }
    }

    private function getAvailableAttributes()
    {
        $attrList = ProductKey::getList();
        $availableAtts = [];

        foreach ($attrList as $ak) {
            if (in_array($ak->getAttributeType()->getAttributeTypeHandle(), $this->attTypes)) {
                $availableAtts[] = $ak;
            }
        }

        return $availableAtts;
    }

    public function getGroupFilters()
    {
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

    public function getGroupList()
    {
        $grouplist = GroupList::getGroupList();
        $this->set("grouplist", $grouplist);
    }

    public function getAttributes()
    {
        $db = $this->app->make('database')->connection();
        $result = $db->query("SELECT akID, `order`, `type`, matchingType, invalidHiding, label FROM btCommunityStoreProductFilterAttributes where bID = ? order by `order` asc", [$this->bID])->fetchAll();

        return $result;
    }

    public function view()
    {
        $request = $this->app->make(Request::class);

        $attrLookup = [];
        $selectedarray = [];
        $groupfilters = [];
        $this->set('manufacturersList', ManufacturerList::getManufacturerList());
        if ('auto' == $this->filterSource) {
            $page = Page::getCurrentPage();
            $blocks = $page->getBlocks();
            $block = null;
            $groupfilters = $this->getGroupFilters();
            foreach ($blocks as $block) {
                if ('community_product_list' == $block->getBlockTypeHandle()) {
                    $blockcontroller = $block->getController();
                    $this->filter = $blockcontroller->filter;
                    $this->filterCID = $blockcontroller->filterCID;
                    $this->relatedPID = $blockcontroller->relatedPID;
                    $this->showFeatured = $blockcontroller->showFeatured;
                    $this->showSale = $blockcontroller->showSale;
                    $this->showOutOfStock = $blockcontroller->showOutOfStock;
                    $this->groupMatchAny = $blockcontroller->groupMatchAny;
                    $groupfilters = $blockcontroller->getGroupFilters();
                    $this->filterManufacturer = $blockcontroller->filterManufacturer;
                    break;
                }
            }
        }

        $selectedAttributeList = $this->getAttributes();

        $attrList = [];
        $optionList = [];
        $attrFilterTypes = [];

        $count = 0;

        foreach ($selectedAttributeList as $attr) {
            if ('attr' == $attr['type']) {
                $attributeKey = ProductKey::getByID($attr['akID']);

                if ($attributeKey) {
                    $attrList[] = $attributeKey;
                    $handle = $attributeKey->getAttributeKeyHandle();
                    $selectedAttributeList[$count]['handle'] = $handle;
                    $attrFilterTypes[$handle] = $attr;
                }
            }
            $count++;
        }

        foreach ($attrList as $attitem) {
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

        $products = new ProductList();

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

        if ($this->groupMatchAny === '-1') {
            $products->setGroupNoMatchAny(true);
        } else {
            $products->setGroupMatchAny($this->groupMatchAny);
        }

        $products->setManufacturer($this->filterManufacturer);

        $unfilteredIDs = $products->getResultIDs();

        $attributemapping = [];

        $fieldnames = array_keys($attrLookup);
        $fieldnamesak = array_map(function ($str) { return 'ak_' . $str; }, $fieldnames);

        $db = Database::connection();
        if (!empty($unfilteredIDs) && !empty($fieldnamesak)) {
            $attributedata = $db->fetchAll('SELECT ' . implode(',', $fieldnamesak) . ' FROM CommunityStoreProductSearchIndexAttributes WHERE pID in (' . implode(',', $unfilteredIDs) . ')');

            if (!empty($attributedata)) {
                foreach ($attributedata as $atdata) {
                    foreach ($atdata as $handle => $data) {
                        if ('pID' != $handle) {
                            $items = explode("\n", trim($data));
                            $handle = substr($handle, 3);

                            foreach ($items as $item) {
                                $item = trim($item);
                                if ($item && isset($attrLookup[$handle]) && isset($attrLookup[$handle])) {
                                    if (isset($attributemapping[$handle][$item])) {
                                        $attributemapping[$handle][$item]++;
                                    } else {
                                        $attributemapping[$handle][$item] = 1;
                                    }
                                }
                            }
                        }
                    }
                }

                foreach ($attributemapping as $attrhandle => $values) {
                    ksort($attributemapping[$attrhandle]);
                }
            }

            $hasfilters = false;

            if (!empty($attributemapping)) {
                //  second pass to work out what attribute values are actually available
                $request = $this->app->make(Request::class);

                if ($request->getQueryString()) {
                    $products->processUrlFilters($request);
                    $hasfilters = true;
                }

                if ($hasfilters) {
                    $afterfilterids = $products->getResultIDs();

                    $attributedata = [];

                    if ($afterfilterids) {
                        $attributedata = $db->fetchAll('SELECT * FROM CommunityStoreProductSearchIndexAttributes WHERE pID in (' . implode(',', $afterfilterids) . ')');
                    }

                    foreach ($attributemapping as $handle => $values) {
                        foreach ($values as $k2 => $val2) {
                            $attributemapping[$handle][$k2] = 0;
                        }
                    }

                    foreach ($attributedata as $atdata) {
                        foreach ($atdata as $handle => $data) {
                            if ('pID' != $handle) {
                                $items = explode("\n", trim($data));
                                $handle = substr($handle, 3);

                                foreach ($items as $item) {
                                    $item = trim($item);
                                    if ($item) {
                                        if (isset($attributemapping[$handle][$item])) {
                                            $attributemapping[$handle][$item]++;
                                        } else {
                                            $attributemapping[$handle][$item] = 1;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        // final loop to create a selection list that includes non-attribute options (like price)
        $finalData = [];

        $hasprice = false;

        foreach ($selectedAttributeList as $att) {
            if (isset($att['handle'])) {

                $handle = $att['handle'];

                if ('attr' == $att['type']) {
                    if (isset($attributemapping[$handle])) {
                        $finalData[$handle] = ['type' => 'attr', 'data' => $attributemapping[$handle], 'label' => $att['label']];
                    }
                } else {
                    $finalData[$att['type']] = ['type' => $att['type'], 'data' => false, 'label' => $att['label']];

                    if ('price' == $att['type']) {
                        $hasprice = true;
                    }
                }
            }
        }

        $minPriceSelected = '';
        $maxPriceSelected = '';
        $minPrice = '';
        $maxPrice = '';

        $this->set('priceFiltering', false);

        if ($hasprice) {

            if (count($unfilteredIDs) > 0) {
                $minmax = $db->fetchAll('SELECT MIN(pPrice) as min_price, MAX(pPrice) as max_price
                                            FROM CommunityStoreProducts
                                            WHERE pID in (' . implode(',', $unfilteredIDs) . ')
                                            AND pPrice > 0
                                            ');
                $minPrice = $minmax[0]['min_price'];
                $maxPrice = $minmax[0]['max_price'];
            }


            $minPriceSelected = $minPrice;
            $maxPriceSelected = $maxPrice;

            $priceparam = $request->get('price');

            $this->set('priceFiltering', (bool) $priceparam);

            if ($priceparam) {
                $price = explode('-', $priceparam);
                if (count($price) > 1) {
                    $minPriceSelected = max($price[0], $minPriceSelected);
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
        $this->set('app', $this->app);
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

        $filtergroups = [];

        if (isset($args['filtergroups'])) {
            $filtergroups = $args['filtergroups'];
        }

        unset($args['filtergroups']);

        $db = $this->app->make('database')->connection();
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

        $attributes = [];

        if (isset($args['attributes'])) {
            $attributes = $args['attributes'];
        }

        $matchingTypes = [];

        if (isset($args['matchingType'])) {
            $matchingTypes = $args['matchingType'];
        }

        $invalidHidings = [];

        if (isset($args['invalidHiding'])) {
            $invalidHidings = $args['invalidHiding'];
        }

        $labels = [];

        if (isset($args['labels'])) {
            $labels = $args['labels'];
        }

        $types = [];

        if (isset($args['types'])) {
            $types = $args['types'];
        }

        //insert attribute selection
        $count = 0;
        if (!empty($attributes)) {
            foreach ($attributes as $attributesid) {
                $vals = [$this->bID, (int) $attributesid, $count, $types[$count], (isset($matchingTypes[$count]) ? $matchingTypes[$count] : ''), (isset($invalidHidings[$count]) ? $invalidHidings[$count] : '' ) ,  $labels[$count]];
                $db->query("INSERT INTO btCommunityStoreProductFilterAttributes (bID, akID,`order`, `type`, matchingType,invalidHiding, label) VALUES (?,?,?,?,?,?,?)", $vals);
                $count++;
            }
        }

        parent::save($args);
    }

    public function validate($args)
    {
        $e = $this->app->make("helper/validation/error");
        $nh = $this->app->make("helper/number");

        if (('page' == $args['filter'] || 'page_children' == $args['filter']) && $args['filterCID'] <= 0) {
            $e->add(t('A page must be selected'));
        }

        return $e;
    }
}
