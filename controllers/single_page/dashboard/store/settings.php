<?php
namespace Concrete\Package\CommunityStore\Controller\SinglePage\Dashboard\Store;

use Concrete\Core\File\Image\Thumbnail\Type\Type as ThumbType;
use Concrete\Core\File\Set\Set as FileSet;
use Concrete\Core\File\Set\SetList;
use Concrete\Core\Form\Service\Widget;
use Concrete\Core\Localization\Localization;
use Concrete\Core\Package\PackageService;
use Concrete\Core\Page\Controller\DashboardPageController;
use Concrete\Core\Page\Page;
use Concrete\Core\Permission\Checker;
use Concrete\Core\Routing\Redirect;
use Concrete\Core\Support\Facade\Config;
use Concrete\Core\Url\Resolver\Manager\ResolverManagerInterface;
use Concrete\Core\User\Group\Group;
use Concrete\Core\User\Group\GroupList;
use Concrete\Package\CommunityStore\Src\CommunityStore\Order\OrderStatus\OrderStatus as OrderStatus;
use Concrete\Package\CommunityStore\Src\CommunityStore\Payment\Method as PaymentMethod;
use Concrete\Package\CommunityStore\Src\CommunityStore\Tax\TaxClass;
use Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\AutoUpdaterQuantitiesFromVariations;
use Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Image;
use Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\OnlineVATChecker;
use Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\ProductImageInfoUpdater;
use Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\ProductPageMetadataUpdater;
use Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\SalesSuspension;
use Punic\Currency;

class Settings extends DashboardPageController
{
    public function view()
    {
        if ($this->request->getMethod() == 'POST') {
            $this->save();
        }

        $this->loadFormAssets();
        $this->set('dateTimeWidget', $this->app->make(Widget\DateTime::class));
        $editor = $this->app->make('editor');
        $editor->getPluginManager()->deselect(['autogrow']);
        $this->set('editor', $editor);
        $this->set('thumbnailTypes', $this->getThumbTypesList());
        $this->set("pageSelector", $this->app->make('helper/form/page_selector'));
        $this->set("countries", $this->app->make('helper/lists/countries')->getCountries());
        $this->set("states", $this->app->make('helper/lists/states_provinces')->getStates());
        $this->set("installedPaymentMethods", PaymentMethod::getMethods());
        $this->set("orderStatuses", OrderStatus::getAll());

        $groupList = [];
        $allGroupList = [];

        $gl = new GroupList();
        foreach ($gl->getResults() as $group) {
            $groupList[$group->getGroupID()] = $group->getGroupName();
        }
        $this->set('groupList', $groupList);

        $gl->includeAllGroups();
        foreach ($gl->getResults() as $group) {
            if ($group->getGroupID() == GUEST_GROUP_ID) {
                // Even registered users belong to the Guests group
                continue;
            }
            $allGroupList[$group->getGroupID()] = $group->getGroupName();
        }

        $this->set('allGroupList', $allGroupList);
        $targetCID = Config::get('community_store.productPublishTarget');

        if ($targetCID) {
            $publishTarget = Page::getByID($targetCID);

            if (!$publishTarget || $publishTarget->isError() || $publishTarget->isInTrash()) {
                $targetCID = false;
            }
        }

        $this->set('productPublishTarget', $targetCID);

        $customerGroupID = Config::get('community_store.customerGroup');
        $customerGroupName = null;

        if ($customerGroupID) {
            $customerGroup = Group::getByID($customerGroupID);

            if (!$customerGroup || !is_object($customerGroup)) {
                $customerGroupID = null;
            } else {
                $customerGroupName = $customerGroup->getGroupName();
            }
        }

        $this->set('customerGroup', $customerGroupID);
        $this->set('customerGroupName', $customerGroupName);

        $wholesaleCustomerGroupID = Config::get('community_store.wholesaleCustomerGroup');

        if ($wholesaleCustomerGroupID) {
            $wholesaleCustomerGroup = Group::getByID($wholesaleCustomerGroupID);

            if (!$wholesaleCustomerGroup || !is_object($wholesaleCustomerGroup)) {
                $wholesaleCustomerGroupID = null;
            }
        }

        $this->set('wholesaleCustomerGroup', $wholesaleCustomerGroupID);

        $fsl = new SetList();
        $fileSets = $fsl->get();
        $sets = [];
        if (count($fileSets)) {
            foreach ($fileSets  as  $fileSet) {
                $sets[$fileSet->getFileSetID()] = $fileSet->getFileSetName();
            }
        }
        $this->set('fileSets', $sets);

        $fsID = Config::get('community_store.digitalDownloadFileSet');

        if ($fsID) {
            $fs = FileSet::getByID($fsID);

            if (!$fs || !is_object($fs)) {
                $fsID = null;
            }
        }

        $this->set('digitalDownloadFileSet', $fsID);

        $currencyList = Currency::getAllCurrencies(false, false,Localization::activeLanguage());
        $this->set('currencyList', $currencyList);

        $this->set('salesSuspension', $this->app->make(SalesSuspension::class));
        $this->set('automaticProductQuantitiesMessage', $this->buildAutomaticProductQuantitiesMessage());

        $this->set('checkVatsOnline', $this->app->make(OnlineVATChecker::class)->isEnabled());
        $this->set('productImageInfoUpdater', $this->app->make(ProductImageInfoUpdater::class));
        $this->set('productPageMetadataUpdater', $this->app->make(ProductPageMetadataUpdater::class));
    }

