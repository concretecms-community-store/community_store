<?php
namespace Concrete\Package\CommunityStore\Controller\SinglePage\Dashboard\Store\Settings;

use Concrete\Core\Navigation\Item\Item;
use Concrete\Core\Page\Controller\DashboardPageController;
use Concrete\Core\Routing\Redirect;
use Concrete\Core\User\Group\GroupList;
use Concrete\Package\CommunityStore\Src\CommunityStore\Shipping\Method\ShippingMethod;
use Concrete\Package\CommunityStore\Src\CommunityStore\Shipping\Method\ShippingMethodType;

class Shipping extends DashboardPageController
{
    public function view()
    {
        $this->set("methodTypes", ShippingMethodType::getAvailableMethodTypes());
    }

    public function add($smtID)
    {
        $smt = ShippingMethodType::getByID($smtID);
        if ($smt === null) {
            return $this->buildRedirect('/dashboard/store/settings/shipping');
        }
        $this->set('pageTitle', t("Add Shipping Method"));
        $this->set('sm', false);
        $this->set('smt', $smt);
        $this->set('actionDescription', t('Add Shipping Method'));

        $allGroupList = [];

        $gl = new GroupList();
        $gl->includeAllGroups();
        foreach ($gl->getResults() as $group) {
            $allGroupList[$group->getGroupID()] = $group->getGroupName();
        }

        $this->set('allGroupList', $allGroupList);
        $this->requireAsset('selectize');
        if (method_exists($this, 'createBreadcrumb')) {
            $this->setBreadcrumb($breacrumb = $this->getBreadcrumb() ?: $this->createBreadcrumb());
            $breacrumb->add(new Item('#', t('Add Shipping Method')));
        }
    }

    public function edit($smID)
    {
        $sm = ShippingMethod::getByID($smID);
        if ($sm === null || $sm === false) {
            return $this->buildRedirect('/dashboard/store/settings/shipping');
        }
        $this->set('pageTitle', t("Edit Shipping Method"));
        $smt = $sm->getShippingMethodType();
        $this->set('sm', $sm);
        $this->set('smt', $smt);
        $this->set('actionDescription', t('Update Shipping Method'));

        $allGroupList = [];

        $gl = new GroupList();
        $gl->includeAllGroups();
        foreach ($gl->getResults() as $group) {
            $allGroupList[$group->getGroupID()] = $group->getGroupName();
        }

        $this->set('allGroupList', $allGroupList);
        $this->requireAsset('selectize');
        if (method_exists($this, 'createBreadcrumb')) {
            $this->setBreadcrumb($breacrumb = $this->getBreadcrumb() ?: $this->createBreadcrumb());
            $breacrumb->add(new Item('#', $sm->getName()));
        }
    }

    public function delete($smID)
    {
        $sm = ShippingMethod::getByID($smID);
        $sm->delete();
        $this->flash('success', t('Shipping Method Deleted'));

        return Redirect::to('/dashboard/store/settings/shipping');
    }

    public function add_method()
    {
        $data = $this->request->request->all();
        $errors = $this->validate($data);
        $this->error = null; //clear errors
        $this->error = $errors;
        if (!$errors->has()) {

            $sortOrder = $this->request->request->get('methodSortOrder');

            if (!$sortOrder) {
                $sortOrder = 1;
            }

            if ($this->request->request->get('shippingMethodID')) {
                //update
                $shippingMethod = ShippingMethod::getByID($this->request->request->get('shippingMethodID'));
                if (!$shippingMethod) {
                    return Redirect::to('/dashboard/store/settings/shipping');
                }
                $shippingMethodTypeMethod = $shippingMethod->getShippingMethodTypeMethod();
                $shippingMethodTypeMethod->update($this->request->request->all());
                $shippingMethod->update($this->request->request->get('methodName'),
                                        $this->request->request->get('methodEnabled'),
                                        $this->request->request->get('methodDetails'),
                                        $sortOrder,
                                        $this->request->request->get('methodUserGroups'),
                                        $this->request->request->get('methodExcludedUserGroups')
                );
                $successMessage = t('Shipping Method Updated');
            } else {
                //first we send the data to the shipping method type.
                $shippingMethodType = ShippingMethodType::getByID($this->request->request->get('shippingMethodTypeID'));
                $shippingMethodTypeMethod = $shippingMethodType->addMethod($this->request->request->all());
                //make a shipping method that correlates with it.
                $shippingMethod = ShippingMethod::add($shippingMethodTypeMethod,
                            $shippingMethodType,
                            $this->request->request->get('methodName'), true,
                            $this->request->request->get('methodDetails'),
                            $sortOrder,
                            $this->request->request->get('methodUserGroups'),
                            $this->request->request->get('methodExcludedUserGroups')
                );
                $successMessage = t('Shipping Method Created');
            }
            $methodProductGroupsCriteria = (int) $this->request->request->get('methodProductGroupsCriteria');
            $shippingMethod->setProductGroupsCriteria($methodProductGroupsCriteria);
            if ($methodProductGroupsCriteria !== 0) {
                $postedData = $this->request->request->all();
                $shippingMethod->setProductGroups(isset($postedData['methodProductGroups']) ? $postedData['methodProductGroups'] : []);
            }
            $shippingMethod->save();
            $this->flash('success', $successMessage);
            return Redirect::to('/dashboard/store/settings/shipping');
        } else {
            if ($this->request->request->get('shippingMethodID')) {
                $this->edit($this->request->request->get('shippingMethodID'));
            } else {
                $this->add($this->request->request->get('shippingMethodTypeID'));
            }
        }
    }

    public function validate($data)
    {
        $this->error = null;
        $e = $this->app->make('helper/validation/error');

        //check our manditory fields
        if ("" == $data['methodName']) {
            $e->add(t("Method Name must be set"));
        }

        //pass the validator to the shipping method to check for it's own errors
        $shippingMethodType = ShippingMethodType::getByID($data['shippingMethodTypeID']);
        $e = $shippingMethodType->getMethodTypeController()->validate($data, $e);

        return $e;
    }
}
