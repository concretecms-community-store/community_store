<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Group;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * @Entity
 * @Table(name="CommunityStoreGroups")
 */
class Group
{
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue
     */
    protected $gID;

    /**
     * @Column(type="string")
     */
    protected $groupName;

    private function setGroupName($groupName)
    {
        $this->groupName = $groupName;
    }

    public function getGroupName()
    {
        return $this->groupName;
    }

    public function getID()
    {
        return $this->gID;
    }

    public function getGroupID()
    {
        return $this->gID;
    }

    /**
     * @OneToMany(targetEntity="Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductGroup", mappedBy="group",cascade={"persist"}))
     */
    protected $products;

    public function getProducts()
    {
        return $this->products;
    }

    public function __construct()
    {
        $this->products = new ArrayCollection();
    }

    public static function getByID($gID)
    {
        $em = \ORM::entityManager();

        return $em->find(get_called_class(), $gID);
    }

    public static function getByName($gName)
    {
        $em = \ORM::entityManager();

        return $em->getRepository(get_class())->findOneBy(['groupName' => $gName]);
    }

    public static function add($groupName)
    {
        $productGroup = new self();
        $productGroup->setGroupName($groupName);
        $productGroup->save();

        return $productGroup;
    }

    public function update($groupName)
    {
        $this->setGroupName($groupName);
        $this->save();

        return $this;
    }

    public function save()
    {
        $em = \ORM::entityManager();
        $em->persist($this);
        $em->flush();
    }

    public function delete()
    {
        $em = \ORM::entityManager();
        $em->remove($this);
        $em->flush();
    }
}
