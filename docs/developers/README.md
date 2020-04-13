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

## Customizating Emails


