# Translations

Interface translations for Community Store are managed at [translate.concretecms.org](https://translate.concretecms.org/translate/package/community_store).
This is the same translation system used for Concrete CMS add-ons in the official marketplace, meaning that you can visit `/index.php/dashboard/system/basics/multilingual/update` within your Concrete CMS installation to download and install/update the most up-to-date translation. 

To provide translations, please register/login at the above link.

In the past, translations have been directly stored alongside the project. If you are starting a new translation on translate.concretecms.org, you may find that existing translations at [https://github.com/concretecms-community-store/community_store/tree/master/languages](https://github.com/concretecms-community-store/community_store/tree/master/languages) provide a good starting point.

## Multilingual Helper

As the translation of product names, details and other customizable text is handled through Community Store's own translation system, a helper function is available to output translated strings.

The multilingual helper is used in front-end templates (blocks, cart, etc), for example:

``` php
$csm = $app->make('cs/helper/multilingual');

// output a translated Product Name
echo $csm->t($product->getName(), 'productName', $product->getID());  

// output an option name for a product
echo $csm->t($option->getName(), 'optionName', $product->getID(), $option->getID())
```

The function excepts a string to be translated, the type of text it is (the 'context'), the ID of the associated product if applicable, and the id of the entity being translated (such as the ID of a product option).

Two additional parameters are used only within the multilingual dashboard pages.

For the full list of translation contexts, review the function's notes on the `Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Multilingual` class.
