<?php
namespace Concrete\Package\CommunityStore;

use Concrete\Core\Package\Package;
use Concrete\Package\CommunityStore\Src\CommunityStore\Payment\Method as PaymentMethod;
use Concrete\Package\CommunityStore\Src\CommunityStore\Shipping\Method\ShippingMethodType as ShippingMethodType;
use Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Installer;
use Concrete\Core\Support\Facade\Route;
use Concrete\Core\Asset\Asset;
use Concrete\Core\Asset\AssetList;
use Concrete\Core\Support\Facade\Url;
use Core;
use Concrete\Core\Multilingual\Page\Section\Section;

class Controller extends Package
{
    protected $pkgHandle = 'community_store';
    protected $appVersionRequired = '8.0';
    protected $pkgVersion = '2.0.6.2.3';

    protected $pkgAutoloaderRegistries = array(
        'src/CommunityStore' => '\Concrete\Package\CommunityStore\Src\CommunityStore',
        'src/Concrete/Attribute' => 'Concrete\Package\CommunityStore\Attribute'
    );

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
        $this->registerCategories();
        parent::install();
        $this->installStore();
    }

    public function upgrade()
    {
        $pkg = Package::getByHandle('community_store');

        Installer::installSinglePage('/dashboard/store/multilingual', $pkg);
        Installer::installSinglePage('/dashboard/store/multilingual/products', $pkg);
        Installer::installSinglePage('/dashboard/store/multilingual/checkout', $pkg);
        Installer::installSinglePage('/dashboard/store/multilingual/common', $pkg);


        parent::upgrade();



        Installer::upgrade($pkg);
        $cms = Core::make('app');
        $cms->clearCaches();
    }

    public function testForUpgrade() {
        $community_store = $this->app->make('Concrete\Core\Package\PackageService')->getByHandle('community_store');

        if ($community_store) {
            $installedversion = $community_store->getPackageVersion();

            if (version_compare($installedversion, '2.0', '<')) {
                $errors = $this->app->make('error');
                $errors->add(t('Upgrading version 1.x version of Community Store to 2.x is not currently supported. Please immediately revert your community_store package folder to the %s release.',$installedversion ));

                return $errors;
            }
        }

        return parent::testForUpgrade();
    }

    public function registerRoutes()
    {
        Route::register('/productmodal', '\Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductModal::getProductModal');
        Route::register('/productfinder', '\Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\ProductFinder::getProductMatch');
        Route::register('/store_download/{fID}/{oID}/{hash}', '\Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Checkout::downloadFile');
    }

    public function on_start()
    {
        $this->registerRoutes();
        $this->registerCategories();

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

        $this->app->singleton('cshelper/multilingual', 'Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Multilingual');
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
        $c = \Page::getCurrentPage();
        $al = Section::getBySectionOfSite($c);
        $langpath = '';
        if ($al !== null) {
            $langpath =  $al->getCollectionHandle();
        }

        return "
        <script type=\"text/javascript\">
            var PRODUCTMODAL = '" . URL::to('/productmodal') . "';
            var CARTURL = '" . rtrim(URL::to($langpath . '/cart'), '/') . "';
            var TRAILINGSLASH = '" . ((bool) \Config::get('concrete.seo.trailing_slash', false) ? '/' : '') . "';
            var CHECKOUTURL = '" . rtrim(URL::to($langpath . '/checkout'), '/') . "';
            var QTYMESSAGE = '" . t('Quantity must be greater than zero') . "';
        </script>
        ";
    }

    private function registerCategories() {
        $this->app['manager/attribute/category']->extend('store_product',
            function($app) {
                return $app->make('Concrete\Package\CommunityStore\Attribute\Category\ProductCategory');
            });

        $this->app['manager/attribute/category']->extend('store_order',
            function($app) {
                return $app->make('Concrete\Package\CommunityStore\Attribute\Category\OrderCategory');
            });
    }
}


