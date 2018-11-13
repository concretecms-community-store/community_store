<?php
namespace Concrete\Package\CommunityStore\Controller\SinglePage\Dashboard\Store;

use Concrete\Core\Page\Controller\DashboardPageController;

class Reports extends DashboardPageController
{
    public function view()
    {
        \Redirect::to('/dashboard/store/reports/sales');
    }
}
