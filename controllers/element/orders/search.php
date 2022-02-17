<?php
namespace Concrete\Package\CommunityStore\Controller\Element\Orders;

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
    protected $paymentMethods;
    protected $paymentMethod;
    protected $paymentStatuses;
    protected $paymentStatus;
    protected $fulfilmentStatuses;
    protected $status;

    /**
     * @var Query
     */
    protected $query;

    public function __construct($paymentMethods, $paymentMethod, $paymentStatuses, $paymentStatus, $fulfilmentStatuses, $status)
    {
        $this->paymentMethods = $paymentMethods;
        $this->paymentMethod = $paymentMethod;
        $this->paymentStatuses = $paymentStatuses;
        $this->paymentStatus = $paymentStatus;
        $this->fulfilmentStatuses = $fulfilmentStatuses;
        $this->status = $status;
    }

    public function getElement()
    {
        return 'orders/search';
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

        $this->set('paymentMethods', $this->paymentMethods);
        $this->set('paymentMethod', $this->paymentMethod);
        $this->set('paymentStatuses', $this->paymentStatuses);
        $this->set('paymentStatus', $this->paymentStatus);
        $this->set('fulfilmentStatuses', $this->fulfilmentStatuses);
        $this->set('status', $this->status);

        $this->set('keywords', $this->app->request->request('keywords'));

        $this->set('token', $this->app->make('token'));
        if (isset($this->headerSearchAction)) {
            $this->set('headerSearchAction', $this->headerSearchAction);
        } else {
            $this->set('headerSearchAction', $this->app->make('url')->to('/dashboard/store/orders/' . ($this->status ?  $this->status : 'all')  .'/' . ($this->paymentMethod ?  $this->paymentMethod : 'all') . '/' . ($this->paymentStatus ?  $this->paymentStatus : 'all')   ));
        }

    }

}
