<?php
namespace Concrete\Package\CommunityStore\Controller\SinglePage\Dashboard\Store;

use Concrete\Core\Page\Page;
use Concrete\Core\Package\Package;
use Concrete\Core\Routing\Redirect;
use Concrete\Core\User\Group\Group;
use Concrete\Core\User\Group\GroupList;
use Concrete\Core\File\Set\SetList;
use Concrete\Core\File\Set\Set as FileSet;
use Concrete\Core\Support\Facade\Config;
use Concrete\Core\Page\Controller\DashboardPageController;
use Concrete\Core\File\Image\Thumbnail\Type\Type as ThumbType;
use Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Image;
use Concrete\Package\CommunityStore\Src\CommunityStore\Tax\TaxClass as StoreTaxClass;
use Concrete\Package\CommunityStore\Src\CommunityStore\Payment\Method as StorePaymentMethod;
use Concrete\Package\CommunityStore\Src\CommunityStore\Order\OrderStatus\OrderStatus as StoreOrderStatus;

class Settings extends DashboardPageController
{
    public function view()
    {
        $this->loadFormAssets();
        $this->set('thumbnailTypes', $this->getThumbTypesList());
        $this->set("pageSelector", $this->app->make('helper/form/page_selector'));
        $this->set("countries", $this->app->make('helper/lists/countries')->getCountries());
        $this->set("states", $this->app->make('helper/lists/states_provinces')->getStates());
        $this->set("installedPaymentMethods", StorePaymentMethod::getMethods());
        $this->set("orderStatuses", StoreOrderStatus::getAll());

        $groupList = [];

        $gl = new GroupList();
        foreach ($gl->getResults() as $group) {
            $groupList[$group->getGroupID()] = $group->getGroupName();
        }

        $this->set('groupList', $groupList);

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
    }

