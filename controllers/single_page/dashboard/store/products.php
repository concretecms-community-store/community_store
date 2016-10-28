<?php

namespace Concrete\Package\CommunityStore\Controller\SinglePage\Dashboard\Store;

use \Concrete\Core\Page\Controller\DashboardPageController;
use Core;
use View;
use FilePermissions;
use TaskPermission;
use File;
use PageType;
use GroupList;
use Request;
use Job;

use \Symfony\Component\HttpFoundation\Session\Session as SymfonySession;

use \Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product as StoreProduct;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductFile as StoreProductFile;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductGroup as StoreProductGroup;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductImage as StoreProductImage;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductList as StoreProductList;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductLocation as StoreProductLocation;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductUserGroup as StoreProductUserGroup;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductVariation\ProductVariation as StoreProductVariation;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductOption\ProductOption as StoreProductOption;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Group\Group as StoreGroup;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Group\GroupList as StoreGroupList;
use \Concrete\Package\CommunityStore\Src\Attribute\Key\StoreProductKey;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Tax\TaxClass as StoreTaxClass;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\ProductImporter;
class Products extends DashboardPageController
{

    public function view($gID=null){
        $products = new StoreProductList();
        $products->setItemsPerPage(20);
        $products->setGroupID($gID);
        $products->setActiveOnly(false);
        $products->setShowOutOfStock(true);


        if ($this->get('ccm_order_by')) {
            $products->setSortBy($this->get('ccm_order_by'));
            $products->setSortByDirection($this->get('ccm_order_by_direction'));
        } else {
            $products->setSortBy('date');
            $products->setSortByDirection('desc');
        }


        if ($this->get('keywords')) {
            $products->setSearch($this->get('keywords'));
        }

        $this->set('productList', $products);
        $paginator = $products->getPagination();
        $pagination = $paginator->renderDefaultView();
        $this->set('products',$paginator->getCurrentPageResults());
        $this->set('pagination',$pagination);
        $this->set('paginator', $paginator);
        $this->requireAsset('css', 'communityStoreDashboard');
        $this->requireAsset('javascript', 'communityStoreFunctions');

        $grouplist = StoreGroupList::getGroupList();
        $this->set("grouplist",$grouplist);

    }
    public function success(){
        $this->set("success",t("Product Added"));
        $this->view();
    }

    public function updated()
    {
        $this->set("success",t("Product Updated"));
        $this->view();
    }
    public function removed(){
        $this->set("success",t("Product Removed"));
        $this->view();
    }
    public function add()
    {
        $this->loadFormAssets();
        $this->set("actionType",t("Add"));

        $grouplist = StoreGroupList::getGroupList();
        $this->set("grouplist",$grouplist);
        foreach($grouplist as $productgroup){
            $productgroups[$productgroup->getGroupID()] = $productgroup->getGroupName();
        }
        $this->set("productgroups",$productgroups);

        $gl = new GroupList();
        $gl->setItemsPerPage(1000);
        $gl->filterByAssignable();
        $usergroups = $gl->get();

        $usergrouparray = array();

        foreach($usergroups as $ug) {
            if ( $ug->gName != 'Administrators') {
                $usergrouparray[$ug->gID] = $ug->gName;
            }
        }

        $this->set('pageTitle', t('Add Product'));
        $this->set('usergroups', $usergrouparray);
    }
    public function edit($pID, $status = '')
    {
        if ($status == 'updated') {
            $this->set("success",t("Product Updated"));
        }

        if ($status == 'added') {
            $this->set("success",t("Product Added"));
        }

        if ($status == 'duplicated') {
            $this->set("success",t("Product Duplicated"));
        }

        $this->loadFormAssets();
        $this->set("actionType",t("Update"));

        //get the product
        $product = StoreProduct::getByID($pID);

        if (!$product) {
            $this->redirect('/dashboard/store/products/');
        }

        $this->set('product',$product);

        $options  = $product->getOptions();

        $variations = $product->getVariations();
        $variationLookup = array();

        $optionArrays = array();
        $optionLookup = array();

        $optionItems = array();

        foreach($options as $opt) {
            $optionLookup[$opt->getID()] = $opt;

            foreach($opt->getOptionItems() as $optItem) {
                $optionArrays[$opt->getID()][] = $optItem->getID();
                $optionItemLookup[$optItem->getID()] = $optItem;
                $optionItems[] = $optItem;
            }
        }

        $this->set('optionItems', $optionItems);
        $this->set('optionLookup', $optionLookup);
        $this->set('optionItemLookup', $optionItemLookup);

        $optionArrays = array_values($optionArrays);

        $comboOptions = StoreProductVariation::combinations($optionArrays);

        $checkedOptions = array();

        foreach($comboOptions as $option) {
            if (!is_array($option)) {
                $checkedOptions[] = array($option);
            } else {
                $checkedOptions[] =$option;
            }
        }

        $comboOptions = $checkedOptions;

        $this->set('comboOptions', $comboOptions);
        $this->set('optionItemLookup', $optionItemLookup);

        foreach($variations as $variation) {
            $options = $variation->getOptions();
            $optionids = array();

            foreach($options as $varoption) {
                $option = $varoption->getOption();

                if ($option) {
                    $optionids[] = $option->getID();
                }
            }

            sort($optionids);
            $variationLookup[implode('_',$optionids)] = $variation;
        }

        $this->set('variations', $variations);
        $this->set('variationLookup', $variationLookup);

        //populate "Groups" select box options
        $grouplist = StoreGroupList::getGroupList();
        foreach($grouplist as $productgroup){
            $productgroups[$productgroup->getGroupID()] = $productgroup->getGroupName();
        }
        $this->set("productgroups",$productgroups);

        $gl = new GroupList();
        $gl->setItemsPerPage(1000);
        $gl->filterByAssignable();
        $usergroups = $gl->get();

        $usergrouparray = array();

        foreach($usergroups as $ug) {
            if ( $ug->gName != 'Administrators') {
                $usergrouparray[$ug->gID] = $ug->gName;
            }
        }

        $this->set('pageTitle', t('Edit Product'));
        $this->set('usergroups', $usergrouparray);
    }


