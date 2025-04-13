# Creating Payment Methods

Like Shipping Methods, new Payment methods are added to Community Store through additional add-ons.

Each payment processor has their own requirements and way of integration, but they can generally be placed into one of two categories:

1. A customer entered payment details (such as for a credit card) either directly within the checkout or in a modal popup, and submits a `token`, which is then used to process payment
2. The customer is taken off-site to a hosted payment page, where they make a payment and are returned back to the website afterwards.

Whilst the first approach provides a more seamless experience for the customer, increasingly strict regulations make this increasingly difficult to accommodate across all markets.
For example, in the EU it is now a requirement that payment systems are Strong Customer Authentication (SCA) compliant. This is easiest to achieve with off-site payment systems such as Stripe Checkout or Paypal.

If you are looking to develop a new payment method add-on for a payment gateway, is is recommended that you opt for a gateway's latest and recommend integration technique.

The [Stripe Checkout payment add-on](https://github.com/concretecms-community-store/community_store_stripe_checkout) is a good example of an off-site payment process.

