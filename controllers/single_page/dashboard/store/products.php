<?php
namespace Concrete\Package\CommunityStore\Controller\SinglePage\Dashboard\Store;

use Concrete\Core\Page\Page;
use Concrete\Core\Http\Request;
use Concrete\Core\Routing\Redirect;
use Concrete\Core\User\Group\GroupList;
use Concrete\Core\Support\Facade\Config;
use Concrete\Core\Support\Facade\Events;
use Concrete\Core\Support\Facade\Session;
use Concrete\Core\Page\Type\Type as PageType;
use Concrete\Core\Search\Pagination\PaginationFactory;
use Concrete\Core\Page\Controller\DashboardSitePageController;
use Concrete\Package\CommunityStore\Src\CommunityStore\Tax\TaxClass as StoreTaxClass;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product as StoreProduct;
use Concrete\Package\CommunityStore\Src\CommunityStore\Group\GroupList as StoreGroupList;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductFile as StoreProductFile;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductList as StoreProductList;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductEvent as StoreProductEvent;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductGroup as StoreProductGroup;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductImage as StoreProductImage;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductRelated as StoreProductRelated;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductLocation as StoreProductLocation;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductPriceTier as StoreProductPriceTier;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductUserGroup as StoreProductUserGroup;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductOption\ProductOption as StoreProductOption;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductVariation\ProductVariation as StoreProductVariation;
use Concrete\Package\CommunityStore\Src\CommunityStore\Manufacturer\ManufacturerList;

class Products extends DashboardSitePageController
{
    public function view($gID = null)
    {
        $productsList = new StoreProductList();
        $productsList->setItemsPerPage(20);
        $productsList->setGroupID($gID);
        $productsList->setActiveOnly(false);
        $productsList->setShowOutOfStock(true);

        if ($this->request->query->get('ccm_order_by')) {
            $productsList->setSortBy($this->request->query->get('ccm_order_by'));
            $productsList->setSortByDirection($this->request->query->get('ccm_order_by_direction'));
        } else {
            $productsList->setSortBy('date');
            $productsList->setSortByDirection('desc');
        }

        $keywords = trim($this->request->query->get('keywords'));

        if ($keywords) {
            $productsList->setSearch($keywords);
            Session::set('communitystore.dashboard.products.keywords', $keywords);
        } else {
            Session::remove('communitystore.dashboard.products.keywords');
        }

        $this->set('productList', $productsList);

        $factory = new PaginationFactory($this->app->make(Request::class));
        $paginator = $factory->createPaginationObject($productsList);

        $pagination = $paginator->renderDefaultView();
        $this->set('products', $paginator->getCurrentPageResults());
        $this->set('pagination', $pagination);
        $this->set('paginator', $paginator);
        $this->requireAsset('css', 'communityStoreDashboard');
        $this->requireAsset('javascript', 'communityStoreFunctions');

        $grouplist = StoreGroupList::getGroupList();
        $this->set("grouplist", $grouplist);
        $this->set('gID', $gID);

        if ($gID) {
            Session::set('communitystore.dashboard.products.group', $gID);
        } else {
            Session::remove('communitystore.dashboard.products.group');
        }

        $site = $this->getSite();
        $pages = \Concrete\Core\Multilingual\Page\Section\Section::getList($site);
        $this->set('multilingualEnabled', (count($pages) > 1));
    }

