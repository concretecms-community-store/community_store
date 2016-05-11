<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Product;

use Database;
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


    public static function getByID($cID)
    {
        $db = \Database::connection();
        $em = $db->getEntityManager();

        return $em->find(get_class(), $cID);
    }

    public static function getRelatedProducts(StoreProduct $product)
    {
        $db = \Database::connection();
        $em = $db->getEntityManager();

        return $em->getRepository(get_class())->findBy(array('pID' => $product->getID()));
    }

    public static function addRelatedProducts(array $products, StoreProduct $product)
    {
        //clear out existing locations
        self::removeRelatedProducts($product);
        //add new ones
        if (!empty($products['pRelatedProducts'])) {
            foreach ($products['pRelatedProducts'] as $pID) {
                if ($pID > 0) {
                    self::add($product, $pID);
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

    public static function add($product, $relatedProductID)
    {
        $relatedProduct = StoreProduct::getByID($relatedProductID);
        if ($relatedProduct) {
            $location = new self();
            $location->setProduct($product);
            $location->setRelatedProduct($relatedProduct);
            $location->save();
        }

        return $location;
    }

    public function __clone() {
        if ($this->id) {
            $this->setID(null);
            $this->setProductID(null);
        }
    }

    public function save()
    {
        $em = \Database::connection()->getEntityManager();
        $em->persist($this);
        $em->flush();
    }

    public function delete()
    {
        $em = \Database::connection()->getEntityManager();
        $em->remove($this);
        $em->flush();
    }
}