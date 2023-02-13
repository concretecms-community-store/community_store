<?php
namespace Concrete\Package\CommunityStore\Controller\SinglePage\Dashboard\Store\Products;

use Concrete\Core\Attribute\Key\Key as AttributeKey;
use Concrete\Core\Page\Type\Composer\Control\CollectionAttributeControl;
use Concrete\Core\Routing\Redirect;
use Concrete\Core\Page\Controller\DashboardPageController;
use Concrete\Core\Support\Facade\Application;
use Concrete\Core\Support\Facade\DatabaseORM as dbORM;
use Concrete\Package\CommunityStore\Attribute\ProductKey;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductList;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductType\ProductType;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductType\ProductTypeLayoutSet;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductType\ProductTypeLayoutSetControl;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductType\ProductTypeList;

class Types extends DashboardPageController
{
    public function view()
    {
        $this->set('pageTitle', t('Product Types'));

        $typeList = ProductTypeList::getProductTypeList();

        $this->set("typeList", $typeList);
        $this->requireAsset('css', 'communityStoreDashboard');
        $this->requireAsset('javascript', 'communityStoreFunctions');
    }


    public function add()
    {
        $this->requireAsset('css', 'communityStoreDashboard');
        $this->set('pageTitle', t('Add Product Type'));
        $this->set('type', new ProductType());

        if ($this->request->request->all() && $this->token->validate('community_store')) {
            $errors = $this->validateType($this->request->request->all());
            $this->error = $errors;
            if (!$errors->has()) {
                $newtype = ProductType::add($this->request->request->get('ptName'),$this->request->request->get('ptHandle'), $this->request->request->get('ptDescription'));

                $this->flash('success', t('Product Type Added'));
                return Redirect::to('/dashboard/store/products/types');
            }
        }
    }

    public function attributes($ptID)
    {
        $type = ProductType::getByID($ptID);
        $this->set('pageTitle', t('Manage Attributes for %s', $type->getTypeName()));


        if (!$type) {
            return Redirect::to('/dashboard/store/products/types');
        }

        $this->set('type', $type);
        $this->render('/dashboard/store/products/types/attributes');


        $keys = AttributeKey::getAttributeKeyList('store_product');

        $this->set('keys', $keys);

    }

    public function edit($ptID)
    {
        $this->requireAsset('css', 'communityStoreDashboard');
        $this->set('pageTitle', t('Edit Product Type'));

        $type = ProductType::getByID($ptID);

        if (!$type) {
            return Redirect::to('/dashboard/store/products/types');
        }

        if ($this->request->request->all() && $this->token->validate('community_store')) {
            $this->error = null; //clear errors
            $errors = $this->validateType($this->request->request->all());
            $this->error = $errors;
            if (!$errors->has()) {
                $type->update($this->request->request->get('ptName'), $this->request->request->get('ptHandle'), $this->request->request->get('ptDescription'));

                $this->flash('success', t('Product Type Edited'));

                return Redirect::to('/dashboard/store/products/types');
            }
        }

        $this->set('type', $type);
    }

    public function validateType($args)
    {
        $e = $this->app->make('helper/validation/error');

        if ("" == $args['ptName']) {
            $e->add(t('Please enter a Product Type Name'));
        }
        if (strlen($args['ptName']) > 255) {
            $e->add(t('A Product Type Name can not be more than 255 characters'));
        }

        return $e;
    }


    public function add_set($ptID = false)
    {
        $sec = $this->app->make('helper/security');

        $name = $sec->sanitizeString($this->post('ptLayoutSetName'));
        $description = $sec->sanitizeString($this->post('ptLayoutSetDescription'));
        if ($this->token->validate('add_set')) {

            $pt = ProductType::getByID($ptID);

            if ($pt) {
                ProductTypeLayoutSet::add($pt, $name, $description);
            }

            $this->flash('success', t('Product Set Created'));
            return Redirect::to('/dashboard/store/products/types/attributes/' . $ptID);
        }
    }

