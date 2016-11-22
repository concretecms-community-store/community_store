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

    /**
     * @Column(type="integer", nullable=true)
     */
    protected $categorySortOrder;

    /**
     * @Column(type="integer", nullable=true)
     */
    protected $productSortOrder;


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

    public function getCategorySortOrder()
    {
        return $this->categorySortOrder;
    }

    public function setCategorySortOrder($categorySortOrder)
    {
        $this->categorySortOrder = $categorySortOrder;
    }

    public function getProductSortOrder()
    {
        return $this->productSortOrder;
    }

    public function setProductSortOrder($productSortOrder)
    {
        $this->productSortOrder = $productSortOrder;
    }

    public static function getByID($cID)
    {
        $em = \ORM::entityManager();
        return $em->find(get_class(), $cID);
    }

    public static function getLocationsForProduct(StoreProduct $product)
    {
        $em = \ORM::entityManager();
        return $em->getRepository(get_class())->findBy(array('pID' => $product->getID()), array('productSortOrder'=>'asc'));
    }

    public static function getProductsForLocation($cID)
    {
        $em = \ORM::entityManager();
        return $em->getRepository(get_class())->findBy(array('cID' => $cID), array('categorySortOrder'=>'asc'));
    }

    public static function addLocationsForProduct(array $locations, StoreProduct $product)
    {
        $saveLocations = array();
        $existingLocationID = array();


        if (!empty($locations['cID'])) {
            foreach ($locations['cID'] as $cID) {
                $saveLocations[] = $cID;
            }
        }

        $existingLocations = self::getLocationsForProduct($product);

        foreach($existingLocations as $existingLocation) {
            if (!in_array($existingLocation->getCollectionID(), $saveLocations)) {
                // no longer in list, so remove
                $existingLocation->delete();
            } else {
                $arrayPosition = array_search($existingLocation->getCollectionID(), $saveLocations);
                $existingLocation->setProductSortOrder($arrayPosition);
                $existingLocation->save();
                $existingLocationID[] = $existingLocation->getCollectionID();
            }
        }

        //add new ones.
        if (!empty($locations['cID'])) {
            foreach ($locations['cID'] as $key=>$cID) {
                if ($cID > 0 && !in_array($cID, $existingLocationID)) {
                    self::add($product, $cID, $key);
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

    // returns an associated array of pages, with the page name as the key, alphabetically sorted
    // each value is an array that includes a page object and product count for that category
    public static function getLocationPages() {
        $app = \Concrete\Core\Support\Facade\Application::getFacadeApplication();
        $db = $app->make('database')->connection();

        $query = $db->query('select count(*) as productCount, max(cID) as cID from CommunityStoreProductLocations group by cID');

        $pages = array();
        while($row = $query->fetchRow()) {
           $page = \Page::getByID($row['cID']);

            if ($page) {
                $pages[$page->getCollectionName()] = array('page'=>$page, 'productCount' =>$row['productCount']);
            }
        }

        ksort($pages);

        return $pages;
    }

    public static function add($product, $cID, $productSortOrder = 0)
    {
        $location = new self();
        $location->setProduct($product);
        $location->setCollectionID($cID);
        $location->setProductSortOrder($productSortOrder);
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