    public function loadFormAssets()
    {
        $pkg = $this->app->make(PackageService::class)->getByHandle('community_store');
        $pkgconfig = $pkg->getConfig();
        $this->set('pkgconfig', $pkgconfig);
        $this->requireAsset('css', 'communityStoreDashboard');
        $this->requireAsset('javascript', 'communityStoreFunctions');
        $this->requireAsset('selectize');
    }

    protected function getThumbTypesList()
    {
        $thumbTypesList = [];
        $thumbTypesList[] = t('None');
        $thumbTypes = ThumbType::getList();

        if (count($thumbTypes)) {
            foreach ($thumbTypes as $tt) {
                if (!is_object($tt)) {
                    continue;
                }

                $height = $tt->getHeight() ? $tt->getHeight() : t('Automatic');
                $sizingMode = method_exists($tt, 'getSizingModeDisplayName') ? ', ' . $tt->getSizingModeDisplayName() : '';
                $displayName = sprintf('%s (w:%s, h:%s%s)', $tt->getDisplayName(), $tt->getWidth(), $height, $sizingMode);
                $thumbTypesList[$tt->getID()] = htmlentities($displayName, ENT_QUOTES, APP_CHARSET);
            }
        }

        return $thumbTypesList;
    }

    public function save()
    {
        $args = $this->request->request->all();
        if ($args && $this->token->validate('community_store')) {
            $errors = $this->validate($args);
            $this->error = $errors;

            if (!$errors->has()) {
                $salesSuspension = $this->app->make(SalesSuspension::class);
                $dateTimeWidget = $this->app->make(Widget\DateTime::class);

                Config::save('community_store.symbol', $args['symbol']);
                Config::save('community_store.currency', $args['currency']);
                Config::save('community_store.whole', $args['whole']);
                Config::save('community_store.thousand', $args['thousand']);
                Config::save('community_store.calculation', trim($args['calculation']));
                Config::save('community_store.vat_number', (bool)trim($args['vat_number']));
                $this->app->make(OnlineVATChecker::class)->setEnabled(!empty($args['checkVatsOnline']));
                Config::save('community_store.weightUnit', $args['weightUnit']);
                Config::save('community_store.sizeUnit', $args['sizeUnit']);
                Config::save('community_store.deliveryInstructions', $args['deliveryInstructions'] ?? '');
                Config::save('community_store.multiplePackages', $args['multiplePackages'] ?? false);
                Config::save('community_store.notificationemails', $args['notificationEmails']);
                Config::save('community_store.emailalerts', $args['emailAlert']);
                Config::save('community_store.emailalertsname', $args['emailAlertName']);
                Config::save('community_store.setReplyTo', isset($args['setReplyTo']) ? (bool)$args['setReplyTo'] : false);
                Config::save('community_store.customerGroup', $args['customerGroup']);
                Config::save('community_store.wholesaleCustomerGroup', $args['wholesaleCustomerGroup']);
                Config::save('community_store.digitalDownloadFileSet', $args['digitalDownloadFileSet']);
                Config::save('community_store.productPublishTarget', $args['productPublishTarget']);
                Config::save('community_store.defaultSingleProductThumbType', $args['defaultSingleProductThumbType']);
                Config::save('community_store.defaultProductListThumbType', $args['defaultProductListThumbType']);
                Config::save('community_store.defaultSingleProductImageWidth', $args['defaultSingleProductImageWidth'] ?: Image::DEFAULT_SINGLE_PRODUCT_IMG_WIDTH);
                Config::save('community_store.defaultSingleProductImageHeight', $args['defaultSingleProductImageHeight'] ?: Image::DEFAULT_SINGLE_PRODUCT_IMG_HEIGHT);
                Config::save('community_store.defaultProductListImageWidth', $args['defaultProductListImageWidth'] ?: Image::DEFAULT_PRODUCT_LIST_IMG_WIDTH);
                Config::save('community_store.defaultProductListImageHeight', $args['defaultProductListImageHeight'] ?: Image::DEFAULT_PRODUCT_LIST_IMG_HEIGHT);
                Config::save('community_store.defaultSingleProductCrop', $args['defaultSingleProductCrop']);
                Config::save('community_store.defaultProductListCrop', $args['defaultProductListCrop']);
                Config::save('community_store.guestCheckout', $args['guestCheckout']);
                Config::save('community_store.useCaptcha', $args['useCaptcha'] ?? false);
                Config::save('community_store.companyField', $args['companyField']);
                Config::save('community_store.orderNotesEnabled', isset($args['orderNotesEnabled']) ??  false );
                Config::save('community_store.placesAPIKey', trim($args['placesAPIKey']));
                Config::save('community_store.checkout_scroll_offset', intval($args['checkoutScrollOffset']));
                Config::save('community_store.receiptHeader', trim($args['receiptHeader']));
                Config::save('community_store.receiptFooter', trim($args['receiptFooter']));
                Config::save('community_store.receiptBCC', trim($args['receiptBCC']));
                Config::save('community_store.noBillingSave', isset($args['noBillingSave']) ?? false);
                Config::save('community_store.noShippingSave', isset($args['noShippingSave']) ?? false);
                Config::save('community_store.noBillingSaveGroups', isset($args['noBillingSaveGroups']) && is_array($args['noBillingSaveGroups']) ? implode(',', $args['noBillingSaveGroups']) : '');
                Config::save('community_store.noShippingSaveGroups', isset($args['noShippingSaveGroups']) && is_array($args['noShippingSaveGroups']) ? implode(',', $args['noShippingSaveGroups']) : '');
                Config::save('community_store.showUnpaidExternalPaymentOrders', isset($args['showUnpaidExternalPaymentOrders'] ) ? $args['showUnpaidExternalPaymentOrders'] : false);
                Config::save('community_store.numberOfOrders', $args['numberOfOrders']);
                Config::save('community_store.download_expiry_hours', $args['download_expiry_hours']);
                Config::save('community_store.logUserAgent', isset($args['logUserAgent']) ?? false);
                Config::save('community_store.cartMode', $args['cartMode'] ?? false);
                Config::save('community_store.orderCompleteCID', $args['orderCompleteCID']);

                Config::save('community_store.hideStockAvailabilityDates', isset($args['hideStockAvailabilityDates']) ?? false);
                Config::save('community_store.hideWholesalePrice',  isset($args['hideWholesalePrice']) ?? false);
                Config::save('community_store.hideCostPrice', isset($args['hideCostPrice']) ?? false);
                Config::save('community_store::products.hideSize', !empty($args['hideSize']));
                Config::save('community_store::products.hideWeight', !empty($args['hideWeight']));
                Config::save('community_store::products.hideBarcode', !empty($args['hideBarcode']));
                Config::save('community_store.hideVariationPrices', isset($args['hideVariationPrices']) ?? false);
                Config::save('community_store.hideVariationShippingFields', isset($args['hideVariationShippingFields']) ?? false);
                Config::save('community_store.hideSalePrice',  isset($args['hideSalePrice']) ?? false);
                Config::save('community_store.hideCustomerPriceEntry',  isset($args['hideCustomerPriceEntry']) ?? false);
                Config::save('community_store.hideQuantityBasedPricing',  isset($args['hideQuantityBasedPricing']) ?? false);

                Config::save('community_store.productDefaultActive', $args['productDefaultActive'] ?? '') ;
                Config::save('community_store.productDefaultShippingNo', $args['productDefaultShippingNo'] ?? '');
                Config::save('community_store.variationDefaultUnlimited', $args['variationDefaultUnlimited'] ?? '');
                Config::save('community_store.variationMaxVariations', (int) $args['variationMaxVariations'] ? : 50);
                Config::save(AutoUpdaterQuantitiesFromVariations::CONFIGURATION_KEY, !empty($args['automaticProductQuantities']));
                Config::save('community_store.attributesRequireType', $args['attributesRequireType'] == '1');
                Config::save('community_store.enableGtagPurchase', isset($args['enableGtagPurchase']) ?? false);

                if ($args['currency']) {
                    $symbol = Currency::getSymbol($args['currency']);
                    Config::save('community_store.symbol', $symbol);
                }

                //save payment methods
                if ($args['paymentMethodHandle']) {
                    $paymentData = [];

                    foreach ($args['paymentMethodEnabled'] as $pmID => $value) {
                        $paymentData[$pmID]['paymentMethodEnabled'] = $value;
                    }

                    foreach ($args['paymentMethodDisplayName'] as $pmID => $value) {
                        $paymentData[$pmID]['paymentMethodDisplayName'] = $value;
                    }

                    foreach ($args['paymentMethodButtonLabel'] as $pmID => $value) {
                        $paymentData[$pmID]['paymentMethodButtonLabel'] = $value;
                    }

                    foreach ($args['paymentMethodSortOrder'] as $pmID => $value) {
                        $paymentData[$pmID]['paymentMethodSortOrder'] = $value;
                    }

                    if (isset($args['paymentMethodUserGroups'])) {
                        foreach ($args['paymentMethodUserGroups'] as $pmID => $value) {
                            $paymentData[$pmID]['paymentMethodUserGroups'] = $value;
                        }
                    }

                    if (isset($args['paymentMethodExcludedUserGroups'])) {
                        foreach ($args['paymentMethodExcludedUserGroups'] as $pmID => $value) {
                            $paymentData[$pmID]['paymentMethodExcludedUserGroups'] = $value;
                        }
                    }

                    foreach ($paymentData as $pmID => $data) {
                        $pm = PaymentMethod::getByID($pmID);
                        $pm->setEnabled($data['paymentMethodEnabled']);
                        $pm->setDisplayName($data['paymentMethodDisplayName']);
                        $pm->setButtonLabel($data['paymentMethodButtonLabel']);
                        $pm->setSortOrder($data['paymentMethodSortOrder']);

                        if (isset($data['paymentMethodUserGroups'])) {
                            $pm->setUserGroups($data['paymentMethodUserGroups']);
                        } else {
                            $pm->setUserGroups([]);
                        }

                        if (isset($data['paymentMethodExcludedUserGroups'])) {
                            $pm->setExcludedUserGroups($data['paymentMethodExcludedUserGroups']);
                        } else {
                            $pm->setExcludedUserGroups([]);
                        }


                        $controller = $pm->getMethodController();
                        $controller->save($args);
                        $pm->save();
                    }
                }

                $this->saveOrderStatuses($args);
                $salesSuspensionSuspend = empty($args['salesSuspensionSuspend']) || !is_numeric($args['salesSuspensionSuspend']) ? 0 : (int) $args['salesSuspensionSuspend'];
                $salesSuspension
                    ->setSuspended($salesSuspensionSuspend !== 0)
                    ->setSuspensionMessage(isset($args['salesSuspensionMessage']) ? $args['salesSuspensionMessage'] : '')
                    ->setSuspendedFrom($salesSuspensionSuspend === 2 ? $dateTimeWidget->translate('salesSuspensionFrom', $args, true) : null)
                    ->setSuspendedTo($salesSuspensionSuspend === 2 ? $dateTimeWidget->translate('salesSuspensionTo', $args, true) : null)
                ;
                $productImageInfoUpdater = $this->app->make(ProductImageInfoUpdater::class);
                $productImageInfoUpdater->setTitleOperation(is_string($args['autoImageUpdate_title'] ?? null) ? $args['autoImageUpdate_title'] : '');
                $productPageMetadataUpdater = $this->app->make(ProductPageMetadataUpdater::class);
                $productPageMetadataUpdater->setIsUpdateDescription(!empty($args['updatePageMetadata_description']));
                $productPageMetadataUpdater->setIsUpdateOpenGraph(!empty($args['updatePageMetadata_opengraph']));

                $this->flash('success', t('Settings Saved'));

                return Redirect::to('/dashboard/store/settings');
            }
        }
    }

