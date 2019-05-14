<?php
namespace Concrete\Package\CommunityStore\Controller\SinglePage\Dashboard\Store;

use Concrete\Core\Page\Controller\DashboardPageController;
use Session;
use Concrete\Package\CommunityStore\Src\CommunityStore\Discount\DiscountRule as StoreDiscountRule;
use Concrete\Package\CommunityStore\Src\CommunityStore\Discount\DiscountCode as StoreDiscountCode;
use Concrete\Package\CommunityStore\Src\CommunityStore\Discount\DiscountRuleList as StoreDiscountRuleList;
use Concrete\Package\CommunityStore\Src\CommunityStore\Group\GroupList as StoreGroupList;
use GroupList;

class Discounts extends DashboardPageController
{
    public function view()
    {
        $discountRuleList = new StoreDiscountRuleList();

        $keywords = trim($this->request->query->get('keywords'));

        if ($keywords) {
            $discountRuleList->setSearch($keywords);
            Session::set('communitystore.dashboard.discounts.keywords', $keywords);
        } else {
            Session::remove('communitystore.dashboard.discounts.keywords');
        }

        $discountRuleList->setItemsPerPage(10);

        $paginator = $discountRuleList->getPagination();
        $pagination = $paginator->renderDefaultView();
        $this->set('discounts', $paginator->getCurrentPageResults());
        $this->set('pagination', $pagination);
        $this->set('paginator', $paginator);

        $this->set('pageTitle', t('Discount Rules'));
    }

    public function add()
    {
        $this->requireAsset('css', 'select2');
        $this->requireAsset('javascript', 'select2');
        $this->set('pageTitle', t('Add Discount Rule'));

        $grouplist = StoreGroupList::getGroupList();
        $this->set("grouplist", $grouplist);
        foreach ($grouplist as $productgroup) {
            $productgroups[$productgroup->getGroupID()] = $productgroup->getGroupName();
        }
        $this->set("productgroups", $productgroups);

        $gl = new GroupList();
        $gl->setItemsPerPage(1000);
        $gl->filterByAssignable();
        $usergroups = $gl->get();

        $usergrouparray = [];

        foreach ($usergroups as $ug) {
            if ('Administrators' != $ug->gName) {
                $usergrouparray[$ug->gID] = $ug->gName;
            }
        }

        $this->set('keywordsSearch', Session::get('communitystore.dashboard.discounts.keywords'));
        $this->set('usergroups', $usergrouparray);
        $this->set('selectedproductgroups', []);
        $this->set('selectedusergroups', []);
    }

    public function edit($drID)
    {
        $this->requireAsset('css', 'select2');
        $this->requireAsset('javascript', 'select2');
        $discountRule = StoreDiscountRule::getByID($drID);

        $this->set('discountRule', $discountRule);
        $this->set('pageTitle', t('Edit Discount Rule'));

        $grouplist = StoreGroupList::getGroupList();
        $this->set("grouplist", $grouplist);
        foreach ($grouplist as $productgroup) {
            $productgroups[$productgroup->getGroupID()] = $productgroup->getGroupName();
        }
        $this->set("productgroups", $productgroups);

        $gl = new GroupList();
        $gl->setItemsPerPage(1000);
        $gl->filterByAssignable();
        $usergroups = $gl->get();

        $usergrouparray = [];

        foreach ($usergroups as $ug) {
            if ('Administrators' != $ug->gName) {
                $usergrouparray[$ug->gID] = $ug->gName;
            }
        }

        $this->set('keywordsSearch', Session::get('communitystore.dashboard.discounts.keywords'));
        $this->set('selectedproductgroups', $discountRule->getProductGroups());
        $this->set('selectedusergroups', $discountRule->getUserGroups());
        $this->set('usergroups', $usergrouparray);
    }

    public function codes($drID)
    {
        $discountRule = StoreDiscountRule::getByID($drID);

        $this->set('discountRule', $discountRule);
        if ($discountRule) {
            $this->set('codes', $discountRule->getCodes());
        }

        $this->set('pageTitle', t('Codes for discount rule') . ': ' . $discountRule->getName());

        if (is_array(Session::get('communitystore.failedcodes'))) {
            $this->set('failedcodes', Session::get('communitystore.failedcodes'));
            Session::set('communitystore.failedcodes', null);
        }

        $this->set('keywordsSearch', Session::get('communitystore.dashboard.discounts.keywords'));
    }

    public function delete()
    {
        if ($this->post() && $this->token->validate('community_store')) {
            $data = $this->post();
            $dr = StoreDiscountRule::getByID($data['drID']);

            if ($dr) {
                $dr->delete();
                $this->flash('success', t('Discount Rule Deleted'));
                $keywordsSearch = Session::get('communitystore.dashboard.discounts.keywords');
                $this->redirect('/dashboard/store/discounts' . ($keywordsSearch ? '/?keywords='.urlencode($keywordsSearch) : ''));
            }
        }

        $this->redirect('/dashboard/store/discounts');
    }

    public function deletecode()
    {
        if ($this->post() && $this->token->validate('community_store')) {
            $data = $this->post();
            $dc = StoreDiscountCode::getByID($data['dcID']);

            if ($dc) {
                $ruleid = $dc->getDiscountRule()->getID();
                $dc->delete();
                $this->flash('success', t('Code Deleted'));
                $this->redirect('/dashboard/store/discounts/codes/' . $ruleid);
            }
        }

        $this->redirect('/dashboard/store/discounts');
    }

    public function addcodes($drID)
    {
        if ($this->post() && $this->token->validate('community_store')) {
            $data = $this->post();

            $codes = trim($data['codes']);

            $discountRule = StoreDiscountRule::getByID($drID);
            if ($codes && $discountRule) {
                $codes = str_replace(",", "\n", $codes);
                $codes = explode("\n", $codes);

                $failed = [];
                $successcount = 0;

                foreach ($codes as $code) {
                    $code = trim($code);

                    if ($code) {
                        if (!StoreDiscountCode::add($discountRule, $code)) {
                            $failed[] = $code;
                        } else {
                            ++$successcount;
                        }
                    }
                }
            }
        }

        if (!empty($failed)) {
            Session::set('communitystore.failedcodes', $failed);
        }

        $this->flash('success', $successcount . ' ' . (1 == $successcount ? t('Code Added') : t('Codes Added')));
        $this->redirect('/dashboard/store/discounts/codes/' . $drID);
    }

    public function save()
    {
        if ($this->post() && $this->token->validate('community_store')) {
            $data = $this->post();

            if ($data['drID']) {
                $this->edit($data['drID']);
            }

            $errors = StoreDiscountCode::validate($data);
            if (!$errors->has()) {
                if ($data['drID']) {
                    StoreDiscountRule::edit($data['drID'], $data);
                    $this->flash('success', t('Discount Rule Updated'));

                    $this->redirect('/dashboard/store/discounts/edit/' . $data['drID']);
                } else {
                    $discountrule = StoreDiscountRule::add($data);
                    if ('code' == $discountrule->getTrigger()) {
                        $this->redirect('/dashboard/store/discounts/codes/' . $discountrule->getID());
                    } else {
                        $this->flash('success', t('Discount Rule Added'));
                        Session::remove('communitystore.dashboard.discounts.keywords');

                        $this->redirect('/dashboard/store/discounts/edit/' .$discountrule->getID());
                    }
                }
            } else {
                $this->error = $errors;
            }
            //if no errors
        } // if post
    }
}
