<?php
namespace Concrete\Package\CommunityStore\Block\CommunityProductList;

use Concrete\Core\Block\BlockController;
use Core;
use Config;
use Page;
use Database;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductList as StoreProductList;
use Concrete\Package\CommunityStore\Src\CommunityStore\Group\GroupList as StoreGroupList;
use Concrete\Package\CommunityStore\Src\Attribute\Key\StoreProductKey;
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
    }

    public function getGroupFilters()
    {
        $db = \Database::connection();
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

        $filters = Array();

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

        $products->setItemsPerPage($this->maxProducts > 0 ? $this->maxProducts : 1000);
        //group filter
        if(!empty($this->get('group-filter'))){
          $groupIDs = Array();
          $products->setGroupIDs($this->get('group-filter'));
          $filters['group-filter'] = $this->get('group-filter');
        }else {
          $products->setGroupIDs($this->getGroupFilters());
          $filters['group-filter'] = Array();
        }
        //keyword filter
        if($this->get('keywords')){
          $products->setSearch($this->get('keywords'));
          $products->setAttributeSearch($this->get('keywords'));
          $filters['keywords'] = $this->get('keywords');
        }
        //price filter
        if($this->get('minprice-filter')){
          $filters['minPrice'] = $this->get('minprice-filter');
          $products->setMinPrice($this->get('minprice-filter'));
        }
        if($this->get('maxprice-filter')){
          $filters['maxPrice'] = $this->get('maxprice-filter');
          $products->setMaxPrice($this->get('maxprice-filter'));
        }
        //width filter
        if($this->get('minwidth-filter')){
          $filters['minWidth'] = $this->get('minwidth-filter');
          $products->setMinWidth($this->get('minwidth-filter'));
        }
        if($this->get('maxwidth-filter')){
          $filters['maxWidth'] = $this->get('maxwidth-filter');
          $products->setMaxWidth($this->get('maxwidth-filter'));
        }
        //height filter
        if($this->get('minheight-filter')){
          $filters['minHeight'] = $this->get('minheight-filter');
          $products->setMinHeight($this->get('minheight-filter'));
        }
        if($this->get('maxheight-filter')){
          $filters['maxHeight'] = $this->get('maxheight-filter');
          $products->setMaxHeight($this->get('maxheight-filter'));
        }
        //attribute filter
        if(!empty($this->get('attribute-filter'))){
          $attributeIDs = Array();
          $products->setAttributeVals($this->get('attribute-filter'));
          $filters['attribute-filter'] = $this->get('attribute-filter');
        }else {
          $filters['attribute-filter'] = Array();
        }


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
        //set group list
        $grouplist = StoreGroupList::getGroupList();
        $this->set('grouplist', $grouplist);
        //set filters
        $this->set('filters', $filters);
        //setting minimum and maximum range of price range slider
        $maxMinPrices = $this->getMaxMinPrice();
        $this->set('maxPrice', $maxMinPrices['max']);
        $this->set('minPrice', $maxMinPrices['min']);
        //setting minimum and maximum range of width
        $maxMinWidth = $this->getMaxMinWidth();
        $this->set('maxWidth', $maxMinWidth['max']);
        $this->set('minWidth', $maxMinWidth['min']);
        //setting minimum and maximum range of height
        $maxMinHeight = $this->getMaxMinHeight();
        $this->set('maxHeight', $maxMinHeight['max']);
        $this->set('minHeight', $maxMinHeight['min']);

        $this->set('akvList',$this->getAttributeKeyValueList());
        $symbol = Config::get('community_store.symbol');

        $this->set('symbol', $symbol);

    }
    public function registerViewAssets($outputContent = '')
    {
        $this->requireAsset('javascript', 'jquery');
        $js = \Concrete\Package\CommunityStore\Controller::returnHeaderJS();
        $this->addFooterItem($js);
        $this->requireAsset('javascript', 'community-store');
        $this->requireAsset('css', 'community-store');
        $this->requireAsset('jquery/ui');
    }
    public function save($args)
    {
        $args['showOutOfStock'] = isset($args['showOutOfStock']) ? 1 : 0;
        $args['showDescription'] = isset($args['showDescription']) ? 1 : 0;
        $args['showQuickViewLink'] = isset($args['showQuickViewLink']) ? 1 : 0;
        $args['showPageLink'] = isset($args['showPageLink']) ? 1 : 0;
        $args['showPrice'] = isset($args['showPrice']) ? 1 : 0;
        $args['showAddToCart'] = isset($args['showAddToCart']) ? 1 : 0;
        $args['showLink'] = isset($args['showLink']) ? 1 : 0;
        $args['showButton'] = isset($args['showButton']) ? 1 : 0;
        $args['truncateEnabled'] = isset($args['truncateEnabled']) ? 1 : 0;
        $args['showPagination'] = isset($args['showPagination']) ? 1 : 0;
        $args['showFeatured'] = isset($args['showFeatured']) ? 1 : 0;
        $args['showSale'] = isset($args['showSale']) ? 1 : 0;
        $args['maxProducts'] = (isset($args['maxProducts']) && $args['maxProducts'] > 0) ? $args['maxProducts'] : 0;

        $filtergroups = $args['filtergroups'];
        unset($args['filtergroups']);

        $db = \Database::connection();
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

    public function getMaxMinPrice(){
      $db = \Database::connection();
      $r = $db->query("SELECT MAX(pPrice) as 'maxPPrice', MIN(pPrice) as 'minPPrice', MAX(pSalePrice) as 'maxSalePrice', MIN(pSalePrice) as 'minSalePrice' FROM CommunityStoreProducts");
      $result = $r->fetchRow();

      if($result['maxPPrice'] > $result['maxSalePrice']){
        $maxPrice = $result['maxPPrice'];
      } else {
        $maxPrice = $result['maxSalePrice'];
      }
      if($result['minSalePrice']!=null && $result['minPPrice']!=null){
        $minPrice = $result['minSalePrice'] < $result['minPPrice'] ? $result['minSalePrice'] :  $result['minPPrice'];
      }else if($result['minSalePrice']==null){
        $minPrice = $result['minPPrice'];
      }else if($result['minPPrice']==null){
        $minPrice = $result['minSalePrice'];
      }else {
        $minPrice = 0;
      }
      $maxMinPrices['max'] = $maxPrice;
      $maxMinPrices['min'] = $minPrice;
      return($maxMinPrices);
    }
    public function getMaxMinWidth(){
      $db = \Database::connection();
      $r = $db->query("SELECT MAX(pWidth) as 'max', MIN(pWidth) as 'min' FROM CommunityStoreProducts");
      $result = $r->fetchRow();
      return $result;
    }

    public function getMaxMinHeight(){
      $db = \Database::connection();
      $r = $db->query("SELECT MAX(pHeight) as 'max', MIN(pHeight) as 'min' FROM CommunityStoreProducts");
      $result = $r->fetchRow();
      return $result;
    }

    public function getAttributeKeyValueList(){
      $list = StoreProductKey::getAttributeKeyValueList();
      return $list;
    }
}