    private function saveOrderStatuses($data)
    {
        if (isset($data['osID'])) {
            foreach ($data['osID'] as $key => $id) {
                $orderStatus = OrderStatus::getByID($id);
                $orderStatusSettings = [
                    'osName' => ((isset($data['osName'][$key]) && '' != $data['osName'][$key]) ?
                        $data['osName'][$key] : $orderStatus->getReadableHandle()),
                    'osInformSite' => isset($data['osInformSite'][$key]) ? 1 : 0,
                    'osInformCustomer' => isset($data['osInformCustomer'][$key]) ? 1 : 0,
                    'osSortOrder' => $key,
                ];
                $orderStatus->update($orderStatusSettings);
            }
            if (isset($data['osIsStartingStatus'])) {
                OrderStatus::setNewStartingStatus(OrderStatus::getByID($data['osIsStartingStatus'])->getHandle());
            } else {
                $orderStatuses = OrderStatus::getAll();
                OrderStatus::setNewStartingStatus($orderStatuses[0]->getHandle());
            }
        }
    }

    public function validate($args)
    {
        $e = $this->app->make('helper/validation/error');
        $nv = $this->app->make('helper/validation/numbers');

        $paymentMethodsEnabled = 0;
        foreach ($args['paymentMethodEnabled'] as $method) {
            if (1 == $method) {
                ++$paymentMethodsEnabled;
            }
        }
        if (0 == $paymentMethodsEnabled) {
            $e->add(t('At least one payment method must be enabled'));
        }
        foreach ($args['paymentMethodEnabled'] as $pmID => $value) {
            $pm = PaymentMethod::getByID($pmID);
            $controller = $pm->getMethodController();
            $e = $controller->validate($args, $e);
        }

        if (!isset($args['osName'])) {
            $e->add(t('You must have at least one Order Status.'));
        }

        //before changing tax settings to "Extract", make sure there's only one rate per class
        if ('extract' == $args['calculation']) {
            $taxClasses = TaxClass::getTaxClasses();
            foreach ($taxClasses as $taxClass) {
                $taxClassRates = $taxClass->getTaxClassRates();
                if (count($taxClassRates) > 1) {
                    $e->add(t("The %s Tax Class can't contain more than 1 Tax Rate if you change how the taxes are calculated", $taxClass->getTaxClassName()));
                }
            }
        }

        $sizeFields = [
            'defaultSingleProductImageWidth',
            'defaultSingleProductImageHeight',
            'defaultProductListImageWidth',
            'defaultProductListImageHeight',
        ];

        foreach ($sizeFields as $field) {
            if (isset($args[$field]) && !empty($args[$field]) && !$nv->integer($args[$field])) {
                $e->add(t("All legacy thumbnail dimensions must be positive integers"));
                break;
            }
        }

        return $e;
    }

