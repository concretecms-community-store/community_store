<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Utilities;

use Package;
use BlockType;
use BlockTypeSet;
use SinglePage;
use Core;
use Page;
use PageTemplate;
use PageType;
use Group;
use Database;
use FileSet;
use Config;
use Localization;
use Concrete\Core\Attribute\Key\Category as AttributeKeyCategory;
use Concrete\Core\Attribute\Key\UserKey as UserAttributeKey;
use Concrete\Core\Attribute\Type as AttributeType;
use AttributeSet;
use Concrete\Core\Page\Type\PublishTarget\Type\AllType as PageTypePublishTargetAllType;
use Concrete\Core\Page\Type\PublishTarget\Configuration\AllConfiguration as PageTypePublishTargetAllConfiguration;
use Concrete\Package\CommunityStore\Src\Attribute\Key\StoreOrderKey as StoreOrderKey;
use Concrete\Package\CommunityStore\Src\CommunityStore\Payment\Method as StorePaymentMethod;
use Concrete\Package\CommunityStore\Src\CommunityStore\Shipping\Method\ShippingMethodType as StoreShippingMethodType;
use Concrete\Package\CommunityStore\Src\CommunityStore\Order\OrderStatus\OrderStatus as StoreOrderStatus;
use Concrete\Package\CommunityStore\Src\CommunityStore\Tax\TaxClass as StoreTaxClass;

defined('C5_EXECUTE') or die(_("Access Denied."));

class Installer
{
    public static function installSinglePages(Package $pkg)
    {
        //install our dashboard single pages
        self::installSinglePage('/dashboard/store', $pkg);
        self::installSinglePage('/dashboard/store/orders/', $pkg);
        self::installSinglePage('/dashboard/store/products/', $pkg);
        self::installSinglePage('/dashboard/store/discounts/', $pkg);
        self::installSinglePage('/dashboard/store/products/attributes', $pkg);
        self::installSinglePage('/dashboard/store/settings/', $pkg);
        self::installSinglePage('/dashboard/store/settings/shipping', $pkg);
        self::installSinglePage('/dashboard/store/settings/shipping/clerk', $pkg);
        self::installSinglePage('/dashboard/store/settings/tax', $pkg);
        self::installSinglePage('/dashboard/store/reports', $pkg);
        self::installSinglePage('/dashboard/store/reports/sales', $pkg);
        self::installSinglePage('/dashboard/store/reports/products', $pkg);
        self::installSinglePage('/cart', $pkg);
        self::installSinglePage('/checkout', $pkg);
        self::installSinglePage('/checkout/complete', $pkg);
        Page::getByPath('/cart/')->setAttribute('exclude_nav', 1);
        Page::getByPath('/checkout/')->setAttribute('exclude_nav', 1);
        Page::getByPath('/checkout/complete')->setAttribute('exclude_nav', 1);
    }
    public static function installSinglePage($path, $pkg)
    {
        $page = Page::getByPath($path);
        if (!is_object($page) || $page->isError()) {
            SinglePage::add($path, $pkg);
        }
    }
    public static function installProductParentPage(Package $pkg)
    {
        $productParentPage = Page::getByPath('/products');
        if (!is_object($productParentPage) || $productParentPage->isError()) {
            $productParentPage = Page::getByID(1)->add(
                PageType::getByHandle('page'),
                array(
                    'cName' => t('Products'),
                    'cHandle' => 'products',
                    'pkgID' => $pkg->getPackageID(),
                )
            );
        }
        $productParentPage->setAttribute('exclude_nav', 1);
    }
    public static function installStoreProductPageType(Package $pkg)
    {
        //install product detail page type
        $pageType = PageType::getByHandle('store_product');
        if (!is_object($pageType)) {
            $template = PageTemplate::getByHandle('full');
            PageType::add(
                array(
                    'handle' => 'store_product',
                    'name' => 'Product Page',
                    'defaultTemplate' => $template,
                    'allowedTemplates' => 'C',
                    'templates' => array($template),
                    'ptLaunchInComposer' => 0,
                    'ptIsFrequentlyAdded' => 0,
                ),
                $pkg
            )->setConfiguredPageTypePublishTargetObject(new PageTypePublishTargetAllConfiguration(PageTypePublishTargetAllType::getByHandle('all')));
        }
    }

    public static function setDefaultConfigValues(Package $pkg)
    {
        self::setConfigValue('community_store.productPublishTarget', Page::getByPath('/products')->getCollectionID());
        self::setConfigValue('community_store.symbol', '$');
        self::setConfigValue('community_store.whole', '.');
        self::setConfigValue('community_store.thousand', ',');
        self::setConfigValue('community_store.sizeUnit', 'in');
        self::setConfigValue('community_store.weightUnit', 'lb');
        self::setConfigValue('community_store.taxName', t('Tax'));
        self::setConfigValue('community_store.sizeUnit', 'in');
        self::setConfigValue('community_store.weightUnit', 'lb');
    }
    public static function setConfigValue($key, $value)
    {
        $config = Config::get($key);
        if (empty($config)) {
            Config::save($key, $value);
        }
    }
    public static function installPaymentMethods(Package $pkg)
    {
        self::installPaymentMethod('invoice', 'Invoice', $pkg, null, true);
    }
    public static function installPaymentMethod($handle, $name, $pkg = null,$displayName = null, $enabled = true)
    {
        $pm = StorePaymentMethod::getByHandle($handle);
        if (!is_object($pm)) {
            StorePaymentMethod::add($handle, $name, $pkg, $displayName, $enabled);
        }
    }
    public static function installShippingMethods(Package $pkg)
    {
        self::installShippingMethod('flat_rate', 'Flat Rate', $pkg);
        self::installShippingMethod('free_shipping', 'Free Shipping', $pkg);
    }

