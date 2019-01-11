<?php
namespace Concrete\Package\CommunityStore;

use Package;
use Concrete\Package\CommunityStore\Src\CommunityStore\Payment\Method as PaymentMethod;
use Concrete\Package\CommunityStore\Src\CommunityStore\Shipping\Method\ShippingMethodType as ShippingMethodType;
use Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Installer;
use Route;
use Asset;
use AssetList;
use URL;
use Core;

class Controller extends Package
{
    protected $pkgHandle = 'community_store';
    protected $appVersionRequired = '5.7.5';
    protected $pkgVersion = '1.4.8';

    public function getPackageDescription()
    {
        return t("Add a store to your site");
    }

    public function getPackageName()
    {
        return t("Community Store");
    }

    public function installStore()
    {
        $pkg = Package::getByHandle('community_store');

        Installer::installSinglePages($pkg);
        Installer::installProductParentPage($pkg);
        Installer::installStoreProductPageType($pkg);
        Installer::setDefaultConfigValues($pkg);
        Installer::installPaymentMethods($pkg);
        Installer::installShippingMethods($pkg);
        Installer::installBlocks($pkg);
        Installer::setPageTypeDefaults($pkg);
        Installer::installCustomerGroups($pkg);
        Installer::installUserAttributes($pkg);
        Installer::installOrderAttributes($pkg);
        Installer::installProductAttributes($pkg);
        Installer::createDDFileset($pkg);
        Installer::installOrderStatuses($pkg);
        Installer::installDefaultTaxClass($pkg);
    }

    public function install()
    {
        parent::install();
        $this->installStore();
    }

    public function upgrade()
    {
        $pkg = Package::getByHandle('community_store');
        parent::upgrade();
        Installer::upgrade($pkg);
        $cms = Core::make('app');
        $cms->clearCaches();
    }

    public function registerRoutes()
    {
        Route::register('/cart/getCartSummary', '\Concrete\Package\CommunityStore\Src\CommunityStore\Cart\CartTotal::getCartSummary');
        Route::register('/cart/getmodal', '\Concrete\Package\CommunityStore\Src\CommunityStore\Cart\CartModal::getCartModal');
        Route::register('/productmodal', '\Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductModal::getProductModal');
        Route::register('/checkout/getstates', '\Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\States::getStateList');
        Route::register('/checkout/getShippingMethods', '\Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Checkout::getShippingMethods');
        Route::register('/checkout/updater', '\Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Checkout::updater');
        Route::register('/checkout/setVatNumber', '\Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Checkout::setVatNumber');
        Route::register('/checkout/selectShipping', '\Concrete\Package\CommunityStore\Src\CommunityStore\Cart\CartTotal::getShippingTotal');
        Route::register('/productfinder', '\Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\ProductFinder::getProductMatch');
        Route::register('/store_download/{fID}/{oID}/{hash}', '\Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Checkout::downloadFile');
    }

    public function on_start()
    {
        $this->registerRoutes();

        $version = $this->getPackageVersion();

        $al = AssetList::getInstance();
        $al->register('css', 'community-store', 'css/community-store.css?v=' . $version, ['version' => $version, 'position' => Asset::ASSET_POSITION_HEADER, 'minify' => false, 'combine' => false], $this);
        $al->register('css', 'communityStoreDashboard', 'css/communityStoreDashboard.css?v=' . $version, ['version' => $version, 'position' => Asset::ASSET_POSITION_HEADER, 'minify' => false, 'combine' => false], $this);
        $al->register('javascript', 'community-store', 'js/communityStore.js?v=' . $version, ['version' => $version, 'position' => Asset::ASSET_POSITION_FOOTER, 'minify' => false, 'combine' => false], $this);
        $al->register('javascript', 'communityStoreFunctions', 'js/communityStoreFunctions.js?v=' . $version, ['version' => $version, 'position' => Asset::ASSET_POSITION_FOOTER, 'minify' => false, 'combine' => false], $this);
        $al->register('javascript', 'community-store-autocomplete', 'js/autoComplete.js?v=' . $version, ['version' => $version, 'position' => Asset::ASSET_POSITION_FOOTER, 'minify' => false, 'combine' => false], $this);

        $al->register('javascript', 'chartist', 'js/chartist.min.js', ['version' => '0.9.7', 'position' => Asset::ASSET_POSITION_FOOTER, 'minify' => false, 'combine' => false], $this);
        $al->register('css', 'chartist', 'css/chartist.min.css', ['version' => '0.9.7', 'position' => Asset::ASSET_POSITION_HEADER, 'minify' => false, 'combine' => false], $this);
        $al->register('javascript', 'chartist-tooltip', 'js/chartist-plugin-tooltip.min.js', ['version' => '0.0.12', 'position' => Asset::ASSET_POSITION_FOOTER, 'minify' => false, 'combine' => false], $this);
        $al->register('css', 'chartist-tooltip', 'css/chartist-plugin-tooltip.css', ['version' => '0.0.12', 'position' => Asset::ASSET_POSITION_HEADER, 'minify' => false, 'combine' => false], $this);
        $al->registerGroup('chartist',
            [
                ['javascript', 'chartist'],
                ['javascript', 'chartist-tooltip'],
                ['css', 'chartist'],
                ['css', 'chartist-tooltip'],
            ]
        );

        if (Core::make('app')->isRunThroughCommandLineInterface()) {
            try {
                $app = Core::make('console');
                $app->add(new Src\CommunityStore\Console\Command\ResetCommand());
            } catch (Exception $e) {
            }
        }
    }

    public function uninstall()
    {
        $invoicepm = PaymentMethod::getByHandle('invoice');
        if (is_object($invoicepm)) {
            $invoicepm->delete();
        }
        $shippingMethodType = ShippingMethodType::getByHandle('flat_rate');
        if (is_object($shippingMethodType)) {
            $shippingMethodType->delete();
        }
        $shippingMethodType = ShippingMethodType::getByHandle('free_shipping');
        if (is_object($shippingMethodType)) {
            $shippingMethodType->delete();
        }

        // change existing product pages back to standard page type to prevent broken pages
        $list = new \Concrete\Core\Page\PageList();
        $list->filterByPageTypeHandle('store_product');
        $pages = $list->getResults();

        $pageType = \PageType::getByHandle('page');

        if ($pageType) {
            foreach ($pages as $page) {
                $page->setPageType($pageType);
            }
        }

        parent::uninstall();
    }

    public static function returnHeaderJS()
    {
        return "
        <script type=\"text/javascript\">
            var PRODUCTMODAL = '" . URL::to('/productmodal') . "';
            var CARTURL = '" . rtrim(URL::to('/cart'), '/') . "';
            var TRAILINGSLASH = '" . ((bool) \Config::get('concrete.seo.trailing_slash', false) ? '/' : '') . "';
            var CHECKOUTURL = '" . rtrim(URL::to('/checkout'), '/') . "';
            var QTYMESSAGE = '" . t('Quantity must be greater than zero') . "';
        </script>
        ";
    }
}
