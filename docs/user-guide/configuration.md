# Initial Setup

After installing you should review some commonly changed settings before populating your store with products.

As an Administrator visit, **Store / Settings** within concrete5's Dashboard.

A suggested checklist of items to review is as follows:

- **Currency** - Adjust the currency for the store
- **Tax** - Adjust whether entered product prices are inclusive or exclusive of taxes
- **Shipping** - Adjust the units for weight and size
- **Notifications and Receipts** - Enter one or more emails for order notifications to be sent to
- **Cart and Checkout** - Adjust the guest checkout setting

## Tax Setup

There are two ways that prices can be entered into Community Store, inclusive and exclusive of taxes.
This setting is adjusted via the Tax section of  **Store / Settings** within the dashboard - ensure you configure this setting before entering products.

There are two steps to configure taxes:
- First, add a new tax rate via **Store / Settings / Tax** and the 'Add Tax Rate' button
- Second, 'Edit a Tax Class' and add the newly added Tax Rate to the list of rates

By default, the add-on installs a 'Default' tax class, and this is then automatically applied to all products.
Further Tax Classes allow different taxes to be applied in groups to specific products. 

## Payment Gateways

By default Community Store installs only one Payment Method, **Invoice**.
This method is intended for situations where immediate payments are not required or for testing purposes.

Additional payment methods can be [downloaded from github](https://github.com/concrete5-community-store) as concrete5 add-ons.

Once installed, each payment method is enabled and configured from the Payments section of **Store / Settings** within the dashboard.
Typically each Payment Method needs keys or other credentials entered.

More than one payment method can be enabled at a time.

## Shipping Methods

There are two shipping methods installed by default:

- **Flat Rate** - allows shipping to be calculated from a base rate, plus a rate based on quantity or weight
- **Free Shipping** - offers free shipping when certain price or weight thresholds are met

The two shipping methods (and most others available) can be configured to only offer shipping to selected countries.
Multiple shipping options of the same kind can be enabled, to create different shipping cost structures per country.

A common situation is that free shipping is offered on orders over a certain price. 
For example, if you offer free shipping on orders $200 and above, Add a 'Free Shipping' shipping method and configure its 'Minimum Purchase Amount for this rate to apply' value to 200.
Then, on your paid shipping method(s), set the 'Maximum Purchase Amount for this rate to apply' to 199.99

Like Payment Methods, additional shipping methods can be  [downloaded from github](https://github.com/concrete5-community-store) as concrete5 add-ons.

For complex shipping calculation requirements, further custom shipping methods can be developed as add-ons. [An example shipping method is available on github](https://github.com/concrete5-community-store/community_store_shipping_example) to act as template for new methods.
