<?php

namespace Concrete\Package\CommunityStore\Src\CommunityStore\Utilities;

use Concrete\Core\Area\Area;
use Concrete\Core\Attribute\Category\CategoryService;
use Concrete\Core\Attribute\Key\Category;
use Concrete\Core\Config\Repository\Repository;
use Concrete\Core\Entity\Package;
use Concrete\Core\Page\Page;
use Concrete\Core\User\Group\Group;
use Concrete\Core\Support\Facade\Config;
use Concrete\Core\File\Set\Set as FileSet;
use Concrete\Core\Page\Single as SinglePage;
use Concrete\Core\Block\BlockType\BlockType;
use Concrete\Core\Localization\Localization;
use Concrete\Core\Support\Facade\Application;
use Concrete\Core\Page\Type\Type as PageType;
use Concrete\Core\Entity\Attribute\Key\UserKey;
use Concrete\Core\Attribute\Set as AttributeSet;
use Concrete\Core\Page\Template as PageTemplate;
use Concrete\Core\Attribute\Type as AttributeType;
use Concrete\Core\Attribute\Key\Category as AttributeKeyCategory;
use Concrete\Package\CommunityStore\Src\CommunityStore\Entity\PaymentMethod;
use Concrete\Package\CommunityStore\Src\CommunityStore\Tax\TaxClass;
use Concrete\Package\CommunityStore\Entity\Attribute\Key\StoreOrderKey;
use Concrete\Package\CommunityStore\Src\CommunityStore\Order\OrderStatus\OrderStatus;
use Concrete\Core\Page\Type\PublishTarget\Type\AllType as PageTypePublishTargetAllType;
use Concrete\Package\CommunityStore\Src\CommunityStore\Shipping\Method\ShippingMethodType;
use Concrete\Core\Page\Type\PublishTarget\Configuration\AllConfiguration as PageTypePublishTargetAllConfiguration;
use Concrete\Core\Entity\Block\BlockType\BlockType as BlockTypeEntity;
use Concrete\Core\Block\BlockType\Set as BlockTypeSet;
use Doctrine\ORM\EntityManagerInterface;

class Installer
{
    const BLOCK_TYPES = [
        'community_product_list',
        'community_utility_links',
        'community_product',
        'community_product_filter'
    ];

    const SINGLE_PAGES = [
        '/dashboard/store',
        '/dashboard/store/overview/',
        '/dashboard/store/orders/',
        '/dashboard/store/orders/attributes',
        '/dashboard/store/products/',
        '/dashboard/store/discounts/',
        '/dashboard/store/products/groups',
        '/dashboard/store/products/categories',
        '/dashboard/store/products/attributes',
        '/dashboard/store/products/types',
        '/dashboard/store/manufacturers/',
        '/dashboard/store/settings/',
        '/dashboard/store/settings/shipping',
        '/dashboard/store/settings/tax',
        '/dashboard/store/reports',
        '/dashboard/store/reports/sales',
        '/dashboard/store/reports/products',
        '/dashboard/store/multilingual',
        '/dashboard/store/multilingual/products',
        '/dashboard/store/multilingual/checkout',
        '/dashboard/store/multilingual/common',
    ];

    const ADDITIONAL_SINGLE_PAGES = [
        '/cart',
        '/checkout',
        '/checkout/complete'
    ];

    public function __construct(
        private Application $application,
        private EntityManagerInterface $entityManager
    ) {}

    public function install(Package $package, array $installerOptions = []): void
    {
        $this->createBlockTypes($package);
        $this->createSinglePages($package);
        $this->createDefaultPaymentMethod($package);
        $this->installDefaultShippingMethods($package);
        $this->setDefaultConfigValues();

        $this->installProductParentPage($package);
        $this->createStoreProductPageType($package);

        $this->setPageTypeDefaults();
        $this->installCustomerGroups();
        $this->installUserAttributes($package);
        $this->installOrderAttributes($package);
        $this->installProductAttributes($package);
        $this->createDDFileset();
        $this->installOrderStatuses();
        $this->installDefaultTaxClass($package);
    }