    public static function installShippingMethod($handle, $name, $pkg)
    {
        $smt = StoreShippingMethodType::getByHandle($handle);
        if (!is_object($smt)) {
            StoreShippingMethodType::add($handle, $name, $pkg);
        }
    }

    public static function installBlocks(Package $pkg)
    {
        $bts = BlockTypeSet::getByHandle('community_store');
        if (!is_object($bts)) {
            BlockTypeSet::add("community_store", "Store", $pkg);
        }
        self::installBlock('community_product_list', $pkg);
        self::installBlock('community_utility_links', $pkg);
        self::installBlock('community_product', $pkg);
    }
    public static function installBlock($handle, $pkg)
    {
        $blockType = BlockType::getByHandle($handle);
        if (!is_object($blockType)) {
            BlockType::installBlockType($handle, $pkg);
        }
    }
    public static function setPageTypeDefaults(Package $pkg)
    {
        $pageType = PageType::getByHandle('store_product');
        $template = $pageType->getPageTypeDefaultPageTemplateObject();
        $pageObj = $pageType->getPageTypePageTemplateDefaultPageObject($template);

        $bt = BlockType::getByHandle('community_product');
        $blocks = $pageObj->getBlocks('Main');
        //only install blocks if there's none on there.
        if (count($blocks) < 1) {
            $data = array(
                'productLocation' => 'page',
                'showProductName' => 1,
                'showProductDescription' => 1,
                'showProductDetails' => 1,
                'showProductPrice' => 1,
                'showImage' => 1,
                'showCartButton' => 1,
                'showGroups' => 1,
            );
            $pageObj->addBlock($bt, 'Main', $data);
        }
    }

    public static function installCustomerGroups(Package $pkg)
    {
        $group = Group::getByName('Store Customer');
        if (!$group || $group->getGroupID() < 1) {
            $group = Group::add('Store Customer', t('Registered Customer in your store'));
        }
    }

    public static function installUserAttributes(Package $pkg)
    {
        //user attributes for customers
        $uakc = AttributeKeyCategory::getByHandle('user');
        $uakc->setAllowAttributeSets(AttributeKeyCategory::ASET_ALLOW_MULTIPLE);

        //define attr group, and the different attribute types we'll use
        $custSet = AttributeSet::getByHandle('customer_info');
        if (!is_object($custSet)) {
            $custSet = $uakc->addSet('customer_info', t('Store Customer Info'), $pkg);
        }
        $text = AttributeType::getByHandle('text');
        $address = AttributeType::getByHandle('address');

        self::installUserAttribute('email', $text, $pkg, $custSet);
        self::installUserAttribute('billing_first_name', $text, $pkg, $custSet);
        self::installUserAttribute('billing_last_name', $text, $pkg, $custSet);
        self::installUserAttribute('billing_address', $address, $pkg, $custSet);
        self::installUserAttribute('billing_phone', $text, $pkg, $custSet);
        self::installUserAttribute('shipping_first_name', $text, $pkg, $custSet);
        self::installUserAttribute('shipping_last_name', $text, $pkg, $custSet);
        self::installUserAttribute('shipping_address', $address, $pkg, $custSet);
    }
    public static function installUserAttribute($handle, $type, $pkg, $set, $data = null)
    {
        $attr = UserAttributeKey::getByHandle($handle);
        if (!is_object($attr)) {
            $name = Core::make("helper/text")->camelcase($handle);
            if (!$data) {
                $data = array(
                    'akHandle' => $handle,
                    'akName' => t($name),
                    'akIsSearchable' => false,
                    'uakProfileEdit' => true,
                    'uakProfileEditRequired' => false,
                    'uakRegisterEdit' => false,
                    'uakProfileEditRequired' => false,
                    'akCheckedByDefault' => true,
                );
            }
            UserAttributeKey::add($type, $data, $pkg)->setAttributeSet($set);
        }
    }