    public function add()
    {
        $this->loadFormAssets();
        $this->set("actionType", t("Add"));

        $grouplist = StoreGroupList::getGroupList();
        $this->set("grouplist", $grouplist);
        foreach ($grouplist as $productgroup) {
            $productgroups[$productgroup->getGroupID()] = $productgroup->getGroupName();
        }
        $this->set("productgroups", $productgroups);

        $gl = new GroupList();
        $gl->setItemsPerPage(1000);
        $gl->filterByAssignable();
        $usergroups = $gl->get();

        $usergrouparray = [];

        foreach ($usergroups as $ug) {
            if ('Administrators' != $ug->gName) {
                $usergrouparray[$ug->gID] = $ug->gName;
            }
        }

        $manufacturesList = ManufacturerList::getManufacturerList();

        $this->set("manufacturesList", $manufacturesList);
        $productmanufacturers = array("0" => t("None"));
        foreach ($manufacturesList as $productmanufacturer) {
            $productmanufacturers[$productmanufacturer->getMID()] = $productmanufacturer->getName();
        }
        $this->set("pManufacturer", $productmanufacturers);

        $targetCID = Config::get('community_store.productPublishTarget');

        $productPublishTarget = false;

        if ($targetCID > 0) {
            $parentPage = Page::getByID($targetCID);
            $productPublishTarget = ($parentPage && !$parentPage->isError() && !$parentPage->isInTrash());
        }

        $this->set('productPublishTarget', $productPublishTarget);
        $this->set('page', false);
        $this->set('pageTitle', t('Add Product'));
        $this->set('usergroups', $usergrouparray);
    }

    public function edit($pID)
    {
        $this->loadFormAssets();
        $this->set("actionType", t("Update"));

        //get the product
        $product = StoreProduct::getByID($pID);

        if (!$product) {
            return Redirect::to('/dashboard/store/products');
        }

        $this->set('product', $product);

        $options = $product->getOptions();

        $variations = $product->getVariations();

        $variationLookup = [];
        $optionArrays = [];
        $optionLookup = [];
        $optionItems = [];

        foreach ($options as $opt) {
            if ($opt->getIncludeVariations()) {
                $optionLookup[$opt->getID()] = $opt;

                foreach ($opt->getOptionItems() as $optItem) {
                    $optionArrays[$opt->getID()][] = $optItem->getID();
                    $optionItemLookup[$optItem->getID()] = $optItem;
                    $optionItems[] = $optItem;
                }
            }
        }

        $this->set('optionItems', $optionItems);
        $this->set('optionLookup', $optionLookup);
        $this->set('optionItemLookup', $optionItemLookup);

        $optionArrays = array_values($optionArrays);

        $comboOptions = StoreProductVariation::combinations($optionArrays);

        $checkedOptions = [];

        foreach ($comboOptions as $option) {
            if (!is_array($option)) {
                $checkedOptions[] = [$option];
            } else {
                $checkedOptions[] = $option;
            }
        }

        $comboOptions = $checkedOptions;

        $this->set('comboOptions', $comboOptions);
        $this->set('optionItemLookup', $optionItemLookup);

        foreach ($variations as $variation) {
            $options = $variation->getOptions();
            $optionids = [];

            foreach ($options as $varoption) {
                $option = $varoption->getOption();

                if ($option) {
                    $optionids[] = $option->getID();
                }
            }

            sort($optionids);
            $variationLookup[implode('_', $optionids)] = $variation;
        }

        $this->set('variations', $variations);
        $this->set('variationLookup', $variationLookup);

        //populate "Groups" select box options
        $grouplist = StoreGroupList::getGroupList();
        foreach ($grouplist as $productgroup) {
            $productgroups[$productgroup->getGroupID()] = $productgroup->getGroupName();
        }
        $this->set("productgroups", $productgroups);

        $gl = new GroupList();
        $gl->setItemsPerPage(1000);
        $gl->filterByAssignable();
        $usergroups = $gl->get();

        $usergrouparray = [];

        foreach ($usergroups as $ug) {
            if ('Administrators' != $ug->gName) {
                $usergrouparray[$ug->gID] = $ug->gName;
            }
        }
        $manufacturesList = ManufacturerList::getManufacturerList();

        $this->set("manufacturesList", $manufacturesList);
        $productmanufacturers = array("0" => t("None"));
        foreach ($manufacturesList as $productmanufacturer) {
            $productmanufacturers[$productmanufacturer->getMID()] = $productmanufacturer->getName();
        }
        $this->set("pManufacturer", $productmanufacturers);

        $targetCID = Config::get('community_store.productPublishTarget');

        $productPublishTarget = false;

        if ($targetCID > 0) {
            $parentPage = Page::getByID($targetCID);
            $productPublishTarget = ($parentPage && !$parentPage->isError() && !$parentPage->isInTrash());
        }

        $this->set('productPublishTarget', $productPublishTarget);

        $pageID = $product->getPageID();
        $page = false;

        if ($pageID) {
            $page = Page::getByID($pageID);

            if ($page->isError()) {
                $page = false;
            }
        }
        $this->set('page', $page);

        $this->set('pageTitle', t('Edit Product'));
        $this->set('usergroups', $usergrouparray);

        $this->set('keywordsSearch', Session::get('communitystore.dashboard.products.keywords'));
        $this->set('groupSearch', Session::get('communitystore.dashboard.products.group'));
    }

