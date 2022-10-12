<?php
namespace Concrete\Package\CommunityStore\Controller\SinglePage\Dashboard\Store\Products;

use Concrete\Core\Routing\Redirect;
use Concrete\Core\Page\Controller\DashboardPageController;
use Concrete\Core\Support\Facade\DatabaseORM as dbORM;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductType\ProductType;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductType\ProductTypeList;

class Types extends DashboardPageController
{
    public function view()
    {
        $this->set('pageTitle', t('Product Types'));

        $typelist = ProductTypeList::getProductTypeList();

        $this->set("typelist", $typelist);
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
                $newtype = ProductType::add($this->request->request->get('ptName'), $this->request->request->get('ptDescription'));

                $this->flash('success', t('Product Type Added'));
                return Redirect::to('/dashboard/store/products/types');
            }
        }
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
                $type->update($this->request->request->get('ptName'), $this->request->request->get('ptDescription'));

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

    public function delete()
    {
        if ($this->token->validate('community_store')) {
            $data = $this->request->request->all();
            ProductType::getByID($data['ptID'])->delete();
            $this->flash('success', t('Product Type Deleted'));

            return Redirect::to('/dashboard/store/products/types');
        }
    }
}
