<?php

namespace Concrete\Package\CommunityStore\Attribute\Category;

use Concrete\Core\Entity\Attribute\Key\Key;
use Concrete\Core\Entity\Attribute\Type;
use Concrete\Package\CommunityStore\Entity\Attribute\Key\StoreOrderKey;
use Concrete\Package\CommunityStore\Entity\Attribute\Value\StoreOrderValue;
use Symfony\Component\HttpFoundation\Request;

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
        return [
            'columns' => [
                [
                    'name' => 'oID',
                    'type' => 'integer',
                    'options' => ['unsigned' => true, 'default' => 0, 'notnull' => true],
                ],
            ],
            'primary' => ['oID'],
        ];
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
        return $this->getAttributeValueRepository()->findBy([
            'order' => $order,
        ]);
    }

    public function getAttributeValue(Key $key, $order)
    {
        $r = $this->entityManager->getRepository(StoreOrderValue::class);

        return $r->findOneBy([
            'order' => $order,
            'attribute_key' => $key,
        ]);
    }

    public function addFromRequest(Type $type, Request $request)
    {
        $key = parent::addFromRequest($type, $request);

        return $this->saveFromRequest($key, $request);
    }

    public function updateFromRequest(Key $key, Request $request)
    {
        $key = parent::updateFromRequest($key, $request);

        return $this->saveFromRequest($key, $request);
    }

    protected function saveFromRequest(Key $key, Request $request)
    {
        $key->setRequired($request->request->get('required') ? '1' : '0');
        $key->setAttributeUserGroups($request->request->get('groups'));
        $this->entityManager->persist($key);
        $this->entityManager->flush();

        return $key;
    }
}
