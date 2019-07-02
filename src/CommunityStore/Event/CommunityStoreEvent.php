<?php

namespace Concrete\Package\CommunityStore\Src\CommunityStore\Event;

use Symfony\Component\EventDispatcher\GenericEvent;

abstract class CommunityStoreEvent extends GenericEvent {
    const CART_PRE_ADD = 'basket.pre.add';
    const CART_POST_ADD = 'basket.post.add';
    const CART_PRE_UPDATE = 'basket.pre.update';
    const CART_POST_UPDATE = 'basket.post.update';
    const CART_PRE_REMOVE = 'basket.pre.remove';
    const CART_POST_REMOVE = 'basket.post.remove';
    const CART_PRE_CLEAR = 'basket.pre.clear';
    const CART_POST_CLEAR = 'basket.post.clear';

    const CART_ACTION = 'basket.action';
}