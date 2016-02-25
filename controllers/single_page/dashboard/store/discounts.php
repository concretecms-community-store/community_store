<?php

namespace Concrete\Package\CommunityStore\Controller\SinglePage\Dashboard\Store;

use \Concrete\Core\Page\Controller\DashboardPageController;
use Session;

use \Concrete\Package\CommunityStore\Src\CommunityStore\Discount\DiscountRule as StoreDiscountRule;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Discount\DiscountCode as StoreDiscountCode;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Discount\DiscountRuleList as StoreDiscountRuleList;

class Discounts extends DashboardPageController
{
    public function view() {
        $discountRuleList = new StoreDiscountRuleList();
        $discountRuleList->setItemsPerPage(10);

        $paginator = $discountRuleList->getPagination();
        $pagination = $paginator->renderDefaultView();
        $this->set('discounts',$paginator->getCurrentPageResults());
        $this->set('pagination',$pagination);
        $this->set('paginator', $paginator);

        $this->set('pageTitle', t('Discount Rules'));
    }

    public function add() {
        $this->set('pageTitle', t('Add Discount Rule'));
    }

    public function edit($drID) {

        $discountRule = StoreDiscountRule::getByID($drID);

        $this->set('d', $discountRule);
        $this->set('pageTitle', t('Edit Discount Rule'));
    }

    public function codes($drID, $successcount = null) {
        $discountRule = StoreDiscountRule::getByID($drID);

        $this->set('discountRule', $discountRule);
        if ($discountRule) {
            $this->set('codes', $discountRule->getCodes());
        }

        $this->set('pageTitle', t('Codes for discount rule') . ': ' . $discountRule->getName());

        if (!is_null($successcount)) {
            $this->set('successCount', $successcount);
        }

        if (is_array(Session::get('communitystore.failedcodes'))) {
            $this->set('failedcodes', Session::get('communitystore.failedcodes'));
            Session::set('communitystore.failedcodes',null);
        }
    }

    public function delete() {
        if ($this->post()) {
            $data = $this->post();
            $dr = StoreDiscountRule::getByID($data['drID']);

            if ($dr) {
                $dr->delete();
            }
            $this->redirect('/dashboard/store/discounts/', 'deleted');
        }

        $this->redirect('/dashboard/store/discounts/');
    }

    public function deletecode() {
        if ($this->post()) {
            $data = $this->post();
            $dc = StoreDiscountCode::getByID($data['dcID']);

            if ($dc) {
                $ruleid = $dc->getDiscountRule()->getID();
                $dc->delete();
                $this->redirect('/dashboard/store/discounts/codes/'. $ruleid);
            }
        }

        $this->redirect('/dashboard/store/discounts/');
    }

    public function addcodes($drID) {
        if ($this->post()) {
            $data = $this->post();

            $codes = trim($data['codes']);

            $discountRule = StoreDiscountRule::getByID($drID);
            if ($codes && $discountRule) {
                $codes = str_replace(",", "\n", $codes);
                $codes = explode("\n", $codes);

                $failed = array();
                $successcount = 0;

                foreach ($codes as $code) {
                    $code = trim($code);

                    if ($code) {
                        if (!StoreDiscountCode::add($discountRule, $code)) {
                            $failed[] = $code;
                        } else {
                            $successcount++;
                        }
                    }
                }
            }
        }

        if (!empty($failed)) {
            Session::set('communitystore.failedcodes', $failed);
        }

        $this->redirect('/dashboard/store/discounts/codes/' . $drID, $successcount );
    }

    public function save()
    {
        if ($this->post() ) {
            $data = $this->post();

            if($data['drID']){
                $this->edit($data['drID']);
            }

            $errors = StoreDiscountCode::validate($data);
            if (!$errors->has()) {



                if($data['drID']){
                    StoreDiscountRule::edit($data['drID'], $data);
                    $this->redirect('/dashboard/store/discounts/', 'updated');
                } else {
                    $discountrule = StoreDiscountRule::add($data);
                    if ( $discountrule->getTrigger() == 'code') {
                        $this->redirect('/dashboard/store/discounts/codes/'. $discountrule->getID());
                    } else {
                        $this->redirect('/dashboard/store/discounts/', 'success');
                    }
                }
           } else {
               $this->error = $errors;
           }
           //if no errors
        } // if post
    }

    public function success() {
        $this->set('success',t("Discount Rule Added"));
        $this->view();
    }

    public function updated() {
        $this->set('success',t("Discount Rule Updated"));
        $this->view();
    }

    public function deleted() {
        $this->set('success',t("Discount Rule Deleted"));
        $this->view();
    }

}
