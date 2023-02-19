<?php

namespace Concrete\Package\CommunityStore\Src\CommunityStore\Payment;

use Concrete\Package\CommunityStore\Src\CommunityStore\Event\Event as StoreEvent;

class PaymentEvent extends StoreEvent
{
    const PAYMENT_ON_AVAILABLE_METHODS_GET = 'on_community_store_payment_methods_get';

    /**
     * @var bool
     */
    private $error = false;

    /**
     * @var bool
     */
    private $changed = false;

    /**
     * @var string
     */
    private $errorMsg;

    /**
     * @var Method[] $methods
     */
    private $methods;

    /**
     * @var array|null
     */
    private $data;

    /**
     * @return Method[]|null
     */
    public function getMethods()
    {
        return $this->methods;
    }

    /**
     * @param Method[] $methods
     *
     * @return $this
     */
    public function setMethods($methods)
    {
        $this->methods = $methods;

        return $this;
    }

    /**
     * @return array|null
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param array|null $data
     *
     * @return PaymentEvent
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @param string $e
     *
     * @return void
     */
    public function setErrorMsg($e)
    {
        $this->errorMsg = $e;
        $this->error = true;
    }

    /**
     * @return bool
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @return string|null
     */
    public function getErrorMsg()
    {
        return $this->errorMsg;
    }

    /**
     * @return bool
     */
    public function getChanged()
    {
        return $this->changed;
    }

    public function setChanged()
    {
        $this->changed = true;
    }
}
