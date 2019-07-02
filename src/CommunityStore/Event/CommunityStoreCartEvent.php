<?php

namespace Concrete\Package\CommunityStore\Src\CommunityStore\Event;

use Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product;

class CommunityStoreCartEvent extends CommunityStoreEvent {
    /** @var Product */
    private $product;

    /** @var array | null */
    private $data;

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
     * @return CommunityStoreCartEvent
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }
}