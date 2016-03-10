<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Product;

use Symfony\Component\EventDispatcher\GenericEvent;

class ProductEvent extends GenericEvent
{
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
