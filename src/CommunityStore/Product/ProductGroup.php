<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Product;

use Doctrine\ORM\Mapping as ORM;
use Concrete\Core\Support\Facade\DatabaseORM as dbORM;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product;
use Concrete\Package\CommunityStore\Src\CommunityStore\Group\Group as StoreGroup;

/**
 * @ORM\Entity
 * @ORM\Table(name="CommunityStoreProductGroups")
 */
class ProductGroup
{
    /**
     * @ORM\Id @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $pgID;

    /**
     * @ORM\Column(type="integer")
     */
    protected $pID;

    /**
     * @ORM\ManyToOne(targetEntity="Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product",inversedBy="groups",cascade={"persist"})
     * @ORM\JoinColumn(name="pID", referencedColumnName="pID", onDelete="CASCADE")
     */
    protected $product;

    /**
     * @ORM\Column(type="integer")
     */
    protected $gID;

    /**
     * @ORM\ManyToOne(targetEntity="Concrete\Package\CommunityStore\Src\CommunityStore\Group\Group", inversedBy="products")
     * @ORM\JoinColumn(name="gID", referencedColumnName="gID", onDelete="CASCADE")
     */
    protected $group;

    /**
     * @ORM\Column(type="integer")
     */
    protected $sortOrder;

    public function getGroup()
    {
        return $this->group;
    }

    public function setGroup($group)
    {
        $this->group = $group;
    }

    private function setProductID($pID)
    {
        $this->pID = $pID;
    }

    private function setGroupID($gID)
    {
        $this->gID = $gID;
    }

    public function setSortOrder($sortOrder)
    {
        $this->sortOrder = $sortOrder;
    }

    public function getSortOrder()
    {
        return $this->sortOrder;
    }

    public function getProductID()
    {
        return $this->pID;
    }

    public function setProduct($product)
    {
        return $this->product = $product;
    }

    public function getProduct()
    {
        return $this->product;
    }

    public function getGroupID()
    {
        return $this->gID;
    }

    public function getID()
    {
        return $this->id;
    }

    public function setID($id)
    {
        $this->id = $id;
    }

    public static function getByID($pgID)
    {
        $em = dbORM::entityManager();

        return $em->find(get_class(), $pgID);
    }

    public static function getGroupsForProduct(Product $product)
    {
        $em = dbORM::entityManager();
        $productGroups = $em->getRepository(get_class())->findBy(['pID' => $product->getID()]);
        $groups = [];
        if (count($productGroups)) {
            foreach ($productGroups as $productGroup) {
                $groups[] = $productGroup->getGroup();
            }
        }

        return $groups;
    }

    public static function isProductInGroup(Product $product, StoreGroup $group)
    {
        $em = dbORM::entityManager();
        $gID = $group->getGroupID();

        $productGroup = $em->getRepository(get_class())->findBy(['pID' => $product->getID(), 'gID' => $gID]);
        if (count($productGroup)) {
            return true;
        }

        return false;
    }

    public static function getGroupIDsForProduct(Product $product)
    {
        $groups = self::getGroupsForProduct($product);
        $ids = [];
        if (count($groups)) {
            foreach ($groups as $g) {
                $ids[] = $g->getGroupID();
            }
        }

        return $ids;
    }

    public static function addGroupsForProduct(array $data, Product $product)
    {
        $existingGroupIDs = [];

        $existingGroups = $product->getGroups();

        if (is_array($data['pProductGroups'])){
            foreach ($existingGroups as $existingGroup) {
                if (!in_array($existingGroup->getGroupID(), $data['pProductGroups'])) {
                    // no longer in list, so remove
                    $existingGroup->delete();
                } else {
                    $existingGroupIDs[] = $existingGroup->getGroupID();
                }
            }
        } else {
            self::removeGroupsForProduct($product);
        }

        //add new ones.
        if (!empty($data['pProductGroups'])) {
            foreach ($data['pProductGroups'] as $gID) {
                if ($gID > 0 && !in_array($gID, $existingGroupIDs)) {
                    self::add($product, $gID);
                }
            }
        }

    }

    public static function removeGroupsForProduct(Product $product)
    {
        $em = dbORM::entityManager();
        $groups = $em->getRepository(get_class())->findBy(['pID' => $product->getID()]);
        foreach ($groups as $productGroup) {
            $productGroup->delete();
        }
    }

    public static function removeProductsForGroup(StoreGroup $group)
    {
        $em = dbORM::entityManager();
        $groups = $em->getRepository(get_class())->findBy(['gID' => $group->getID()]);
        foreach ($groups as $productGroup) {
            $productGroup->delete();
        }
    }

    public static function add($product, $gID, $sortOrder = 0)
    {
        if (!is_object($product)) {
            $product = Product::getByID($product);
        }

        if ($product) {
            $group = StoreGroup::getByID($gID);
            if ($group) {
                $productGroup = new self();
                $productGroup->setProduct($product);
                $productGroup->setGroup($group);
                $productGroup->setSortOrder($sortOrder);
                $productGroup->save();
            }

            return $productGroup;
        }

        return false;
    }

    public function __clone()
    {
        if ($this->pgID) {
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
