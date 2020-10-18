<?php

namespace Concrete\Package\CommunityStore\Src\CommunityStore\Payment;

use Concrete\Package\CommunityStore\Src\CommunityStore\Payment\Method;
use Concrete\Package\CommunityStore\Src\CommunityStore\Event\Event as StoreEvent;

class PaymentEvent extends StoreEvent {
	const PAYMENT_ON_AVAILABLE_METHODS_GET = 'on_community_store_payment_methods_get';

	/** @var boolean */
	private $error = false;

	/** @var boolean */
	private $changed = false;

	/** @var string */
	private $errorMsg = null;

	/** @var $methods Method[] */
	private $methods;

	/** @var array | null */
	private $data;

	/**
	 * @return Method[] | null
	 */
	public function getMethods()
	{
		return $this->methods;
	}

	/**
	 * @param Method[] $methods
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
	 * @return PaymentEvent
	 */
	public function setData($data)
	{
		$this->data = $data;
		return $this;
	}

	/**
	 * @param string
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
	 * @return null|string
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