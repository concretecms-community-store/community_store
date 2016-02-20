<?php

namespace Concrete\Package\CommunityStore\Controller\SinglePage\Dashboard\Store\Settings\Shipping;

use \Concrete\Core\Page\Controller\DashboardPageController;
use View;
use Core;

use \Concrete\Package\CommunityStore\Src\CommunityStore\Shipping\Clerk\ClerkPackage as StoreClerkPackage;

class Clerk extends DashboardPageController
{
    
    public function view()
    {
        $packages = StoreClerkPackage::getPackages();
        $this->set('packages',$packages);
        
    }
    public function add()
    {
        $this->set('task',t("Add"));
    }
    public function edit($id)
    {
        $package = StoreClerkPackage::getByID($id);
        $this->set('reference',$package->getReference());
        $this->set('outerWidth',$package->getOuterWidth());
        $this->set('outerLength',$package->getOuterLength());
        $this->set('outerDepth',$package->getOuterDepth());
        $this->set('innerDepth',$package->getInnerDepth());
        $this->set('innerWidth',$package->getInnerWidth());
        $this->set('innerLength',$package->getInnerLength());
        $this->set('maxWeight',$package->getMaxWeight());
        $this->set('emptyWeight',$package->getEmptyWeight());
        $this->set('id',$id);
        $this->set('task',t("Update"));
    }
    public function save()
    {
        $errors = $this->validate($this->post());
        $this->error = null; //clear errors
        $this->error = $errors;
        if(!$errors->has())
        {
            if($this->post('id') > 0){
                StoreClerkPackage::add($this->post());
                $this->redirect('/dashboard/store/settings/shipping/clerk/success');
            } else {
                StoreClerkPackage::getByID($this->post('id'))->update($this->post());
                $this->redirect('/dashboard/store/settings/shipping/clerk/updated');
            }
        }
        if($this->post('id') > 0){
            $this->add();
        } else {
            $this->edit($this->post('id'));
        }
        
    }
    public function delete($id)
    {
        StoreClerkPackage::getByID($id)->delete();
        $this->redirect('/dashboard/store/settings/shipping/clerk/removed');
    }
    public function success(){
        $this->view();
        $this->set('success',t("Package Added"));
    }
    public function updated(){
        $this->view();
        $this->set('success',t("Package Updated"));
    }
    public function removed(){
        $this->view();
        $this->set('success',t("Package Removed"));
    }
    public function validate($data)
    {
        $e = Core::make('helper/validation/error');
        $numbers = new \Punic\Number;
        if(!$numbers->isInteger($data['outerWidth'])){
            $e->add(t('Outer Width must be a whole number'));
        }
        if(!$numbers->isInteger($data['outerLength'])){
            $e->add(t('Outer Length must be a whole number'));
        }
        if(!$numbers->isInteger($data['outerDepth'])){
            $e->add(t('Outer Depth must be a whole number'));
        }
        if(!$numbers->isInteger($data['innerWidth'])){
            $e->add(t('Inner Width must be a whole number'));
        }
        if(!$numbers->isInteger($data['innerLength'])){
            $e->add(t('Inner Length must be a whole number'));
        }
        if(!$numbers->isInteger($data['innerDepth'])){
            $e->add(t('Inner Depth must be a whole number'));
        }
        if(!$numbers->isInteger($data['maxWeight'])){
            $e->add(t('Max Weight must be a whole number'));
        }
        if(!$numbers->isInteger($data['emptyWeight'])){
            $e->add(t('Empty Weight must be a whole number'));
        }
        return $e;
    }
}
