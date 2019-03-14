<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Product;

use File;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product as StoreProduct;

/**
 * @Entity
 * @Table(name="CommunityStoreProductImages")
 */
class ProductImage
{
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue
     */
    protected $piID;

    /**
     * @Column(type="integer")
     */
    protected $pID;

    /**
     * @ManyToOne(targetEntity="Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product",inversedBy="images",cascade={"persist"})
     * @JoinColumn(name="pID", referencedColumnName="pID", onDelete="CASCADE")
     */
    protected $product;

    /**
     * @Column(type="integer")
     */
    protected $pifID;

    /**
     * @Column(type="integer")
     */
    protected $piSort;

    private function setProductID($pID)
    {
        $this->pID = $pID;
    }

    public function setProduct($product)
    {
        return $this->product = $product;
    }

    private function setFileID($pifID)
    {
        $this->pifID = $pifID;
    }

    private function setSort($piSort)
    {
        $this->piSort = $piSort;
    }

    public function getID()
    {
        return $this->piID;
    }

    public function getProductID()
    {
        return $this->pID;
    }

    public function getFileID()
    {
        return $this->pifID;
    }

    public function getSort()
    {
        return $this->piSort;
    }

    public static function getByID($piID)
    {
        $em = \ORM::entityManager();

        return $em->find(get_class(), $piID);
    }

    public static function getImagesForProduct(StoreProduct $product)
    {
        $em = \ORM::entityManager();

        return $em->getRepository(get_class())->findBy(['pID' => $product->getID()]);
    }

    public static function getImageObjectsForProduct(StoreProduct $product)
    {
        $images = self::getImagesForProduct($product);
        $imageObjects = [];
        foreach ($images as $img) {
            $imageObjects[] = File::getByID($img->getFileID());
        }

        return $imageObjects;
    }

    public static function addImagesForProduct(array $images, StoreProduct $product)
    {
        self::removeImagesForProduct($product);

        //add new ones.
        if (is_array($images['pifID'])) {
            for ($i = 0; $i < count($images['pifID']); ++$i) {
                self::add($product, $images['pifID'][$i], $i);
            }
        }
    }

    public static function removeImagesForProduct(StoreProduct $product)
    {
        $existingImages = self::getImagesForProduct($product);
        foreach ($existingImages as $img) {
            $img->delete();
        }
    }

    public static function add($product, $pifID, $piSort)
    {
        $productImage = new self();
        $productImage->setProduct($product);
        $productImage->setFileID($pifID);
        $productImage->setSort($piSort);
        $productImage->save();

        return $productImage;
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
