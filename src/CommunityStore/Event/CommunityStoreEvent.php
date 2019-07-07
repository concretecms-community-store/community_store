<?php

namespace Concrete\Package\CommunityStore\Src\CommunityStore\Event;

use Symfony\Component\EventDispatcher\GenericEvent;

abstract class CommunityStoreEvent extends GenericEvent {
    const CART_PRE_ADD = 'on_community_store_cart_pre_add';
    const CART_POST_ADD = 'on_community_store_cart_post_add';
    const CART_PRE_UPDATE = 'on_community_store_cart_pre_update';
    const CART_POST_UPDATE = 'on_community_store_cart_post_update';
    const CART_PRE_REMOVE = 'on_community_store_cart_pre_remove';
    const CART_POST_REMOVE = 'on_community_store_cart_post_remove';
    const CART_PRE_CLEAR = 'on_community_store_cart_pre_clear';
    const CART_POST_CLEAR = 'on_community_store_cart_post_clear';

    const CART_ACTION = 'on_community_store_cart_action';
}