    public function loadFormAssets()
    {
        $pkg = Package::getByHandle('community_store');
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
        $this->view();
        $args = $this->request->request->all();

        if ($args && $this->token->validate('community_store')) {
            $errors = $this->validate($args);
            $this->error = $errors;

            if (!$errors->has()) {
                Config::save('community_store.symbol', $args['symbol']);
                Config::save('community_store.currency', $args['currency']);
                Config::save('community_store.whole', $args['whole']);
                Config::save('community_store.thousand', $args['thousand']);
                Config::save('community_store.taxenabled', $args['taxEnabled']);
                Config::save('community_store.taxcountry', $args['taxCountry']);
                Config::save('community_store.taxstate', $args['taxState']);
                Config::save('community_store.taxcity', trim($args['taxCity']));
                Config::save('community_store.taxAddress', trim($args['taxAddress']));
                Config::save('community_store.taxMatch', trim($args['taxMatch']));
                Config::save('community_store.taxBased', trim($args['taxBased']));
                Config::save('community_store.taxrate', trim($args['taxRate']));
                Config::save('community_store.taxName', trim($args['taxName']));
                Config::save('community_store.calculation', trim($args['calculation']));
                Config::save('community_store.vat_number', trim($args['vat_number']));
                Config::save('community_store.shippingenabled', $args['shippingEnabled']);
                Config::save('community_store.shippingbase', $args['shippingBasePrice']);
                Config::save('community_store.shippingitem', $args['shippingItemPrice']);
                Config::save('community_store.weightUnit', $args['weightUnit']);
                Config::save('community_store.sizeUnit', $args['sizeUnit']);
                Config::save('community_store.deliveryInstructions', $args['deliveryInstructions']);
                Config::save('community_store.multiplePackages', $args['multiplePackages']);
                Config::save('community_store.notificationemails', $args['notificationEmails']);
                Config::save('community_store.emailalerts', $args['emailAlert']);
                Config::save('community_store.emailalertsname', $args['emailAlertName']);
                Config::save('community_store.customerGroup', $args['customerGroup']);
                Config::save('community_store.digitalDownloadFileSet', $args['digitalDownloadFileSet']);
                Config::save('community_store.productPublishTarget', $args['productPublishTarget']);
                Config::save('community_store.defaultSingleProductThumbType', $args['defaultSingleProductThumbType']);
                Config::save('community_store.defaultProductListThumbType', $args['defaultProductListThumbType']);
                Config::save('community_store.defaultProductModalThumbType', $args['defaultProductModalThumbType']);
                Config::save('community_store.defaultSingleProductImageWidth', $args['defaultSingleProductImageWidth'] ?: Image::DEFAULT_SINGLE_PRODUCT_IMG_WIDTH);
                Config::save('community_store.defaultSingleProductImageHeight', $args['defaultSingleProductImageHeight'] ?: Image::DEFAULT_SINGLE_PRODUCT_IMG_HEIGHT);
                Config::save('community_store.defaultProductListImageWidth', $args['defaultProductListImageWidth'] ?: Image::DEFAULT_PRODUCT_LIST_IMG_WIDTH);
                Config::save('community_store.defaultProductListImageHeight', $args['defaultProductListImageHeight'] ?: Image::DEFAULT_PRODUCT_LIST_IMG_HEIGHT);
                Config::save('community_store.defaultProductModalImageWidth', $args['defaultProductModalImageWidth'] ?: Image::DEFAULT_PRODUCT_MODAL_IMG_WIDTH);
                Config::save('community_store.defaultProductModalImageHeight', $args['defaultProductModalImageHeight'] ?: Image::DEFAULT_PRODUCT_MODAL_IMG_HEIGHT);
                Config::save('community_store.defaultSingleProductCrop', $args['defaultSingleProductCrop']);
                Config::save('community_store.defaultProductListCrop', $args['defaultProductListCrop']);
                Config::save('community_store.defaultProductListCrop', $args['defaultProductModalCrop']);
                Config::save('community_store.guestCheckout', $args['guestCheckout']);
                Config::save('community_store.companyField', $args['companyField']);
                Config::save('community_store.shoppingDisabled', trim($args['shoppingDisabled']));
                Config::save('community_store.placesAPIKey', trim($args['placesAPIKey']));
                Config::save('community_store.receiptHeader', trim($args['receiptHeader']));
                Config::save('community_store.receiptFooter', trim($args['receiptFooter']));
                Config::save('community_store.noBillingSave', trim($args['noBillingSave']));
                Config::save('community_store.noShippingSave', trim($args['noShippingSave']));
                Config::save('community_store.noBillingSaveGroups', is_array($args['noBillingSaveGroups']) ? implode(',', $args['noBillingSaveGroups']) : '');
                Config::save('community_store.noShippingSaveGroups', is_array($args['noShippingSaveGroups']) ? implode(',', $args['noShippingSaveGroups']) : '');
                Config::save('community_store.showUnpaidExternalPaymentOrders', $args['showUnpaidExternalPaymentOrders']);
                Config::save('community_store.numberOfOrders', $args['numberOfOrders']);
                Config::save('community_store.download_expiry_hours', $args['download_expiry_hours']);

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

                    foreach ($paymentData as $pmID => $data) {
                        $pm = StorePaymentMethod::getByID($pmID);
                        $pm->setEnabled($data['paymentMethodEnabled']);
                        $pm->setDisplayName($data['paymentMethodDisplayName']);
                        $pm->setButtonLabel($data['paymentMethodButtonLabel']);
                        $pm->setSortOrder($data['paymentMethodSortOrder']);
                        $controller = $pm->getMethodController();
                        $controller->save($args);
                        $pm->save();
                    }
                }

                $this->saveOrderStatuses($args);
                $this->flash('success', t('Settings Saved'));

                return Redirect::to('/dashboard/store/settings');
            }
        }
    }

    private function saveOrderStatuses($data)
    {
        if (isset($data['osID'])) {
            foreach ($data['osID'] as $key => $id) {
                $orderStatus = StoreOrderStatus::getByID($id);
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
                StoreOrderStatus::setNewStartingStatus(StoreOrderStatus::getByID($data['osIsStartingStatus'])->getHandle());
            } else {
                $orderStatuses = StoreOrderStatus::getAll();
                StoreOrderStatus::setNewStartingStatus($orderStatuses[0]->getHandle());
            }
        }
    }

    public function validate($args)
    {
        $e = $this->app->make('helper/validation/error');
        $nv = $this->app->make('helper/validation/numbers');

        if ("" == $args['symbol']) {
            $e->add(t('You must set a currency symbol'));
        }

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
            $pm = StorePaymentMethod::getByID($pmID);
            $controller = $pm->getMethodController();
            $e = $controller->validate($args, $e);
        }

        if (!isset($args['osName'])) {
            $e->add(t('You must have at least one Order Status.'));
        }

        //before changing tax settings to "Extract", make sure there's only one rate per class
        if ('extract' == $args['calculation']) {
            $taxClasses = StoreTaxClass::getTaxClasses();
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
}
