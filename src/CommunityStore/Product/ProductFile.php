<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Product;

use Database;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product as StoreProduct;

/**
 * @Entity
 * @Table(name="CommunityStoreDigitalFiles")
 */
class ProductFile
{
    /** 
     * @Id @Column(type="integer") 
     * @GeneratedValue 
     */
    protected $dfID;

    /**
     * @Column(type="integer")
     */
    protected $pID;

    /**
     * @ManyToOne(targetEntity="Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product",inversedBy="files",cascade={"persist"})
     * @JoinColumn(name="pID", referencedColumnName="pID", onDelete="CASCADE")
     */
    protected $product;

    /**
     * @Column(type="integer")
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
        $em = \ORM::entityManager();
        return $em->find(get_class(), $id);
    }

    public static function getFilesForProduct(StoreProduct $product)
    {
        $em = \ORM::entityManager();
        return $em->getRepository(get_class())->findBy(array('pID' => $product->getID()));
    }

    public static function getFileObjectsForProduct(StoreProduct $product)
    {
        $results = self::getFilesForProduct($product);
        $fileObjects = array();
        foreach ($results as $result) {
            $fileObjects[] = \File::getByID($result->getFileID());
        }

        return $fileObjects;
    }

    public static function addFilesForProduct(array $files, StoreProduct $product)
    {
        self::removeFilesForProduct($product);
        //add new ones.
        if (!empty($files['ddfID'])) {
            foreach ($files['ddfID'] as $fileID) {
                if ($fileID) {
                    self::add($product, $fileID);
                    $fileObj = \File::getByID($fileID);
                    $fs = \FileSet::getByName("Digital Downloads");
                    $fs->addFileToSet($fileObj);
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

    public function __clone() {
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
