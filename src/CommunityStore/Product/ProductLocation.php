<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Product;

use Database;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product as StoreProduct;

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
     * @ManyToOne(targetEntity="Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product",inversedBy="locations",cascade={"persist"})
     * @JoinColumn(name="pID", referencedColumnName="pID", onDelete="CASCADE")
     */
    protected $product;

    /**
     * @Column(type="integer")
     */
    protected $cID;

    private function setProductID($pID)
    {
        $this->pID = $pID;
    }
    private function setCollectionID($cID)
    {
        $this->cID = $cID;
    }

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

    public function getCollectionID()
    {
        return $this->cID;
    }

    public static function getByID($cID)
    {
        $em = \ORM::entityManager();
        return $em->find(get_class(), $cID);
    }

    public static function getLocationsForProduct(StoreProduct $product)
    {
        $em = \ORM::entityManager();
        return $em->getRepository(get_class())->findBy(array('pID' => $product->getID()));
    }

    public static function addLocationsForProduct(array $locations, StoreProduct $product)
    {
        //clear out existing locations
        self::removeLocationsForProduct($product);
        //add new ones.
        if (!empty($locations['cID'])) {
            foreach ($locations['cID'] as $cID) {
                if ($cID > 0) {
                    self::add($product, $cID);
                }
            }
        }
    }

    public static function removeLocationsForProduct(StoreProduct $product)
    {
        $existingLocations = self::getLocationsForProduct($product);
        foreach ($existingLocations as $location) {
            $location->delete();
        }
    }

    public static function add($product, $cID)
    {
        $location = new self();
        $location->setProduct($product);
        $location->setCollectionID($cID);
        $location->save();

        return $location;
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
