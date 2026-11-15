# Installation

## Requirements

* Concrete CMS 8.5 or higher
* PHP 7.4 or higher

Community Store is an _add on_ for the content management system Concrete CMS, so it is not a stand-alone shopping cart.
It is recommended to use a release of Community Store, rather than the master branch.

## Downloading
The latest release of Community Store should be downloaded from the [releases section of the project on github](https://github.com/concretecms-community-store/community_store/releases).

## Installing
The `community_store` package folder is placed within the top level `packages` folder and Community Store is installed via the **Extend** section of the Dashboard.

During installation you can choose the page template used for the Product page type, and whether to create a parent page (and where) for product pages to be published under.

Once installed, the Dashboard page **Store / Settings** should be visited to configure default settings such the store's currency, notification emails, shipping units and checkout modes. 
Refer to the [Initial Setup](/user-guide/configuration.html) documentation as to a recommended set of options to review.

## Updating

To update Community Store, completely replace the `community_store` folder and immediately trigger the upgrade of the add-on via the **Extend** section of the Dashboard.
When replacing the add-on's folder, do not rename and leave the previous version of the folder within the packages directory - this will cause errors.

## Installing Related Add-ons

Related add-ons such as additional payment or shipping methods are installed in the same manner.
Refer to the [top level Community Store page on github](https://github.com/concretecms-community-store) for additional add-ons.
Where possible, also install releases of the add-ons instead of downloading the *master* branches.
