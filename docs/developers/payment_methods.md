# Creating Payment Methods

Like Shipping Methods, new Payment methods are added to Community Store through additional add-ons.

Each payment processor has their own requirements and way of integration, but they can generally be placed into one of two categories:

1. A customer entered payment details (such as for a credit card) either directly within the checkout or in a modal popup, and submits a `token`, which is then used to process payment
2. The customer is taken off-site to a hosted payment page, where they make a payment and are returned back to the website afterwards.

Whilst the first approach provides a more seamless experience for the customer, increasingly strict regulations make this increasingly difficult to accommodate across all markets.
For example, in the EU it is now a requirement that payment systems are Strong Customer Authentication (SCA) compliant. This is easiest to achieve with off-site payment systems such as Stripe Checkout or Paypal.

If you are looking to develop a new payment method add-on for a payment gateway, it is recommended that you opt for a gateway's latest and recommended integration technique.

The [Stripe Checkout payment add-on](https://github.com/concretecms-community-store/community_store_stripe_checkout) is a good example of an off-site payment process.

## Structure of a payment method

A payment method add-on's structure is as follows:
- A package, with a package controller.php, used to install the package and add the new payment method, e.g. `PaymentMethod::add('my_payment_method', 'My Payment Method', $pkg)`
- A payment method class, extending `\Concrete\Package\CommunityStore\Src\CommunityStore\Payment\Method`. It is named after the method's handle, e.g. for a handle `my_payment_method`, the class `MyPaymentMethodPaymentMethod` in the namespace `Concrete\Package\<PackageHandle>\Src\CommunityStore\Payment\Methods\MyPaymentMethod`, within `src/CommunityStore/Payment/Methods/MyPaymentMethod/`
- Element files, within an `elements` folder named after the payment method's handle:
    - `dashboard_form.php` - the method's settings form, shown in the Payments section of **Store / Settings**
    - `checkout_form.php` - output within the checkout when the method is selected
    - `redirect_form.php` - for off-site payment methods, the form used to send the customer to the payment gateway

### Methods to implement

- `getName()` - the name of the payment method
- `dashboardForm()` - sets the values used by the `dashboard_form` element
- `save(array $data)` - saves the method's settings from the Dashboard form
- `validate($args, $e)` - validates the Dashboard form, adding any errors to `$e`
- `checkoutForm()` - sets the values used by the `checkout_form` element
- `submitPayment()` - for on-site payments, processes the payment when the order is submitted. It returns an array: `['error' => 0, 'transactionReference' => '...']` on success, or `['error' => 1, 'errorMessage' => '...']` on failure
- `getPaymentMinimum()` / `getPaymentMaximum()` - the order totals the method is available for

### Optional methods

- `headerScripts($view)` - called on the checkout page, so the method can add any scripts or assets it needs (for example a gateway's JavaScript library)
- `isExternal()` - return `true` for off-site payment methods
- `getAction()` - for off-site payment methods, the URL the customer is sent to
- `redirectForm()` - for off-site payment methods, sets the values used by the `redirect_form` element
- `isExternalActionGET()` - return `true` if the customer should be sent to the `getAction()` URL with a GET request, instead of submitting the redirect form
- `markPaid()` - return `false` if completing an order with this method should not mark it as paid (as with the Invoice method)
- `sendReceipt()` - return `false` to not send a receipt when the order is completed
- `getPaymentInstructions()` - instructions to display to the customer (and translatable via **Store / Multilingual**)

### Completing orders from off-site payments

For off-site payment methods, the order is created with an 'incomplete' status before the customer is sent to the payment gateway.
The add-on registers its own route to handle the gateway's response or webhook, and once the payment is confirmed calls:

``` php
$order->completeOrder($transactionReference);
```

This marks the order as paid, sends the notification and receipt emails, and performs other post-purchase processes such as creating user accounts.

The checkout page submits the redirect form using JavaScript. A payment method can add a `submit` listener to the form (`#store-checkout-redirect-form`) and call `preventDefault()` to stop the redirect, for example if an error has occurred.
