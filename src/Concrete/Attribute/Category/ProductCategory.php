<?php

namespace Concrete\Package\CommunityStore\Attribute\Category;

use Concrete\Core\Entity\Attribute\Key\Key;
use Concrete\Package\CommunityStore\Entity\Attribute\Key\StoreProductKey;
use Concrete\Package\CommunityStore\Entity\Attribute\Value\StoreProductValue;

class ProductCategory extends \Concrete\Core\Attribute\Category\AbstractStandardCategory
{
    public function createAttributeKey()
    {
        return new StoreProductKey();
    }

    public function getIndexedSearchTable()
    {
        return 'CommunityStoreProductSearchIndexAttributes';
    }

    public function getIndexedSearchPrimaryKeyValue($product)
    {
        return $product->getID();
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

    public function getAttributeKeyRepository()
    {
        return $this->entityManager->getRepository(StoreProductKey::class);
    }

    public function getAttributeValueRepository()
    {
        return $this->entityManager->getRepository(StoreProductValue::class);
    }

    public function getAttributeValues($product)
    {
        return $this->getAttributeValueRepository()->findBy([
            'product' => $product,
        ]);
    }

    public function getAttributeValue(Key $key, $product)
    {
        $r = $this->entityManager->getRepository(StoreProductValue::class);

        return $r->findOneBy([
            'product' => $product,
            'attribute_key' => $key,
        ]);
    }
}
