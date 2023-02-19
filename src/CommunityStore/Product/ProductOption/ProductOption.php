<?php

namespace Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductOption;

use Concrete\Core\Support\Facade\DatabaseORM as dbORM;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

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
     * @ORM\Column(type="text", nullable=true)
     */
    protected $poDetails;

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

    public function __construct()
    {
        $this->optionItems = new ArrayCollection();
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

    public function setProduct($product)
    {
        return $this->product = $product;
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

    public function getDetails()
    {
        return $this->poDetails;
    }

    public function setDetails($poDetails)
    {
        $this->poDetails = $poDetails;
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
        return (int) ($this->poIncludeVariations === null || $this->poIncludeVariations == 1);
    }

    public function setIncludeVariations($poIncludeVariations)
    {
        $this->poIncludeVariations = $poIncludeVariations;
    }

    public function getOptionItems()
    {
        return $this->optionItems;
    }

    public static function getByID($id)
    {
        $em = dbORM::entityManager();

        return $em->find(__CLASS__, $id);
    }

    public static function getOptionsForProduct(Product $product)
    {
        $em = dbORM::entityManager();

        return $em->getRepository(__CLASS__)->findBy(['pID' => $product->getID()]);
    }

    public static function removeOptionsForProduct(Product $product, $excluding = [])
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

    public static function add($product, $name, $sort, $type = '', $handle = '', $required = false, $includeVariations = false, $displayType = '', $details = '')
    {
        $ProductOption = new self();

        return self::addOrUpdate($product, $name, $sort, $type, $handle, $required, $includeVariations, $displayType, $details, $ProductOption);
    }

    public function update($product, $name, $sort, $type = '', $handle = '', $required = false, $includeVariations = false, $displayType = '', $details = '')
    {
        $ProductOption = $this;

        return self::addOrUpdate($product, $name, $sort, $type, $handle, $required, $includeVariations, $displayType, $details, $ProductOption);
    }

    public static function addOrUpdate($product, $name, $sort, $type, $handle, $required, $includeVariations, $displayType, $details, $obj)
    {
        $obj->setProduct($product);
        $obj->setName($name);
        $obj->setSort($sort);
        $obj->setType($type);
        $obj->setHandle($handle);
        $obj->setRequired($required);
        $obj->setIncludeVariations($includeVariations);
        $obj->setDetails($details);
        $obj->setDisplayType($displayType);
        $obj->save();

        return $obj;
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
        if (isset($data['poID'])) {
            self::removeOptionsForProduct($product, $data['poID']);

            if (isset($data['poiID'])) {
                ProductOptionItem::removeOptionItemsForProduct($product, $data['poiID']);
            }
        }

        if (isset($data['poSort']) && is_array($data['poSort'])) {
            $count = count($data['poSort']);
            $ii = 0; //set counter for items

            if ($count > 0) {
                for ($i = 0; $i < $count; $i++) {
                    if (isset($data['poID'][$i])) {
                        $option = self::getByID($data['poID'][$i]);

                        if ($option) {
                            $option->update($product, $data['poName'][$i], $data['poSort'][$i], $data['poType'][$i], $data['poHandle'][$i], $data['poRequired'][$i], $data['poIncludeVariations'][$i], $data['poDisplayType'][$i], $data['poDetails'][$i]);
                        }
                    }

                    if (!$option) {
                        if ($data['poName'][$i]) {
                            $option = self::add($product, $data['poName'][$i], $data['poSort'][$i], $data['poType'][$i], $data['poHandle'][$i], $data['poRequired'][$i], $data['poIncludeVariations'][$i], $data['poDisplayType'][$i], $data['poDetails'][$i]);
                            $product->getOptions()->add($option);
                        }
                    }

                    if ($option) {
                        //add option items
                        $itemsInGroup = (isset($data['optGroup' . $i]) && is_array($data['optGroup' . $i])) ? count($data['optGroup' . $i]) : 0;

                        if ($itemsInGroup > 0) {
                            for ($gi = 0; $gi < $itemsInGroup; $gi++, $ii++) {
                                if ($data['poiID'][$ii] > 0) {
                                    $optionItem = ProductOptionItem::getByID($data['poiID'][$ii]);
                                    if ($optionItem) {
                                        $optionItem->update(
                                            $data['poiName'][$ii],
                                            $data['poiSort'][$ii],
                                            $data['poiSelectorName'][$ii],
                                            $data['poiPriceAdjust'][$ii],
                                            $data['poiWeightAdjust'][$ii],
                                            $data['poiHidden'][$ii],
                                            true
                                        );
                                    }
                                } else {
                                    if ($data['poiName'][$ii]) {
                                        $optionItem = ProductOptionItem::add(
                                            $option,
                                            $data['poiName'][$ii],
                                            $data['poiSort'][$ii],
                                            $data['poiSelectorName'][$ii],
                                            $data['poiPriceAdjust'][$ii],
                                            $data['poiWeightAdjust'][$ii],
                                            $data['poiHidden'][$ii],
                                            true
                                        );
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

    private function setID($poID)
    {
        $this->poID = $poID;
    }

    private function setName($name)
    {
        $this->poName = $name;
    }

    private function setSort($sort)
    {
        $this->poSort = $sort;
    }
}
