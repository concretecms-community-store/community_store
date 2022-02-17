<?php
namespace Concrete\Package\CommunityStore\Controller\SinglePage\Dashboard\Store;

use Concrete\Core\Filesystem\ElementManager;
use Concrete\Core\Http\Request;
use Concrete\Core\Routing\Redirect;
use Concrete\Core\User\Group\GroupList;
use Concrete\Core\Support\Facade\Session;
use Concrete\Core\Search\Pagination\PaginationFactory;
use Concrete\Core\Page\Controller\DashboardPageController;
use Concrete\Package\CommunityStore\Src\CommunityStore\Discount\DiscountCode;
use Concrete\Package\CommunityStore\Src\CommunityStore\Discount\DiscountRule;
use Concrete\Package\CommunityStore\Src\CommunityStore\Discount\DiscountRuleList;
use Concrete\Package\CommunityStore\Src\CommunityStore\Group\GroupList as StoreGroupList;

class Discounts extends DashboardPageController
{
    public function view()
    {
        $discountRuleList = new DiscountRuleList();
        $discountRuleList->setItemsPerPage(10);

        $keywords = trim($this->request->query->get('keywords'));

        if ($keywords) {
            $discountRuleList->setSearch($keywords);
            Session::set('communitystore.dashboard.discounts.keywords', $keywords);
        } else {
            Session::remove('communitystore.dashboard.discounts.keywords');
        }

        $factory = new PaginationFactory($this->app->make(Request::class));
        $paginator = $factory->createPaginationObject($discountRuleList);

        $pagination = $paginator->renderDefaultView();
        $this->set('discounts', $paginator->getCurrentPageResults());
        $this->set('pagination', $pagination);
        $this->set('paginator', $paginator);

        $this->set('pageTitle', t('Discount Rules'));

        $headerSearch = $this->getHeaderSearch();
        $this->set('headerSearch', $headerSearch);
    }

    public function add()
    {
        $this->requireAsset('selectize');
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
        $usergroups = $gl->getResults();

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
        $this->requireAsset('selectize');
        $discountRule = DiscountRule::getByID($drID);

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
        $usergroups = $gl->getResults();

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
        $discountRule = DiscountRule::getByID($drID);

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
        if ($this->request->request->all() && $this->token->validate('community_store')) {
            $data = $this->request->request->all();
            $dr = DiscountRule::getByID($data['drID']);

            if ($dr) {
                $dr->delete();
                $this->flash('success', t('Discount Rule Deleted'));
                $keywordsSearch = Session::get('communitystore.dashboard.discounts.keywords');
                return Redirect::to('/dashboard/store/discounts' . ($keywordsSearch ? '/?keywords='.urlencode($keywordsSearch) : ''));
            }
        }

        return Redirect::to('/dashboard/store/discounts');
    }

    public function deletecode()
    {
        if ($this->request->request->all() && $this->token->validate('community_store')) {
            $data = $this->request->request->all();
            $dc = DiscountCode::getByID($data['dcID']);

            if ($dc) {
                $ruleid = $dc->getDiscountRule()->getID();
                $dc->delete();
                $this->flash('success', t('Code Deleted'));
                return Redirect::to('/dashboard/store/discounts/codes/' . $ruleid);
            }
        }

        return Redirect::to('/dashboard/store/discounts');
    }

    public function addcodes($drID)
    {
        if ($this->request->request->all() && $this->token->validate('community_store')) {
            $data = $this->request->request->all();

            $codes = trim($data['codes']);

            $discountRule = DiscountRule::getByID($drID);
            if ($codes && $discountRule) {
                $codes = str_replace(",", "\n", $codes);
                $codes = explode("\n", $codes);

                $failed = [];
                $successcount = 0;

                foreach ($codes as $code) {
                    $code = trim($code);

                    if ($code) {
                        if (!DiscountCode::add($discountRule, $code)) {
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

        return Redirect::to('/dashboard/store/discounts/codes/' . $drID);
    }

    public function save()
    {
        if ($this->request->request->all() && $this->token->validate('community_store')) {
            $data = $this->request->request->all();

            if ($data['drID']) {
                $this->edit($data['drID']);
            }

            $errors = DiscountCode::validate($data);
            if (!$errors->has()) {
                if ($data['drID']) {
                    DiscountRule::edit($data['drID'], $data);
                    $this->flash('success', t('Discount Rule Updated'));

                    return Redirect::to('/dashboard/store/discounts/edit/' . $data['drID']);
                } else {
                    $discountrule = DiscountRule::add($data);
                    if ('code' == $discountrule->getTrigger()) {
                        return Redirect::to('/dashboard/store/discounts/codes/' . $discountrule->getID());
                    } else {
                        $this->flash('success', t('Discount Rule Added'));
                        Session::remove('communitystore.dashboard.discounts.keywords');
                        return Redirect::to('/dashboard/store/discounts/edit/' . $discountrule->getID());
                    }
                }
            } else {
                $this->error = $errors;
            }
            //if no errors
        } // if post
    }

    protected function getHeaderSearch()
    {
        if (!isset($this->headerSearch)) {
            $this->headerSearch = $this->app->make(ElementManager::class)->get('discounts/search', 'community_store');
        }
        return $this->headerSearch;
    }
}
