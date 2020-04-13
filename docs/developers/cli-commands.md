# CLI Commands

See the [concrete5 documentation covering how to run CLI commands](http://documentation.concrete5.org/developers/appendix/cli-commands)

## Reset Store Data
A CLI command is available to clear all orders, products, discounts or all three at once.
This command is useful if you have been testing an install and which to clear test data before go-live.

    cstore:reset [<type>]

Where the type options are:
* orders
* products
* discounts
* all - combining all three options above
