<?php

namespace Concrete\Package\CommunityStore\Controller\SinglePage\Dashboard\Store\Products;

use \Concrete\Core\Page\Controller\DashboardPageController;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Group\Group as StoreGroup;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Group\GroupList as StoreGroupList;


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
    public function groupadded()
    {
        $this->set('success',"Product Group Created");
        $this->view();
    }
    public function addgroup()
    {
        $this->view();
        $this->error = null; //clear errors
        $errors = $this->validateGroup($this->post());
        $this->error = $errors;
        if (!$errors->has()) {
            StoreGroup::add($this->post('groupName'));
            $this->redirect('/dashboard/store/products/groups/', 'groupadded');
        }
    }
    public function editgroup($gID)
    {
        StoreGroup::getByID($gID)->update($this->post('gName'));
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
