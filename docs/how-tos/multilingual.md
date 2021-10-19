# Multilingual Setup

Community Store is aware of multilingual site setups, and allows for all aspects of a store to be offered in multiple languages.

The full setup steps for a multilingual site in Concrete CMS is outside the scope of the documentation, but the steps to add a new language to a site generally are as follows:

- Visit **System & Settings / Multilingual / Multilingual Setup** within the Dashboard
- Select **Add Locale**, and configure a new language/tree
- Visit **System & Settings / Multilingual / Copy Languages** within the same section of the Dashboard
- Copy the default language tree into the new language
- A **Switch Language** block is generally then added to a global area on the site

Once performed, there will be new copies of the `/cart`, `/checkout` and `/checkout/complete` pages within the new languages's sitemap.
Product pages will also be duplicated. 

Therefore, when adding further languages in the future, ensure that you perform the **Copy Locale Tree** tree action after adding.

When new products are added, Community Store will create the required copies of the product page in the different language trees of your site. 

The setup means that a customer can view a product in the site, swap language to see a translated version of the product, and can progress through all steps of the checkout in their chosen language.

### Store Translations

Translations of product details and other common pieces of text are performed via the **Store / Multilingual** section of the Dashboard. 

### Languages and orders
When an order is placed the language used by the customer at the time is recorded against the order. This means that if an order receipt is re-sent via the Dashboard, the email will send in the language that the order was placed. 
