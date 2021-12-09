# Products

Products are created and edited via the Dashboard, and are stored independently of pages or blocks.

Key details about a product, such as its pricing, SKU, stock level and descriptions are edited on the 'Overview' tab when editing a product.
Other options on a product are found through the other tabs - on adding a product for the first time it is recommended to review all options that are available for a product through these tabs.

## Options and Variants

Configurable options for a product, for example a product's size, can be added via the **Options and Variants** tab when adding or editing a product.
Such options are selected by a customer when adding to the cart, and are then carried through to be shown within an order against that product.

An Option List provides a drop down or set of radio buttons for a product. Such options can be configured to directly adjust the price or weight of a product, using the **Price Adjustment** and **Weight Adjustment** fields of the product option respectively.
Other options types such as **Text Field** or **Text Area** allow text to be entered.

When a product option or set of options changes the product Code / SKU, has a specific price, stock levels, a specific image, or shipping requirements, **Variants** can be used for greater control.

To create variations for a product:
- Select one or more options to be part of variations by setting their 'In Variants' setting to yes
- Check the 'Options have different prices, SKUs or stock levels' towards the bottom of the Options and Variants tab
- Update the Product and scroll down past your product options - the combinations of options, the variations will be displayed and be configured

By default new variations are set to be unavailable.
 
Values or options entered for variations will apply for a product when its particular options are selected.
If a variation value is not entered, for example the price, the store will refer to the product 'base' price.
This means that only the values that need to be adjusted need be entered. 

Keep in mind that a variation of a product is created for every combination of option that is selected to be in variations, meaning that many options in variations can result in very large numbers of combinations. 


## Groups, Categories and Manufacturers

There are three different ways that products can be categorized, with each way being used for different organizational purposes:

- Using Product Groups - use to group products so they can be filtered within the dashboard, as well as on product list blocks
- Categorised under pages - uses pages within a site's sitemap 
- Using Manufacturers - a way to group products that can be shown on product blocks, linking to a manufacturer page

Review the [Categorization How-To](/how-tos/categorization) page for more detail on how these group concepts are used

## Attributes

Live page, user or file attributes in Concrete CMS, product attributes can be created for products.
Such attributes are intended to be used programmatically, such as in custom block templates, or by custom shipping methods.

Attributes for a product can be _edited_ via the Attributes tab when adding or editing a product.

The display of product attributes is only handled programmatically - see  [Product Attributes](/developers/attributes.html#product-attributes) for how to access product attribute data.


