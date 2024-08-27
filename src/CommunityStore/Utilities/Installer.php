<?php

namespace Concrete\Package\CommunityStore\Src\CommunityStore\Utilities;

use ReflectionException;
use Concrete\Core\Job\Job;
use Concrete\Core\Area\Area;
use Concrete\Core\Page\Page;
use Concrete\Core\Entity\Package;
use Concrete\Core\User\Group\Group;
use Concrete\Core\Entity\Page\Template;
use Concrete\Core\Attribute\SetFactory;
use Concrete\Core\Attribute\TypeFactory;
use Concrete\Core\Support\Facade\Config;
use Doctrine\ORM\EntityManagerInterface;
use Concrete\Core\Attribute\Key\Category;
use Concrete\Core\Entity\Automation\Task;
use Concrete\Core\Package\PackageService;
use Concrete\Core\File\Set\Set as FileSet;
use Concrete\Core\Entity\Attribute\Key\Key;
use Concrete\Core\Block\BlockType\BlockType;
use Concrete\Core\Localization\Localization;
use Concrete\Core\Page\Single as SinglePage;
use Concrete\Core\Page\Type\Type as PageType;
use Concrete\Core\Support\Facade\Application;
use Concrete\Core\Command\Task\TaskSetService;
use Concrete\Core\Entity\Attribute\Key\UserKey;
use Concrete\Core\Config\Repository\Repository;
use Concrete\Core\Attribute\Set as AttributeSet;
use Concrete\Core\Page\Template as PageTemplate;
use Concrete\Core\Attribute\AttributeKeyInterface;
use Concrete\Core\Attribute\Type as AttributeType;
use Concrete\Core\Attribute\Category\UserCategory;
use Concrete\Core\Database\DatabaseStructureManager;
use Concrete\Core\User\Group\Command\AddGroupCommand;
use Concrete\Core\Attribute\Category\CategoryService;
use Concrete\Core\Block\BlockType\Set as BlockTypeSet;
use Concrete\Core\Support\Facade\DatabaseORM as dbORM;
use Concrete\Core\Attribute\Category\CategoryInterface;
use Concrete\Core\Entity\Attribute\Set as AttributeSetEntity;
use Illuminate\Contracts\Container\BindingResolutionException;
use Concrete\Core\Entity\Attribute\Type as AttributeTypeEntity;
use Concrete\Core\Attribute\Key\Category as AttributeKeyCategory;
use Doctrine\ORM\Mapping\MappingException as ORMMappingException;
use Concrete\Package\CommunityStore\Src\CommunityStore\Tax\TaxClass;
use Concrete\Core\Entity\Block\BlockType\BlockType as BlockTypeEntity;
use Concrete\Package\CommunityStore\Entity\Attribute\Key\StoreOrderKey;
use Concrete\Package\CommunityStore\Entity\Attribute\Key\StoreProductKey;
use Concrete\Package\CommunityStore\Attribute\Category\OrderCategory;
use Doctrine\Persistence\Mapping\MappingException as PersistenceMappingException;
use Concrete\Package\CommunityStore\Src\CommunityStore\Order\OrderStatus\OrderStatus;
use Concrete\Core\Page\Type\PublishTarget\Type\AllType as PageTypePublishTargetAllType;
use Concrete\Package\CommunityStore\Src\CommunityStore\Payment\Method as PaymentMethod;
use Concrete\Package\CommunityStore\Src\CommunityStore\Shipping\Method\ShippingMethodType;
use Concrete\Core\Page\Type\PublishTarget\Configuration\AllConfiguration as PageTypePublishTargetAllConfiguration;

class Installer
{
    const STORE_PRODUCT_PAGE_TYPE = 'store_product';

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

    const KEY_CATEGORIES = [
        'storeorderkey' => StoreOrderKey::class,
        'storeproductkey' => StoreProductKey::class,
    ];

    private EntityManagerInterface $entityManager;
    private Repository $config;
    private CategoryService $categoryService;

    public function __construct(
        EntityManagerInterface $entityManager,
        Repository $config,
        CategoryService $categoryService
    ) {
        $this->categoryService = $categoryService;
        $this->config = $config;
        $this->entityManager = $entityManager;
    }