    public function generate($pID,$templateID=null)
    {
        StoreProduct::getByID($pID)->generatePage($templateID);
        $this->redirect('/dashboard/store/products/edit',$pID);
    }
    public function duplicate($pID)
    {
        $product = StoreProduct::getByID($pID);
        if (!$product) {
            $this->redirect('/dashboard/store/products');
        }

        if ($this->post()) {
            $newproduct = $product->duplicate($this->post('newName'), $this->post('newSKU'));
            $this->redirect('/dashboard/store/products/edit/'. $newproduct->getID().'/duplicated');
        }

        $this->set('pageTitle', t('Duplicate Product'));
        $this->set('product', $product);
    }


    public function delete($pID)
    {
        $product = StoreProduct::getByID($pID);
        if ($product) {
            $product->remove();
        }
        $this->redirect('/dashboard/store/products/removed');
    }
    public function loadFormAssets()
    {
        $this->requireAsset('core/file-manager');
        $this->requireAsset('core/sitemap');
        $this->requireAsset('css', 'select2');
        $this->requireAsset('javascript', 'select2');

        $this->set('fp',FilePermissions::getGlobal());
        $this->set('tp', new TaskPermission());
        $this->set('al', Core::make('helper/concrete/asset_library'));

        $this->requireAsset('css', 'communityStoreDashboard');
        $this->requireAsset('javascript', 'communityStoreFunctions');

        $attrList = StoreProductKey::getAttributeKeyValueList();
        $this->set('attribs',$attrList);

        $pageType = PageType::getByHandle("store_product");
        $pageTemplates = $pageType->getPageTypePageTemplateObjects();
        $templates = array();
        foreach($pageTemplates as $pt){
            $templates[$pt->getPageTemplateID()] = $pt->getPageTemplateName();
        }
        $this->set('pageTemplates',$templates);
        $taxClasses = array();
        foreach(StoreTaxClass::getTaxClasses() as $taxClass){
            $taxClasses[$taxClass->getID()] = $taxClass->getTaxClassName();
        }
        $this->set('taxClasses',$taxClasses);
    }
    public function save()
    {
        $data = $this->post();
        if($data['pID']){
            $this->edit($data['pID']);
        } else{
            $this->add();
        }
        if ($this->post()) {
            $errors = $this->validate($data);
            $this->error = null; //clear errors
            $this->error = $errors;
            if (!$errors->has()) {

                //save the product
                $product = StoreProduct::saveProduct($data);
                //save product attributes
                if(!empty($data['akID'])){
                  foreach($data['akID'] as $key => $value){
                    $ak = StoreProductKey::getByID($key);
                    if(!empty($value['value'])){
                      $ak->saveAttribute($product,false,$value['value']);
                    }

                  }
                }

                //save images
                StoreProductImage::addImagesForProduct($data,$product);

                //save product groups
                StoreProductGroup::addGroupsForProduct($data,$product);

                //save product user groups
                StoreProductUserGroup::addUserGroupsForProduct($data,$product);

                //save product options
                StoreProductOption::addProductOptions($data,$product);

                //save files
                StoreProductFile::addFilesForProduct($data,$product);

                //save category locations
                StoreProductLocation::addLocationsForProduct($data,$product);

                // save variations
                StoreProductVariation::addVariations($data, $product);


                if($data['pID']){
                    $this->redirect('/dashboard/store/products/edit/' . $product->getID(), 'updated');
                } else {
                    $this->redirect('/dashboard/store/products/edit/' . $product->getID(), 'added');
                }
            }//if no errors
        }//if post
    }
    public function validate($args)
    {
        $e = Core::make('helper/validation/error');

        if($args['pName']==""){
            $e->add(t('Please enter a Product Name'));
        }
        if(strlen($args['pName']) > 255){
            $e->add(t('The Product Name can not be greater than 255 Characters'));
        }
        if(!is_numeric($args['pPrice'])){
            $e->add(t('The Price must be set, and numeric'));
        }
        if(!is_numeric($args['pQty']) && !$args['pQtyUnlim']){
            $e->add(t('The Quantity must be set, and numeric'));
        }
        if(!is_numeric($args['pWidth'])){
            $e->add(t('The Product Width must be a number'));
        }
        if(!is_numeric($args['pHeight'])){
            $e->add(t('The Product Height must be a number'));
        }
        if(!is_numeric($args['pLength'])){
            $e->add(t('The Product Length must be a number'));
        }
        if(!is_numeric($args['pWeight'])){
            $e->add(t('The Product Weight must be a number'));
        }

        return $e;

    }

