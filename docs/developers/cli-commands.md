# CLI Commands

See the [Concrete CMS documentation covering how to run CLI commands](http://documentation.concretecms.org/developers/appendix/cli-commands)

## Reset Store Data
A CLI command is available to clear all orders, products, discounts or all three at once.
This command is useful if you have been testing an install and wish to clear test data before go-live.

    cstore:reset [<type>]

Where the type options are:
* `orders`
* `order-number [number]`
* `products`
* `discounts`
* `all` - combining all four options above

The `order-number` option will reset the next order number that will be allocated. This option defaults to 1, but a number can be specified as additional argument, e.g.:
    
    cstore:reset order-number 1000

## Update Product Quantities From Variations
For products with variations, updates the product's stock quantity from the quantities of its variations.

    cstore:product:quantity:update [--all] [--product=<ID>] [--force]

Use `--all` to update all products with variations, or `--product` (repeatable) to limit it to specific product IDs. `--force` runs the update even if automatic product quantities are disabled.

## Update Product Image Titles
Updates the titles of product image files.

    cstore:product:image:info:update [--all] [--product=<ID>] [--title=<operation>] [--flags=<flag>] [--yes]

The `--title` option can be:
* `keep` - use the file titles
* `cleanup` - use the file titles, removing any file extensions and replacing underscores with spaces
* `prodname-once` - match the product name, if the file title has not been altered since upload
* `prodname-always` - always match the product name

The `--flags` option selects which images to update: `1` for primary product images, `2` for secondary product images, and `4` for variation images.

## Update Product Page Metadata
Updates the metadata of product pages.

    cstore:product:page:metadata:update [--all] [--product=<ID>] [--flags=<flag>] [--yes]

The `--flags` option selects what to update: `1` to update page descriptions, and `2` to update page OpenGraph meta tags.

For each command, `--yes` runs the operation without asking for confirmation. Run `concrete help <command>` for the full list of options.