    /**
     * @throws ReflectionException|ORMMappingException|PersistenceMappingException|BindingResolutionException
     */
    public function install(Package $package, array $installerOptions = []): void
    {
        $this->createCustomerGroups($package);
        $this->createProductCategory($package);
        $this->createOrderCategory($package);

        $this->addNewCategoriesToDiscriminatorMap();

        $this->createBlockTypes($package);
        $this->createSinglePages($package);
        $this->createStoreProductPageType($package, $installerOptions['pageTemplate'] ?? null);
        $this->createUserAttributes($package);

        if (isset($installerOptions['createParentProductPage'])) {
            $this->installProductParentPage($package, $installerOptions['parentPage'] ?? null);
        }

        $this->createDefaultPaymentMethod($package);
        $this->createDefaultShippingMethodTypes($package);
        $this->createOrderAttributes($package);
        $this->createDefaultTaxClass();
        $this->installOrderStatuses();
        $this->createDigitalDownloadFileset();
        $this->installJobs();
        $this->installTasks();
        $this->setDefaultConfigValues();
    }

    public function upgrade($package)
    {
        $this->createBlockTypes($package);
        $this->createSinglePages($package, true);
        $this->createUserAttributes($package);
        $this->createOrderAttributes($package);

        $this->installJobs();
        $this->installTasks();
        Localization::clearCache();
    }

    private function createCustomerGroups(Package $package) : void
    {
        $customerGroupId = $this->config->get('community_store.customerGroup');

        if (empty($customerGroupId)) {
            $group = Group::getByName('Store Customer');
        } else {
            $group = Group::getByID($customerGroupId);
        }

        if (!$group instanceof Group) {
            $group = $this->createCustomerGroup(
                'Store Customer',
                t('Registered Customer in your store'),
                $package->getPackageID()
            );
        }

        $this->config->save('community_store.customerGroup', $group->getGroupID());

        $group = Group::getByName('Wholesale Customer');

        if (!$group instanceof Group) {
            $group = $this->createCustomerGroup(
                'Wholesale Customer',
                t('These Customers get wholesale pricing in your store.'),
                $package->getPackageID()
            );
        }

        $this->config->save('community_store.wholesaleCustomerGroup', $group->getGroupID());
    }

    private function createProductCategory(Package $package): void
    {
        $category = $this->categoryService->getByHandle('store_product');
        if (!$category instanceof Category) {
            $categoryController = $this->categoryService->add('store_product', 1, $package);
        } else {
            $categoryController = $category->getController();
        }

        $this->associateAttributeKeyTypes($categoryController);
    }

    private function createUserAttributes(Package $package): void
    {
        $category = $this->categoryService->getByHandle('user');
        $category->setAllowAttributeSets(AttributeKeyCategory::ASET_ALLOW_SINGLE);

        /** @var UserCategory $categoryController */
        $categoryController = $category->getController();
        $app = Application::getFacadeApplication();
        $setFactory = $app->make(SetFactory::class);
        $customerInfoSet = $setFactory->getByHandle('customer_info');
        if (!$customerInfoSet instanceof AttributeSetEntity) {
            $customerInfoSet = $categoryController->getSetManager()->addSet('customer_info', t('Store Customer Info'), $package);
        }

        $text = AttributeType::getByHandle('text');
        $address = AttributeType::getByHandle('address');

        $this->installUserAttribute('email', $text, $package, $customerInfoSet, $categoryController);
        $this->installUserAttribute('billing_first_name', $text, $package, $customerInfoSet, $categoryController);
        $this->installUserAttribute('billing_last_name', $text, $package, $customerInfoSet, $categoryController);
        $this->installUserAttribute('billing_address', $address, $package, $customerInfoSet, $categoryController);
        $this->installUserAttribute('billing_phone', $text, $package, $customerInfoSet, $categoryController);
        $this->installUserAttribute('billing_company', $text, $package, $customerInfoSet, $categoryController);
        $this->installUserAttribute('shipping_first_name', $text, $package, $customerInfoSet, $categoryController);
        $this->installUserAttribute('shipping_last_name', $text, $package, $customerInfoSet, $categoryController);
        $this->installUserAttribute('shipping_address', $address, $package, $customerInfoSet, $categoryController);
        $this->installUserAttribute('shipping_company', $text, $package, $customerInfoSet, $categoryController);
        $this->installUserAttribute('vat_number', $text, $package, $customerInfoSet, $categoryController);
    }

