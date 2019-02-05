<?php
namespace Concrete\Package\CommunityStore\Controller\SinglePage\Dashboard\Store\Multilingual;

use Concrete\Core\Page\Controller\DashboardPageController;

class Common extends DashboardPageController
{
    public function view()
    {
        $this->set('pageTitle', t('Common Translations'));
    }
}
