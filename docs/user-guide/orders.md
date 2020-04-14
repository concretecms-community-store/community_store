# Orders

Orders are managed via the **Store / Orders** section of the Dashboard. Orders can be filtered by status, printed, etc.

Depending on your needs, the status of an order can be adjusted through the **Update Fulfilment Status** section of each order.
This can be used to change whether an order has been shipped, delivered or returned.

## Refunding

Under the **Payment Status** section of an order are controls to either manually mark an order as paid (e.g. if a customer has paid via bank transfer), or can be marked as refunded.

::: tip Automatic Refunds
Note that some payment methods such as **Stripe Checkout** will automatically mark orders as refunded if they are refunded through their Dashboard.
:::
 
Invoices can also be resent through the order summary page in the Dashboard. Resent order receipts/invoices will reflect the current payment status, so can be a way to notify a customer that their payment has been received in full.



## Cancelling

Orders can be cancelled via the **Cancel Order** button at the bottom of the order summary page.
Once cancelled, a **Delete Order** button will appear, allowing the order to be completely removed.


::: warning Stock Levels on refunded/cancelled orders
Stock levels on stock controlled products will be adjusted automatically when orders are placed and paid for.
However, when orders are cancelled or refunded, stock levels are _not_ adjusted back to their pre-order levels. Ensure that you manually adjust the stock levels to reflect the stock you are no longer including within the cancelled order. 
:::

