<?php
namespace Concrete\Package\CommunityStore;

use Concrete\Core\Asset\Asset;
use Concrete\Core\Asset\AssetList;
use Concrete\Core\Command\Task\Manager as TaskManager;
use Concrete\Core\Database\EntityManager\Provider\ProviderAggregateInterface;
use Concrete\Core\Database\EntityManager\Provider\StandardPackageProvider;
use Concrete\Core\Multilingual\Page\Section\Section;
use Concrete\Core\Package\Package;
use Concrete\Core\Page\Page;
use Concrete\Core\Page\Template as PageTemplate;
use Concrete\Core\Page\Type\Type as PageType;
use Concrete\Core\Support\Facade\Config;
use Concrete\Core\Support\Facade\Route;
use Concrete\Core\Support\Facade\Url;
use Concrete\Package\CommunityStore\Src\CommunityStore\Payment\Method as PaymentMethod;
use Concrete\Package\CommunityStore\Src\CommunityStore\Shipping\Method\ShippingMethodType as ShippingMethodType;
use Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\DoctrineORMEventsSubscriber;
use Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Installer;
use Doctrine\ORM\EntityManagerInterface;
use Whoops\Exception\ErrorException;

class Controller extends Package implements ProviderAggregateInterface
{
    protected $pkgHandle = 'community_store';
    protected $appVersionRequired = '8.5';
    protected $pkgVersion = '2.6.5-alpha1';

    protected $npmPackages = [
        'sysend' => '1.3.4',
        'chartist' => '0.11.4',
        'chartist-plugin-tooltips' => '0.0.17',
    ];
    protected $pkgAutoloaderRegistries = [
        'src/CommunityStore' => '\Concrete\Package\CommunityStore\Src\CommunityStore',
        'src/Concrete/Attribute' => 'Concrete\Package\CommunityStore\Attribute',
    ];