    private function createBlockTypes(Package $package): void
    {
        $blockTypeSet = BlockTypeSet::getByHandle($package->getPackageHandle());
        if (!$blockTypeSet instanceof BlockTypeSet) {
            BlockTypeSet::add("community_store", "Store", $package);
        }

        foreach (self::BLOCK_TYPES as $handle) {
            $blockType = BlockType::getByHandle($handle);
            if (!$blockType instanceof BlockTypeEntity) {
                BlockType::installBlockType($handle, $package);
            } else {
                $blockType->refresh();
            }
        }
    }

    private function createSinglePages(Package $package): void
    {
        foreach (self::SINGLE_PAGES as $singlePagePath) {
            $this->installSinglePage($singlePagePath, $package);
        }

        foreach (self::ADDITIONAL_SINGLE_PAGES as $singlePagePath) {
            $singlePage = $this->installSinglePage($singlePagePath, $package);

            $singlePage->setAttribute('exclude_nav', 1);
            $singlePage->setAttribute('exclude_search_index', 1);
            $singlePage->setAttribute('exclude_page_list', 1);
        }
    }

    public function createDefaultPaymentMethod(Package $package): void
    {
        $paymentMethod = $this->entityManager->getRepository(PaymentMethod::class)->findOneBy(['handle' => 'invoice']);
        if ($paymentMethod instanceof PaymentMethod) {
            return;
        }

        $paymentMethod = new PaymentMethod();
        $paymentMethod->setHandle('invoice')
            ->setName('Invoice')
            ->setPackage($package)
            ->setEnabled(true);

        $this->entityManager->persist($paymentMethod);
        $this->entityManager->flush();
    }

    private function setDefaultConfigValues(): void
    {
        /** @var Repository $config */
        $config = $this->application->make('config');
        $config->save('community_store', [
            'symbol' => '$',
            'whole' => '.',
            'thousand' => ',',
            'sizeUnit' => 'in',
            'weightUnit' => 'lb',
            'taxName' =>  t('Tax'),
            'guestCheckout' => 'always'
        ]);
    }

    private function createStoreProductPageType(Package $package, int $pageTypeId): void
    {
        $pageType = PageType::getByHandle('store_product');
        if ($pageType instanceof PageType) {
            return;
        }

        $template = PageTemplate::getByID($pageTypeId);
        $pageTypeOptions = [
            'handle' => 'store_product',
            'name' => t('Product'),
            'defaultTemplate' => $template,
            'allowedTemplates' => 'C',
            'templates' => [$template],
            'ptLaunchInComposer' => 0,
            'ptIsFrequentlyAdded' => 0,
        ];
        $publishTarget = new PageTypePublishTargetAllConfiguration(
            PageTypePublishTargetAllType::getByHandle('all')
        );

        PageType::add($pageTypeOptions, $package)->setConfiguredPageTypePublishTargetObject($publishTarget);
    }

    private function installSinglePage($path, $pkg): Page
    {
        $page = Page::getByPath($path);
        if ($page instanceof Page || $page->getError() !== COLLECTION_NOT_FOUND) {
            return $page;
        }

        return SinglePage::add($path, $pkg);
    }

    private function installProductParentPage(Package $package): void
    {
        $defaultSlug = self::getDefaultSlug();
        $productParentPage = Page::getByPath($defaultSlug . '/products');
        if (!is_object($productParentPage) || $productParentPage->isError()) {
            if ($defaultSlug === '' || $defaultSlug === '/' || !$defaultSlug) {
                $parentPage = Page::getByID(1);
            } else {
                $parentPage = Page::getByPath($defaultSlug);
            }

            $productParentPage = $parentPage->add(
                PageType::getByHandle('page'),
                [
                    'cName' => t('Products'),
                    'cHandle' => 'products',
                    'pkgID' => $package->getPackageID(),
                ]
            );


            $main = new Area('Main');

            $bt = BlockType::getByHandle('content');
            $productParentPage->addBlock($bt, $main, ['content'=>'<h1>' .t('Products') , '</h1>']);

            $bt = BlockType::getByHandle('community_product_list');
            $data = [
                'sortOrder' =>  'alpha',
                'filter' =>  'all',
                'filterCID' =>  0,
                'relatedPID' =>  0,
                'groupMatchAny' =>  0,
                'maxProducts' =>  10,
                'showOutOfStock' =>  0,
                'productsPerRow' =>  1,
                'displayMode' =>  'grid',
                'showPagination' => 1,
                'enableExternalFiltering' =>  1,
                'showFeatured' =>  0,
                'showSale' =>  0,
                'showDescription' =>  1,
                'showName' =>  1,
                'showPrice' =>  1,
                'showQuickViewLink' =>  0,
                'showPageLink' =>  1,
                'showSortOption' =>  0,
                'pageLinkText' =>  '',
                'showAddToCart' =>  1,
                'btnText' =>  '',
                'showQuantity' =>  0,
                'noProductsMessage' =>  ''
            ];

            $productParentPage->addBlock($bt, $main, $data);
        }
    }

