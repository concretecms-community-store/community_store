# Community Store add-on for concrete5

[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)

An open, free and community developed eCommerce system for concrete5

Please refer to the project wiki for more extended details - https://github.com/concrete5-community-store/community_store/wiki

The goal of this project is to provide a stable, feature rich, highly extensible and customizable 'shopping cart toolkit' for concrete5.7.

At this point the add-on and it's associated payment and shipping methods are functioning well, but should still be considered beta.
If you are not a concrete5 developer and wish to use the Community Store on a production site it is advised that you find a developer to assist you.

The add-on will work directly with a Bootstrap based theme, primarily Elemental, but is intended to be easily modified and overriden for a custom theme.

## Setup
The package can be downloaded, unzipped into the /packages directory (ensuring the folder name is simply 'community_store') and installed via the 'Extend concrete5' option within the dashboard. 

## Payment gateways
To keep the 'core' store component as lightweight as possible it does not include a payment gateway other than the 'Invoice' type.
Payment gateways are to be installed as additional add-ons.

Additional gateways can be found at:

### Paypal Standard
https://github.com/concrete5-community-store/community_store_paypal_standard

### Stripe
https://github.com/concrete5-community-store/community_store_stripe

### Pin Payments
https://github.com/concrete5-community-store/community_store_pin_payments

### Square
https://github.com/Babinsky/community_store_square

### Authorize.Net
https://github.com/concrete5-community-store/community_store_authorize_net

## Shipping Methods
The store features two built in shipping methods, but like payment gateways these can be extended with further packages. 
An simple example shipping method has been created for reference and can be found at:
https://github.com/concrete5-community-store/community_store_shipping_example

## Translations
The Community Store package has multiple translations available at http://concrete5.github.io/package-translations/
Translations are not included within the package, these need to be manually installed.

## PHP Version
This add-on is intended to support PHP5.4+ onwards, being 100% operational on PHP7.
