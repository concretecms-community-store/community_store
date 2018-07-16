<?php

namespace Concrete\Package\CommunityStore\Controller\SinglePage\Dashboard\Store\Products;

use \Concrete\Core\Page\Controller\DashboardPageController;
use View;
use Core;
use \Concrete\Core\Attribute\Key\Category as AttributeKeyCategory;
use \Concrete\Core\Attribute\Type as AttributeType;

use \Concrete\Package\CommunityStore\Src\Attribute\Key\StoreProductKey;

class Attributes extends DashboardPageController
{
    
    public function view() {
        $this->set('category', AttributeKeyCategory::getByHandle('store_product'));
        $attrTypes = AttributeType::getList('store_product');
        $types = array();
        foreach($attrTypes as $at) {
            $types[$at->getAttributeTypeID()] = $at->getAttributeTypeName();
        }
        $attrList = StoreProductKey::getList();
        $this->set('attrList',$attrList);
        $this->set('types', $types);
        $this->set('pageTitle', t('Product Attributes'));
    }
    
    public function update_attributes() {
        $uats = $_REQUEST['akID'];
        StoreProductKey::updateAttributesDisplayOrder($uats);
        exit;
    }

    public function delete($akID, $token = null){
        try {
            $ak = StoreProductKey::getByID($akID);
                
            if(!($ak instanceof StoreProductKey)) {
                throw new Exception(t('Invalid attribute ID.'));
            }

            $valt = Core::make('helper/validation/token');

            if (!$valt->validate('delete_attribute', $token)) {
                throw new Exception($valt->getErrorMessage());
            }
            
            $ak->delete();

            $this->flash('success', t('Attribute Deleted'));
            $this->redirect("/dashboard/store/products/attributes");
        } catch (Exception $e) {
            $this->set('error', $e);
        }
    }

    public function select_type() {
        $atID = $this->request('atID');
        $at = AttributeType::getByID($atID);
        $this->set('type', $at);
        $this->set('category', AttributeKeyCategory::getByHandle('store_product'));
        $this->set('pageTitle', t('Create Product Attribute'));
    }
    
    public function add() {
        $pkg = \Package::getByHandle('community_store');
        $this->select_type();
        $type = $this->get('type');
        $cnt = $type->getController();
        $e = $cnt->validateKey($this->post());
        if ($e->has()) {
            $this->set('error', $e);
        } else {
            $type = AttributeType::getByID($this->post('atID'), $pkg, 'store_product');
            StoreProductKey::add('store_product',$type, $this->post(),$pkg);
            $this->flash('success', t('Attribute Created'));
            $this->redirect('/dashboard/store/products/attributes');
        }
    }
    
    public function edit($akID = 0) {
        if ($this->post('akID')) {
            $akID = $this->post('akID');
        }
        $key = StoreProductKey::getByID($akID);
        $type = $key->getAttributeType();
        $this->set('key', $key);
        $this->set('type', $type);
        $this->set('category', AttributeKeyCategory::getByHandle('store_product'));
        
        if ($this->post()) {
            $cnt = $type->getController();
            $cnt->setAttributeKey($key);
            $e = $cnt->validateKey($this->post());
            if ($e->has()) {
                $this->set('error', $e);
            } else {
                $key->update($this->post());
                $this->flash('success', t('Attribute Updated'));
                $this->redirect('/dashboard/store/products/attributes');
            }
        }
    }
}
