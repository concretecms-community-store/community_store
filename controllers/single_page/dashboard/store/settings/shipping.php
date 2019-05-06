<?php
namespace Concrete\Package\CommunityStore\Controller\SinglePage\Dashboard\Store\Settings;

use Concrete\Core\Routing\Redirect;
use Concrete\Core\Page\Controller\DashboardPageController;
use Concrete\Package\CommunityStore\Src\CommunityStore\Shipping\Method\ShippingMethod as StoreShippingMethod;
use Concrete\Package\CommunityStore\Src\CommunityStore\Shipping\Method\ShippingMethodType as StoreShippingMethodType;

class Shipping extends DashboardPageController
{
    public function view()
    {
        $this->set("methodTypes", StoreShippingMethodType::getAvailableMethodTypes());
    }

    public function add($smtID)
    {
        $this->set('pageTitle', t("Add Shipping Method"));
        $smt = StoreShippingMethodType::getByID($smtID);
        $this->set('smt', $smt);
        $this->set("task", t("Add"));
    }

    public function edit($smID)
    {
        $this->set('pageTitle', t("Edit Shipping Method"));
        $sm = StoreShippingMethod::getByID($smID);
        $smt = $sm->getShippingMethodType();
        $this->set('sm', $sm);
        $this->set('smt', $smt);
        $this->set("task", t("Update"));
    }

    public function delete($smID)
    {
        $sm = StoreShippingMethod::getByID($smID);
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
            if ($this->request->request->get('shippingMethodID')) {
                //update
                $shippingMethod = StoreShippingMethod::getByID($this->request->request->get('shippingMethodID'));
                if ($shippingMethod) {
                    $shippingMethodTypeMethod = $shippingMethod->getShippingMethodTypeMethod();
                    $shippingMethodTypeMethod->update($this->request->request->all());
                    $shippingMethod->update($this->request->request->get('methodName'), $this->request->request->get('methodEnabled'), $this->request->request->get('methodDetails'));
                    $this->flash('success', t('Shipping Method Updated'));

                    return Redirect::to('/dashboard/store/settings/shipping');
                } else {
                    return Redirect::to('/dashboard/store/settings/shipping');
                }
            } else {
                //first we send the data to the shipping method type.
                $shippingMethodType = StoreShippingMethodType::getByID($this->request->request->get('shippingMethodTypeID'));
                $shippingMethodTypeMethod = $shippingMethodType->addMethod($this->request->request->all());
                //make a shipping method that correlates with it.
                StoreShippingMethod::add($shippingMethodTypeMethod, $shippingMethodType, $this->request->request->get('methodName'), true, $this->request->request->get('methodDetails'));
                $this->flash('success', t('Shipping Method Created'));

                return Redirect::to('/dashboard/store/settings/shipping');
            }
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
        $shippingMethodType = StoreShippingMethodType::getByID($data['shippingMethodTypeID']);
        $e = $shippingMethodType->getMethodTypeController()->validate($data, $e);

        return $e;
    }
}
