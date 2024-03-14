<?php

namespace Concrete\Package\CommunityStore\Controller\Dialog;

defined('C5_EXECUTE') or die('Access Denied.');

use Concrete\Core\Error\UserMessageException;
use Concrete\Core\Page\Page;
use Concrete\Core\Permission\Checker;
use Concrete\Controller\Backend\UserInterface;
use Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\OnlineVATChecker;

class ViesStatus extends UserInterface
{
    protected $viewPath = '/dialog/vies_status';

    public function view()
    {
        $checker = $this->app->make(OnlineVATChecker::class);
        try {
            $vowAvailable = null;
            $this->set('countryStatuses', $checker->getCountryStatuses($vowAvailable));
            $this->set('vowAvailable', $vowAvailable);
            $this->set('viesError', '');
        } catch (UserMessageException $x) {
            $this->set('countryStatuses', []);
            $this->set('vowAvailable', null);
            $this->set('viesError', $x->getMessage());
        }
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Controller\Backend\UserInterface::canAccess()
     */
    protected function canAccess()
    {
        $page = Page::getByPath('/dashboard/store/settings');
        if (!$page || $page->isError()) {
            return false;
        }

        $c = new Checker($page);
        return $c->canRead();
    }
}
