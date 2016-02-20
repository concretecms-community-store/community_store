<?php 
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Product;

use Database;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Group as StoreGroup;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product as StoreProduct;

/**
 * @Entity
 * @Table(name="CommunityStoreProductGroups")
 */
class ProductGroup
{
    /** 
     * @Id @Column(type="integer") 
     * @GeneratedValue 
     */
    protected $pgID;
    
    /**
     * @Column(type="integer")
     */
    protected $pID;
    
    /**
     * @Column(type="integer")
     */
    protected $gID; 
    
    private function setProductID($pID){ $this->pID = $pID; }
    private function setGroupID($gID){ $this->gID = $gID; }
    
    public function getProductID(){ return $this->pID; }
    public function getGroupID() { return $this->gID; }
    
    public static function getByID($pgID) {
        $db = Database::connection();
        $em = $db->getEntityManager();
        return $em->find('Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductGroup', $pgID);
    }
    
    public static function getGroupsForProduct(StoreProduct $product)
    {
        $db = Database::connection();
        $em = $db->getEntityManager();
        $groups = $em->getRepository('Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductGroup')->findBy(array('pID' => $product->getProductID()));
        foreach ($groups as $key => $value) {
            $group = new StoreGroup\Group;
            $groups[$key]->gName = $group->getByID($groups[$key]->gID)->getGroupName();
        }
        return $groups;
    }
    
    public static function getGroupIDsForProduct(StoreProduct $product)
    {
        $groups = self::getGroupsForProduct($product);
        $ids = array();
        foreach($groups as $g) {
            $ids[] = $g->getGroupID();
        }
        return $ids;
    }
    
    public static function addGroupsForProduct(array $data, StoreProduct $product)
    {
        self::removeGroupsForProduct($product);
        //add new ones.
        if (!empty($data['pProductGroups'])) {
            foreach ($data['pProductGroups'] as $gID) {
                self::add($product->getProductID(), $gID);
            }
        }
    }
    
    public static function removeGroupsForProduct(StoreProduct $product)
    {
        $existingGroups = self::getGroupsForProduct($product);
        foreach($existingGroups as $group){
            $group->delete();
        }
    }
    
    public static function add($pID,$gID)
    {
        $productGroup = new self();
        $productGroup->setProductID($pID);
        $productGroup->setGroupID($gID);
        $productGroup->save();
        return $productGroup;
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
