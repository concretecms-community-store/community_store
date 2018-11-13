<?php
namespace Concrete\Package\CommunityStore\Controller\SinglePage\Dashboard\Store;

use Concrete\Core\Page\Controller\DashboardPageController;

class Reports extends DashboardPageController
{
    public function view()
    {
        return \Redirect::to('/dashboard/store/reports/sales');
    }
}
