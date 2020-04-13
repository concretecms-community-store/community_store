# Customizations

A core goal of Community Store is to be both straightforward for site administrators to manage, as well as being a very flexible base for developers to override and extent to meet custom requirements.

With this in mind, Community Store has been build with the intention that it will be integrated into custom themes, be overridden and translated.
The majority of typical customisations follow the same patterns as other components in concrete5:

## Customizing Blocks

Custom templates can be created for the four blocks provided by Community Store in the same way as other blocks in concrete5 - see the [concrete5 documentation](https://documentation.concrete5.org/developers/working-with-blocks/working-with-existing-block-types/creating-additional-custom-view-templates/creating-a-template-file) for an outline of this process.
 
::: warning A note on class names
When creating custom block templates, the HTML can be customized freely, but leave all classes prefixed with `store-` in place - these classes are often used by a block's corresponding Javascript.
:::

## Overriding Cart and Checkout Pages

The cart and checkout pages are 'single pages' and can be overridden by copying:
`/packages/community_store/single_pages/cart.php`
into
`/application/single_pages/cart.php`
(or `checkout.php` or `checkout/cart.php`)

Single page templates can also be included in theme folders.

## Overriding Elements
Similar to single pages, various 'elements' can be overridden as well, those found within `/packages/community_store/elements`
Such elements control the output of components such as the cart modal overlay, the list of shipping methods offered in the checkout and the printable order slip, as examples.
These files can be copied into `/application/elements`.

## Customizating Emails

There are three emails templates used by Community Store:
- `new_order_notification.php` - send to the store's administrator
- `new_user.php` - sent to the customer when a new user account is created
- `order_receipt.php` - the order receipt email sent to the customer

These can be overridden by copying them into `/application/mail` and customizing them there.
