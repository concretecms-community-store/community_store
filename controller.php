<?php
namespace Concrete\Package\CommunityStore;

use Concrete\Core\Entity\Attribute\Key\Key;
use Concrete\Core\Error\UserMessageException;
use Concrete\Core\Http\Request;
use Concrete\Core\Asset\Asset;
use Concrete\Core\Asset\AssetList;
use Concrete\Core\Command\Task\Manager as TaskManager;
use Concrete\Core\Config\Repository\Repository;
use Concrete\Core\Database\EntityManager\Provider\ProviderAggregateInterface;
use Concrete\Core\Database\EntityManager\Provider\StandardPackageProvider;
use Concrete\Core\Multilingual\Page\Section\Section;
use Concrete\Core\Package\Package;
use Concrete\Core\Page\Page;
use Concrete\Core\Page\Template as PageTemplate;
use Concrete\Core\Page\Type\Type as PageType;
use Concrete\Core\Support\Facade\Route;
use Concrete\Core\Support\Facade\Url;
use Concrete\Package\CommunityStore\Src\CommunityStore\Payment\Method as PaymentMethod;
use Concrete\Package\CommunityStore\Src\CommunityStore\Shipping\Method\ShippingMethodType as ShippingMethodType;
use Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\DoctrineORMEventsSubscriber;
use Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Installer;
use Doctrine\ORM\EntityManagerInterface;

class Controller extends Package implements ProviderAggregateInterface
{
    protected $pkgHandle = 'community_store';
    protected $appVersionRequired = '8.5';
    protected $phpVersionRequired = '7.4';
    protected $pkgVersion = '2.8.0';

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

    public function installStore($pkg, array $options = [])
    {
        $this->registerCategories();
        $installer = $this->app->make(Installer::class);
        $installer->install($pkg, $options);
    }

    public function install()
    {
        $template = PageTemplate::getByHandle('full');

        if (!$template) {
            throw new UserMessageException(t("This package requires that a page template exists with the handle of 'full'. This can be adjusted or removed after the installation if required."));
        }

        $pkg = parent::install();
        if ($this->app->isRunThroughCommandLineInterface()) {
            $args = func_get_args();
            $options = is_array($args[0] ?? null) ? $args[0] : [];
        } else {
            $options = $this->app->make(Request::class)->request->all();
        }
        $this->installStore($pkg, $options);
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
            $config = $this->app->make(Repository::class);
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

        $installer = $this->app->make(Installer::class);
        $installer->upgrade($pkg,  self::$upgradingFromVersion);
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
            $this->app->make(Repository::class)->save('cs.pkgversion', $community_store->getPackageVersion());
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
        $entityManager = $this->app->make(EntityManagerInterface::class);
        $metaData = $entityManager->getMetadataFactory()->getMetadataFor(Key::class);
        foreach (Installer::KEY_CATEGORIES as $name => $className) {
            $metaData->addDiscriminatorMapClass($name, $className);
        }
        $entityManager->getMetadataFactory()->setMetadataFor(Key::class, $metaData);

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
        $al->register('javascript', 'communityStoreFunctions', 'js/communityStoreFunctions.js', ['version' => $version, 'position' => Asset::ASSET_POSITION_HEADER, 'minify' => false, 'combine' => false], $this);
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

        $al->register('javascript', 'tom-select', 'js/tom-select/tom-select.complete.min.js', ['version' => '2.6.2', 'position' => Asset::ASSET_POSITION_HEADER, 'minify' => false, 'combine' => false], $this);
        $al->register('css', 'tom-select', 'css/tom-select/tom-select.default.min.css', ['version' => '2.6.2', 'position' => Asset::ASSET_POSITION_HEADER, 'minify' => false, 'combine' => false], $this);
        $al->register('javascript', 'sortablejs', 'js/sortable/Sortable.min.js', ['version' => '1.15.7', 'position' => Asset::ASSET_POSITION_HEADER, 'minify' => false, 'combine' => false], $this);

        // Assets used by Community Store's dashboard pages and block edit forms
        $al->registerGroup('community-store-dashboard',
            [
                ['javascript', 'tom-select'],
                ['javascript', 'sortablejs'],
                ['javascript', 'communityStoreFunctions'],
                ['css', 'tom-select'],
                ['css', 'communityStoreDashboard'],
            ]
        );

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
                $app->add(new Src\CommunityStore\Console\Command\AutoUpdateProductPageMetadata());
            } catch (\Exception $e) {
            }
        }

        // Tasks are only available in Concrete CMS 9+ (V8 uses the jobs in /jobs)
        if (class_exists(TaskManager::class)) {
            $manager = $this->app->make(TaskManager::class);
            $manager->extend('auto_update_quantities_from_variations', function () {
                return $this->app->make(\Concrete\Package\CommunityStore\Src\CommunityStore\Command\Task\Controller\AutoUpdateQuantitiesFromVariations::class);
            });
            $manager->extend('remove_incomplete_orders', function () {
                return $this->app->make(\Concrete\Package\CommunityStore\Src\CommunityStore\Command\Task\Controller\RemoveIncompleteOrders::class);
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
        $config = app(Repository::class);

        $vars = [
            'CARTURL' => rtrim((string) Url::to($langpath . '/cart'), '/'),
            'TRAILINGSLASH' => (bool) $config->get('concrete.seo.trailing_slash', false) ? '/' : '',
            'CHECKOUTURL' => rtrim((string) Url::to($langpath . '/checkout'), '/'),
            'HELPERSURL' => rtrim((string) Url::to('/helpers'), '/'),
            'QTYMESSAGE' => t('Quantity must be greater than zero'),
            'CHECKOUTSCROLLOFFSET' => (int) $config->get('community_store.checkout_scroll_offset', 0),
            'CURRENCYCODE' => (string) ($config->get('community_store.currency') ?: ''),
            'CURRENCYSYMBOL' => (string) $config->get('community_store.symbol'),
            'CURRENCYDECIMAL' => (string) $config->get('community_store.whole'),
            'CURRENCYGROUP' => (string) $config->get('community_store.thousand'),
        ];

        $js = '';
        foreach ($vars as $name => $value) {
            $js .= '            var ' . $name . ' = ' . json_encode($value, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) . ";\n";
        }

        return "\n        <script type=\"text/javascript\">\n" . $js . "        </script>\n";
    }

    /**
     * Require the core jQuery asset when enabled in the store settings,
     * for custom code that still expects jQuery on store pages and blocks.
     *
     * @param \Concrete\Core\Controller\AbstractController $controller
     */
    public static function requireLegacyJquery($controller)
    {
        if (app(Repository::class)->get('community_store.requireJquery')) {
            $controller->requireAsset('javascript', 'jquery');
        }
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

        return new StandardPackageProvider($this->app, $this, $locations);
    }
}
