<?php

namespace Concrete\Package\CommunityStore\Src\CommunityStore\Product;

use Concrete\Package\CommunityStore\Src\CommunityStore\Event\Event;

class ProductEvent extends Event
{
    const PRODUCT_ADD = 'on_community_store_product_add';

    const PRODUCT_UPDATE = 'on_community_store_product_update';

    const PRODUCT_DUPLICATE = 'on_community_store_product_duplicate';

    const PRODUCT_DELETE = 'on_community_store_product_delete';

    protected $event;

    // if an event just needs to refer to a product, just pass it the one product object.
    // if an event has a 'before' and 'after', pass the previous product before and the new one second
    public function __construct($product, $newProduct = null)
    {
        $this->product = $product;
        $this->newProduct = $newProduct;
    }

    public function getProduct()
    {
        return $this->product;
    }

    public function getNewProduct()
    {
        return $this->newProduct;
    }
}
