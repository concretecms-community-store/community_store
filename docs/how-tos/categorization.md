# Product Categorization

There are three key ways to categorize products within Community Store

- Using Product Groups
- Categorised under pages
- Using Manufacturers

## Product Groups

Product Groups are a broad way to categorize products, either for reference within the Dashboard, or, for display to customers.

Product List blocks can be filtered to show products from one or more product groups, or to exclude products from product groups.

An example of product group could be 'Great Gift Ideas', where you selectively pick products from your store for this group.
You could then place a Product List block anywhere on your site filtering to this group.

## Categorised under pages

If your site presents different categories of products through corresponding pages within your sitemap, this method of categorization allows for grouping products _without_ having to create a Product Group for each category page.

For example, your site might sell games and have the following site structure:

- Home
- Shop
    - Games
        - Card Games
        - Board Games
        - Table-top Games
        - Puzzles
    - Video Games
- About, Contact, etc

To manage such a site, you would likely have a page type, 'Product Category', configured so you can easily add a new product category page.
On this Product Category page type, you would have a default Product List block, configured with **List Products** set to **Under current page**.
 
Then when adding new products, to have them display on their category pages all that is required is to select one or more of the category pages from the sitemap using the **Categorized under page** selector on a product.

Larger catalogs are likely to be easier to manage if there is a specific page type created to categorise products, but this is not a requirement - you can categorise products under any page within your sitemap. 

::: tip Showing products from all nested pages
In the above example you may wish to show all games products on the top level 'Games' page. 
To do this, you would add a Product List block and configure the **List Products** option to be **Under current page and child pages**.
:::

## Product Types

A product can be marked as being of a specified Product Type, allowing a more specific set of attributes to be associated with that product.

## Manufacturers

Manufacturers are a grouping of products specifically intended to represent the brand or manufacturer.

Manufacturers are managed through the **Store / Products / Manufacturers** Dashboard page, and can be selected against each product.
Each manufacturer added can be associated with a page within your site, and when displayed against a product will link through to that page.
 
::: tip Terminology
The term 'manufacturer' is used only to suggest who or where a product ordinates from.

Another example of usage could be on a website that sells artwork, treating the manufacturer as the artists of each piece.
Using this way of categorizing products, when an individual product is viewed the artist for the product can be displayed, presenting a link to view their artist profile and other works.
This setup would only require a modest update of the product block templates to change the word from Manufacturer to Artist.
:::

