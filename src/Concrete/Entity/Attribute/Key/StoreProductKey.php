<?php
namespace Concrete\Package\CommunityStore\Entity\Attribute\Key;

use Concrete\Core\Entity\Attribute\Key\Key;
use Doctrine\ORM\Mapping as ORM;
use Concrete\Package\CommunityStore\Repository\StoreProductKeyRepository;

/**
 * @ORM\Entity(repositoryClass=StoreProductKeyRepository::class)
 * @ORM\Table(name="CommunityStoreProductAttributeKeys")
 * @ORM\DiscriminatorMap({ "storeproductkey" = "StoreProductKey"})
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

    public static function getByHandle($handle)
    {
        $em = \ORM::entityManager();
        $type = $em->getRepository(self::class)->findOneBy(
            array('akHandle' => $handle,
            ));

        return $type;
    }
}
