<?php
namespace Concrete\Package\CommunityStore\Controller\SinglePage\Dashboard\Store\Settings;

use Concrete\Core\Routing\Redirect;
use Concrete\Core\Support\Facade\Config;
use Concrete\Core\Page\Controller\DashboardPageController;
use Concrete\Package\CommunityStore\Src\CommunityStore\Tax\Tax as StoreTax;
use Concrete\Package\CommunityStore\Src\CommunityStore\Tax\TaxRate as StoreTaxRate;
use Concrete\Package\CommunityStore\Src\CommunityStore\Tax\TaxClass as StoreTaxClass;

class Tax extends DashboardPageController
{
    public function view()
    {
        $this->set("taxRates", StoreTax::getTaxRates());
        $this->set("taxClasses", StoreTaxClass::getTaxClasses());
    }

    public function add()
    {
        $this->set('pageTitle', t("Add Tax Rate"));
        $this->set("task", t("Add"));
        $this->set("taxRate", new StoreTaxRate()); //shuts up errors when adding
        $this->loadFormAssets();
    }

    public function edit($trID)
    {
        $this->set('pageTitle', t("Edit Tax Rate"));
        $this->set("task", t("Update"));
        $this->set("taxRate", StoreTaxRate::getByID($trID));
        $this->loadFormAssets();
    }

    public function delete($trID)
    {
        StoreTaxRate::getByID($trID)->delete();
        $this->flash('success', t('Tax Rate Deleted'));

        return Redirect::to('/dashboard/store/settings/tax');
    }

    public function loadFormAssets()
    {
        $this->set("countries", $this->app->make('helper/lists/countries')->getCountries());
        $this->set("states", $this->app->make('helper/lists/states_provinces')->getStates());
        $this->requireAsset('javascript', 'communityStoreFunctions');
    }

    public function add_rate()
    {
        $data = $this->request->request->all();
        $errors = $this->validate($data);
        $this->error = null; //clear errors
        $this->error = $errors;
        if (!$errors->has()) {
            if ($this->request->request->get('taxRateID')) {
                //update
                StoreTaxRate::add($data);
                $this->flash('success', t('Tax Rate Updated'));

                return Redirect::to('/dashboard/store/settings/tax');
            } else {
                //first we send the data to the shipping method type.
                StoreTaxRate::add($data);
                $this->flash('success', t('Tax Rate Added'));

                return Redirect::to('/dashboard/store/settings/tax');
            }
        } else {
            if ($this->request->request->get('taxRateID')) {
                $this->edit($this->request->request->get('taxRateID'));
            } else {
                //first we send the data to the shipping method type.
                $this->add();
            }
        }
    }

    public function validate($data)
    {
        $this->error = null;
        $e = $this->app->make('helper/validation/error');

        if ("" == $data['taxLabel']) {
            $e->add(t("You need a label for this Tax Rate"));
        }
        if ("" != $data['taxRate']) {
            if (!is_numeric($data['taxRate'])) {
                $e->add(t("Tax Rate must be a number"));
            }
        } else {
            $e->add(t("You need to enter a tax rate"));
        }

        return $e;
    }

    public function add_class()
    {
        $this->set('task', t("Add"));
        $this->set('tc', new StoreTaxClass());
        $this->set('taxRates', StoreTax::getTaxRates());
        $this->requireAsset('select2');
    }

    public function edit_class($tcID)
    {
        $this->set('task', t("Update"));
        $this->set('tc', StoreTaxClass::getByID($tcID));
        $this->set('taxRates', StoreTax::getTaxRates());
        $this->requireAsset('select2');
    }

    public function save_class()
    {
        $data = $this->request->request->all();
        $errors = $this->validateClass($data);
        $this->error = null; //clear errors
        $this->error = $errors;
        if ($this->request->request->get('taxClassID')) {
            $this->edit_class($this->request->request->get('taxClassID'));
        } else {
            $this->add_class();
        }
        if (!$errors->has()) {
            if ($this->request->request->get('taxClassID')) {
                //update
                $taxClass = StoreTaxClass::getByID($this->request->request->get('taxClassID'));
                $taxClass->update($data);
                $this->flash('success', t('Tax Class Updated'));

                return Redirect::to('/dashboard/store/settings/tax');
            } else {
                //add.
                StoreTaxClass::add($data);
                $this->flash('success', t('Tax Class Added'));

                return Redirect::to('/dashboard/store/settings/tax');
            }
        }
    }

    public function validateClass($data)
    {
        $this->error = null;
        $e = $this->app->make('helper/validation/error');

        if ("" == $data['taxClassName']) {
            $e->add(t("You need a name for this Tax Class"));
        }
        if ("extract" == Config::get('community_store.calculation')) {
            $countries = [];

            foreach ($data['taxClassRates'] as $taxrateID) {
                $taxrate = StoreTaxRate::getByID($taxrateID);

                if (in_array($taxrate->getTaxCountry(), $countries)) {
                    $e->add(t("You can only have one tax rate per country with your current tax settings"));
                    break;
                } else {
                    $countries[] = $taxrate->getTaxCountry();
                }
            }
        }

        return $e;
    }

    public function delete_class($tcID)
    {
        StoreTaxClass::getByID($tcID)->delete();
        $this->flash('success', t('Tax Class Deleted'));

        return Redirect::to("/dashboard/store/settings/tax");
    }
}
