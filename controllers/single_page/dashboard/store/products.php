<?php
namespace Concrete\Package\CommunityStore\Controller\SinglePage\Dashboard\Store;

use Concrete\Core\Page\Page;
use Concrete\Core\Http\Request;
use Concrete\Core\Routing\Redirect;
use Concrete\Core\User\Group\GroupList;
use Concrete\Core\Support\Facade\Config;
use Concrete\Core\Support\Facade\Events;
use Concrete\Core\Support\Facade\Session;
use Concrete\Core\Attribute\Key\Category;
use Concrete\Core\Page\Type\Type as PageType;
use Concrete\Core\Search\Pagination\PaginationFactory;
use Concrete\Core\Page\Controller\DashboardSitePageController;
use Concrete\Package\CommunityStore\Src\CommunityStore\Tax\TaxClass;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductFile;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductList;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductEvent;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductGroup;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductImage;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductRelated;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductLocation;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductPriceTier;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductUserGroup;
use Concrete\Package\CommunityStore\Src\CommunityStore\Manufacturer\ManufacturerList;
use Concrete\Package\CommunityStore\Src\CommunityStore\Group\GroupList as StoreGroupList;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductOption\ProductOption;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductVariation\ProductVariation;

class Products extends DashboardSitePageController
{
    public function view($gID = null)
    {
        $productsList = new ProductList();
        $productsList->setItemsPerPage(20);
        $productsList->setGroupID($gID);
        $productsList->setActiveOnly(false);
        $productsList->setShowOutOfStock(true);
        $productsList->setGroupMatchAny(true);

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
        if ($this->request->getMethod() == 'POST') {
           return $this->save();
        }

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
        $usergroups = $gl->getResults();

        $usergrouparray = [];

        foreach ($usergroups as $ug) {
            if ('Administrators' != $ug->gName) {
                $usergrouparray[$ug->gID] = $ug->gName;
            }
        }

       $manufacturersList = ManufacturerList::getManufacturerList();

        $this->set('manufacturersList', $manufacturersList);
        $productmanufacturers = array("0" => t("None"));
        foreach ($manufacturersList as $productmanufacturer) {
            $productmanufacturers[$productmanufacturer->getID()] = $productmanufacturer->getName();
        }
        $this->set('manufacturers', $productmanufacturers);

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
        if ($this->request->getMethod() == 'POST') {
            return $this->save();
        }

        $this->loadFormAssets();
        $this->set("actionType", t("Update"));

        //get the product
        $product = Product::getByID($pID);

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

        $comboOptions = ProductVariation::combinations($optionArrays);

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
                $option = $varoption->getOptionItem();

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
        $usergroups = $gl->getResults();

        $usergrouparray = [];

        foreach ($usergroups as $ug) {
            if ('Administrators' != $ug->gName) {
                $usergrouparray[$ug->gID] = $ug->gName;
            }
        }
        $manufacturersList = ManufacturerList::getManufacturerList();

        $this->set("manufacturersList", $manufacturersList);
        $productmanufacturers = array("0" => t("None"));
        foreach ($manufacturersList as $productmanufacturer) {
            $productmanufacturers[$productmanufacturer->getID()] = $productmanufacturer->getName();
        }
        $this->set('manufacturers', $productmanufacturers);

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
        Product::getByID($pID)->generatePage($templateID);

        return Redirect::to('/dashboard/store/products/edit', $pID);
    }

    public function duplicate($pID)
    {
        $product = Product::getByID($pID);
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
            $product = Product::getByID($pID);
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

        $this->set('productAttributeCategory', Category::getByHandle('store_product'));

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
        foreach (TaxClass::getTaxClasses() as $taxClass) {
            $taxClasses[$taxClass->getID()] = $taxClass->getTaxClassName();
        }
        $this->set('taxClasses', $taxClasses);
    }

    public function save()
    {
        $data = $this->request->request->all();

        if ($this->request->request->all() && $this->token->validate('community_store')) {
            $errors = $this->validate($data);
            $this->error = null; //clear errors
            $this->error = $errors;
            if (!$errors->has()) {
                $originalProduct = false;

                if ($data['pID']) {
                    $product = Product::getByID($data['pID']);
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
                $product = Product::saveProduct($data);
                //save product attributes
                $productCategory = $this->app->make('Concrete\Package\CommunityStore\Attribute\Category\ProductCategory');
                $aks = $productCategory->getList();

                foreach ($aks as $uak) {
                    $controller = $uak->getController();
                    $value = $controller->createAttributeValueFromRequest();
                    $product->setAttribute($uak, $value);
                }
                //save images
                ProductImage::addImagesForProduct($data, $product);

                //save product groups
                ProductGroup::addGroupsForProduct($data, $product);

                //save product user groups
                ProductUserGroup::addUserGroupsForProduct($data, $product);

                //save product options
                ProductOption::addProductOptions($data, $product);

                //save files
                ProductFile::addFilesForProduct($data, $product);

                //save category locations
                ProductLocation::addLocationsForProduct($data, $product);

                // save variations
                ProductVariation::addVariations($data, $product);

                // save related products
                ProductRelated::addRelatedProducts($data, $product);

                ProductPriceTier::addPriceTiersForProduct($data, $product);

                //$product->reindex();

                // create product event and dispatch
                if (!$originalProduct) {
                    $event = new ProductEvent($product);
                    Events::dispatch(ProductEvent::PRODUCT_ADD, $event);
                } else {
                    $event = new ProductEvent($originalProduct, $product);
                    Events::dispatch(ProductEvent::PRODUCT_UPDATE, $event);
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