    private function installProductAttributes(Package $package): void
    {
        /** @var CategoryService $categoryService */
        $categoryService = $this->application->make(CategoryService::class);
        $category = $categoryService->getByHandle('store_product');
        if (!$category instanceof \Concrete\Core\Entity\Attribute\Category) {
            $category = $categoryService->add('store_product', 1, $package);
        }

        $category->getController()->associateAttributeKeyType(AttributeType::getByHandle('text'));
        $category->getController()->associateAttributeKeyType(AttributeType::getByHandle('textarea'));
        $category->getController()->associateAttributeKeyType(AttributeType::getByHandle('number'));
        $category->getController()->associateAttributeKeyType(AttributeType::getByHandle('address'));
        $category->getController()->associateAttributeKeyType(AttributeType::getByHandle('boolean'));
        $category->getController()->associateAttributeKeyType(AttributeType::getByHandle('select'));
        $category->getController()->associateAttributeKeyType(AttributeType::getByHandle('date_time'));
    }

//    public function getDefaultSlug()
//    {
//        $site = $this->app->make('site')->getSite();
//        $defaultLocale = $site->getDefaultLocale();
//        $defaultHome = $defaultLocale->getSiteTree()->getSiteHomePageObject();
//        $defaultSlug = '';
//
//        if (is_object($defaultHome)) {
//            $defaultSlug = (string)$defaultHome->getCollectionHandle();
//
//            if (!empty($defaultSlug)) {
//                $defaultSlug = '/' . $defaultSlug;
//            }
//        }
//
//        return $defaultSlug;
//    }

    public static function installShippingMethods($pkg)
    {
        self::installShippingMethod('flat_rate', 'Flat Rate', $pkg);
        self::installShippingMethod('free_shipping', 'Free Shipping', $pkg);
    }

    public static function installShippingMethod($handle, $name, $pkg)
    {
        $smt = ShippingMethodType::getByHandle($handle);
        if (!is_object($smt)) {
            ShippingMethodType::add($handle, $name, $pkg);
        }
    }

    public static function setPageTypeDefaults()
    {
        $pageType = PageType::getByHandle('store_product');
        $template = $pageType->getPageTypeDefaultPageTemplateObject();
        $pageObj = $pageType->getPageTypePageTemplateDefaultPageObject($template);

        $bt = BlockType::getByHandle('community_product');
        $blocks = $pageObj->getBlocks('Main');
        //only install blocks if there's none on there.
        if (count($blocks) < 1) {
            $data = [
                'productLocation' => 'page',
                'showProductName' => 1,
                'showProductDescription' => 1,
                'showProductDetails' => 1,
                'showProductPrice' => 1,
                'showImage' => 1,
                'showCartButton' => 1,
                'showGroups' => 1,
            ];
            $pageObj->addBlock($bt, 'Main', $data);
        }
    }

    public static function installOrderStatuses()
    {
        $table = OrderStatus::getTableName();
        $app = Application::getFacadeApplication();
        $db = $app->make('database')->connection();
        $statuses = [
            ['osHandle' => 'incomplete', 'osName' => t('Awaiting Processing'), 'osInformSite' => 1, 'osInformCustomer' => 0, 'osIsStartingStatus' => 1],
            ['osHandle' => 'processing', 'osName' => t('Processing'), 'osInformSite' => 1, 'osInformCustomer' => 0, 'osIsStartingStatus' => 0],
            ['osHandle' => 'shipped', 'osName' => t('Shipped'), 'osInformSite' => 1, 'osInformCustomer' => 1, 'osIsStartingStatus' => 0],
            ['osHandle' => 'delivered', 'osName' => t('Delivered'), 'osInformSite' => 1, 'osInformCustomer' => 1, 'osIsStartingStatus' => 0],
            ['osHandle' => 'nodelivery', 'osName' => t('Will not deliver'), 'osInformSite' => 1, 'osInformCustomer' => 1, 'osIsStartingStatus' => 0],
            ['osHandle' => 'returned', 'osName' => t('Returned'), 'osInformSite' => 1, 'osInformCustomer' => 0, 'osIsStartingStatus' => 0],
        ];

        $db->query("DELETE FROM " . $table);

        foreach ($statuses as $status) {
            OrderStatus::add($status['osHandle'], $status['osName'], $status['osInformSite'], $status['osInformCustomer'], $status['osIsStartingStatus']);
        }
    }

