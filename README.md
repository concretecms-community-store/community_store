# Community Store add-on for Concrete CMS

[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)

An open, free and community developed eCommerce system for Concrete CMS (previously known as concrete5)

Please refer to the project documentation for extended details - https://concretecms-community-store.github.io/community_store/

The goal of this project is to provide a stable, feature rich, highly extensible and customizable 'shopping cart toolkit' for version 8+ of Concrete CMS.
The add-on will work directly with a Bootstrap based theme, primarily Elemental, but is intended to be easily modified and overriden for a custom theme.

## PHP Version
This add-on is intended to be run in a PHP 8 environment, supporting the same PHP version as the latest version of Concrete CMS.

Community Store _may_ still run on PHP 7.4, but this version of PHP has reached end-of-life so is no longer supported in recent releases.

## Concrete CMS Version
It is recommended to use the very latest release of Concrete CMS, with the latest release of Community Store.

Version 2.4.3 and beyond of Community Store is intended for use with version 9 and version 8 of Concrete CMS.
Earlier releases of Community Store will not work or display correctly under version 9 of Concrete. If you are updating an existing version 8 install of Concrete with Community Store installed, please upgrade Community Store to version 2.4.3 or later.

## Setup
The package can be downloaded, unzipped into the /packages directory (ensuring the folder name is simply 'community_store') and installed via the 'Extend Concrete' option within the dashboard.  It is recommended that a 'release' be used instead of the master branch - https://github.com/concretecms-community-store/community_store/releases

## Development
To install dependencies, run:
- npm install --include=dev

Once installed, the node_modules folder is not needed for deployment.

## Documentation
Further documentation and how-tos can be found at https://concretecms-community-store.github.io/community_store/
The documentation is automatically generated using https://vuepress.vuejs.org/, from the files within the [docs folder of the master branch](https://github.com/concretecms-community-store/community_store/tree/master/docs).
Pull requests to the documentation are also welcome.

## Payment gateways
To keep the 'core' store component as lightweight as possible it does not include a payment gateway other than the 'Invoice' type.
Payment gateways are to be installed as additional add-ons.

Additional gateways can be found at https://github.com/concretecms-community-store.

## Shipping Methods
The store features two built in shipping methods, but like payment gateways these can be extended with further packages.
An simple example shipping method has been created for reference and can be found at:
https://github.com/concretecms-community-store/community_store_shipping_example

Further pre-built shipping methods are also available at Additional gateways can be found at https://github.com/concretecms-community-store.

## Translations
Interface translations for Community Store are managed at https://translate.concretecms.org/translate/package/community_store and can be installed via Concrete's dashboard.

To provide translations, please register/login at the above link.

## Additional Addons

### User Account Order History
Show a user's order history as a single page /account/orders:
https://github.com/JeRoNZ/community_store_order_history

### Omni Gallery for Community Store
Show product and product list images in galleries, sliders and carousels.
https://marketplace.concretecms.com/marketplace/addons/omni-gallery-for-community-store

### Community Store Import
Import products via a CSV file.
https://github.com/dbuonomo/community-store-import

### Sendy Subscribing
https://github.com/cahueya/community_store_sendy_subscribing
