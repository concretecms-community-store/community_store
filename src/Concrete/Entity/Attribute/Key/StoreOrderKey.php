<?php
namespace Concrete\Package\CommunityStore\Entity\Attribute\Key;

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
