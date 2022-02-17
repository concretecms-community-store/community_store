<?php

namespace Concrete\Package\CommunityStore\Src\CommunityStore\Cart;

use Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product;
use Concrete\Package\CommunityStore\Src\CommunityStore\Event\Event as StoreEvent;

class CartEvent extends StoreEvent {
    const CART_PRE_ADD = 'on_community_store_cart_pre_add';
    const CART_POST_ADD = 'on_community_store_cart_post_add';
    const CART_PRE_UPDATE = 'on_community_store_cart_pre_update';
    const CART_POST_UPDATE = 'on_community_store_cart_post_update';
    const CART_PRE_REMOVE = 'on_community_store_cart_pre_remove';
    const CART_POST_REMOVE = 'on_community_store_cart_post_remove';
    const CART_PRE_CLEAR = 'on_community_store_cart_pre_clear';
    const CART_POST_CLEAR = 'on_community_store_cart_post_clear';
    const CART_ACTION = 'on_community_store_cart_action';
    const CART_GET = 'on_community_store_cart_get';

    /** @var boolean */
    private $error = false;

    /** @var string */
    private $errorMsg = null;

    /** @var Product */
    private $product;

    /** @var array | null */
    private $data;

    private $updatedCart = null;

    private $updatedDiscounts = null;

    /**
     * @return Product | null
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @param Product $product
     * @return $this
     */
    public function setProduct($product)
    {
        $this->product = $product;
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
     * @return CartEvent
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

        public function setUpdatedDiscounts($updatedDiscounts) {
            $this->updatedDiscounts = $updatedDiscounts;
        }

        public function setUpdatedCart($updatedCart) {
            $this->updatedCart = $updatedCart;
        }

        public function updatedCart() {
            return $this->updatedCart;
        }

        public function updatedDiscounts() {
            return $this->updatedDiscounts;
        }
}
