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
