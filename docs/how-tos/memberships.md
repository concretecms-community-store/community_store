# Memberships

Memberships and protected access to content is managed through concrete5's users, user groups and permission systems.

Community Store allows products to be flagged to create a user account on purchase (if not already logged in), adding the user to one or more user groups.
Such configuration is performed on the 'Downloads and User Groups' tab when editing products.

For example, you may wish to set up a website to sell access to a protected set of pages and resources. To do this, you would configure the following in your site:

- Create a new User Group, through the **Members / User Groups** Dashboard page, e.g. '12 Months membership'
- Configure the new user group to **Automatically remove users from this group** after 365 days, removing the user from the group on expiration
- Create a membership product, e.g. '12 Month Access to Resources', and configure the product: 
    - set **Product is Shippable** to no
    - set **Offer quantity selection** to no
    - set the **Stock Level** to unlimited
    - check the **Create user account on purchase** option
    - select the newly created user group within the list **On purchase add user to user groups**
    - you may also wish to set the **Send customer directly to checkout when added to cart** option to streamline the checkout process
    - finally, you may with to set the **Prevent this item from being in the cart with other items** option if you are offering multiple levels of membership and do not wish users to buy multiple at once
- Set permissions on your site at the page level, allowing only the membership user group to view those pages

With the configuration above, when a customer places and pays for an order including the membership product, they will receive an additional email with new login credentials and a link to log into the site.
If supported by the payment gateway (i.e. it is not processed externally), the user will be logged in automatically after completing the transaction.
As the new user has been placed in a user group that automatically expires, they will be limited to 365 days worth of access to the protected resources.

::: tip Advanced Permissions
Concrete5's [advanced permissions system](https://documentation.concrete5.org/user-guide/editors-reference/dashboard/system-and-maintenance/permissions-and-access/advanced-permissions) is well suited to this setup and gives a high level of control over content.
For example, using advanced permissions individual blocks of content can be placed on the site, only visible to users within selected user groups.
A content block directly highlighting and linking the newly accessible content could be placed on the page members log in to, being configured to only be visible to users within the membership group.
:::


       
    
