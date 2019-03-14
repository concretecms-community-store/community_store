<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Product;

use Doctrine\ORM\Mapping as ORM;
use Concrete\Core\File\File;
use Concrete\Core\File\Set\Set as FileSet;
use Concrete\Core\Support\Facade\DatabaseORM as dbORM;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product as StoreProduct;

/**
 * @ORM\Entity
 * @ORM\Table(name="CommunityStoreDigitalFiles")
 */
class ProductFile
{
    /**
     * @ORM\Id @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $dfID;

    /**
     * @ORM\Column(type="integer")
     */
    protected $pID;

    /**
     * @ORM\ManyToOne(targetEntity="Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product",inversedBy="files",cascade={"persist"})
     * @ORM\JoinColumn(name="pID", referencedColumnName="pID", onDelete="CASCADE")
     */
    protected $product;

    /**
     * @ORM\Column(type="integer")
     */
    protected $dffID;

    private function setProductID($pID)
    {
        $this->pID = $pID;
    }

    public function setProduct($product)
    {
        return $this->product = $product;
    }

    private function setFileID($fID)
    {
        $this->dffID = $fID;
    }

    public function getID()
    {
        return $this->dfID;
    }

    public function getProductID()
    {
        return $this->pID;
    }

    public function getFileID()
    {
        return $this->dffID;
    }

    public static function getByID($id)
    {
        $em = dbORM::entityManager();

        return $em->find(get_class(), $id);
    }

    public static function getFilesForProduct(StoreProduct $product)
    {
        $em = dbORM::entityManager();

        return $em->getRepository(get_class())->findBy(['pID' => $product->getID()]);
    }

    public static function getFileObjectsForProduct(StoreProduct $product)
    {
        $results = self::getFilesForProduct($product);
        $fileObjects = [];
        foreach ($results as $result) {
            $fileObjects[] = File::getByID($result->getFileID());
        }

        return $fileObjects;
    }

    public static function addFilesForProduct(array $files, StoreProduct $product)
    {
        self::removeFilesForProduct($product);
        //add new ones.
        if (!empty($files['ddfID'])) {
            $fs = FileSet::getByID(\Config::get('community_store.digitalDownloadFileSet', 0));

            foreach ($files['ddfID'] as $fileID) {
                if ($fileID) {
                    self::add($product, $fileID);
                    $fileObj = File::getByID($fileID);
                    if (is_object($fs)) {
                        $fs->addFileToSet($fileObj);
                    }
                }
            }
        }
    }

    public static function removeFilesForProduct(StoreProduct $product)
    {
        $existingFiles = self::getFilesForProduct($product);
        foreach ($existingFiles as $file) {
            $file->delete();
        }
    }

    public static function add($product, $fID)
    {
        $productFile = new self();
        $productFile->setProduct($product);
        $productFile->setFileID($fID);
        $productFile->save();

        return $productFile;
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