    /**
     * @return string
     */
    private function buildAutomaticProductQuantitiesMessage()
    {
        $useTasks = class_exists(\Concrete\Core\Entity\Automation\Task::class);
        $pageUrl = '';
        $pagePath = $useTasks ? '/dashboard/system/automation/tasks' : '/dashboard/system/optimization/jobs';
        $page = Page::getByPath($pagePath);
        if ($page && !$page->isError()) {
            $checker = new Checker($page);
            if ($checker->canViewPage()) {
                $pageUrl = h((string) $this->app->make(ResolverManagerInterface::class)->resolve([$page]));
            }
        }
        $cliName = '<code>' . h($this->app->make(\Concrete\Package\CommunityStore\Src\CommunityStore\Console\Command\AutoUpdateQuantitiesFromVariations::class)->getName()) . '</code>';
        if ($useTasks) {
            $taskName = h($this->app->make(\Concrete\Package\CommunityStore\Src\CommunityStore\Command\Task\Controller\AutoUpdateQuantitiesFromVariations::class)->getName());
            if ($pageUrl !== '') {
                $taskName = "<a href=\"{$pageUrl}\" target=\"_blank\">{$taskName}</a>";
            } else {
                $taskName = "<b>{$taskName}</b>";
            }
            return t(
                'You can automatically update all the products with variations with the %s CLI Command or with the %s Task',
                $cliName,
                $taskName
            );
        }

        $jobName = h($this->app->make(\Concrete\Package\CommunityStore\Job\AutoUpdateQuantitiesFromVariations::class)->getJobName());
        if ($pageUrl !== '') {
            $jobName = "<a href=\"{$pageUrl}\" target=\"_blank\">{$jobName}</a>";
        } else {
            $jobName = "<b>{$taskName}</b>";
        }
        
        return t(
            'You can automatically update all the products with variations with the %s CLI Command or with the %s Automated Job',
            $cliName,
            $jobName
        );
    }
}