    public static function installDefaultTaxClass($pkg)
    {
        $defaultTaxClass = TaxClass::getByHandle("default");
        if (!is_object($defaultTaxClass)) {
            $data = [
                'taxClassName' => t('Default'),
                'taxClassLocked' => true,
            ];
            $defaultTaxClass = TaxClass::add($data);
        }
    }

    public static function upgrade($pkg)
    {
        // trigger a reinstall to add the select attribute type to the product category
        self::installProductAttributes($pkg);
        // trigger a reinstall in case new fields have been added
        self::installOrderAttributes($pkg);
        self::installUserAttributes($pkg);
        self::installBlocks($pkg);

        // pass an upgrade value of true, to avoid recreating cart/checkout pages again
        self::createSinglePages($pkg, true);

        // in case the customer group and digital download fileset are not saved in config yet
        self::installCustomerGroups($pkg);
        self::createDDFileset($pkg);

        Localization::clearCache();
    }



    public static function installOrderAttributes($pkg)
    {
        //create custom attribute category for orders

        $orderCategory = Category::getByHandle('store_order');

        if (!is_object($orderCategory)) {
            $orderCategory = Category::add('store_order', 1, $pkg);
        } else {
            $indexer = $orderCategory->getController()->getSearchIndexer();
            if (is_object($indexer)) {
                $indexer->createRepository($orderCategory->getController());
            }
        }

        $orderCategory->associateAttributeKeyType(AttributeType::getByHandle('text'));
        $orderCategory->associateAttributeKeyType(AttributeType::getByHandle('textarea'));
        $orderCategory->associateAttributeKeyType(AttributeType::getByHandle('number'));
        $orderCategory->associateAttributeKeyType(AttributeType::getByHandle('address'));
        $orderCategory->associateAttributeKeyType(AttributeType::getByHandle('boolean'));
        $orderCategory->associateAttributeKeyType(AttributeType::getByHandle('select'));
        $orderCategory->associateAttributeKeyType(AttributeType::getByHandle('date_time'));

        $orderCustSet = AttributeSet::getByHandle('order_customer');
        if (!is_object($orderCustSet)) {
            $orderCustSet = $orderCategory->addSet('order_customer', t('Store Customer Info'), $pkg);
        }

        $orderChoiceSet = AttributeSet::getByHandle('order_choices');
        if (!is_object($orderChoiceSet)) {
            $orderChoiceSet = $orderCategory->addSet('order_choices', t('Other Customer Choices'), $pkg);
        }

        if (!$orderCustSet) {
            $sets = $orderCategory->getAttributeSets();

            foreach ($sets as $set) {
                if ('order_customer' == $set->getAttributeSetHandle()) {
                    $orderCustSet = $set;
                }
            }
        }

        $text = AttributeType::getByHandle('text');
        $address = AttributeType::getByHandle('address');

        self::installOrderAttribute('email', $text, $pkg, $orderCustSet);
        self::installOrderAttribute('billing_first_name', $text, $pkg, $orderCustSet);
        self::installOrderAttribute('billing_last_name', $text, $pkg, $orderCustSet);
        self::installOrderAttribute('billing_address', $address, $pkg, $orderCustSet);
        self::installOrderAttribute('billing_phone', $text, $pkg, $orderCustSet);
        self::installOrderAttribute('billing_company', $text, $pkg, $orderCustSet);
        self::installOrderAttribute('shipping_first_name', $text, $pkg, $orderCustSet);
        self::installOrderAttribute('shipping_last_name', $text, $pkg, $orderCustSet);
        self::installOrderAttribute('shipping_address', $address, $pkg, $orderCustSet);
        self::installOrderAttribute('shipping_company', $text, $pkg, $orderCustSet);
        self::installOrderAttribute('vat_number', $text, $pkg, $orderCustSet, [
            'akHandle' => 'vat_number',
            'akName' => t('VAT Number'),
        ]);
    }

