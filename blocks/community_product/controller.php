<?php
namespace Concrete\Package\CommunityStore\Block\CommunityProduct;

use Concrete\Core\Block\BlockController;
use Config;
use Page;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product as StoreProduct;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductVariation\ProductVariation as StoreProductVariation;

defined('C5_EXECUTE') or die("Access Denied.");
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
        if ($this->productLocation == 'page') {
            $cID = Page::getCurrentPage()->getCollectionID();
            $product = StoreProduct::getByCollectionID($cID);
        } else {
            $product = StoreProduct::getByID($this->pID);
        }

        if ($product) {
            if ($product->hasVariations()) {
                $variations = StoreProductVariation::getVariationsForProduct($product);

                $variationLookup = array();

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

            $this->set('product', $product);
        }

        if (Config::get('community_store.shoppingDisabled') == 'all') {
            $this->set('showCartButton', false);
        }
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
        if ($this->productLocation == 'page') {
            $page = $this->getCollectionObject();
            $cID = $page->getCollectionID();
            $product = StoreProduct::getByCollectionID($cID);
        } else {
            $product = StoreProduct::getByID($this->pID);
        }

        if ($product) {
            $sku = $product->getSKU();
            return $product->getName() . ($sku ? ' (' .$sku. ')' : '') . ' ' . $product->getDesc() . ' ' . $product->getDetail();
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
        if ($args['productLocation'] == 'search') {
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