    public function generate($pID, $templateID = null)
    {
        StoreProduct::getByID($pID)->generatePage($templateID);

        return Redirect::to('/dashboard/store/products/edit', $pID);
    }

    public function duplicate($pID)
    {
        $product = StoreProduct::getByID($pID);
        if (!$product) {
            return Redirect::to('/dashboard/store/products');
        }

        if ($this->request->request->all() && $this->token->validate('community_store')) {
            $newproduct = $product->duplicate($this->request->request->get('newName'), $this->request->request->get('newSKU'));
            $this->flash('success', t('Product Duplicated'));

            return Redirect::to('/dashboard/store/products/edit/' . $newproduct->getID());
        }

        $this->set('pageTitle', t('Duplicate Product'));
        $this->set('product', $product);
    }

    public function delete($pID)
    {
        if ($this->token->validate('community_store')) {
            $product = StoreProduct::getByID($pID);
            if ($product) {
                $product->remove();
            }
            $this->flash('success', t('Product Removed'));

            return Redirect::to('/dashboard/store/products');
        }

        $this->flash('success', t('Product Removed'));

        return Redirect::to('/dashboard/store/products');
    }

    public function loadFormAssets()
    {
        $this->requireAsset('css', 'select2');
        $this->requireAsset('javascript', 'select2');

        $this->set('al', $this->app->make('helper/concrete/asset_library'));

        $this->requireAsset('css', 'communityStoreDashboard');
        $this->requireAsset('javascript', 'communityStoreFunctions');

        $productCategory = $this->app->make('Concrete\Package\CommunityStore\Attribute\Category\ProductCategory');

        $attrList = $productCategory->getList();
        $this->set('attribs', $attrList);

        $pageType = PageType::getByHandle("store_product");
        $templates = [];

        $defaultTemplateID = 0;

        if ($pageType) {
            $pageTemplates = $pageType->getPageTypePageTemplateObjects();

            foreach ($pageTemplates as $pt) {
                $templates[$pt->getPageTemplateID()] = $pt->getPageTemplateName();
            }

            $defaultTemplateID = $pageType->getPageTypeDefaultPageTemplateID();
        }
        $this->set('pageTemplates', $templates);
        $this->set('defaultTemplateID', $defaultTemplateID);
        $taxClasses = [];
        foreach (StoreTaxClass::getTaxClasses() as $taxClass) {
            $taxClasses[$taxClass->getID()] = $taxClass->getTaxClassName();
        }
        $this->set('taxClasses', $taxClasses);
    }