    // GROUPS PAGE
    public function groups()
    {
        $grouplist = StoreGroupList::getGroupList();
        $this->set("grouplist",$grouplist);
        $this->requireAsset('css', 'communityStoreDashboard');
        $this->requireAsset('javascript', 'communityStoreFunctions');
    }
    public function groupadded()
    {
        $this->set('success',"Product Group Created");
        $this->groups();
    }
    public function addgroup()
    {
        $this->groups();
        $this->error = null; //clear errors
        $errors = $this->validateGroup($this->post());
        $this->error = $errors;
        if (!$errors->has()) {
            StoreGroup::add($this->post('groupName'));
            $this->redirect('/dashboard/store/products/', 'groupadded');
        }
    }
    public function editgroup($gID)
    {
        StoreGroup::getByID($gID)->update($this->post('gName'));
    }
    public function validateGroup($args)
    {
        $e = Core::make('helper/validation/error');

        if($args['groupName']==""){
            $e->add(t('Please enter a Group Name'));
        }
        if(strlen($args['groupName']) > 100){
            $e->add(t('A Group Name can not be more than 100 characters'));
        }
        return $e;
    }
    public function deletegroup($gID)
    {
        StoreGroup::getByID($gID)->delete();
    }

    // IMPORT PAGE
    public function import()
    {
        $product = new StoreProduct();
        $this->set('importFields', $product::getImportFields());
        $this->requireAsset('css', 'communityStoreDashboard');
        $this->requireAsset('javascript', 'communityStoreFunctions');

        // clear the environment overrides cache first
        $env = \Environment::get();
        $env->clearOverrideCache();
        $this->set('auth', Job::generateAuth());
        $session = new SymfonySession();
        $preview = $session->get('csv_rows');
        $headers = $session->get('csv_headers');
        if($headers) {
            $this->set('headers',$headers);
            $this->set('rows',$preview);

            $session->remove('csv_headers');
            $session->remove('csv_rows');
        }
    }

    public function importproducts(){
      ProductImporter::importCsv();
    }

    public function processQueue(){
      ProductImporter::processQueue();
    }
    public function beginImport(){
      $post = \Request::post();
      if($post['wipeProducts']){
        //get all products and delete them
        $productIds = StoreProduct::getAllProductIDs();
        foreach ($productIds as $pID) {
          $product = StoreProduct::getByID($pID);
          if ($product) {
              $product->remove();
          }
        }
      }
      echo serialize($post);
      exit;
    }


}
