# Community Store add-on for Concrete CMS

[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)

An open, free and community developed eCommerce system for Concrete CMS (previously known as concrete5)

Please refer to the project documentation for extended details - https://concretecms-community-store.github.io/community_store/

The goal of this project is to provide a stable, feature rich, highly extensible and customizable 'shopping cart toolkit' for version 8.5+ of Concrete CMS.
The add-on will work directly with a Bootstrap based theme, such as Elemental (Concrete CMS 8) or Atomik (Concrete CMS 9), but is intended to be easily modified and overridden for a custom theme.

As of version 2.8, Community Store no longer requires jQuery. If a theme or other add-on relies on jQuery being loaded with the store, enable the **Legacy jQuery Support** option in the **Cart and Checkout** section of **Store / Settings**.

## PHP Version
Community Store requires PHP 7.4 or higher. PHP 8 is recommended, supporting the same PHP versions as the latest version of Concrete CMS.

## Concrete CMS Version
It is recommended to use the very latest release of Concrete CMS, with the latest release of Community Store.

Version 2.4.3 and beyond of Community Store is intended for use with version 9 and version 8 of Concrete CMS.
Earlier releases of Community Store will not work or display correctly under version 9 of Concrete. If you are updating an existing version 8 install of Concrete with Community Store installed, please upgrade Community Store to version 2.4.3 or later.

## Setup
The package can be downloaded, unzipped into the /packages directory (ensuring the folder name is simply 'community_store') and installed via the 'Extend Concrete' option within the dashboard.  It is recommended that a 'release' be used instead of the master branch - https://github.com/concretecms-community-store/community_store/releases

## Development
Front-end libraries (such as Tom Select, SortableJS and PhotoSwipe) are managed with [Yarn](https://classic.yarnpkg.com/) and copied into the `js` and `css` folders when installing.

To install dependencies, run:
- `yarn`

To work on the documentation:
- `yarn docs:dev` - run a local development server
- `yarn docs:build` - build the documentation
- `yarn docs:preview` - preview the built documentation

Once installed, the node_modules folder is not needed for deployment.

## Documentation
Further documentation and how-tos can be found at https://concretecms-community-store.github.io/community_store/
The documentation is automatically generated using [VitePress](https://vitepress.dev/), from the files within the [docs folder of the master branch](https://github.com/concretecms-community-store/community_store/tree/master/docs).
Pull requests to the documentation are also welcome.

## Payment gateways
To keep the 'core' store component as lightweight as possible it does not include a payment gateway other than the 'Invoice' type.
Payment gateways are to be installed as additional add-ons.

Additional gateways can be found at https://github.com/concretecms-community-store.

## Shipping Methods
The store features two built in shipping methods, but like payment gateways these can be extended with further packages.
A simple example shipping method has been created for reference and can be found at:
https://github.com/concretecms-community-store/community_store_shipping_example

Further pre-built shipping methods are also available at https://github.com/concretecms-community-store.

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

### Sendy Subscribing
https://github.com/cahueya/community_store_sendy_subscribing
