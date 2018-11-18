<?php
namespace Concrete\Package\CommunityStore\Controller\SinglePage\Dashboard\Store;

use Concrete\Core\Page\Controller\DashboardPageController;
use Session;
use Concrete\Package\CommunityStore\Src\CommunityStore\Discount\DiscountRule as StoreDiscountRule;
use Concrete\Package\CommunityStore\Src\CommunityStore\Discount\DiscountCode as StoreDiscountCode;
use Concrete\Package\CommunityStore\Src\CommunityStore\Discount\DiscountRuleList as StoreDiscountRuleList;
use Concrete\Package\CommunityStore\Src\CommunityStore\Group\GroupList as StoreGroupList;
use GroupList;
use Concrete\Core\Search\Pagination\PaginationFactory;

class Discounts extends DashboardPageController
{
    public function view()
    {
        $discountRuleList = new StoreDiscountRuleList();
        $discountRuleList->setItemsPerPage(10);

        $factory = new PaginationFactory(\Request::getInstance());
        $paginator = $factory->createPaginationObject($discountRuleList);

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
    }

    public function delete()
    {
        if ($this->post() && $this->token->validate('community_store')) {
            $data = $this->post();
            $dr = StoreDiscountRule::getByID($data['drID']);

            if ($dr) {
                $dr->delete();
            }
            $this->flash('success', t('Discount Rule Deleted'));
            return \Redirect::to('/dashboard/store/discounts');
        }

        return \Redirect::to('/dashboard/store/discounts');
    }

    public function deletecode()
    {
        if ($this->post() && $this->token->validate('community_store')) {
            $data = $this->post();
            $dc = StoreDiscountCode::getByID($data['dcID']);

            if ($dc) {
                $ruleid = $dc->getDiscountRule()->getID();
                $dc->delete();
                return \Redirect::to('/dashboard/store/discounts/codes/' . $ruleid);
            }
        }

        $this->flash('success', t('Code Deleted'));
        return \Redirect::to('/dashboard/store/discounts');
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
        return \Redirect::to('/dashboard/store/discounts/codes/' . $drID);
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
                    return \Redirect::to('/dashboard/store/discounts');
                } else {
                    $discountrule = StoreDiscountRule::add($data);
                    if ('code' == $discountrule->getTrigger()) {
                        return \Redirect::to('/dashboard/store/discounts/codes/' . $discountrule->getID());
                    } else {
                        $this->flash('success', t('Discount Rule Added'));
                        return \Redirect::to('/dashboard/store/discounts');
                    }
                }
            } else {
                $this->error = $errors;
            }
            //if no errors
        } // if post
    }
}
