<?php
namespace Concrete\Package\CommunityStore\Attribute;

use Concrete\Core\Support\Facade\Facade;

class ProductKey extends Facade
{
    public static function getFacadeAccessor()
    {
        return 'Concrete\Package\CommunityStore\Attribute\Category\ProductCategory';
    }

    public static function getByHandle($handle)
    {
        return static::getFacadeRoot()->getAttributeKeyByHandle($handle);
    }
}
