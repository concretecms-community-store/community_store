<?php
namespace Concrete\Package\CommunityStore\Attribute;

use Concrete\Core\Support\Facade\Facade;

class OrderKey extends Facade
{
    public static function getFacadeAccessor()
    {
        return 'Concrete\Package\CommunityStore\Attribute\Category\OrderCategory';
    }

    public static function getByHandle($handle)
    {
        return static::getFacadeRoot()->getAttributeKeyByHandle($handle);
    }
}
