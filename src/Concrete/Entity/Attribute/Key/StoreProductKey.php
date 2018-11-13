<?php
namespace Concrete\Package\CommunityStore\Entity\Attribute\Key;

use Concrete\Core\Entity\Attribute\Key\Key;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="CommunityStoreProductAttributeKeys")
 */
class StoreProductKey extends Key
{

    public function getAttributeKeyCategoryHandle()
    {
        return 'store_product';
    }
}