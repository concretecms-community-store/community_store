<?php
namespace Concrete\Package\CommunityStore\Block\CommunityProduct;

use Concrete\Core\Page\Page;
use Concrete\Core\Block\BlockController;
use Concrete\Core\Support\Facade\Config;
use Concrete\Core\Support\Facade\Session;
use Concrete\Core\Multilingual\Page\Section\Section;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product;
use Concrete\Package\CommunityStore\Src\CommunityStore\Discount\DiscountRule;
use Concrete\Package\CommunityStore\Src\CommunityStore\Manufacturer\Manufacturer;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductVariation\ProductVariation;
use Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\SalesSuspension;

class Controller extends BlockController
{
    protected $btTable = 'btCommunityStoreProduct';
    protected $btInterfaceWidth = "680";
    protected $btWrapperClass = 'ccm-ui';
    protected $btInterfaceHeight = "538";
    protected $btDefaultSet = 'community_store';
    public $pID = false;

    public function getBlockTypeDescription()
    {
        return t("Add a Product to the Page");
    }

    public function getBlockTypeName()
    {
        return t("Product");
    }

    public function view()
    {
        $product = false;

        if ('page' == $this->productLocation || !$this->productLocation) {
            $page = Page::getCurrentPage();
            $cID = $page->getCollectionID();

            if ($cID) {
                $product = Product::getByCollectionID($cID);
            }
        } else {
            if ($this->pID) {
                $product = Product::getByID($this->pID);
            }
        }

        if ($product) {
            if ($product->hasVariations()) {
                $variations = $product->getVariations();

                $variationLookup = [];

                if (!empty($variations)) {
                    foreach ($variations as $variation) {
                        // returned pre-sorted
                        $ids = $variation->getOptionItemIDs();
                        $variationLookup[implode('_', $ids)] = $variation;
                    }
                }

                $product->setInitialVariation();
                $this->set('variationLookup', $variationLookup);
            }

            $automaticdiscounts = DiscountRule::findAutomaticDiscounts();

            if (!empty($automaticdiscounts)) {
                $product->addDiscountRules($automaticdiscounts);
            }

            $this->set('product', $product);
            $this->set('showProductName', $this->showProductName);
            $this->set('showProductSKU', $this->showProductSKU);
            $this->set('showProductPrice', $this->showProductPrice);
            $this->set('showProductDescription', $this->showProductDescription);
            $this->set('showManufacturer', $this->showManufacturer);
            $this->set('showManufacturerDescription', $this->showManufacturerDescription);
            $this->set('showDimensions', $this->showDimensions);
            $this->set('showWeight', $this->showWeight);
            $this->set('showGroups', $this->showGroups);
            $this->set('showCartButton', $this->showCartButton);
            $this->set('showQuantity', $this->showQuantity);
            $this->set('showImage', $this->showImage);
            $this->set('showProductDetails', $this->showProductDetails);
            $this->set('btnText', isset($this->btnText) ? $this->btnText : false);
        } else {
            $this->set('product', false);
        }

        if ('all' == Config::get('community_store.shoppingDisabled') || $this->app->make(SalesSuspension::class)->salesCurrentlySuspended()) {
            $this->set('showCartButton', false);
        }

        $this->set('token', $this->app->make('token'));

        $c = Page::getCurrentPage();
        $al = Section::getBySectionOfSite($c);
        $langpath = '';
        if (null !== $al) {
            $langpath = $al->getCollectionHandle();
        }
        $this->set('langpath', $langpath);
        $this->set('isWholesale', \Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Wholesale::isUserWholesale());
        $this->set('app', $this->app);
    }

    public function registerViewAssets($outputContent = '')
    {
        $this->requireAsset('javascript', 'jquery');
        $this->requireAsset('javascript', 'sysend');
        $this->requireAsset('javascript', 'community-store');
        $js = \Concrete\Package\CommunityStore\Controller::returnHeaderJS();
        $this->addFooterItem($js);
        $this->requireAsset('css', 'community-store');
        $this->requireAsset('core/lightbox');
    }

    public function getSearchableContent()
    {
        $product = false;

        if ('page' == $this->productLocation) {
            $page = $this->getCollectionObject();

            if ($page) {
                $cID = $page->getCollectionID();
                $product = Product::getByCollectionID($cID);
            }
        } else {
            $product = Product::getByID($this->pID);
        }

        if ($product) {
            $sku = $product->getSKU();

            return $product->getName() . ($sku ? ' (' . $sku . ')' : '') . ' ' . $product->getDesc() . ' ' . $product->getDetail();
        } else {
            return '';
        }
    }

    public function save($args)
    {
        $args['showProductName'] = isset($args['showProductName']) ? (int)$args['showProductName'] : 0;
        $args['showProductSKU'] = isset($args['showProductSKU']) ? (int)$args['showProductSKU'] : 0;
        $args['showProductDescription'] = isset($args['showProductDescription']) ? (int)$args['showProductDescription'] : 0;
        $args['showManufacturer'] = isset($args['showManufacturer']) ? (int)$args['showManufacturer'] : 0;
        $args['showManufacturerDescription'] = isset($args['showManufacturerDescription']) ? (int)$args['showManufacturerDescription'] : 0;
        $args['showProductDetails'] = isset($args['showProductDetails']) ? (int)$args['showProductDetails'] : 0;
        $args['showProductPrice'] = isset($args['showProductPrice']) ? (int)$args['showProductPrice'] : 0;
        $args['showWeight'] = isset($args['showWeight']) ? (int)$args['showWeight'] : 0;
        $args['showImage'] = isset($args['showImage']) ? (int)$args['showImage'] : 0;
        $args['showCartButton'] = isset($args['showCartButton']) ? (int)$args['showCartButton'] : 0;
        $args['showIsFeatured'] = isset($args['showIsFeatured']) ? (int)$args['showIsFeatured'] : 0;
        $args['showGroups'] = isset($args['showGroups']) ? (int)$args['showGroups'] : 0;
        $args['showDimensions'] = isset($args['showDimensions']) ? (int)$args['showDimensions'] : 0;
        $args['showQuantity'] = isset($args['showQuantity']) ? (int)$args['showQuantity'] : 0;
        $args['pID'] = isset($args['pID']) && is_numeric($args['pID']) ? $args['pID'] : null;

        if ('search' == $args['productLocation']) {
            if (!is_numeric($args['pID']) || $args['pID'] < 1) {
                $args['productLocation'] = "page";
            }
        }
        parent::save($args);
    }

    public function add()
    {
        $this->requireAsset('css', 'select2');
        $this->requireAsset('javascript', 'select2');
    }

    public function edit()
    {
        $this->requireAsset('css', 'select2');
        $this->requireAsset('javascript', 'select2');

        if ($this->pID) {
            $this->set('product', Product::getByID($this->pID));
        } else {
            $this->set('product', false);
        }
    }
}
