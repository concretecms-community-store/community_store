<?php
namespace Concrete\Package\CommunityStore\Controller\SinglePage\Dashboard\Store;

use Concrete\Core\Page\Controller\DashboardPageController;
use Package;
use Core;
use Config;
use Concrete\Package\CommunityStore\Src\CommunityStore\Order\OrderStatus\OrderStatus as StoreOrderStatus;
use Concrete\Package\CommunityStore\Src\CommunityStore\Tax\TaxClass as StoreTaxClass;
use Concrete\Package\CommunityStore\Src\CommunityStore\Payment\Method as StorePaymentMethod;

class Settings extends DashboardPageController
{
    public function view()
    {
        $this->loadFormAssets();
        $this->set("pageSelector", Core::make('helper/form/page_selector'));
        $this->set("countries", Core::make('helper/lists/countries')->getCountries());
        $this->set("states", Core::make('helper/lists/states_provinces')->getStates());
        $this->set("installedPaymentMethods", StorePaymentMethod::getMethods());
        $this->set("orderStatuses", StoreOrderStatus::getAll());
        $targetCID = Config::get('community_store.productPublishTarget');

        if ($targetCID > 0) {
            $parentPage = \Page::getByID($targetCID);

            if (!$parentPage || $parentPage->isError()) {
                $targetCID = false;
            }
        }

        $groupList = [];

        $gl = new \GroupList();
        foreach ($gl->getResults() as $group) {
            $groupList[$group->getGroupID()] = $group->getGroupName();
        }

        $this->set('groupList', $groupList);

        if ($targetCID) {
            $publishTarget = \Page::getByID($targetCID);

            if (!$publishTarget || $publishTarget->isError() || $publishTarget->isInTrash()) {
                $targetCID = false;
            }
        }

        $this->set('productPublishTarget', $targetCID);
    }

    public function loadFormAssets()
    {
        $pkg = Package::getByHandle('community_store');
        $pkgconfig = $pkg->getConfig();
        $this->set('pkgconfig', $pkgconfig);
        $this->requireAsset('css', 'communityStoreDashboard');
        $this->requireAsset('javascript', 'communityStoreFunctions');
        $this->requireAsset('select2');
    }

    public function save()
    {
        $this->view();
        $args = $this->post();

        if ($args) {
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
                Config::save('community_store.productPublishTarget', $args['productPublishTarget']);
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
                $this->redirect('/dashboard/store/settings');
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
        $e = Core::make('helper/validation/error');

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

        return $e;
    }
}