    public static function installOrderAttribute($handle, $type, $pkg, $set, $data = null)
    {
        $app = Application::getFacadeApplication();
        $orderCategory = $app->make('Concrete\Package\CommunityStore\Attribute\Category\OrderCategory');

        $attr = $orderCategory->getAttributeKeyByHandle($handle);

        if (!is_object($attr)) {
            $name = Application::getFacadeApplication()->make("helper/text")->unhandle($handle);

            $key = new StoreOrderKey();
            $key->setAttributeKeyHandle($handle);
            $key->setAttributeKeyName(t($name));
            $key = $orderCategory->add($type, $key, null, $pkg);
            $key->setAttributeSet($set);
        }
    }

    private function installUserAttributes($pkg): void
    {
        //user attributes for customers
        $uakc = AttributeKeyCategory::getByHandle('user');
        $uakc->setAllowAttributeSets(AttributeKeyCategory::ASET_ALLOW_SINGLE);

        //define attr group, and the different attribute types we'll use
        $custSet = AttributeSet::getByHandle('customer_info');
        if (!is_object($custSet)) {
            $custSet = $uakc->addSet('customer_info', t('Store Customer Info'), $pkg);
        }

        $text = AttributeType::getByHandle('text');
        $address = AttributeType::getByHandle('address');

        $this->installUserAttribute('email', $text, $pkg, $custSet);
        $this->installUserAttribute('billing_first_name', $text, $pkg, $custSet);
        $this->installUserAttribute('billing_last_name', $text, $pkg, $custSet);
        $this->installUserAttribute('billing_address', $address, $pkg, $custSet);
        $this->installUserAttribute('billing_phone', $text, $pkg, $custSet);
        $this->installUserAttribute('billing_company', $text, $pkg, $custSet);
        $this->installUserAttribute('shipping_first_name', $text, $pkg, $custSet);
        $this->installUserAttribute('shipping_last_name', $text, $pkg, $custSet);
        $this->installUserAttribute('shipping_address', $address, $pkg, $custSet);
        $this->installUserAttribute('shipping_company', $text, $pkg, $custSet);
        $this->installUserAttribute('vat_number', $text, $pkg, $custSet, [
            'akHandle' => 'vat_number',
            'akName' => t('VAT Number'),
        ]);
    }

    private function installUserAttribute($handle, $type, $pkg, $set, $data = null): void
    {
        $service = $this->application->make(CategoryService::class);
        $categoryEntity = $service->getByHandle('user');
        $category = $categoryEntity->getController();

        $attr = $category->getAttributeKeyByHandle($handle);
        if (!is_object($attr)) {
            $name = $this->application->make("helper/text")->unhandle($handle);

            $key = new UserKey();
            $key->setAttributeKeyHandle($handle);
            $key->setAttributeKeyName(t($name));
            $key = $category->add($type, $key, null, $pkg);

            $key->setAttributeSet($set);
        }
    }

    private function installCustomerGroups(): void
    {
        $groupID = Config::get('community_store.customerGroup');

        if (empty($groupID)) {
            $group = Group::getByName('Store Customer');
        } else {
            $group = Group::getByID($groupID);
        }

        if (!$group || $group->getGroupID() < 1) {
            $group = Group::add('Store Customer', t('Registered Customer in your store'));
        }

        Config::save('community_store.customerGroup', $group->getGroupID());

        $group = Group::getByName('Wholesale Customer');

        if (!$group || $group->getGroupID() < 1) {
            $group = Group::add('Wholesale Customer', t('These Customers get wholesale pricing in your store. '));
        }
        Config::save('community_store.wholesaleCustomerGroup', $group->getGroupID());

    }

    private function createDDFileset(): void
    {
        //create fileset to place digital downloads
        $fsID = Config::get('community_store.digitalDownloadFileSet');

        if (empty($fsID)) {
            $fs = FileSet::getByName(t('Digital Downloads'));
        } else {
            $fs = FileSet::getByID($fsID);
        }

        if (!is_object($fs)) {
            $fs = FileSet::create(t("Digital Downloads"));
        }

        Config::save('community_store.digitalDownloadFileSet', $fs->getFileSetID());
    }
}
