<?php 
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Product;

use Database;

use \Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product as StoreProduct;

/**
 * @Entity
 * @Table(name="CommunityStoreProductLocations")
 */
class ProductLocation
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
     * @Column(type="integer")
     */
    protected $cID; 
    
    private function setProductID($pID){ $this->pID = $pID; }
    private function setCollectionID($cID){ $this->cID = $cID; }
    
    public function getID(){ return $this->id; }
    public function getProductID() { return $this->pID; }
    public function getCollectionID() { return $this->cID; }
    
    public static function getByID($cID) {
        $db = Database::connection();
        $em = $db->getEntityManager();
        return $em->find('Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductLocation', $cID);
    }
    
    public static function getLocationsForProduct(StoreProduct $product)
    {
        $db = Database::connection();
        $em = $db->getEntityManager();
        return $em->getRepository('Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductLocation')->findBy(array('pID' => $product->getProductID()));
    }
    
    public static function addLocationsForProduct(array $locations, StoreProduct $product)
    {
        //clear out existing locations
        self::removeLocationsForProduct($product);
        //add new ones.
        if (!empty($locations['cID'])) {
            foreach($locations['cID'] as $cID){
                self::add($product->getProductID(),$cID);
            }
        }
    }

    public static function removeLocationsForProduct(StoreProduct $product)
    {
        $existingLocations = self::getLocationsForProduct($product);
        foreach($existingLocations as $location){
            $location->delete();
        }
    }
    
    public static function add($pID,$cID)
    {
        $location = new self();
        $location->setProductID($pID);
        $location->setCollectionID($cID);
        $location->save();
        return $location;
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
