<?php
namespace Concrete\Package\CommunityStore\Controller\SinglePage\Dashboard\Store\Products;

use Concrete\Core\Routing\Redirect;
use Concrete\Core\Page\Controller\DashboardPageController;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductGroup;
use Concrete\Package\CommunityStore\Src\CommunityStore\Group\Group as StoreGroup;
use Concrete\Package\CommunityStore\Src\CommunityStore\Group\GroupList as StoreGroupList;

class Groups extends DashboardPageController
{
    public function view()
    {
        $this->set('pageTitle', t('Product Groups'));
        $grouplist = StoreGroupList::getGroupList();
        $this->set("grouplist", $grouplist);
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

        if ($this->request->request->all() && $this->token->validate('community_store')) {
            $errors = $this->validateGroup($this->request->request->all());
            $this->error = $errors;
            if (!$errors->has()) {
                $newproductgroup = StoreGroup::add($this->request->request->get('groupName'));

                $productids = $this->request->request->get('products');

                if (is_array($productids)) {
                    $productids = array_unique($productids);

                    foreach ($productids as $productid) {
                        ProductGroup::add($productid, $newproductgroup->getID());
                    }
                }

                $this->flash('success', t('Product Group Added'));

                return Redirect::to('/dashboard/store/products/groups');
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

        if (!$group) {
            return Redirect::to('/dashboard/store/products/groups');
        }

        if ($this->request->request->all() && $this->token->validate('community_store')) {
            $this->error = null; //clear errors
            $errors = $this->validateGroup($this->request->request->all());
            $this->error = $errors;
            if (!$errors->has()) {
                $group->update($this->request->request->get('groupName'));

                ProductGroup::removeProductsForGroup($group);

                $productids = $this->request->request->get('products');

                if (is_array($productids)) {
                    $productids = array_unique($productids);

                    foreach ($productids as $productid) {
                        ProductGroup::add($productid, $group->getID());
                    }
                }

                $this->flash('success', t('Product Group Edited'));

                return Redirect::to('/dashboard/store/products/groups');
            }
        }

        $this->set('group', $group);
    }

    public function validateGroup($args)
    {
        $e = $this->app->make('helper/validation/error');

        if ("" == $args['groupName']) {
            $e->add(t('Please enter a Group Name'));
        }
        if (strlen($args['groupName']) > 100) {
            $e->add(t('A Group Name can not be more than 100 characters'));
        }

        return $e;
    }

    public function delete()
    {
        if ($this->token->validate('community_store')) {
            $data = $this->request->request->all();
            StoreGroup::getByID($data['grID'])->delete();
            $this->flash('success', t('Product Group Deleted'));

            return Redirect::to('/dashboard/store/products/groups');
        }
    }
}
