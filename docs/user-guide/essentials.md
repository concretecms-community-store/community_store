# Essentials

Like most add-ons for Concrete CMS, Community Store follows a common pattern of design, making its functionality intuitive, leaving the purpose of documentation to cover non-obvious configurations or development tasks.

Because of this common design, if you are an existing Concrete CMS user or developer you may find that the following outline of how Community Store is structured may be all that is required to start working with it:

## Installation
The `community_store` package folder is placed within the top level `packages` folder and Community Store is installed via the **Extend Concrete CMS** section of the Dashboard.

Once installed, the Dashboard page **Store / Settings** should be visited to configure default settings such the store's currency, notification emails, shipping units and checkout modes. 

## What is installed
- A Dashboard page, **Store**, with sub-pages to view orders, configure products and manage other settings
- Four _Block_ types under the grouping Store:
    - **Product** - displays an individual product
    - **Product List** - displays a list of products, with or without add-to-cart controls
    - **Product List Filter** - adds controls to filter one or more Product List blocks on a page by product attributes
    - **Utility Links** - displays links to display the cart and checkout, typically placed in a Global Area
- A _Single Page_ for the cart, located at `/cart`
- A _Single Page_ for the checkout, located at `/checkout`
- A _Single Page_ underneath the checkout, at `/checkout/complete`, which displays to a customer 
- A _Page Type_, **Product**, set up to use a 'full' layout by default, with a default Product block in the Main area
- Various user attributes for storing customer related detail
- Two shipping methods, **Flat Rate** and **Free Shipping**. Additional shipping methods are installed through add-ons.
- A single payment method, **Invoice**. Additional payment methods are installed through add-ons.
- A **Product Breadcrumb** custom template for the **Auto-Nav** block is made available, to display a breadcrumb trail of pages for a product's _categorization_, instead of it's true location within a site

## Products and Product Pages
- Products are added via the Dashboard and exist independently of pages and/or blocks. When added, a corresponding Product page is created under `/products`
- The default Product page has a Product block on it, configured to look at the page to determine which product to display
- Products exist independently of product pages and blocks - product pages are not required, and product blocks can be placed freely across a site
- Product pages can be moved in the sitemap if desired
- Products can be categorized (and therefore filtered) through Product Groups, but also under pages of a site

## Product Category Pages
By default, a page type to manage and display categories is not installed.
The creation of a category page is similar to other custom page types in Concrete CMS:
- Create a new page type, such as 'Product Category', with a template of your choosing
- On the new page type, add a default Product List block, with the setting **List Products**, set to _Under current page_

New categories can then be created as would other pages with Concrete CMS (i.e. through Composer), being linked to via common blocks as as Auto-Nav and Page List. 
Products can then be placed within the created categories by adding the category page(s) via the 'Categorized under pages' section when editing a product.
 
## Product List Filter blocks
Product List Filter blocks allow a visitor to filter Product List blocks on a page via product price as well as _product attributes_.
Note that product _options_ are not filterable. If you offer product options such as _size_ and wish to filter by the size, created a corresponding product attribute. 
 
## Customizations and Theming
- All blocks can be overridden and customized using Custom Block Templates
- The three single pages, `/cart`, `/checkout` and `/checkout/complete` can also be overridden
- The default output of single pages and blocks is designed for Bootstrap 3, but can be styled with any CSS/Framework
- When customizing HTML output, leave all classes in place that start with the prefix `store-`
- Receipt, order notification and new user emails can be customized by overriding the templates found in `/mail`
- Additional header and footer content can be added to customer receipt emails via the **Notifications and Receipts** section of the **Store / Settings** Dashboard page.
- Other components of the store, such as the cart overlay or shipping methods displayed in the checkout can be customized by overriding the files 

## Multilingual
When a Concrete CMS site has multiple languages set up, the cart, checkout and product pages are copied across language trees in the same way as other pages. Product names, descriptions and other text can then be translated via the **Store / Multilingual** Dashboard pages.
 




 


