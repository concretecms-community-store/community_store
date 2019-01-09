# Community Store add-on for concrete5

**Important - this is the 2.x version of Community Store and is intended for use on V8+ versions of concrete5 only.** 

**Please also check that the additional add-ons you install are compatible with this version. In particular, additional shipping methods will have both 1.x and 2.x versions, please use 2.x releases of these add-ons.**

[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)

An open, free and community developed eCommerce system for concrete5

Please refer to the project wiki for more extended details - https://github.com/concrete5-community-store/community_store/wiki

The goal of this project is to provide a stable, feature rich, highly extensible and customizable 'shopping cart toolkit' for concrete5.7 and v8.

At this point the add-on and it's associated payment and shipping methods are functioning well, but should still be considered beta.
If you are not a concrete5 developer and wish to use the Community Store on a production site it is advised that you find a developer to assist you.

The add-on will work directly with a Bootstrap based theme, primarily Elemental, but is intended to be easily modified and overriden for a custom theme.

## Setup
The package can be downloaded, unzipped into the /packages directory (ensuring the folder name is simply 'community_store') and installed via the 'Extend concrete5' option within the dashboard.  It is recommended that a 'release' be used instead of the master branch - https://github.com/concrete5-community-store/community_store/releases

### Important note regarding versions
For new installations on V8 of concrete5, please use a version 2.x release.
For installations into existing 5.7 sites, please use a 1.x release.
While the feature set is the same between both version of Community Store, it is currently not possible to upgrade from the 1.x to the 2.x version.

## Payment gateways
To keep the 'core' store component as lightweight as possible it does not include a payment gateway other than the 'Invoice' type.
Payment gateways are to be installed as additional add-ons.

Additional gateways can be found at:

### Paypal Standard - https://www.paypal.com
https://github.com/concrete5-community-store/community_store_paypal_standard

### Stripe - https://stripe.com
https://github.com/concrete5-community-store/community_store_stripe

### Pin Payments - https://pin.net.au
https://github.com/concrete5-community-store/community_store_pin_payments

### Square - https://squareup.com
https://github.com/Babinsky/community_store_square

### Authorize.Net - https://www.authorize.net
https://github.com/concrete5-community-store/community_store_authorize_net

### SOFORT - https://www.sofort.com
https://github.com/concrete5-community-store/community_store_sofort

### Mollie - https://www.mollie.com
https://github.com/concrete5-community-store/community_store_mollie

### DPS Payment Express - https://www.paymentexpress.com
https://github.com/JeRoNZ/community_store_dps_pxpay

### eWAY - https://eway.io
https://github.com/JeRoNZ/community_store_eway

### Worldpay - https://www.worldpay.com
https://github.com/concrete5-community-store/community_store_worldpay_hosted

### Payrexx - https://www.payrexx.com/
https://github.com/concrete5-community-store/community_store_payrexx

## Shipping Methods
The store features two built in shipping methods, but like payment gateways these can be extended with further packages. 
An simple example shipping method has been created for reference and can be found at:
https://github.com/concrete5-community-store/community_store_shipping_example

## Translations
The Community Store package has multiple translations available at http://concrete5.github.io/package-translations/
Translations are not managed with the master branch but are included within releases.

## Addons

### User Account Order History
Show a user's order history as a single page /account/orders:
https://github.com/JeRoNZ/community_store_order_history

## PHP Version
This add-on is intended to support PHP5.6+ onwards, being 100% operational on PHP7.

## Compatibility
This version of Community Store is compatible with version 8+ of concrete5, and will not work on version 5.7.x. There is a separate branch/release of Community Store that still supports 5.7, this is version 1.x of Community Store.
Version 1.x of Community Store cannot be updated to version 2.x at this point. For new installs, please use this version.

