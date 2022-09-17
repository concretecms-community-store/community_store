<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Product;

use Doctrine\ORM\Mapping as ORM;
use Concrete\Core\Support\Facade\DatabaseORM as dbORM;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product;

/**
 * @ORM\Entity
 * @ORM\Table(name="CommunityStoreProductRelated")
 */
class ProductRelated
{
    /**
     * @ORM\Id @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ORM\Column(type="integer")
     */
    protected $pID;

    /**
     * @ORM\ManyToOne(targetEntity="Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product",inversedBy="related",cascade={"persist"})
     * @ORM\JoinColumn(name="pID", referencedColumnName="pID", onDelete="CASCADE")
     */
    protected $product;

    /**
     * @ORM\Column(type="integer")
     */
    protected $relatedPID;

    /**
     * @ORM\Column(type="integer")
     */
    protected $relatedSort;

    /**
     * @ORM\ManyToOne(targetEntity="Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product",cascade={"persist"})
     * @ORM\JoinColumn(name="relatedPID", referencedColumnName="pID", onDelete="CASCADE")
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
        $em = dbORM::entityManager();

        return $em->find(get_class(), $cID);
    }

    public static function getRelatedProducts(Product $product)
    {
        $em = dbORM::entityManager();

        return $em->getRepository(get_class())->findBy(['pID' => $product->getID()]);
    }

    public static function addRelatedProducts(array $products, Product $product)
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

    public static function removeRelatedProducts(Product $product)
    {
        $existingRelations = self::getRelatedProducts($product);
        foreach ($existingRelations as $relation) {
            $relation->delete();
        }
    }

    public static function add($product, $relatedProductID, $sort)
    {
        $relatedProduct = Product::getByID($relatedProductID);
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
