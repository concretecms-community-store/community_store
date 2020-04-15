# Billing and Shipping Countries

A common configuration is to restrict the countries that a customer can enter for shipping and billing addresses.

The configuration of what countries are offered is performed at the _User Attribute_ level, and **not** through the _Order Attributes_ - this is a common confusion since both users and orders have the same address attributes created.

To restrict billing and shipping countries:
- visit **Members / Attributes** within the Dashboard
- select either the **Billing Address** or **Shipping Address** attribute
- scroll to the **Available Countries** section and configure as required

The list of countries configured on each attribute will then be reflected in the country dropdowns in the store's checkout.

### Restricting Countries Through Shipping Methods

The default shipping methods (and most other available methods) allow available countries to be configured for each shipping method that applies.
This configuration can be used to only offer shipping to certain countries, even though the customer may be able to select such countries from the country drop down for their shipping address.
You may wish to limit shipping to countries this way instead, potentially displaying a message to contact the store owner for more details.
