<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Product;

use Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product as StoreProduct;

/**
 * @Entity
 * @Table(name="CommunityStoreProductRelated")
 */
class ProductRelated
{
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;

    /**
     * @Column(type="integer")
     */
    protected $pID;

    /**
     * @ManyToOne(targetEntity="Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product",inversedBy="related",cascade={"persist"})
     * @JoinColumn(name="pID", referencedColumnName="pID", onDelete="CASCADE")
     */
    protected $product;

    /**
     * @Column(type="integer")
     */
    protected $relatedPID;

    /**
     * @Column(type="integer")
     */
    protected $relatedSort;

    /**
     * @ManyToOne(targetEntity="Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product",cascade={"persist"})
     * @JoinColumn(name="relatedPID", referencedColumnName="pID", onDelete="CASCADE")
     */
    protected $relatedProduct;

    public function getID()
    {
        return $this->id;
    }

    public function setID($id)
    {
        $this->id = $id;
    }

    public function getProductID()
    {
        return $this->pID;
    }

    public function setProduct($product)
    {
        return $this->product = $product;
    }

    public function getRelatedProductID()
    {
        return $this->relatedPID;
    }

    public function getRelatedProduct()
    {
        return $this->relatedProduct;
    }

    public function setRelatedProduct($product)
    {
        return $this->relatedProduct = $product;
    }

    public function getSort()
    {
        return $this->relatedSort;
    }

    public function setSort($relatedSort)
    {
        $this->relatedSort = $relatedSort;
    }

    public static function getByID($cID)
    {
        $em = \ORM::entityManager();

        return $em->find(get_class(), $cID);
    }

    public static function getRelatedProducts(StoreProduct $product)
    {
        $em = \ORM::entityManager();

        return $em->getRepository(get_class())->findBy(['pID' => $product->getID()]);
    }

    public static function addRelatedProducts(array $products, StoreProduct $product)
    {
        //clear out existing locations
        self::removeRelatedProducts($product);
        //add new ones
        if (!empty($products['pRelatedProducts'])) {
            $count = 0;
            foreach ($products['pRelatedProducts'] as $pID) {
                if ($pID > 0) {
                    self::add($product, $pID, $count);
                    ++$count;
                }
            }
        }
    }

    public static function removeRelatedProducts(StoreProduct $product)
    {
        $existingRelations = self::getRelatedProducts($product);
        foreach ($existingRelations as $relation) {
            $relation->delete();
        }
    }

    public static function add($product, $relatedProductID, $sort)
    {
        $relatedProduct = StoreProduct::getByID($relatedProductID);
        if ($relatedProduct) {
            $relation = new self();
            $relation->setProduct($product);
            $relation->setRelatedProduct($relatedProduct);
            $relation->setSort($sort);
            $relation->save();
        }

        return $relation;
    }

    public function __clone()
    {
        if ($this->id) {
            $this->setID(null);
            $this->setProductID(null);
        }
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
