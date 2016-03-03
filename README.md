# Community Store add-on for concrete5

An open, free and community developed eCommerce system for concrete5

The goal of this project is to provide a stable, feature rich, highly extensible and customizable 'shopping cart toolkit' for concrete5.7.

At this point the add-on and it's associated payment and shipping methods are functioning well, but should still be considered beta.
If you are not a concrete5 developer and wish to use the Community Store on a production site it is advised that you find a developer to assist you.

At this point we'd like to heavily test and debug the system before adding futher features.

The add-on is to follow concrete5 best practices and appropriate PHP Standards Recommendations.
The 'master' branch should aim to always be stable and deployable, however, at this point in time the add-on is intended for use by concrete5 developers rather than newcomers to concrete5.

The add-on will work directly with a Bootstrap based theme, primarily Elemental, but is intended to be easily modified and overriden for custom theme.

## Setup
This project uses [Composer](https://getcomposer.org/) to install third-party librares. Run composer at the root of the add-on folder before installing:

        composer install

## Payment gateways
To keep the 'core' store component as lightweight as possible it does not include a payment gateway other than the 'Invoice' type.
Payment gateways are to be installed as additional add-ons.

Additional gateways can be found at:

### Paypal Standard
https://github.com/concrete5-community-store/community_store_paypal_standard

### Stripe
https://github.com/concrete5-community-store/community_store_stripe

The use of the Ominpay library is encouraged to developed further payment add-ons (used by the Stripe add-on).

## Conventions
### Use Statements
Use statements that include Community Store classes should be aliased with the word 'Store'.
i.e. 
        use \Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Price as StorePrice;

### Class/ID Naming
All classes and IDs that relate to functionality/processing should be prefixed with 'store-' and appear first in class lists.
Such classes/IDs are therefore meant to always remain in markup to support javascript related functionality.
For custom styling purposes, Bootstrap replated classes can be safely removed from elements without concern of breaking functionality.

## PHP Version
This add-on is intended to support PHP5.4+ onwards, being 100% operational on PHP7.
