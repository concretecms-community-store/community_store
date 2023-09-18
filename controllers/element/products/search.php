<?php
namespace Concrete\Package\CommunityStore\Controller\Element\Products;

use Concrete\Core\Entity\Search\Query;
use Concrete\Core\Controller\ElementController;

class Search extends ElementController
{

    /**
     * This is where the header search bar in the page should point. This search bar allows keyword searching in
     * different contexts. Valid options are `view` and `folder`.
     *
     * @var string
     */
    protected $headerSearchAction;
    protected $groupList;
    protected $gID;

    /**
     * @var Query
     */
    protected $query;

    public function __construct($groupList, $gID)
    {
        $this->groupList = $groupList;
        $this->gID = $gID;
    }

    public function getElement()
    {
        return 'products/search';
    }

    /**
     * @param Query $query
     */
    public function setQuery(Query $query = null): void
    {
        $this->query = $query;
    }

    /**
     * @param string $headerSearchAction
     */
    public function setHeaderSearchAction(string $headerSearchAction): void
    {
        $this->headerSearchAction = $headerSearchAction;
    }

    public function view()
    {
        $this->set('form', $this->app->make('helper/form'));
        $this->set('gID', $this->gID);
        $this->set('groupList', $this->groupList);
        $this->set('token', $this->app->make('token'));
        $request = isset($this->request) ? $this->request : $this->app->request;
        $this->set('keywords', $request->request('keywords'));
        $featured = $request->request('featured');
        if ($featured) {
            $featured = true;
        } elseif ($featured !== null && $featured !== '') {
            $featured = false;
        } else {
            $featured = null;
        }
        $this->set('featured', $featured);
        if (isset($this->headerSearchAction)) {
            $this->set('headerSearchAction', $this->headerSearchAction);
        } else {
            $this->set('headerSearchAction', $this->app->make('url')->to('/dashboard/store/products' . ($this->gID ? '/' . $this->gID : '')));
        }

    }

}
