<?php 
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Shipping\Clerk;

use Database;

/**
 * @Entity
 * @Table(name="CommunityStorePackages")
 */
class ClerkPackage implements \DVDoug\BoxPacker\Box
{
    /** @Id @Column(type="integer") @GeneratedValue **/
    protected $id;
    
    /**
     * @Column(type="string")
     */
    protected $reference;
    
    /**
     * @Column(type="integer")
     */
    protected $outerWidth;
    
    /**
     * @Column(type="integer")
     */
    protected $outerLength;
    
    /**
     * @Column(type="integer")
     */
    protected $outerDepth;
    
    /**
     * @Column(type="integer")
     */
    protected $emptyWeight;
    
    /**
     * @Column(type="integer")
     */
    protected $innerWidth;
    
    /**
     * @Column(type="integer")
     */
    protected $innerLength;
    
    /**
     * @Column(type="integer")
     */
    protected $innerDepth;
    
    /**
     * @Column(type="integer")
     */
    protected $innerVolume;
    
    /**
     * @Column(type="integer")
     */
    protected $maxWeight;
    
    public function setReference($reference){ $this->reference = $reference; }
    public function setOuterWidth($outerWidth){ $this->outerWidth = $outerWidth; }
    public function setOuterLength($outerLength){ $this->outerLength = $outerLength; }
    public function setOuterDepth($outerDepth){ $this->outerDepth = $outerDepth; }
    public function setEmptyWeight($emptyWeight){ $this->emptyWeight = $emptyWeight; }
    public function setInnerWidth($innerWidth){ $this->innerWidth = $innerWidth; }
    public function setInnerLength($innerLength){ $this->innerLength = $innerLength; }
    public function setInnerDepth($innerDepth){ $this->innerDepth = $innerDepth; }
    public function setInnerVolume($innerVolume){ $this->innerVolume = $innerVolume; }
    public function setMaxWeight($maxWeight){ $this->maxWeight = $maxWeight; }
    
    /**
     * @return int
     */
    public function getID(){ return $this->id; }
    
    /**
     * Reference for box type (e.g. SKU or description)
     * @return string
     */
    public function getReference(){ return $this->reference; }
    
    /**
     * Outer width in mm
     * @return int
     */
    public function getOuterWidth(){ return $this->outerWidth; }
    /**
     * Outer length in mm
     * @return int
     */
    public function getOuterLength(){ return $this->outerLength; }
    /**
     * Outer depth in mm
     * @return int
     */
    public function getOuterDepth(){ return $this->outerDepth; }
    /**
     * Empty weight in g
     * @return int
     */
    public function getEmptyWeight(){ return $this->emptyWeight; }
    /**
     * Inner width in mm
     * @return int
     */
    public function getInnerWidth(){ return $this->innerWidth; }
    /**
     * Inner length in mm
     * @return int
     */
    public function getInnerLength(){ return $this->innerLength; }
    /**
     * Inner depth in mm
     * @return int
     */
    public function getInnerDepth(){ return $this->innerDepth; }
    /**
     * Total inner volume of packing in mm^3
     * @return int
     */
    public function getInnerVolume(){ return $this->innerVolume; }
    /**
     * Max weight the packaging can hold in g
     * @return int
     */
    public function getMaxWeight(){ return $this->maxWeight; }
    
    public static function getByID($id) {
        $db = Database::connection();
        $em = $db->getEntityManager();
        return $em->find('Concrete\Package\CommunityStore\Src\CommunityStore\Shipping\Clerk\ClerkPackage', $id);
    }

    public static function add($data)
    {
        $package = new ClerkPackage();
        return self::addOrUpdate($data,$package);
    }

    public function update($data)
    {
        return $this->addOrUpdate($data,$this);
    }
    
    public function addOrUpdate($data,$package)
    {
        $package->setReference($data['reference']);
        $package->setOuterWidth($data['outerWidth']);
        $package->setOuterLength($data['outerLength']);
        $package->setOuterDepth($data['outerDepth']);
        $package->setEmptyWeight($data['emptyWeight']);
        $package->setInnerWidth($data['innerWidth']);
        $package->setInnerLength($data['innerLength']);
        $package->setInnerDepth($data['innerDepth']);
        $innerVolume = $data['innerWidth'] * $data['innerLength'] * $data['innerDepth'];
        $package->setInnerVolume($innerVolume);
        $package->setMaxWeight($data['maxWeight']);
        $package->save();
        return $package;
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
    public static function getPackages()
    {
        $em = Database::connection()->getEntityManager();
        $packages = $em->createQuery('select package from \Concrete\Package\CommunityStore\Src\CommunityStore\Shipping\Clerk\ClerkPackage package')->getResult();
        return $packages;
    }
}
