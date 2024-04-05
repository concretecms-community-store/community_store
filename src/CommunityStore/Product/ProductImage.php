<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Product;

use Concrete\Core\File\File;
use Doctrine\ORM\Mapping as ORM;
use Concrete\Core\Support\Facade\DatabaseORM as dbORM;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product;

/**
 * @ORM\Entity
 * @ORM\Table(name="CommunityStoreProductImages")
 */
class ProductImage
{
    /**
     * @ORM\Id @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $piID;

    /**
     * @ORM\Column(type="integer")
     */
    protected $pID;

    /**
     * @ORM\ManyToOne(targetEntity="Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product",inversedBy="images",cascade={"persist"})
     * @ORM\JoinColumn(name="pID", referencedColumnName="pID", onDelete="CASCADE")
     */
    protected $product;

    /**
     * @ORM\Column(type="integer")
     */
    protected $pifID;

    /**
     * @ORM\Column(type="integer")
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
        $em = dbORM::entityManager();

        return $em->find(get_class(), $piID);
    }

    public static function getImagesForProduct(Product $product)
    {
        $em = dbORM::entityManager();

        return $em->getRepository(get_class())->findBy(['pID' => $product->getID()], ['piSort' => 'ASC']);
    }

    /**
     * @return \Concrete\Core\Entity\File\File[]
     */
    public static function getImageObjectsForProduct(Product $product)
    {
        $images = self::getImagesForProduct($product);
        $imageObjects = [];
        foreach ($images as $img) {
            $file = File::getByID($img->getFileID());
            if ($file !== null) {
                $imageObjects[] = $file;
            }
        }

        return $imageObjects;
    }

    public static function addImagesForProduct(array $images, Product $product)
    {
        self::removeImagesForProduct($product);

        //add new ones.
        if (isset($images['pifID']) && is_array($images['pifID'])) {
            for ($i = 0; $i < count($images['pifID']); ++$i) {
                self::add($product, $images['pifID'][$i], $i);
            }
        }
    }

    public static function removeImagesForProduct(Product $product)
    {
        $existingImages = self::getImagesForProduct($product);
        foreach ($existingImages as $img) {
            $img->delete();
        }
        $product->getImages()->clear();
    }

    public static function add($product, $pifID, $piSort)
    {
        $productImage = new self();
        $productImage->setProduct($product);
        $productImage->setFileID($pifID);
        $productImage->setSort($piSort);
        $productImage->save();
        $product->getImages()->add($productImage);

        return $productImage;
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
