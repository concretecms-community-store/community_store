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

    public function getIndexedSearchTable()
    {
        return 'CommunityStoreProductSearchIndexAttributes';
    }

    public function getSearchIndexFieldDefinition()
    {
        return [
            'columns' => [
                [
                    'name' => 'pID',
                    'type' => 'integer',
                    'options' => ['unsigned' => true, 'default' => 0, 'notnull' => true],
                ],
            ],
            'primary' => ['pID'],
        ];
    }

    public static function getByHandle($handle)
    {
        $em = \ORM::entityManager();

        return $em->getRepository(self::class)->findOneBy(
            [
                'akHandle' => $handle,
            ]
        );
    }
}
