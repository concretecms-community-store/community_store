<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductOption;

use Database;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product as StoreProduct;

/**
 * @Entity
 * @Table(name="CommunityStoreProductOptionItems")
 */
class ProductOptionItem
{
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue
     */
    protected $poiID;

    /**
     * @Column(type="integer")
     */
    protected $pID;

    /**
     * @Column(type="integer")
     */
    protected $pogID;

    /**
     * @Column(type="string")
     */
    protected $poiName;

    /**
     * @Column(type="integer")
     */
    protected $poiSort;

    /**
     * @Column(type="boolean")
     */
    protected $poiHidden = 0;

    /** @OneToMany(targetEntity="Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductVariation\ProductVariationOptionItem", mappedBy="option", cascade={"persist", "remove"})
     * @JoinColumn(name="poiID", referencedColumnName="poiID", onDelete="CASCADE")
     */
    private $variationoptionitems;

    private function setProductID($pID)
    {
        $this->pID = $pID;
    }
    private function setProductOptionGroupID($id)
    {
        $this->pogID = $id;
    }
    private function setProductOptionItemName($name)
    {
        $this->poiName = $name;
    }
    private function setSort($sort)
    {
        $this->poiSort = $sort;
    }
    private function setName($name)
    {
        $this->poiName = $name;
    }
    private function setHidden($hidden)
    {
        $this->poiHidden = (bool) $hidden;
    }

    public function getID()
    {
        return $this->poiID;
    }
    public function getProductID()
    {
        return $this->pID;
    }
    public function getProductOptionGroupID()
    {
        return $this->pogID;
    }
    public function getName()
    {
        return $this->poiName;
    }
    public function getSort()
    {
        return $this->poiSort;
    }
    public function getHidden()
    {
        return $this->poiHidden;
    }
    public function isHidden()
    {
        return (bool) $this->poiHidden;
    }

    public static function getByID($id)
    {
        $db = Database::connection();
        $em = $db->getEntityManager();

        return $em->find('Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductOption\ProductOptionItem', $id);
    }

    public static function getOptionItemsForProduct(StoreProduct $product, $onlyvisible = false)
    {
        $db = Database::connection();
        $em = $db->getEntityManager();
        if ($onlyvisible) {
            return $em->getRepository('Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductOption\ProductOptionItem')->findBy(array('pID' => $product->getProductID(), 'poiHidden' => '0'), array('poiSort' => 'asc'));
        } else {
            return $em->getRepository('Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductOption\ProductOptionItem')->findBy(array('pID' => $product->getProductID()), array('poiSort' => 'asc'));
        }
    }

    public static function getOptionItemsForProductOptionGroup(ProductOptionGroup $pog)
    {
        $db = Database::connection();
        $em = $db->getEntityManager();

        return $em->getRepository('Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductOption\ProductOptionItem')->findBy(array('pogID' => $pog->getID()), array('poiSort' => 'asc'));
    }

    public static function removeOptionItemsForProduct(StoreProduct $product, $excluding = array())
    {
        if (!is_array($excluding)) {
            $excluding = array();
        }

        //clear out existing product option items
        $existingOptionItems = self::getOptionItemsForProduct($product);
        foreach ($existingOptionItems as $optionItem) {
            if (!in_array($optionItem->getID(), $excluding)) {
                $optionItem->delete();
            }
        }
    }

    public static function add(StoreProduct $product, $pogID, $name, $sort, $hidden = false)
    {
        $productOptionItem = new self();
        $pID = $product->getProductID();
        $productOptionItem->setProductID($pID);
        $productOptionItem->setProductOptionGroupID($pogID);
        $productOptionItem->setProductOptionItemName($name);
        $productOptionItem->setSort($sort);
        $productOptionItem->setHidden($hidden);
        $productOptionItem->save();

        return $productOptionItem;
    }

    public function update(StoreProduct $product, $name, $sort, $hidden = false)
    {
        $pID = $product->getProductID();
        $this->setProductID($pID);
        $this->setName($name);
        $this->setSort($sort);
        $this->setHidden($hidden);
        $this->save();

        return $this;
    }

    public function save()
    {
        $em = Database::connection()->getEntityManager();
        $em->persist($this);
        $em->flush();
    }

    public function delete()
    {
        $em = Database::connection()->getEntityManager();
        $em->remove($this);
        $em->flush();
    }
}
