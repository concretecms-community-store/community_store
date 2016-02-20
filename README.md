*This repository (and those for the addtional gateways) has been initially been made private not to exclude others, but to simply give those involved a chance to collectively refine and test the add-on without the broader community being tempted to use it in production environments. 

If you are reading this now, it's because you're a collaborator. You have almost full control of the github account, so please be mindful of your commits and issue management. Please create branches and pull requests for anything but obvious/trivial changes.

If you know of others that would like to work on this project, please let @mesuva know. 

Down the track it will/can be opened up publically, perhaps under an organisation account* 


# Community Store add-on for concrete5

An online shop add-on for concrete5.7.

The goal of this project is to continue the development of a stable, feature rich, highly extensible and customizable 'shopping cart toolkit' for concrete5.7.

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
https://github.com/Mesuva/community_store_paypal_standard

### Stripe
https://github.com/Mesuva/community_store_stripe

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

## Acknowledgements
This add-on is a fork from the Vivid Store add-on - without the huge amount of work and expertise that went into the development of Vivid Store this add-on would certainly not have been feasible! 