    public static function installOrderAttributes(Package $pkg)
    {
        //create custom attribute category for orders
        $oakc = AttributeKeyCategory::getByHandle('store_order');
        if (!is_object($oakc)) {
            $oakc = AttributeKeyCategory::add('store_order', AttributeKeyCategory::ASET_ALLOW_SINGLE, $pkg);
            $oakc->associateAttributeKeyType(AttributeType::getByHandle('text'));
            $oakc->associateAttributeKeyType(AttributeType::getByHandle('textarea'));
            $oakc->associateAttributeKeyType(AttributeType::getByHandle('number'));
            $oakc->associateAttributeKeyType(AttributeType::getByHandle('address'));
            $oakc->associateAttributeKeyType(AttributeType::getByHandle('boolean'));
            $oakc->associateAttributeKeyType(AttributeType::getByHandle('date_time'));

            $orderCustSet = $oakc->addSet('order_customer', t('Store Customer Info'), $pkg);
        }

        $text = AttributeType::getByHandle('text');
        $address = AttributeType::getByHandle('address');

        self::installOrderAttribute('email', $text, $pkg, $orderCustSet);
        self::installOrderAttribute('billing_first_name', $text, $pkg, $orderCustSet);
        self::installOrderAttribute('billing_last_name', $text, $pkg, $orderCustSet);
        self::installOrderAttribute('billing_address', $address, $pkg, $orderCustSet);
        self::installOrderAttribute('billing_phone', $text, $pkg, $orderCustSet);
        self::installOrderAttribute('shipping_first_name', $text, $pkg, $orderCustSet);
        self::installOrderAttribute('shipping_last_name', $text, $pkg, $orderCustSet);
        self::installOrderAttribute('shipping_address', $address, $pkg, $orderCustSet);
    }

    public static function installOrderAttribute($handle, $type, $pkg, $set, $data = null)
    {
        $attr = StoreOrderKey::getByHandle($handle);
        if (!is_object($attr)) {
            $name = Core::make("helper/text")->camelcase($handle);
            if (!$data) {
                $data = array(
                    'akHandle' => $handle,
                    'akName' => t($name),
                );
            }
            StoreOrderKey::add($type, $data, $pkg)->setAttributeSet($set);
        }
    }

    public static function installProductAttributes(Package $pkg)
    {
        //create custom attribute category for products
        $pakc = AttributeKeyCategory::getByHandle('store_product');
        if (!is_object($pakc)) {
            $pakc = AttributeKeyCategory::add('store_product', AttributeKeyCategory::ASET_ALLOW_SINGLE, $pkg);
            $pakc->associateAttributeKeyType(AttributeType::getByHandle('text'));
            $pakc->associateAttributeKeyType(AttributeType::getByHandle('textarea'));
            $pakc->associateAttributeKeyType(AttributeType::getByHandle('number'));
            $pakc->associateAttributeKeyType(AttributeType::getByHandle('address'));
            $pakc->associateAttributeKeyType(AttributeType::getByHandle('boolean'));
            $pakc->associateAttributeKeyType(AttributeType::getByHandle('date_time'));
        }
    }

    public static function createDDFileset(Package $pkg)
    {
        //create fileset to place digital downloads
        $fs = FileSet::getByName(t('Digital Downloads'));
        if (!is_object($fs)) {
            FileSet::add(t("Digital Downloads"));
        }
    }

    public static function installOrderStatuses(Package $package)
    {
        $table = StoreOrderStatus::getTableName();
        $db = Database::connection();
        $statuses = array(
            array('osHandle' => 'incomplete', 'osName' => t('Incomplete'), 'osInformSite' => 1, 'osInformCustomer' => 0, 'osIsStartingStatus' => 0),
            array('osHandle' => 'pending', 'osName' => t('Pending'), 'osInformSite' => 1, 'osInformCustomer' => 1, 'osIsStartingStatus' => 1),
            array('osHandle' => 'processing', 'osName' => t('Processing'), 'osInformSite' => 1, 'osInformCustomer' => 1, 'osIsStartingStatus' => 0),
            array('osHandle' => 'shipped', 'osName' => t('Shipped'), 'osInformSite' => 1, 'osInformCustomer' => 1, 'osIsStartingStatus' => 0),
            array('osHandle' => 'complete', 'osName' => t('Complete'), 'osInformSite' => 1, 'osInformCustomer' => 1, 'osIsStartingStatus' => 0),
        );
        foreach ($statuses as $status) {
            $row = $db->GetRow("SELECT * FROM " . $table . " WHERE osHandle=?", array($status['osHandle']));
            if (!isset($row['osHandle'])) {
                StoreOrderStatus::add($status['osHandle'], $status['osName'], $status['osInformSite'], $status['osInformCustomer'], $status['osIsStartingStatus']);
            } else {
                $orderStatus = StoreOrderStatus::getByID($row['osID']);
                $orderStatus->update($status, true);
            }
        }
    }

    public static function installDefaultTaxClass($pkg)
    {
        $defaultTaxClass = StoreTaxClass::getByHandle("default");
        if (!is_object($defaultTaxClass)) {
            $data = array(
                'taxClassName' => t('Default'),
                'taxClassLocked' => true,
            );
            $defaultTaxClass = StoreTaxClass::add($data);
        }
    }

    public static function upgrade(Package $pkg)
    {
        // now we refresh all blocks
        $items = $pkg->getPackageItems();
        if (is_array($items['block_types'])) {
            foreach ($items['block_types'] as $item) {
                $item->refresh();
            }
        }
        Localization::clearCache();
    }
}
