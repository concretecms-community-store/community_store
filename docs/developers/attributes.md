# Product and Order Attributes

Product and Order attributes can be used in the same way that other attributes in concrete5 can be used, such as page attributes.

## Product Attributes

Custom Product attributes are created through the **Store / Products / Attributes** Dashboard page, and then stored against products through their attributes tab (or programmatically assigned).

A product attribute can be output in a template, etc, by simply using:
``` php
echo $product->getAttribute('attribute_handle');
```

where `$product` is a product object. Similarly, an attribute value can be _stored_ against a product with:

``` php
echo $product->setAttribute('attribute_handle', 'A value to set');
```

When outputting product attributes on Product and Product List blocks, the `$product` object is directly available (in the case of the Product List block it's within a loop).

If a product attribute is needing to be used in other places, such as in a shipping method, it is a case of be able to fetch the product object.
For example, to loop through the products and add together values from an attribute with the handle `handling_fee`:

``` php
$cartItems = \Concrete\Package\CommunityStore\Src\CommunityStore\Cart\Cart::getCart();
    
$totalHandling = 0;         
foreach ($cartItems as $cartitem) {
    $product = $cartitem['product']['object'];

    $handlingFee = $product->getAttribute('handling_fee');
    $totalHandling  += floatval($handlingFee); 
}
   
```
In the above case, the cart items are returned as an array, with the product objects available within each item.

## Order Attributes

Order attributes work the same way as product attributes, except that both the getting and setting of order attributes is handled programmatically.
