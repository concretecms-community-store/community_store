<?php
namespace Concrete\Package\CommunityStore\Attribute\Category;

use Concrete\Core\Entity\Attribute\Key\Key;
use Concrete\Package\CommunityStore\Entity\Attribute\Key\StoreOrderKey;
use Concrete\Package\CommunityStore\Entity\Attribute\Value\StoreOrderValue;
use Concrete\Core\Entity\Attribute\Type;
use Symfony\Component\HttpFoundation\Request;
use Concrete\Core\Attribute\Category\AbstractStandardCategory;

class StoreOrderCategory extends AbstractStandardCategory
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

    protected function saveFromRequest(Key $key, Request $request)
    {
        $key->setRequired($request->request->get('required') ? '1' : '0');
        $key->setAttributeUserGroups($request->request->get('groups'));
        $this->entityManager->persist($key);
        $this->entityManager->flush();
        return $key;
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
}
