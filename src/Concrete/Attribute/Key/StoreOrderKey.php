<?php
namespace Concrete\Package\CommunityStore\Attribute\Key;

use Concrete\Core\Entity\Attribute\Key\Key;
use Doctrine\ORM\Mapping as ORM;
use AttributeSet;

/**
 * @ORM\Entity
 * @ORM\Table(name="CommunityStoreOrderAttributeKeys")
 */
class StoreOrderKey extends Key
{

    public function getAttributeKeyCategoryHandle()
    {
        return 'store_order';
    }

    public function getIndexedSearchTable()
    {
        return 'CommunityStoreOrderSearchIndexAttributes';
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

    public static function getAttributeListBySet($set, $user = null)
    {
        if (!$set instanceof AttributeSet) {
            $set = AttributeSet::getByHandle($set);
        }

        if ($user) {
            $uGroupIDs = array_keys($user->getUserGroups());
        }

        $akList = [];
//        foreach (parent::getList('store_order') as $ak) {
//            if (in_array($set, $ak->getAttributeSets())) {
//                $attributeGroups = $ak->getAttributeGroups();
//
//                if (is_null($user) || (empty($attributeGroups) || array_intersect($ak->getAttributeGroups(), $uGroupIDs))) {
//                    $akList[] = $ak;
//                }
//            }
//        }

        return $akList;
    }
}
