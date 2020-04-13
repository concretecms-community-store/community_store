# Creating Shipping Methods

Additional shipping methods can be added to Community Store by developing them as additional add-ons.

[An example package showing how to create Community Store shipping methods is available on github](https://github.com/concrete5-community-store/community_store_shipping_example).
Additional shipping methods are also available within the [Community Store project page](https://github.com/concrete5-community-store) and can act as further examples, as can the two built in methods.

It is recommended to download the master branch of the example shipping method, change its namespace and use it as a base.

A shipping method add-on's structure is as followings:
- A package, with a package controller.php, used to install the package and installed the new Shipping Method
- A shipping method class that extends `ShippingMethodTypeMethod`. This class is also a Doctrine entity, so refers to a particular database table to store configuration values.
- An elements template file, used to display the shipping method's dashboard form

A shipping method class defines the fields required to configure the shipping method and can be as simple or complex as required.
Beyond the saving of the specific fields, there are five required functions in the class:

### `addMethodTypeMethod`
Used to initially save the new shipping method. The method is pased `$data`, which contains the values send from the form defined in the shipping method's dashboard element file.  

### `update`
Used to update the shipping method's configuration. In the example method, note that a private method `addOrUpdate` is used to simplify saving and updated. It is in this function that the invidual configuration items for the method are saved.

### `dashboardForm`
Called when displaying the shipping method's dashboard form, initialiing the values send to the element file

### `isEligible`
Called to determined whether a shipping method is applicable to the order and should offered. It simply returned true or false.
This method can contain as much or as little checking as required. For example, it may need to:

Fetch the cart's items and fetch details about it:
``` php
$shippableItems = \Concrete\Package\CommunityStore\Src\CommunityStore\Cart\Cart::getShippableItems();

foreach ($shippableItems as $item) {
    $product = $item['product']['object'];
    $totalProductWeight = $product->getWeight() * $item['product']['qty'];
}
```

Check the customer's shipping country:
``` php
$customer = new \Concrete\Package\CommunityStore\Src\CommunityStore\Customer\Customer();
$custCountry = $customer->getValue('shipping_address')->country;
``` 

Check the cart's subtotal:
``` php
$subtotal = \Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Calculator::getSubTotal();
```
 
Check the cart's weight:
``` php
$totalWeight = \Concrete\Package\CommunityStore\Src\CommunityStore\Cart\Cart::getCartWeight();
```

### `getOffers `
This is called once the `isEligible` function returns true, and returns one ore more 'offers' - each offer being a specific type of shipping and rate.
A shipping method may return more than one offer at time, such as to offer different delivery classes, such as 'express' shipping versus regular shipping.

Offers are created and returned within the function as per the following example:
``` php
    // at top of class:
    use Concrete\Package\CommunityStore\Src\CommunityStore\Shipping\Method\ShippingMethodOffer;

    function getOffers() {
    $offers = [];
    
    // for each sub-rate, create a ShippingMethodOffer
    $offer = new ShippingMethodOffer();
    
    // then set the rate
    $offer->setRate(10);  // 10 is an example, this would be calculated
    
    // then set a label for it
    $offer->setOfferLabel('First Offer');
    
    // add it to the array
    $offers[] = $offer;
    
    // continue adding further rates
    $offer = new ShippingMethodOffer();
    $offer->setRate(15);
    $offer->setOfferLabel('Second Offer');
    
    // further text details for the specific offer can be added via:
    $offer->setOfferDetails('Signature required on delivery');
    
    $offers[] = $offer;
    return $offers;
}
```
