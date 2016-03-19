<?php

namespace Concrete\Package\CommunityStore\Controller\SinglePage\Dashboard\Store\Orders;

use Concrete\Core\Page\Controller\DashboardPageController;
use View;
use Core;
use Package;
use Concrete\Core\Attribute\Key\Category as AttributeKeyCategory;
use Concrete\Core\Attribute\Type as AttributeType;
use GroupList;

use Concrete\Package\CommunityStore\Src\Attribute\Key\StoreOrderKey;

class Attributes extends DashboardPageController {
    
    public function view() {

        $this->set('category', AttributeKeyCategory::getByHandle('store_order'));
        $attrTypes = AttributeType::getList('store_order');
        $types = array();
        foreach($attrTypes as $at) {
            $types[$at->getAttributeTypeID()] = $at->getAttributeTypeName();
        }
        $attrList = StoreOrderKey::getList();
        $this->set('attrList',$attrList);
        $this->set('types', $types);
        $this->set('pageTitle', t('Order Attributes'));
    }
    
    public function update_attributes() {
        $uats = $_REQUEST['akID'];
        StoreProductKey::updateAttributesDisplayOrder($uats);
        exit;
    }
        
    public function removed() {
        $this->set('message', t('Attribute Deleted.'));
        $this->view();
    }
    
    public function success() {
        $this->set('message', t('Attribute Created.'));
        $this->view();
    }

    public function updated() {
        $this->set('message', t('Attribute Updated.'));
        $this->view();
    }
    
    public function delete($akID, $token = null){
        try {
            $ak = StoreOrderKey::getByID($akID);
                
            if(!($ak instanceof StoreOrderKey)) {
                throw new Exception(t('Invalid attribute ID.'));
            }

            $valt = Core::make('helper/validation/token');

            if (!$valt->validate('delete_attribute', $token)) {
                throw new Exception($valt->getErrorMessage());
            }
            
            $ak->delete();
            
            $this->redirect("/dashboard/store/orders/attributes", 'removed');
        } catch (Exception $e) {
            $this->set('error', $e);
        }
    }

    public function select_type() {
        $atID = $this->request('atID');
        $at = AttributeType::getByID($atID);
        $this->set('type', $at);
        $this->set('category', AttributeKeyCategory::getByHandle('store_order'));
        $this->set('pageTitle', t('Create Order Attribute'));

        $this->set('oaGroups', array());
        $this->set('groupList', $this->getGroupList());
        $this->requireAsset('select2');
    }
    
    public function add() {
        $this->select_type();
        $type = $this->get('type');
        $cnt = $type->getController();
        $e = $cnt->validateKey($this->post());
        if ($e->has()) {
            $this->set('error', $e);
        } else {
            $type = AttributeType::getByID($this->post('atID'));
            StoreOrderKey::add($type, $this->post(), Package::getByHandle('community_store'));
            $this->redirect('/dashboard/store/orders/attributes/', 'success');
        }
    }
    
    public function edit($akID = 0) {
        if ($this->post('akID')) {
            $akID = $this->post('akID');
        }
        $key = StoreOrderKey::getByID($akID);
        $type = $key->getAttributeType();
        $this->set('key', $key);
        $this->set('type', $type);
        $this->set('category', AttributeKeyCategory::getByHandle('store_order'));

        $this->set('oaGroups', $key->getAttributeGroups());
        $this->set('groupList', $this->getGroupList());
        $this->requireAsset('select2');
        
        if ($this->post()) {
            $cnt = $type->getController();
            $cnt->setAttributeKey($key);
            $e = $cnt->validateKey($this->post());
            if ($e->has()) {
                $this->set('error', $e);
            } else {
                $key->update($this->post());
                $this->redirect('/dashboard/store/orders/attributes', 'updated');
            }
        }
    }

    public function getGroupList()
    {
        $gl = new GroupList;
        foreach ($gl->getResults() as $group) {
            $groupList[$group->getGroupID()] = $group->getGroupName();
        }
        return (is_array($groupList))? $groupList : array();
    }
}
