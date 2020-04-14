# Discounts

Discounts in Community Store can either be automatically applied to shopping carts/orders, or applied using discount codes.

## Discount Rules

A discount rule defines the type and amount of discount applied to an order, and importantly under what circumstances it applies.
A discount rule can be configured to:
- either deduct a percentage, or a fixed value, or change the order total to a value
- deduct the discount from the items sub-total or the shipping
- apply automatically, or through the use of discount codes
- apply within a certain date range
- apply to selected groups of products
- be available to selected user groups only
- apply when the item count in a cart is within a minimum and maximum

Using the above restrictions, discounts can be configured to target very specific use-cases. Multiple discount rules can be created and made active at once.

### Code based Discounts
To enter codes for a code based discount, first configure and create the discount rule. Once saved, a **Manage Codes** button is made available. Within the codes page, enter each code to be used to trigger the discount on a new line within the Code(s) field.

::: tip Codes are case-insensitive
Note that entered codes are case-insentive, meaning that a code entered as `DISCOUNT123` is the same as `discount123`, and a customer can enter either variant to apply that discount.
:::

When code based discounts are selected on a discount rule there is the option to make them 'Single Use Code'.
When selected, codes entered into the code list can only be used once and are immediately removed. This is applicable for cases where you might want to offer a customer a one-off discount, or are printing discount vouchers. 
