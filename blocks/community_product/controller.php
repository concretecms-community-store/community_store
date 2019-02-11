<?php
namespace Concrete\Package\CommunityStore\Block\CommunityProduct;

use Concrete\Core\Block\BlockController;
use Config;
use Page;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product as StoreProduct;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductVariation\ProductVariation as StoreProductVariation;
use Concrete\Package\CommunityStore\Src\CommunityStore\Discount\DiscountRule as StoreDiscountRule;
use \Concrete\Core\Multilingual\Page\Section\Section;

class Controller extends BlockController
{
    protected $btTable = 'btCommunityStoreProduct';
    protected $btInterfaceWidth = "450";
    protected $btWrapperClass = 'ccm-ui';
    protected $btInterfaceHeight = "538";
    protected $btDefaultSet = 'community_store';

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
                $product = StoreProduct::getByCollectionID($cID);
            }

            if (!$product) {
                $site = $this->app->make('site')->getSite();
                if ($site) {
                    $locale = $site->getDefaultLocale();

                    if ($locale) {

                        $originalcID = Section::getRelatedCollectionIDForLocale($cID, $locale->getLocale() );
                        $product = StoreProduct::getByCollectionID($originalcID);
                    }
                }
            }
        } else {
            if ($this->pID) {
                $product = StoreProduct::getByID($this->pID);
            }
        }

        if ($product) {
            if ($product->hasVariations()) {
                $variations = StoreProductVariation::getVariationsForProduct($product);

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

            $codediscounts = false;
            $automaticdiscounts = StoreDiscountRule::findAutomaticDiscounts();
            $code = trim(\Session::get('communitystore.code'));

            if ($code) {
                $codediscounts = StoreDiscountRule::findDiscountRuleByCode($code);
            }

            if (!empty($automaticdiscounts)) {
                $product->addDiscountRules($automaticdiscounts);
            }

            if (!empty($codediscounts)) {
                $product->addDiscountRules($codediscounts);
            }

            $this->set('product', $product);
            $this->set('showProductName', $this->showProductName);
            $this->set('showProductPrice', $this->showProductPrice);
            $this->set('showProductDescription', $this->showProductDescription);
            $this->set('showDimensions', $this->showDimensions);
            $this->set('showWeight', $this->showWeight);
            $this->set('showGroups', $this->showGroups);
            $this->set('showCartButton', $this->showCartButton);
            $this->set('showImage', $this->showImage);
            $this->set('showProductDetails', $this->showProductDetails);
            $this->set('btnText', $this->btnText);
        }

        if ('all' == Config::get('community_store.shoppingDisabled')) {
            $this->set('showCartButton', false);
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
    }

    public function registerViewAssets($outputContent = '')
    {
        $this->requireAsset('javascript', 'jquery');
        $this->requireAsset('javascript', 'community-store');
        $js = \Concrete\Package\CommunityStore\Controller::returnHeaderJS();
        $this->addFooterItem($js);
        $this->requireAsset('css', 'community-store');
        $this->requireAsset('core/lightbox');
    }

    public function getSearchableContent()
    {
        if ('page' == $this->productLocation) {
            $page = $this->getCollectionObject();
            $cID = $page->getCollectionID();
            $product = StoreProduct::getByCollectionID($cID);
        } else {
            $product = StoreProduct::getByID($this->pID);
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
        $args['showProductName'] = isset($args['showProductName']) ? 1 : 0;
        $args['showProductDescription'] = isset($args['showProductDescription']) ? 1 : 0;
        $args['showProductDetails'] = isset($args['showProductDetails']) ? 1 : 0;
        $args['showProductPrice'] = isset($args['showProductPrice']) ? 1 : 0;
        $args['showWeight'] = isset($args['showWeight']) ? 1 : 0;
        $args['showImage'] = isset($args['showImage']) ? 1 : 0;
        $args['showCartButton'] = isset($args['showCartButton']) ? 1 : 0;
        $args['showIsFeatured'] = isset($args['showIsFeatured']) ? 1 : 0;
        $args['showGroups'] = isset($args['showGroups']) ? 1 : 0;
        $args['showDimensions'] = isset($args['showDimensions']) ? 1 : 0;
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
            $this->set('product', StoreProduct::getByID($this->pID));
        } else {
            $this->set('product', false);
        }
    }
}
