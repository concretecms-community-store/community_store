<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Product;

use Concrete\Core\Page\Page;
use Doctrine\ORM\Mapping as ORM;
use Concrete\Core\Support\Facade\DatabaseORM as dbORM;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product;

/**
 * @ORM\Entity
 * @ORM\Table(name="CommunityStoreProductLocations")
 */
class ProductLocation
{
    /**
     * @ORM\Id @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ORM\Column(type="integer")
     */
    protected $pID;

    /**
     * @ORM\ManyToOne(targetEntity="Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product",inversedBy="locations",cascade={"persist"})
     * @ORM\JoinColumn(name="pID", referencedColumnName="pID", onDelete="CASCADE")
     */
    protected $product;

    /**
     * @ORM\Column(type="integer")
     */
    protected $cID;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $categorySortOrder;

    /**
     * @ORM\Column(type="integer", nullable=true)
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
        $em = dbORM::entityManager();

        return $em->find(get_class(), $cID);
    }

    public static function getLocationsForProduct(Product $product)
    {
        $em = dbORM::entityManager();

        return $em->getRepository(get_class())->findBy(['pID' => $product->getID()], ['productSortOrder' => 'asc']);
    }

    public static function getProductsForLocation($cID)
    {
        $em = dbORM::entityManager();

        return $em->getRepository(get_class())->findBy(['cID' => $cID], ['categorySortOrder' => 'asc']);
    }

    public static function addLocationsForProduct(array $locations, Product $product)
    {
        $saveLocations = [];
        $existingLocationID = [];

        if (!empty($locations['cID'])) {
            foreach ($locations['cID'] as $cID) {
                $saveLocations[] = $cID;
            }
        }

        $existingLocations = self::getLocationsForProduct($product);

        foreach ($existingLocations as $existingLocation) {
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
            foreach ($locations['cID'] as $key => $cID) {
                if ($cID > 0 && !in_array($cID, $existingLocationID)) {
                    self::add($product, $cID, $key);
                }
            }
        }
    }

    public static function removeLocationsForProduct(Product $product)
    {
        $existingLocations = self::getLocationsForProduct($product);
        foreach ($existingLocations as $location) {
            $location->delete();
        }
    }

    // returns an associated array of pages, with the page name as the key, alphabetically sorted
    // each value is an array that includes a page object and product count for that category
    public static function getLocationPages()
    {
        $app = \Concrete\Core\Support\Facade\Application::getFacadeApplication();
        $db = $app->make('database')->connection();

        $query = $db->query('select count(*) as productCount, max(cID) as cID from CommunityStoreProductLocations group by cID');

        $pages = [];
        while ($row = $query->fetchRow()) {
            $page = Page::getByID($row['cID']);

            if ($page) {
                $pages[$page->getCollectionName()] = ['page' => $page, 'productCount' => $row['productCount']];
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
