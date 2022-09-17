<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Product;

use Doctrine\ORM\Mapping as ORM;
use Concrete\Core\Support\Facade\DatabaseORM as dbORM;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product;

/**
 * @ORM\Entity
 * @ORM\Table(name="CommunityStoreProductUserGroups")
 */
class ProductUserGroup
{
    /**
     * @ORM\Id @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $pugID;

    /**
     * @ORM\Column(type="integer")
     */
    protected $pID;

    /**
     * @ORM\ManyToOne(targetEntity="Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product",inversedBy="userGroups",cascade={"persist"})
     * @ORM\JoinColumn(name="pID", referencedColumnName="pID", onDelete="CASCADE")
     */
    protected $product;

    /**
     * @ORM\Column(type="integer")
     */
    protected $gID;

    public function setProduct($product)
    {
        return $this->product = $product;
    }

    private function setUserGroupID($gID)
    {
        $this->gID = $gID;
    }

    public function getProductID()
    {
        return $this->pID;
    }

    public function getUserGroupID()
    {
        return $this->gID;
    }

    public static function getByID($pgID)
    {
        $em = dbORM::entityManager();

        return $em->find('Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductGroup', $pgID);
    }

    public static function getUserGroupsForProduct(Product $product)
    {
        $em = dbORM::entityManager();

        return $em->getRepository('Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductUserGroup')->findBy(['pID' => $product->getID()]);
    }

    public static function getUserGroupIDsForProduct($product)
    {
        $userGroups = self::getUserGroupsForProduct($product);
        $groupIDs = [];
        foreach ($userGroups as $userGroup) {
            $groupIDs[] = $userGroup->getUserGroupID();
        }

        return $groupIDs;
    }

    public static function addUserGroupsForProduct(array $data, Product $product)
    {
        //clear out existing groups
        self::removeUserGroupsForProduct($product);

        //add new ones.
        if (!empty($data['pUserGroups'])) {
            foreach ($data['pUserGroups'] as $gID) {
                self::add($product, $gID);
            }
        }
    }

    public static function removeUserGroupsForProduct(Product $product)
    {
        $existingUserGroups = self::getUserGroupsForProduct($product);
        foreach ($existingUserGroups as $group) {
            $group->delete();
        }
    }

    public static function add($product, $gID)
    {
        $productUserGroup = new self();
        $productUserGroup->setProduct($product);
        $productUserGroup->setUserGroupID($gID);
        $productUserGroup->save();

        return $productUserGroup;
    }

    public function __clone()
    {
        if (isset($this->id) && $this->id) {
            $this->setID(null);
            $this->setProductID(null);
        }
    }

    public function save()
    {
        $em = dbORM::entityManager();
        $em->persist($this);
        $em->flush();
    }

    public function delete()
    {
        $em = dbORM::entityManager();
        $em->remove($this);
        $em->flush();
    }
}
