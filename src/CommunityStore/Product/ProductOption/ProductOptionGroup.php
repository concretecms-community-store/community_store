<?php 
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductOption;

use Database;

use \Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product as StoreProduct;

/**
 * @Entity
 * @Table(name="CommunityStoreProductOptionGroups")
 */
class ProductOptionGroup
{
    
    /** 
     * @Id @Column(type="integer") 
     * @GeneratedValue 
     */
    protected $pogID;
    
    /**
     * @Column(type="integer")
     */
    protected $pID; 
    
    /**
     * @Column(type="string")
     */
    protected $pogName;
    
    /**
     * @Column(type="integer")
     */
    protected $pogSort;

    private function setID($pogID){ $this->pogID = $pogID; }
    private function setProductID($pID){ $this->pID = $pID; }
    private function setName($name){ $this->pogName = $name; }
    private function setSort($sort){ $this->pogSort = $sort; }
    
    public function getID(){ return $this->pogID; }
    public function getProductID() { return $this->pID; }
    public function getName() { return $this->pogName; }
    public function getSort() { return $this->pogSort; }
    
    public static function getByID($id) {
        $db = Database::connection();
        $em = $db->getEntityManager();
        return $em->find('Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductOption\ProductOptionGroup', $id);
    }
    
    public static function getOptionGroupsForProduct(StoreProduct $product)
    {
        $db = Database::connection();
        $em = $db->getEntityManager();
        return $em->getRepository('Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductOption\ProductOptionGroup')->findBy(array('pID' => $product->getProductID()));
    }
    
    public static function removeOptionGroupsForProduct(StoreProduct $product, $excluding = array())
    {
        if (!is_array($excluding)) {
            $excluding = array();
        }

        //clear out existing product option groups
        $existingOptionGroups = self::getOptionGroupsForProduct($product);
        foreach($existingOptionGroups as $optionGroup){
            if (!in_array($optionGroup->getID(), $excluding)) {
                $optionGroup->delete();
            }
        }
    }
    
    public static function add($product,$name,$sort)
    {
        $productOptionGroup = new self();
        $pID = $product->getProductID();
        return self::addOrUpdate($pID,$name,$sort,$productOptionGroup);
    }
    public function update($product,$name,$sort)
    {
        $productOptionGroup = $this;
        $pID = $product->getProductID();
        return self::addOrUpdate($pID,$name,$sort,$productOptionGroup);
    }
    public static function addOrUpdate($pID,$name,$sort,$obj)
    {
        $obj->setProductID($pID);
        $obj->setName($name);
        $obj->setSort($sort);
        $obj->save();
        return $obj;
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