    public function add_control() {
        $setID = $this->post('ptlsID');
        $akID = $this->post('akID');
        $hidden = $this->post('hidden');

        $set = ProductTypeLayoutSet::getByID($setID);

        if ($this->token->validate('add_control')) {
            if ($set) {
                $id = $set->getProductType()->getTypeID();

                $attrKey = ProductKey::getByID($akID);

                $control = new ProductTypeLayoutSetControl();
                $control->setAttributeKey($attrKey);
                $control->setLayoutSet($set);
                $control->setDisplayOrder(0);
                $control->setHidden($hidden);
                $control->save();

                $this->flash('success', t('Attribute Added'));
                return Redirect::to('/dashboard/store/products/types/attributes/' . $id);
            }
        }
    }

    public function edit_control() {
        $ptlscID = $this->post('ptlscID');
        $customLabel = $this->post('customLabel');
        $hidden = $this->post('hidden');

        $control = ProductTypeLayoutSetControl::getByID($ptlscID);

        if ($this->token->validate('edit_control')) {
            if ($control) {

                $id = $control->getLayoutSet()->getProductType()->getTypeID();

                $control->setCustomLabel($customLabel);
                $control->setHidden($hidden);
                $control->save();

                $this->flash('success', t('Attribute Updated'));
                return Redirect::to('/dashboard/store/products/types/attributes/' . $id);
            }
        }
    }

    public function update_set($setID = false)
    {
        $sec = $this->app->make('helper/security');

        $name = $sec->sanitizeString($this->post('ptLayoutSetName'));
        $description = $sec->sanitizeString($this->post('ptLayoutSetDescription'));
        if ($this->token->validate('update_set')) {

            $set = ProductTypeLayoutSet::getByID($setID);

            if ($set) {
                $set->update($name, $description);


                $this->flash('success', t('Product Set Updated'));
                return Redirect::to('/dashboard/store/products/types/attributes/' . $set->getProductType()->getTypeID());
            }
        }
    }


    public function delete_set($setID = false)
    {
        if ($this->token->validate('delete_set')) {

            $set = ProductTypeLayoutSet::getByID($setID);

            if ($set) {
                $id = $set->getProductType()->getTypeID();
                $set->delete();

                $this->flash('success', t('Product Set Deleted'));
                return Redirect::to('/dashboard/store/products/types/attributes/' . $id);
            }
        }
    }


    public function delete_set_control($controlID = false)
    {
        if ($this->token->validate('delete_set_control')) {

            $control = ProductTypeLayoutSetControl::getByID($controlID);

            if ($control) {

                $id = $control->getLayoutSet()->getProductType()->getTypeID();
                $control->delete();

                $this->flash('success', t('Attribute Removed From Set'));
                return Redirect::to('/dashboard/store/products/types/attributes/' . $id);
            }

        }
    }

    public function delete()
    {
        if ($this->token->validate('community_store')) {
            $data = $this->request->request->all();

            $productType = ProductType::getByID($data['ptID']);

            if ($productType) {

                $app = Application::getFacadeApplication();
                $db = $app->make('database')->connection();
                $sql = 'Update CommunityStoreProducts set pType = null where pType = ?';
                $db->query($sql, [$data['ptID']]);

                $productType->delete();
                $this->flash('success', t('Product Type Deleted'));
            }

            return Redirect::to('/dashboard/store/products/types');
        }

    }

    public function update_set_display_order() {

        if ($this->token->validate('update_set_display_order')) {
            $setIDs = $this->post('ptLayoutSetID');

            $count = 1;
            foreach($setIDs as $setID) {
                $set = ProductTypeLayoutSet::getByID($setID);
                $set->setLayoutSetDisplayOrder($count);
                $set->save();
                $count++;
            }
        }

        exit();
    }

    public function update_set_control_display_order() {

        if ($this->token->validate('update_set_control_display_order')) {
            $controlIDs = $this->post('ptLayoutSetControlID');

            $count = 1;
            foreach($controlIDs as $controlID) {
                $control = ProductTypeLayoutSetControl::getByID($controlID);

                if ($control) {
                    $control->setDisplayOrder($count);
                    $control->save();
                    $count++;
                }
            }
        }

        exit();
    }
}