    /**
     * @var string
     */
    private static $upgradingFromVersion = '';

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
        Installer::installBlocks($pkg);
        Installer::installProductParentPage($pkg);
        Installer::installSinglePages($pkg);
        Installer::installStoreProductPageType($pkg);
        Installer::setDefaultConfigValues($pkg);
        Installer::installPaymentMethods($pkg);
        Installer::installShippingMethods($pkg);
        Installer::setPageTypeDefaults($pkg);
        Installer::installCustomerGroups($pkg);
        Installer::installUserAttributes($pkg);
        Installer::installOrderAttributes($pkg);
        Installer::installProductAttributes($pkg);
        Installer::createDDFileset($pkg);
        Installer::installOrderStatuses($pkg);
        Installer::installDefaultTaxClass($pkg);
        Installer::installJobs();
        Installer::installTasks();
    }

    public function install()
    {
        $template = PageTemplate::getByHandle('full');

        if (!$template) {
            throw new ErrorException(t("This package requires that a page template exists with the handle of 'full'. This can be adjusted or removed after the installation if required."));
        }

        $this->registerCategories();
        parent::install();


        if ($this->app->isRunThroughCommandLineInterface()) {
            $pkg = $this->app->make('Concrete\Core\Package\PackageService')->getByHandle('community_store');
            $this->installStore($pkg);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Package\Package::upgradeCoreData()
     */
    public function upgradeCoreData()
    {
        $packageEntity = $this->getPackageEntity();
        self::$upgradingFromVersion = $packageEntity === null ? '' : (string) $packageEntity->getPackageVersion();
        parent::upgradeCoreData();
    }

    public function upgrade()
    {
        if (self::$upgradingFromVersion !== '' && version_compare(self::$upgradingFromVersion, '2.6.0') < 0) {
            $config = $this->app->make('config');
            $config->save('community_store.salesSuspension.suspend', $config->get('community_store.shoppingDisabled') === 'all');
        }
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

        Installer::upgrade($pkg, self::$upgradingFromVersion);
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
        Route::register('/helpers/shipping/getpaymentmethods', '\Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Payment::getPaymentMethods');
        Route::register('/helpers/shipping/selectshipping', '\Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Shipping::selectShipping');
        Route::register('/helpers/tax/setvatnumber', '\Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Tax::setVatNumber');
        Route::register('/productfinder', '\Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\ProductFinder::getProductMatch');
        Route::register('/store_download/{fID}/{oID}/{hash}', '\Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Download::downloadFile');
        Route::register('/cs/dashboard/vies/status', 'Concrete\Package\CommunityStore\Controller\Dialog\ViesStatus::view');
    }

    private function registerAutoload()
    {
        $file = $this->getPackagePath() . '/vendor/autoload.php';
        if (file_exists($file)) {
            require_once $file;
        }
    }

    public function registerHelpers()
    {
        $singletons = [
            'Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Multilingual' => [
                'cs/helper/multilingual',
            ]
        ];

        $binds = [
            'cs/helper/image' => '\Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Image',
        ];

        foreach ($singletons as $key => $aliases) {
            $this->app->singleton($key);
            foreach ($aliases as $alias) {
                $this->app->alias($key, $alias);
            }
        }

        foreach ($binds as $key => $value) {
            $this->app->bind($key, $value);
        }
    }

    public function on_start()
    {
        $this->registerAutoload();
        $this->registerHelpers();
        $this->registerRoutes();
        $this->registerCategories();
        $this->registerEventSubscribers();

        $version = $this->getPackageVersion();

        $al = AssetList::getInstance();
        $al->register('css', 'community-store', 'css/community-store.css', ['version' => $version, 'position' => Asset::ASSET_POSITION_HEADER, 'minify' => false, 'combine' => false], $this);
        $al->register('css', 'communityStoreDashboard', 'css/communityStoreDashboard.css', ['version' => $version, 'position' => Asset::ASSET_POSITION_HEADER, 'minify' => false, 'combine' => false], $this);
        $al->register('javascript', 'community-store', 'js/communityStore.js', ['version' => $version, 'position' => Asset::ASSET_POSITION_FOOTER, 'minify' => false, 'combine' => false], $this);
        $al->register('javascript', 'sysend', 'js/sysend/sysend.js', ['version' => $this->npmPackages['sysend'], 'position' => Asset::ASSET_POSITION_FOOTER, 'minify' => false, 'combine' => false], $this);
        $al->register('javascript', 'communityStoreFunctions', 'js/communityStoreFunctions.js', ['version' => $version, 'position' => Asset::ASSET_POSITION_FOOTER, 'minify' => false, 'combine' => false], $this);
        $al->register('javascript', 'community-store-autocomplete', 'js/autoComplete.js', ['version' => $version, 'position' => Asset::ASSET_POSITION_FOOTER, 'minify' => false, 'combine' => false], $this);

        $al->register('javascript', 'chartist', 'js/chartist/chartist.min.js', ['version' => $this->npmPackages['chartist'], 'position' => Asset::ASSET_POSITION_FOOTER, 'minify' => false, 'combine' => false], $this);
        $al->register('css', 'chartist', 'css/chartist/chartist.min.css', ['version' => $this->npmPackages['chartist'], 'position' => Asset::ASSET_POSITION_HEADER, 'minify' => false, 'combine' => false], $this);
        $al->register('javascript', 'chartist-tooltip', 'js/chartist/chartist-plugin-tooltip.min.js', ['version' => $this->npmPackages['chartist-plugin-tooltips'], 'position' => Asset::ASSET_POSITION_FOOTER, 'minify' => false, 'combine' => false], $this);
        $al->register('css', 'chartist-tooltip', 'css/chartist/chartist-plugin-tooltip.css', ['version' => $this->npmPackages['chartist-plugin-tooltips'], 'position' => Asset::ASSET_POSITION_HEADER, 'minify' => false, 'combine' => false], $this);
        $al->registerGroup('chartist',
            [
                ['javascript', 'chartist'],
                ['javascript', 'chartist-tooltip'],
                ['css', 'chartist'],
                ['css', 'chartist-tooltip'],
            ]
        );

        $select2 = $al->getAssetGroup('select2');

        if (!$select2) {
            $al->register('css', 'select2', 'css/select2/select2.css', ['version' => '3.5.4', 'position' => Asset::ASSET_POSITION_HEADER, 'minify' => false, 'combine' => false], $this);
            $al->register('javascript', 'select2', 'js/select2.js', ['version' => '3.5.4', 'position' => Asset::ASSET_POSITION_FOOTER, 'minify' => false, 'combine' => false], $this);

            $al->registerGroup('select2',
                [
                    ['javascript', 'select2'],
                    ['css', 'select2']
                ]
            );
        }

        $selectize = $al->getAssetGroup('selectize');

        if (!$selectize) {

            $al->register('css', 'selectize', 'css/selectize/selectize.css', ['version' => '0.12.6', 'position' => Asset::ASSET_POSITION_HEADER, 'minify' => false, 'combine' => false], $this);
            $al->register('javascript', 'selectize', 'js/selectize/selectize.min.js', ['version' => '0.12.6', 'position' => Asset::ASSET_POSITION_FOOTER, 'minify' => false, 'combine' => false], $this);

            $al->registerGroup('selectize',
                [
                    ['javascript', 'selectize'],
                    ['css', 'selectize']
                ]
            );
        }

        $lightbox = $al->getAssetGroup('core/lightbox');

        if (!$lightbox) {

            $al->register('css', 'lightbox', 'css/magnific-popup/magnific-popup.css', ['version' => '1.1.0', 'position' => Asset::ASSET_POSITION_HEADER, 'minify' => false, 'combine' => false], $this);
            $al->register('javascript', 'lightbox', 'js/magnific-popup/jquery.magnific-popup.js', ['version' => '1.1.0', 'position' => Asset::ASSET_POSITION_FOOTER, 'minify' => false, 'combine' => false], $this);

            $al->registerGroup('core/lightbox',
                [
                    ['javascript', 'lightbox'],
                    ['css', 'lightbox']
                ]
            );
        }

        $al->register('css', 'photoswipe', 'css/photoswipe.css', ['version' => '5.4.3'], $this);
        $al->registerGroup('photoswipe',
            [
                ['css', 'photoswipe'],
            ]
        );

        if ($this->app->isRunThroughCommandLineInterface()) {
            try {
                $app = $this->app->make('console');
                $app->add(new Src\CommunityStore\Console\Command\ResetCommand());
                $app->add(new Src\CommunityStore\Console\Command\AutoUpdateQuantitiesFromVariations());
                $app->add(new Src\CommunityStore\Console\Command\AutoUpdateProductImageInfo());
            } catch (\Exception $e) {
            }
        }

        if (class_exists(TaskManager::class)) {
            $manager = $this->app->make(TaskManager::class);
            $manager->extend('auto_update_quantities_from_variations', function () {
                return $this->app->make(\Concrete\Package\CommunityStore\Src\CommunityStore\Command\Task\Controller\AutoUpdateQuantitiesFromVariations::class);
            });
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
            var CURRENCYCODE = '" . (Config::get('community_store.currency') ? Config::get('community_store.currency') : '') . "';
            var CURRENCYSYMBOL = '" . Config::get('community_store.symbol') . "';
            var CURRENCYDECIMAL = '" . Config::get('community_store.whole') . "';
            var CURRENCYGROUP = '" . Config::get('community_store.thousand') . "';
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

    private function registerEventSubscribers()
    {
        $subscriber = $this->app->make(DoctrineORMEventsSubscriber::class);
        $entityManager = $this->app->make(EntityManagerInterface::class);
        $entityManager->getEventManager()->addEventSubscriber($subscriber);
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Database\EntityManager\Provider\ProviderAggregateInterface::getEntityManagerProvider()
     */
    public function getEntityManagerProvider()
    {
        $locations = [
            'src/Concrete/Entity' => 'Concrete\Package\CommunityStore\Entity',
        ];
        foreach ([
            'Discount',
            'Group',
            'Manufacturer',
            'Multilingual',
            'Order',
            'Payment',
            'Product',
            'Shipping',
            'Tax',
        ] as $chunk) {
            $locations['src/CommunityStore/' . $chunk] = 'Concrete\Package\CommunityStore\Src\CommunityStore\\' . $chunk;
        }

        return new StandardPackageProvider($this->app, $this, $locations);;
    }
}
