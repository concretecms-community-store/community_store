<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductOption;

use Doctrine\ORM\Mapping as ORM;
use Concrete\Core\Support\Facade\DatabaseORM as dbORM;
use Doctrine\Common\Collections\ArrayCollection;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product as StoreProduct;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductOption\ProductOptionItem as StoreProductOptionItem;

/**
 * @ORM\Entity
 * @ORM\Table(name="CommunityStoreProductOptions")
 */
class ProductOption
{
    /**
     * @ORM\Id @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $poID;

    /**
     * @ORM\Column(type="integer")
     */
    protected $pID;

    /**
     * @ORM\ManyToOne(targetEntity="Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product",inversedBy="options",cascade={"persist"})
     * @ORM\JoinColumn(name="pID", referencedColumnName="pID", onDelete="CASCADE")
     */
    protected $product;

    /**
     * @ORM\OneToMany(targetEntity="ProductOptionItem", mappedBy="option",cascade={"all"}, orphanRemoval=true)
     * @ORM\OrderBy({"poiSort" = "ASC"})
     */
    protected $optionItems;

    /**
     * @ORM\Column(type="string")
     */
    protected $poName;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $poType;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $poDisplayType;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $poHandle;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $poRequired;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $poIncludeVariations;

    /**
     * @ORM\Column(type="integer")
     */
    protected $poSort;

    private function setID($poID)
    {
        $this->poID = $poID;
    }

    public function setProduct($product)
    {
        return $this->product = $product;
    }

    private function setName($name)
    {
        $this->poName = $name;
    }

    private function setSort($sort)
    {
        $this->poSort = $sort;
    }

    public function getID()
    {
        return $this->poID;
    }

    public function getProductID()
    {
        return $this->pID;
    }

    public function getName()
    {
        return $this->poName;
    }

    public function getSort()
    {
        return $this->poSort;
    }

    public function getType()
    {
        return $this->poType;
    }

    public function setType($type)
    {
        $this->poType = $type;
    }

    public function getDisplayType()
    {
        return $this->poDisplayType;
    }

    public function setDisplayType($type)
    {
        $this->poDisplayType = $type;
    }

    public function getHandle()
    {
        return $this->poHandle;
    }

    public function setHandle($poHandle)
    {
        $this->poHandle = $poHandle;
    }

    public function getRequired()
    {
        return $this->poRequired;
    }

    public function setRequired($poRequired)
    {
        $this->poRequired = $poRequired;
    }

    public function getIncludeVariations()
    {
        return (int) (is_null($this->poIncludeVariations) || 1 == $this->poIncludeVariations);
    }

    public function setIncludeVariations($poIncludeVariations)
    {
        $this->poIncludeVariations = $poIncludeVariations;
    }

    public function __construct()
    {
        $this->optionItems = new ArrayCollection();
    }

    public function getOptionItems()
    {
        return $this->optionItems;
    }

    public static function getByID($id)
    {
        $em = dbORM::entityManager();

        return $em->find(get_class(), $id);
    }

    public static function getOptionsForProduct(StoreProduct $product)
    {
        $em = dbORM::entityManager();

        return $em->getRepository(get_class())->findBy(['pID' => $product->getID()]);
    }

    public static function removeOptionsForProduct(StoreProduct $product, $excluding = [])
    {
        if (!is_array($excluding)) {
            $excluding = [];
        }

        //clear out existing product option groups
        $existingOptions = self::getOptionsForProduct($product);
        foreach ($existingOptions as $optionGroup) {
            if (!in_array($optionGroup->getID(), $excluding)) {
                $optionGroup->delete();
            }
        }
    }

    public static function add($product, $name, $sort, $type = '', $handle = '', $required = false, $includeVariations = false, $displayType = '')
    {
        $ProductOption = new self();

        return self::addOrUpdate($product, $name, $sort, $type, $handle, $required, $includeVariations, $displayType, $ProductOption);
    }

    public function update($product, $name, $sort, $type = '', $handle = '', $required = false, $includeVariations = false, $displayType = '')
    {
        $ProductOption = $this;

        return self::addOrUpdate($product, $name, $sort, $type, $handle, $required, $includeVariations, $displayType, $ProductOption);
    }

    public static function addOrUpdate($product, $name, $sort, $type, $handle, $required, $includeVariations, $displayType, $obj)
    {
        $obj->setProduct($product);
        $obj->setName($name);
        $obj->setSort($sort);
        $obj->setType($type);
        $obj->setDisplayType($displayType);
        $obj->setHandle($handle);
        $obj->setRequired($required);
        $obj->setIncludeVariations($includeVariations);
        $obj->save();

        return $obj;
    }

    public function __clone()
    {
        $this->setID(null);
        $this->setProduct(null);

        $optionItems = $this->getOptionItems();
        $this->optionItems = new ArrayCollection();
        if (count($optionItems) > 0) {
            foreach ($optionItems as $optionItem) {
                $cloneOptionItem = clone $optionItem;
                $cloneOptionItem->originalID = $optionItem->getID();
                $cloneOptionItem->setOption($this);
                $this->optionItems->add($cloneOptionItem);
            }
        }
    }

    public function save($persistonly = false)
    {
        $em = dbORM::entityManager();
        $em->persist($this);

        if (!$persistonly) {
            $em->flush();
        }
    }

    public function delete()
    {
        $em = dbORM::entityManager();
        $em->remove($this);
        $em->flush();
    }

    public static function addProductOptions($data, $product)
    {
        self::removeOptionsForProduct($product, $data['poID']);
        StoreProductOptionItem::removeOptionItemsForProduct($product, $data['poiID']);

        if (is_array($data['poSort'])) {
            $count = count($data['poSort']);
            $ii = 0; //set counter for items

            if ($count > 0) {
                for ($i = 0; $i < $count; ++$i) {
                    if (isset($data['poID'][$i])) {
                        $option = self::getByID($data['poID'][$i]);

                        if ($option) {
                            $option->update($product, $data['poName'][$i], $data['poSort'][$i], $data['poType'][$i], $data['poHandle'][$i], $data['poRequired'][$i], $data['poIncludeVariations'][$i], $data['poDisplayType'][$i]);
                        }
                    }

                    if (!$option) {
                        if ($data['poName'][$i]) {
                            $option = self::add($product, $data['poName'][$i], $data['poSort'][$i], $data['poType'][$i], $data['poHandle'][$i], $data['poRequired'][$i], $data['poIncludeVariations'][$i], $data['poDisplayType'][$i]);
                            $product->getOptions()->add($option);
                        }
                    }

                    if ($option) {
                        //add option items
                        $itemsInGroup = is_array($data['optGroup' . $i]) ? count($data['optGroup' . $i]) : 0;

                        if ($itemsInGroup > 0) {
                            for ($gi = 0; $gi < $itemsInGroup; $gi++, $ii++) {
                                if ($data['poiID'][$ii] > 0) {
                                    $optionItem = StoreProductOptionItem::getByID($data['poiID'][$ii]);
                                    if ($optionItem) {
                                        $optionItem->update($data['poiName'][$ii], $data['poiSort'][$ii], $data['poiHidden'][$ii], true);
                                    }
                                } else {
                                    if ($data['poiName'][$ii]) {
                                        $optionItem = StoreProductOptionItem::add($option, $data['poiName'][$ii], $data['poiSort'][$ii], $data['poiHidden'][$ii], true);
                                        $option->getOptionItems()->add($optionItem);
                                    }
                                }
                            }
                        }
                    }
                }

                $em = dbORM::entityManager();
                $em->flush();
            }
        }
    }
}
