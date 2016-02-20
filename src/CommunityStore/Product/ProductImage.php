<?php 
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Product;

use Database;
use File;

use \Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product as StoreProduct;

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
     * @Column(type="integer")
     */
    protected $pifID; 
    
    /**
     * @Column(type="integer")
     */
    protected $piSort; 
    
    private function setProductID($pID){ $this->pID = $pID; }
    private function setFileID($pifID){ $this->pifID = $pifID; }
    private function setSort($piSort){ $this->piSort = $piSort; }
    
    public function getID(){ return $this->piID; }
    public function getProductID() { return $this->pID; }
    public function getFileID() { return $this->pifID; }
    public function getSort() { return $this->piSort; }
    
    public static function getByID($piID) {
        $db = Database::connection();
        $em = $db->getEntityManager();
        return $em->find('Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductImage', $piID);
    }
    
    public static function getImagesForProduct(StoreProduct $product)
    {
        $db = Database::connection();
        $em = $db->getEntityManager();
        return $em->getRepository('Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductImage')->findBy(array('pID' => $product->getProductID()));
    }
    
    public static function getImageObjectsForProduct(StoreProduct $product)
    {
        $images = self::getImagesForProduct($product);
        $imageObjects = array();
        foreach($images as $img){
            $imageObjects[] = File::getByID($img->getFileID());
        }
        return $imageObjects;
    }
    
    public static function addImagesForProduct(array $images, StoreProduct $product)
    {
        self::removeImagesForProduct($product);
        
        //add new ones.
        for($i=0;$i<count($images['pifID']);$i++){
            self::add($product->getProductID(),$images['pifID'][$i],$images['piSort'][$i]);
        }
    }

    public static function removeImagesForProduct(StoreProduct $product)
    {
        $existingImages = self::getImagesForProduct($product);
        foreach($existingImages as $img){
            $img->delete();
        }
    }
    
    public static function add($pID,$pifID,$piSort)
    {
        $productImage = new self();
        $productImage->setProductID($pID);
        $productImage->setFileID($pifID);
        $productImage->setSort($piSort);
        $productImage->save();
        return $productImage;
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
