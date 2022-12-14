<?php
namespace Concrete\Package\CommunityStore\Entity\Attribute\Key;

use Concrete\Core\Entity\Attribute\Key\Key;
use Doctrine\ORM\Mapping as ORM;
use Concrete\Core\Attribute\Set as AttributeSet;
use Concrete\Package\CommunityStore\Repository\StoreOrderKeyRepository;

/**
 * @ORM\Entity(repositoryClass=StoreOrderKeyRepository::class)
 * @ORM\Table(name="CommunityStoreOrderAttributeKeys")
 */
class StoreOrderKey extends Key
{

    /**
     * @ORM\Column(type="string",nullable=true)
     */
    protected $akUserGroups;

    /**
     * @ORM\Column(type="boolean",nullable=true)
     */
    protected $akRequired;

    public function getAttributeKeyCategoryHandle(): string
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
        $app = \Concrete\Core\Support\Facade\Application::getFacadeApplication();
        $orderCategory = $app->make('Concrete\Package\CommunityStore\Attribute\Category\OrderCategory');
        $attlist = $orderCategory->getList();


        foreach ($attlist as $ak) {
            if (in_array($set, $ak->getAttributeSets())) {
                $attributeGroups = $ak->getAttributeUserGroups();

                if (is_null($user) || (empty($attributeGroups) || array_intersect($attributeGroups, $uGroupIDs))) {
                    $akList[] = $ak;
                }
            }
        }

        return $akList;
    }

    public function isRequired()
    {
        return (bool)$this->akRequired;
    }

    public function setRequired($required)
    {
        $this->akRequired = $required;
    }

    public function getAttributeUserGroups()
    {
        $groupids = trim($this->akUserGroups);
        if (!$groupids) {
            return array();
        }

        return explode(',', $this->akUserGroups);
    }

    public function setAttributeUserGroups($groupids)
    {
        if ($groupids && is_array($groupids))  {
            $this->akUserGroups = implode(',', $groupids);
        } else {
            $this->akUserGroups = '';
        }
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
