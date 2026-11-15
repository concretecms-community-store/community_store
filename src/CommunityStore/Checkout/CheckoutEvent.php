<?php

namespace Concrete\Package\CommunityStore\Src\CommunityStore\Checkout;

use Concrete\Package\CommunityStore\Src\CommunityStore\Event\Event as StoreEvent;
use Concrete\Core\Error\ErrorList\ErrorList;
use Concrete\Core\Page\Controller\PageController;
use Concrete\Package\CommunityStore\Src\CommunityStore\Order\Order;
use Symfony\Component\HttpFoundation\Response;

class CheckoutEvent extends StoreEvent
{
    const CHECKOUT_REQUEST = 'on_community_store_checkout_request';
    const CHECKOUT_COMPLETE_VIEW = 'on_community_store_checkout_complete_view';
    const CHECKOUT_VALIDATE = 'on_community_store_checkout_validate';

    /** @var string */
    private $action;

    /** @var array */
    private $parameters;

    /** @var Response|null */
    private $response = null;

    /** @var Order|null */
    private $order = null;

    /** @var PageController|null */
    private $controller = null;

    /** @var ErrorList|null */
    private $errors = null;

    /** @var array|null */
    private $data = null;

    /** @var string|null */
    private $addressType = null;

    /**
     * @param string $action
     * @param array $parameters
     */
    public function __construct($action, $parameters = [])
    {
        $this->action = $action;
        $this->parameters = $parameters;
    }

    /**
     * The checkout controller action being requested, e.g. view, submit, external, updater.
     *
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Set a response (e.g. a redirect) to be returned instead of running the checkout action.
     *
     * @param Response $response
     * @return $this
     */
    public function setResponse(Response $response)
    {
        $this->response = $response;
        return $this;
    }

    /**
     * @return Response|null
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param Order|null $order
     * @return $this
     */
    public function setOrder($order)
    {
        $this->order = $order;
        return $this;
    }

    /**
     * The order the event relates to, if any.
     *
     * @return Order|null
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param PageController $controller
     * @return $this
     */
    public function setController(PageController $controller)
    {
        $this->controller = $controller;
        return $this;
    }

    /**
     * The page controller handling the request, allowing listeners to set view variables or add header/footer items.
     *
     * @return PageController|null
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * @param ErrorList $errors
     * @return $this
     */
    public function setErrors(ErrorList $errors)
    {
        $this->errors = $errors;
        return $this;
    }

    /**
     * The validation error list - listeners can add errors to it to prevent the checkout step from completing.
     *
     * @return ErrorList|null
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @param array|null $data
     * @return $this
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * The submitted form data.
     *
     * @return array|null
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param string|null $addressType
     * @return $this
     */
    public function setAddressType($addressType)
    {
        $this->addressType = $addressType;
        return $this;
    }

    /**
     * The address being validated, either 'billing' or 'shipping'.
     *
     * @return string|null
     */
    public function getAddressType()
    {
        return $this->addressType;
    }
}
