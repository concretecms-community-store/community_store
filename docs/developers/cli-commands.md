# CLI Commands

See the [Concrete CMS documentation covering how to run CLI commands](http://documentation.concretecms.org/developers/appendix/cli-commands)

## Reset Store Data
A CLI command is available to clear all orders, products, discounts or all three at once.
This command is useful if you have been testing an install and wish to clear test data before go-live.

    cstore:reset [<type>]

Where the type options are:
* `orders`
* `products`
* `discounts`
* `all` - combining all three options above
