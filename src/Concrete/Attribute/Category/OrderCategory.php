<?php
namespace Concrete\Package\CommunityStore\Attribute\Category;

use Concrete\Core\Attribute\Category\SearchIndexer\StandardSearchIndexerInterface;
use Concrete\Core\Entity\Attribute\Key\Key;
use Concrete\Package\CommunityStore\Attribute\Key\StoreOrderKey;
use Concrete\Package\CommunityStore\Attribute\Value\StoreOrderValue;

class OrderCategory extends \Concrete\Core\Attribute\Category\AbstractStandardCategory
{
    public function createAttributeKey()
    {
        return new StoreOrderKey();
    }

    public function getIndexedSearchTable()
    {
        return 'CommunityStoreOrderSearchIndexAttributes';
    }

    public function getIndexedSearchPrimaryKeyValue($order)
    {
        return $order->getOrderID();
    }

    public function getSearchIndexFieldDefinition()
    {
        return array(
            'columns' => array(
                array(
                    'name' => 'oID',
                    'type' => 'integer',
                    'options' => array('unsigned' => true, 'default' => 0, 'notnull' => true),
                ),
            ),
            'primary' => array('oID'),
        );
    }

    public function getAttributeKeyRepository()
    {
        return $this->entityManager->getRepository(StoreOrderKey::class);
    }

    public function getAttributeValueRepository()
    {
        return $this->entityManager->getRepository(StoreOrderValue::class);
    }

    public function getAttributeValues($order)
    {
        $values = $this->getAttributeValueRepository()->findBy(array(
            'order' => $order,
        ));
        return $values;
    }

    public function getAttributeValue(Key $key, $order)
    {
        $r = $this->entityManager->getRepository(StoreOrderValue::class);
        $value = $r->findOneBy(array(
            'order' => $order,
            'attribute_key' => $key,
        ));

        return $value;
    }
}