    public function save()
    {
        $data = $this->request->request->all();
        if ($data['pID']) {
            $this->edit($data['pID']);
        } else {
            $this->add();
        }
        if ($this->request->request->all() && $this->token->validate('community_store')) {
            $errors = $this->validate($data);
            $this->error = null; //clear errors
            $this->error = $errors;
            if (!$errors->has()) {
                $originalProduct = false;

                if ($data['pID']) {
                    $product = StoreProduct::getByID($data['pID']);
                    $originalProduct = clone $product;
                    $originalProduct->setID($data['pID']);
                }

                // if the save sent no options with variation inclusion, uncheck the variations box
                if (isset($data['poIncludeVariations'])) {
                    $allowVariations = false;

                    foreach ($data['poIncludeVariations'] as $variationInclude) {
                        if (1 == $variationInclude) {
                            $allowVariations = true;
                        }
                    }

                    if (!$allowVariations) {
                        $data['pVariations'] = 0;
                    }
                }

                //save the product
                $product = StoreProduct::saveProduct($data);
                //save product attributes
                $productCategory = $this->app->make('Concrete\Package\CommunityStore\Attribute\Category\ProductCategory');
                $aks = $productCategory->getList();

                foreach ($aks as $uak) {
                    $controller = $uak->getController();
                    $value = $controller->createAttributeValueFromRequest();
                    $product->setAttribute($uak, $value);
                }
                //save images
                StoreProductImage::addImagesForProduct($data, $product);

                //save product groups
                StoreProductGroup::addGroupsForProduct($data, $product);

                //save product user groups
                StoreProductUserGroup::addUserGroupsForProduct($data, $product);

                //save product options
                StoreProductOption::addProductOptions($data, $product);

                //save files
                StoreProductFile::addFilesForProduct($data, $product);

                //save category locations
                StoreProductLocation::addLocationsForProduct($data, $product);

                // save variations
                StoreProductVariation::addVariations($data, $product);

                // save related products
                StoreProductRelated::addRelatedProducts($data, $product);

                StoreProductPriceTier::addPriceTiersForProduct($data, $product);

                //$product->reindex();

                // create product event and dispatch
                if (!$originalProduct) {
                    $event = new StoreProductEvent($product);
                    Events::dispatch(StoreProductEvent::PRODUCT_ADD, $event);
                } else {
                    $event = new StoreProductEvent($originalProduct, $product);
                    Events::dispatch(StoreProductEvent::PRODUCT_UPDATE, $event);
                }

                if ($data['pID']) {
                    $this->flash('success', t('Product Updated'));

                    return Redirect::to('/dashboard/store/products/edit/' . $product->getID());
                } else {
                    $this->flash('success', t('Product Added'));

                    return Redirect::to('/dashboard/store/products/edit/' . $product->getID());
                }
            }//if no errors
        }//if post
    }

    public function validate($args)
    {
        $e = $this->app->make('helper/validation/error');

        if ("" == $args['pName']) {
            $e->add(t('Please enter a Product Name'));
        }
        if (strlen($args['pName']) > 255) {
            $e->add(t('The Product Name can not be greater than 255 Characters'));
        }
        if (!is_numeric($args['pQty']) && !$args['pQtyUnlim']) {
            $e->add(t('The Quantity must be set, and numeric'));
        }
        if (!is_numeric($args['pWidth'])) {
            $e->add(t('The Product Width must be a number'));
        }
        if (!is_numeric($args['pHeight'])) {
            $e->add(t('The Product Height must be a number'));
        }
		if (!is_numeric($args['pStackedHeight'])) {
			$e->add(t('The Product Stacked Height must be a number'));
		}
        if (!is_numeric($args['pLength'])) {
            $e->add(t('The Product Length must be a number'));
        }
        if (!is_numeric($args['pWeight'])) {
            $e->add(t('The Product Weight must be a number'));
        }
        if (strlen($args['pEan']) > 13) {
            $e->add(t('The EAN can not be greater than 13 Characters'));
        }
        if (strlen($args['pMpn']) > 255) {
            $e->add(t('The MPN can not be greater than 255 Characters'));
        }
        if (strlen($args['pIsbn']) > 13) {
            $e->add(t('The ISBN can not be greater than 13 Characters'));
        }
        if (strlen($args['pJan']) > 13) {
            $e->add(t('The JAN can not be greater than 13 Characters'));
        }
        if (strlen($args['pUpc']) > 12) {
            $e->add(t('The UPC can not be greater than 12 Characters'));
        }

        return $e;
    }
}
