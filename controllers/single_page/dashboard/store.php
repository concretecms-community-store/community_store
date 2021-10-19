<?php
namespace Concrete\Package\CommunityStore\Controller\SinglePage\Dashboard;

use Concrete\Core\Page\Controller\DashboardPageController;
use Concrete\Core\Routing\Redirect;

class Store extends DashboardPageController
{
    public function view() {
        return Redirect::to('/dashboard/store/overview')->send();
    }
}
