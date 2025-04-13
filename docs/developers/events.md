# Events
 
The Community Store also includes 'Events' - these are hooks that [can be subscribed to with custom code](https://documentation.concretecms.org/developers/framework/application-events/hooking-application-events), allowing additional processing to take place.

An example might be that you wish to communicate with a separate warehouse database/API to determine current stock levels or prices. By subscribing to the `on_community_store_product_add` and `on_community_store_product_update` events, you could create code that takes the product SKU, looks it up in the external database and then updates the product accordingly. 

Another usage example is for when you need to do some additional processing when an order is placed. The example might be that you may need to send an email to a supplier when a particular product is purchased. Events allow such functionality to be added through additional Concrete CMS add-ons.

[An example package showing how to respond to Community Store events is available on github](https://github.com/concretecms-community-store/community_store_event_example).

## Order events
- `on_community_store_order` - when a new order is placed
- `on_community_store_order_status_update` - when an order changes status (but not initially)
- `on_community_store_payment_complete` - when an order is marked as paid (via a gateway or manually)

## Product events
- `on_community_store_product_add` - when a new product is added
- `on_community_store_product_update`- when an existing product is updated
- `on_community_store_product_delete` - when a product is deleted
- `on_community_store_product_duplicate` - when a product is duplicated

## Cart events
- `on_community_store_cart_action` - any time an action with a cart is performed
- `on_community_store_cart_pre_add`
- `on_community_store_cart_post_add`
- `on_community_store_cart_pre_update`
- `on_community_store_cart_post_update`
- `on_community_store_cart_pre_remove`
- `on_community_store_cart_post_remove`
- `on_community_store_cart_pre_clear`
- `on_community_store_cart_post_clear`

When these events are fired, the event object passed includes the order or product it relates to. In the case of when a product is updated, or duplicated, the event object includes both the before and after products. 

The `on_community_store_order_status_update` event also includes the previous status of the order.

The community_store_event_examples package includes sample code as to how to subscribe to and use the data from all the events above - [https://github.com/concretecms-community-store/community_store_event_example](https://github.com/concretecms-community-store/community_store_event_example)

### Slack Integration
To hook into events and send notifications to Slack, try the package: [https://github.com/a3020/slife_community_store](https://github.com/a3020/slife_community_store) 

