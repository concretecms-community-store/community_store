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
use Concrete\Core\Multilingual\Page\Section\Section;
use Concrete\Core\Support\Facade\Config;
use Concrete\Core\Page\Type\Type as PageType;
use Concrete\Core\Page\Page;

class Controller extends Package
{
    protected $pkgHandle = 'community_store';
    protected $appVersionRequired = '8.2.1';
    protected $pkgVersion = '2.1.5.1';

    protected $pkgAutoloaderRegistries = [
        'src/CommunityStore' => '\Concrete\Package\CommunityStore\Src\CommunityStore',
        'src/Concrete/Attribute' => 'Concrete\Package\CommunityStore\Attribute',
    ];

    public function getPackageDescription()
    {
        return t("Add a store to your site");
    }

    public function getPackageName()
    {
        return t("Community Store");
    }

    public function installStore($pkg)
    {
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

        $pkg = $this->app->make('Concrete\Core\Package\PackageService')->getByHandle('community_store');
        $this->installStore($pkg);
    }

    public function upgrade()
    {
        $pkg = $this->app->make('Concrete\Core\Package\PackageService')->getByHandle('community_store');
        $db = $this->app->make('database')->connection();
        $db = Installer::prepareUpgradeFromLegacy($db);

        if ($db) {
            parent::upgrade();

            // this was set to false in the Installer so setting it back to normal
            $db->query("SET foreign_key_checks = 1");

            // We need to refresh our entities after install, otherwise the order attributes installation will fail
            Installer::refreshEntities();
        } else {
            parent::upgrade();
        }

        Installer::upgrade($pkg);
        $this->app->clearCaches();
    }

    public function testForInstall($testForAlreadyInstalled = true)
    {
        $community_store = $this->app->make('Concrete\Core\Package\PackageService')->getByHandle('community_store');

        if ($community_store) {
            // this is ridiculous but I found out the hard way that
            // getting the version from inside the upgrade() function
            // was giving me different result depending on the C5 version I was using.
            // So I'm getting the version twice, once here and once in the upgrade function
            // and I check both. I tried to set a variable instead of saving it in config
            // but for some reason it didn't work

            Config::save('cs.pkgversion', $community_store->getPackageVersion());
        }

        return parent::testForInstall($testForAlreadyInstalled);
    }

    public function registerRoutes()
    {
        Route::register('/helpers/stateprovince/getstates', '\Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\StateProvince::getStates');
        Route::register('/helpers/shipping/getshippingmethods', '\Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Shipping::getShippingMethods');
        Route::register('/helpers/shipping/selectshipping', '\Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Shipping::selectShipping');
        Route::register('/helpers/tax/setvatnumber', '\Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Tax::setVatNumber');

        Route::register('/productmodal', '\Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductModal::getProductModal');
        Route::register('/productfinder', '\Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\ProductFinder::getProductMatch');
        Route::register('/store_download/{fID}/{oID}/{hash}', '\Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Download::downloadFile');
    }

    public function registerHelpers()
    {
        $singletons = [
            'cs/helper/image' => '\Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Image',
            'cs/helper/multilingual' => '\Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Multilingual',
        ];

        foreach ($singletons as $key => $value) {
            $this->app->singleton($key, $value);
        }
    }

    public function on_start()
    {
        $this->registerHelpers();
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

        if ($this->app->isRunThroughCommandLineInterface()) {
            try {
                $app = $this->app->make('console');
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

        $pageType = PageType::getByHandle('page');

        if ($pageType) {
            foreach ($pages as $page) {
                $page->setPageType($pageType);
            }
        }

        parent::uninstall();
    }

    public static function returnHeaderJS()
    {
        $c = Page::getCurrentPage();
        $al = Section::getBySectionOfSite($c);
        $langpath = '';
        if (null !== $al) {
            $langpath = $al->getCollectionHandle();
        }

        return "
        <script type=\"text/javascript\">
            var PRODUCTMODAL = '" . Url::to('/productmodal') . "';
            var CARTURL = '" . rtrim(Url::to($langpath . '/cart'), '/') . "';
            var TRAILINGSLASH = '" . ((bool) Config::get('concrete.seo.trailing_slash', false) ? '/' : '') . "';
            var CHECKOUTURL = '" . rtrim(Url::to($langpath . '/checkout'), '/') . "';
            var HELPERSURL = '" . rtrim(Url::to('/helpers'), '/') . "';
            var QTYMESSAGE = '" . t('Quantity must be greater than zero') . "';
            var CHECKOUTSCROLLOFFSET = " . Config::get('community_store.checkout_scroll_offset', 0) . ";
        </script>
        ";
    }

    private function registerCategories()
    {
        $this->app['manager/attribute/category']->extend(
            'store_product',
            function ($app) {
                return $app->make('Concrete\Package\CommunityStore\Attribute\Category\ProductCategory');
            }
        );

        $this->app['manager/attribute/category']->extend(
            'store_order',
            function ($app) {
                return $app->make('Concrete\Package\CommunityStore\Attribute\Category\OrderCategory');
            }
        );
    }
}
