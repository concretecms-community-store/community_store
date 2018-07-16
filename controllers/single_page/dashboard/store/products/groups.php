<?php

namespace Concrete\Package\CommunityStore\Controller\SinglePage\Dashboard\Store\Products;

use \Concrete\Core\Page\Controller\DashboardPageController;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Group\Group as StoreGroup;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Group\GroupList as StoreGroupList;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductGroup as StoreProductGroup;


class Groups extends DashboardPageController
{
    public function view()
    {
        $this->set('pageTitle', t('Product Groups'));
        $grouplist = StoreGroupList::getGroupList();
        $this->set("grouplist",$grouplist);
        $this->requireAsset('css', 'communityStoreDashboard');
        $this->requireAsset('javascript', 'communityStoreFunctions');
    }

    public function add()
    {
        $this->requireAsset('css', 'communityStoreDashboard');
        $this->set('pageTitle', t('Add Product Group'));
        $this->set('group', new StoreGroup());
        $this->requireAsset('css', 'select2');
        $this->requireAsset('javascript', 'select2');

        if ($this->post()) {
            $errors = $this->validateGroup($this->post());
            $this->error = $errors;
            if (!$errors->has()) {
                $newproductgroup = StoreGroup::add($this->post('groupName'));

                $productids = $this->post('products');

                if (is_array($productids)) {
                    $productids = array_unique($productids);

                    foreach($productids as $productid) {
                        StoreProductGroup::add($productid, $newproductgroup->getID());
                    }
                }

                $this->flash('success', t('Product Group Added'));
                $this->redirect('/dashboard/store/products/groups/');
            }
        }
    }
    public function edit($gID)
    {
        $this->requireAsset('css', 'communityStoreDashboard');
        $this->set('pageTitle', t('Edit Product Group'));
        $this->requireAsset('css', 'select2');
        $this->requireAsset('javascript', 'select2');

        $group = StoreGroup::getByID($gID);

        if ($this->post()) {
            $this->error = null; //clear errors
            $errors = $this->validateGroup($this->post());
            $this->error = $errors;
            if (!$errors->has()) {
                $group->update($this->post('groupName'));

                StoreProductGroup::removeProductsForGroup($group);

                $productids = $this->post('products');

                if (is_array($productids)) {
                    $productids = array_unique($productids);

                    foreach($productids as $productid) {
                        StoreProductGroup::add($productid, $group->getID());
                    }
                }

                $this->flash('success', t('Product Group Edited'));
                $this->redirect('/dashboard/store/products/groups/');
            }
        }

        $this->set('group', $group);
    }
    public function validateGroup($args)
    {
        $e = \Core::make('helper/validation/error');

        if($args['groupName']==""){
            $e->add(t('Please enter a Group Name'));
        }
        if(strlen($args['groupName']) > 100){
            $e->add(t('A Group Name can not be more than 100 characters'));
        }
        return $e;
    }
    public function deletegroup($gID)
    {
        StoreGroup::getByID($gID)->delete();
    }
}
