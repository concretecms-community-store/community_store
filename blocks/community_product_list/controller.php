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
use Concrete\Package\CommunityStore\Src\Attribute\Key\StoreProductKey;
use Concrete\Package\CommunityStore\Src\CommunityStore\Group\Group as StoreGroup;
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
        $this->set('groupfilters', $this->getGroupFilters());
        $this->set("attributeList", $this->getAttributeKeyValueList());
        $this->set('attributefilters',$this->getAttributeFilters());
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

        $this->set("attributeList", $this->getAttributeKeyValueList());
        $this->set('attributefilters',$this->getAttributeFilters());

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

        //group filter
        if(!empty($this->get('group-filter'))){
          $products->setGroupIDs($this->get('group-filter'));
          $filters['group-filter'] = $this->get('group-filter');
        }else {
          //$products->setGroupIDs($this->getGroupFilters());
          $filters['group-filter'] = Array();
        }

        //set group list
        if(!empty($this->getGroupFilters())){
          $grouplist = Array();
          foreach($this->getGroupFilters() as $groupID){
            $grouplist[] = StoreGroup::getByID($groupID);
          }
          $this->set('grouplist', $grouplist);
        }

        //attribute filter
        if(!empty($this->get('attribute-filter'))){
          $attributeIDs = Array();
          $products->setAttributeVals($this->get('attribute-filter'));
          $filters['attribute-filter'] = $this->get('attribute-filter');
        }else {
          $filters['attribute-filter'] = Array();
        }

        if(!empty($this->getAttributeFilters())){
          $akvList = $this->getAttributeKeyValueList($this->getAttributeFilters());
          $this->set('akvList',$akvList);
          foreach($akvList as $id => $akv){
            if (is_array($akv['values']) || is_object($akv['values']))            {
              $ak = StoreProductKey::getByID($id);
              $type = $ak->getAttributeType();
              $atHandle  = $type->getAttributeTypeHandle();
              if ($atHandle == "number" && $ak->getEnableNumericSlider($id)){
                $minVar = "min".$akv['name'];
                $maxVar = "max".$akv['name'];
                //setting minimum and maximum range of var
                $maxMinVar = $this->getMaxMinVar($akv['values']);
                $this->set($maxVar, $maxMinVar['max']);
                $this->set($minVar, $maxMinVar['min']);
              }
            }
          }
        }

        //attribute-range
        if(!empty($this->get('attribute-range'))){
          $products->setAttributeRange($this->get('attribute-range'));
          foreach($this->get('attribute-range') as $akID => $vals){
            $tempAk = StoreProductKey::getByID($akID);
            $minVar = "min".$tempAk->getAttributeKeyName();
            $maxVar = "max".$tempAk->getAttributeKeyName();
            $filters[$minVar] = $vals['min'];
            $filters[$maxVar] = $vals['max'];
          }
          $filters['attribute-range'] = $this->get('attribute-range');
        }else {
          $filters['attribute-range'] = Array();
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

        //length filter
        if($this->get('minlength-filter')){
          $filters['minLength'] = $this->get('minlength-filter');
          $products->setMinLength($this->get('minlength-filter'));
        }
        if($this->get('maxlength-filter')){
          $filters['maxLength'] = $this->get('maxlength-filter');
          $products->setMaxLength($this->get('maxlength-filter'));
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
        //setting minimum and maximum range of length
        $maxMinLength = $this->getMaxMinLength();
        $this->set('maxLength', $maxMinLength['max']);
        $this->set('minLength', $maxMinLength['min']);



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

        $args['showWidthFilter'] = isset($args['showWidthFilter']) ? 1 : 0;
        $args['showHeightFilter'] = isset($args['showHeightFilter']) ? 1 : 0;
        $args['showLengthFilter'] = isset($args['showLengthFilter']) ? 1 : 0;
        $args['showPriceFilter'] = isset($args['showPriceFilter']) ? 1 : 0;
        $args['showKeywordFilter'] = isset($args['showKeywordFilter']) ? 1 : 0;

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
        //insert attributes
        $filterattributes = $args['filterattributes'];
        unset($args['filterattributes']);
        $db = \Database::connection();
        $vals = array($this->bID);
        $db->query("DELETE FROM btCommunityStoreProductListAttributes where bID = ?", $vals);

        if (!empty($filterattributes)) {
            foreach ($filterattributes as $akID) {
                $vals = array($this->bID, (int) $akID);
                $db->query("INSERT INTO btCommunityStoreProductListAttributes (bID,akID) VALUES (?,?)", $vals);
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

    public function getMaxMinLength(){
      $db = \Database::connection();
      $r = $db->query("SELECT MAX(pLength) as 'max', MIN(pLength) as 'min' FROM CommunityStoreProducts");
      $result = $r->fetchRow();
      return $result;
    }

    public function getAttributeKeyValueList($akIDs = array()){
      $list = StoreProductKey::getAttributeKeyValueList($akIDs);
      return $list;
    }

    public function getAttributeFilters()
    {
        $db = \Database::connection();
        $result = $db->query("SELECT akID FROM btCommunityStoreProductListAttributes where bID = ?", array($this->bID));

        $list = array();

        if ($result) {
            foreach ($result as $ak) {
                $list[] = $ak['akID'];
            }
        }
        return $list;
    }

    public function getMaxMinVar($akvValues){
      $max = max($akvValues);
      $min = min($akvValues);
      return array('max' => $max, 'min' => $min);
    }
}