    private function createOrderCategory(Package $package): void
    {
        /** @var SetFactory $attributeSetFactory */
        $app = Application::getFacadeApplication();
        $attributeSetFactory = $app->make(SetFactory::class);

        $category = $this->categoryService->getByHandle('store_order');
        /** @var OrderCategory $categoryController */
        if (!$category instanceof Category) {
            $categoryController = $this->categoryService->add('store_order', 1, $package);
        } else {
            $categoryController = $category->getController();
        }

        $this->associateAttributeKeyTypes($categoryController);

        $orderCustomerSet = $attributeSetFactory->getByHandle('order_customer');
        if (!$orderCustomerSet instanceof AttributeSet) {
            $categoryController->getSetManager()->addSet('order_customer', t('Store Customer Info'), $package);
        }

        $orderChoiceSet = $attributeSetFactory->getByHandle('order_choices');
        if (!$orderChoiceSet instanceof AttributeSet) {
            $categoryController->getSetManager()->addSet('order_choices', t('Other Customer Choices'), $package);
        }
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

    private function createSinglePages(Package $package, $upgrade = false): void
    {
        foreach (self::SINGLE_PAGES as $singlePagePath) {
            $this->installSinglePage($singlePagePath, $package);
        }

        if (!$upgrade) {
            foreach (self::ADDITIONAL_SINGLE_PAGES as $singlePagePath) {
                $singlePage = $this->installSinglePage($singlePagePath, $package);

                $singlePage->setAttribute('exclude_nav', 1);
                $singlePage->setAttribute('exclude_search_index', 1);
                $singlePage->setAttribute('exclude_page_list', 1);
            }
        }
    }

    public function createDefaultPaymentMethod(Package $package): void
    {
        $paymentMethod = $this->entityManager->getRepository(PaymentMethod::class)->findOneBy(['pmHandle' => 'invoice']);
        if ($paymentMethod instanceof PaymentMethod) {
            return;
        }

        $paymentMethod = new PaymentMethod();
        $paymentMethod->setHandle('invoice');
        $paymentMethod->setName('Invoice');
        $paymentMethod->setPackageID($package->getPackageID());
        $paymentMethod->setEnabled(true);

        $this->entityManager->persist($paymentMethod);
        $this->entityManager->flush();
    }

    private function createDefaultShippingMethodTypes(Package $package): void
    {
        $shippingMethodTypes = ['flat_rate' => 'Flat Rate', 'free_shipping' => 'Free Shipping'];
        foreach ($shippingMethodTypes as $handle => $name) {
            $shippingMethodType = $this->entityManager->getRepository(ShippingMethodType::class)->findOneBy(['smtHandle' => $handle]);
            if (!$shippingMethodType instanceof ShippingMethodType) {
                $shippingMethodType = new ShippingMethodType();
                $shippingMethodType->setHandle($handle);
                $shippingMethodType->setName($name);
                $shippingMethodType->setPackageID($package->getPackageID());

                $this->entityManager->persist($shippingMethodType);
            }
        }

        $this->entityManager->flush();
    }

    private function setDefaultConfigValues(): void
    {

        $defaultSlug = self::getDefaultSlug();

        $this->config->save('community_store', [
            'productPublishTarget' => Page::getByPath($defaultSlug . '/products')->getCollectionID(),
            'symbol' => '$',
            'whole' => '.',
            'thousand' => ',',
            'sizeUnit' => 'in',
            'weightUnit' => 'lb',
            'taxName' =>  t('Tax'),
            'guestCheckout' => 'always'
        ]);
    }

    private function createDefaultTaxClass(): void
    {
        $taxClass = $this->entityManager->getRepository(TaxClass::class)->findOneBy(['taxClassHandle' => 'default']);
        if ($taxClass instanceof TaxClass) {
            return;
        }

        $taxClass = new TaxClass();
        $taxClass->setHandle('default');
        $taxClass->setName(t('Default'));
        $taxClass->setLocked(true);

        $this->entityManager->persist($taxClass);
        $this->entityManager->flush();
    }

    private function createStoreProductPageType(Package $package, ?int $pageTypeId): void
    {
        $pageType = PageType::getByHandle(self::STORE_PRODUCT_PAGE_TYPE);
        if ($pageType instanceof PageType) {
            return;
        }

        $template = PageTemplate::getByID($pageTypeId);
        if (!$template instanceof Template) {
            return;
        }

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

        $this->setPageTypeDefaults();
    }

    private function installSinglePage($path, $pkg): Page
    {
        $page = Page::getByPath($path);
        if ($page instanceof Page && $page->getError() !== COLLECTION_NOT_FOUND) {
            return $page;
        }

        return SinglePage::add($path, $pkg);
    }


    private function installProductParentPage(Package $package, ?int $parentPageId): void
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
            $productParentPage->addBlock($bt, $main, ['content'=>'<h1>' .t('Products') . '</h1>']);

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

    private function createDigitalDownloadFileset(): void
    {
        $fileSetId = $this->config->get('community_store.digitalDownloadFileSet');
        $fileSet = empty($fileSetId) ? FileSet::getByName(t('Digital Downloads')) : FileSet::getByID($fileSetId);
        if (!$fileSet instanceof FileSet) {
            $fileSet = FileSet::create(t("Digital Downloads"));
        }

        $this->config->save('community_store.digitalDownloadFileSet', $fileSet->getFileSetID());
    }

    private function installOrderStatuses(): void
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

    /** Dependency methods */
    private function createCustomerGroup(string $name, string $description, int $packageId): Group
    {
        $group = Group::add($name, $description);
        return $group;
    }

    private function associateAttributeKeyTypes(CategoryInterface $categoryController): void
    {
        $app = Application::getFacadeApplication();
        $attributeTypeFactory = $app->make(TypeFactory::class);

        $categoryController->associateAttributeKeyType($attributeTypeFactory->getByHandle('text'));
        $categoryController->associateAttributeKeyType($attributeTypeFactory->getByHandle('textarea'));
        $categoryController->associateAttributeKeyType($attributeTypeFactory->getByHandle('number'));
        $categoryController->associateAttributeKeyType($attributeTypeFactory->getByHandle('address'));
        $categoryController->associateAttributeKeyType($attributeTypeFactory->getByHandle('boolean'));
        $categoryController->associateAttributeKeyType($attributeTypeFactory->getByHandle('select'));
        $categoryController->associateAttributeKeyType($attributeTypeFactory->getByHandle('date_time'));
    }

    private function installUserAttribute(
        string $handle,
        AttributeTypeEntity $type,
        Package $pkg,
        AttributeSetEntity $set,
        UserCategory $userCategoryController
    ): void {
        $attributeKey = $userCategoryController->getAttributeKeyByHandle($handle);
        if ($attributeKey instanceof AttributeKeyInterface) {
            return;
        }

        $key = new UserKey();
        $key->setAttributeKeyHandle($handle);
        $app = Application::getFacadeApplication();
        $key->setAttributeKeyName($app->make("helper/text")->unhandle($handle));
        $key = $userCategoryController->add($type, $key, null, $pkg);

        $set->addKey($key);
    }

    /**
     * @throws BindingResolutionException
     */
    public function createOrderAttributes(Package $package): void
    {
        $app = Application::getFacadeApplication();
        $attributeTypeFactory = $app->make(TypeFactory::class);
        $attributeSetFactory = $app->make(SetFactory::class);

        $text = $attributeTypeFactory->getByHandle('text');
        $address = $attributeTypeFactory->getByHandle('address');

        $attributes = [
            'email' => $text,
            'billing_first_name' => $text,
            'billing_last_name' => $text,
            'billing_address' => $address,
            'billing_phone' => $text,
            'billing_company' => $text,
            'shipping_first_name' => $text,
            'shipping_last_name' => $text,
            'shipping_address' => $address,
            'shipping_company' => $text,
            'vat_number' => $text,
        ];

        $orderCategory = $app->make(OrderCategory::class);
        $orderCustomerSet = $attributeSetFactory->getByHandle('order_customer');

        foreach ($attributes as $handle => $type) {
            $attr = $orderCategory->getAttributeKeyByHandle($handle);
            if (is_object($attr)) {
                continue;
            }

            $name = $app->make("helper/text")->unhandle($handle);

            $key = new StoreOrderKey();
            $key->setAttributeKeyHandle($handle);
            $key->setAttributeKeyName(t($name));
            $key->setPackage($package);

            $key = $orderCategory->add($type, $key, null, $package);

            $key->setAttributeSet($orderCustomerSet);
        }
    }

    private function setPageTypeDefaults(): void
    {
        $pageType = PageType::getByHandle(self::STORE_PRODUCT_PAGE_TYPE);
        $template = $pageType->getPageTypeDefaultPageTemplateObject();
        $pageObj = $pageType->getPageTypePageTemplateDefaultPageObject($template);

        $bt = BlockType::getByHandle('community_product');
        $blocks = $pageObj->getBlocks('Main');

        if (count($blocks) > 0) {
            return;
        }

        $pageObj->addBlock($bt, new Area('Main'), [
            'productLocation' => 'page',
            'showProductName' => 1,
            'showProductDescription' => 1,
            'showProductDetails' => 1,
            'showProductPrice' => 1,
            'showImage' => 1,
            'showCartButton' => 1,
            'showGroups' => 1,
        ]);
    }

    /**
     * @throws ReflectionException|ORMMappingException|PersistenceMappingException
     */
    private function addNewCategoriesToDiscriminatorMap(): void
    {
        $metaData = $this->entityManager->getMetadataFactory()->getMetadataFor(Key::class);
        foreach (self::KEY_CATEGORIES as $name => $className) {
            $metaData->addDiscriminatorMapClass($name, $className);
        }
        $this->entityManager->getMetadataFactory()->setMetadataFor(Key::class, $metaData);
    }

    public static function installPaymentMethod($handle, $name, $pkg = null, $displayName = null, $enabled = true)
    {
        $pm = PaymentMethod::getByHandle($handle);
        if (!is_object($pm)) {
            PaymentMethod::add($handle, $name, $pkg, $displayName, $enabled);
        }
    }

    public static function installShippingMethod($handle, $name, $pkg)
    {
        $smt = ShippingMethodType::getByHandle($handle);
        if (!is_object($smt)) {
            ShippingMethodType::add($handle, $name, $pkg);
        }
    }


    public static function refreshEntities()
    {
        $em = dbORM::entityManager();
        $manager = new DatabaseStructureManager($em);
        $manager->refreshEntities();
    }

    public static function prepareUpgradeFromLegacy($db)
    {
        $app = Application::getFacadeApplication();
        $installedVersion = $db->fetchColumn("SELECT pkgVersion from Packages WHERE pkgHandle=?", ['community_store']);
        $installedVersionFromConfig = Config::get('cs.pkgversion');
        $community_store = $app->make('Concrete\Core\Package\PackageService')->getByHandle('community_store');
        if (
            $community_store
            && (
                ($installedVersion && version_compare($installedVersion, '2.0', '<'))
                || ($installedVersionFromConfig && version_compare($installedVersionFromConfig, '2.0', '<'))
            )
        ) {
            $db->query("SET foreign_key_checks = 0");

            // First we have to delete orphan attribute values and keys so constraints can be added to the table
            $db->query("DELETE FROM CommunityStoreProductAttributeValues WHERE NOT EXISTS(SELECT * FROM CommunityStoreProducts WHERE pID = CommunityStoreProductAttributeValues.pID)");
            $db->query("DELETE FROM CommunityStoreOrderAttributeValues WHERE NOT EXISTS(SELECT * FROM CommunityStoreOrders WHERE oID = CommunityStoreOrderAttributeValues.oID)");

            $db->query("DELETE FROM CommunityStoreProductAttributeKeys WHERE NOT EXISTS(SELECT * FROM AttributeKeys WHERE akID = CommunityStoreProductAttributeKeys.akID)");
            $db->query("DELETE FROM CommunityStoreOrderAttributeKeys WHERE NOT EXISTS(SELECT * FROM AttributeKeys WHERE akID = CommunityStoreOrderAttributeKeys.akID)");

            // then we make sure our attributes keys are not marked legacy anymore
            $db->query("UPDATE AttributeKeys SET akCategory=? WHERE akCategory=? AND akID IN (SELECT akID FROM CommunityStoreProductAttributeKeys)", ["storeproductkey", "legacykey"]);
            $db->query("UPDATE AttributeKeys SET akCategory=? WHERE akCategory=? AND akID IN (SELECT akID FROM CommunityStoreOrderAttributeKeys)", ["storeorderkey", "legacykey"]);

            // And we remove them from the LegacyAttributeKeys table
            $db->query("DELETE FROM LegacyAttributeKeys WHERE EXISTS(SELECT * FROM CommunityStoreProductAttributeKeys WHERE akID = LegacyAttributeKeys.akID)");
            $db->query("DELETE FROM LegacyAttributeKeys WHERE EXISTS(SELECT * FROM CommunityStoreOrderAttributeKeys WHERE akID = LegacyAttributeKeys.akID)");

            return $db;
        } else {
            return false;
        }
    }

    public static function getDefaultSlug()
    {
        $app = Application::getFacadeApplication();
        $site = $app->make('site')->getSite();
        $defaultLocale = $site->getDefaultLocale();
        $defaultHome = $defaultLocale->getSiteTree()->getSiteHomePageObject();
        $defaultSlug = '';

        if (is_object($defaultHome)) {
            $defaultSlug = (string)$defaultHome->getCollectionHandle();

            if (!empty($defaultSlug)) {
                $defaultSlug = '/' . $defaultSlug;
            }
        }

        return $defaultSlug;
    }

    private function installJobs()
    {
        if (Job::getByHandle('auto_update_quantities_from_variations') === null) {
            $app = Application::getFacadeApplication();
            $package = $app->make(PackageService::class)->getByHandle('community_store');
            Job::installByPackage('auto_update_quantities_from_variations', $package);
        }
        if (Job::getByHandle('remove_incomplete_orders') === null) {
            $app = Application::getFacadeApplication();
            $package = $app->make(PackageService::class)->getByHandle('community_store');
            Job::installByPackage('remove_incomplete_orders', $package);
        }
    }

    private function installTasks()
    {
        if (!class_exists(Task::class)) {
            return;
        }
        $app = Application::getFacadeApplication();
        $package = $app->make(PackageService::class)->getByHandle('community_store');
        $em = $app->make(EntityManagerInterface::class);
        $taskSetService = $app->make(TaskSetService::class);
        $taskSet = $taskSetService->getByHandle('community_store');
        if ($taskSet === null) {
            $taskSet = $taskSetService->add('community_store', t('Community Store'), $package);
        }
        $task = $em->getRepository(Task::class)->findOneByHandle('auto_update_quantities_from_variations');
        if ($task === null) {
            $task = new Task();
            $task->setHandle('auto_update_quantities_from_variations');
            $task->setPackage($package);
            $em->persist($task);
            $em->flush();
        }
        if (!$taskSetService->taskSetContainsTask($taskSet, $task)) {
            $taskSetService->addTaskToSet($task, $taskSet);
        }

        $task = $em->getRepository(Task::class)->findOneByHandle('remove_incomplete_orders');
        if ($task === null) {
            $task = new Task();
            $task->setHandle('remove_incomplete_orders');
            $task->setPackage($package);
            $em->persist($task);
            $em->flush();
        }
        if (!$taskSetService->taskSetContainsTask($taskSet, $task)) {
            $taskSetService->addTaskToSet($task, $taskSet);
        }

    }
}
