<?php
namespace Concrete\Package\CommunityStore\Attribute\Category;

use Concrete\Core\Attribute\Category\SearchIndexer\StandardSearchIndexerInterface;
use Concrete\Core\Entity\Attribute\Key\Key;
use Concrete\Package\CommunityStore\Attribute\Key\StoreProductKey;
use Concrete\Package\CommunityStore\Attribute\Value\ProductValue;

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
        return array(
            'columns' => array(
                array(
                    'name' => 'pID',
                    'type' => 'integer',
                    'options' => array('unsigned' => true, 'default' => 0, 'notnull' => true),
                ),
            ),
            'primary' => array('pID'),
        );
    }

    public function getAttributeKeyRepository()
    {
        return $this->entityManager->getRepository(StoreProductKey::class);
    }

    public function getAttributeValueRepository()
    {
        return $this->entityManager->getRepository(ProductValue::class);
    }

    public function getAttributeValues($product)
    {
        $values = $this->getAttributeValueRepository()->findBy(array(
            'product' => $product,
        ));
        return $values;
    }

    public function getAttributeValue(Key $key, $product)
    {
        $r = $this->entityManager->getRepository(ProductValue::class);
        $value = $r->findOneBy(array(
            'product' => $product,
            'attribute_key' => $key,
        ));

        return $value;
    }
}