<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductOption;

use Doctrine\ORM\Mapping as ORM;
use Concrete\Core\Support\Facade\DatabaseORM as dbORM;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product as StoreProduct;

/**
 * @ORM\Entity
 * @ORM\Table(name="CommunityStoreProductOptionItems")
 */
class ProductOptionItem
{
    /**
     * @ORM\Id @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $poiID;

    /**
     * @ORM\Column(type="integer")
     */
    protected $poID;

    /**
     * @ORM\ManyToOne(targetEntity="ProductOption",inversedBy="optionItems",cascade={"persist"})
     * @ORM\JoinColumn(name="poID", referencedColumnName="poID", onDelete="CASCADE")
     */
    protected $option;

    public function setOption($option)
    {
        return $this->option = $option;
    }

    public function getOption()
    {
        return $this->option;
    }

    /**
     * @ORM\Column(type="string")
     */
    protected $poiName;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $poiSelectorName;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    protected $pPriceAdjust;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    protected $pWeightAdjust;

    /**
     * @ORM\Column(type="integer")
     */
    protected $poiSort;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $poiHidden = 0;

    /** @ORM\OneToMany(targetEntity="Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductVariation\ProductVariationOptionItem", mappedBy="option", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="poiID", referencedColumnName="poiID", onDelete="CASCADE")
     */
    private $variationoptionitems;

    private function setName($name)
    {
        $this->poiName = $name;
    }

    private function setSelectorName($name)
    {
        $this->poiSelectorName = $name;
    }

    public function getSelectorName()
    {
        return $this->poiSelectorName;
    }

    public function getSelectorDisplayValue()
    {
        if ($this->poiSelectorName) {
            return  $this->getSelectorName();
        }

        return $this->getName();
    }

    public function getPriceAdjustment()
    {
        return $this->pPriceAdjust;
    }

    public function setPriceAdjustment($priceAdjust)
    {
        $this->pPriceAdjust = $priceAdjust;
    }

    public function getWeightAdjustment()
    {
        return $this->pWeightAdjust;
    }

    public function setWeightAdjustment($weightAdjust)
    {
        $this->pWeightAdjust = $weightAdjust;
    }

    private function setSort($sort)
    {
        $this->poiSort = $sort;
    }

    private function setHidden($hidden)
    {
        $this->poiHidden = (bool) $hidden;
    }

    public function getID()
    {
        return $this->poiID;
    }

    public function getOptionID()
    {
        return $this->poID;
    }

    public function getName()
    {
        return $this->poiName;
    }

    public function getSort()
    {
        return $this->poiSort;
    }

    public function getHidden()
    {
        return $this->poiHidden;
    }

    public function isHidden()
    {
        return (bool) $this->poiHidden;
    }

    public static function getByID($id)
    {
        $em = dbORM::entityManager();

        return $em->find(get_class(), $id);
    }

    public static function getOptionItemsForProductOption(ProductOption $po)
    {
        $em = dbORM::entityManager();

        return $em->getRepository(get_class())->findBy(['poID' => $po->getID()], ['poiSort' => 'asc']);
    }

    public static function removeOptionItemsForProduct(StoreProduct $product, $excluding = [])
    {
        if (!is_array($excluding)) {
            $excluding = [];
        }
        //clear out existing product option items
        $options = $product->getOptions();
        if (!empty($options)) {
            foreach ($options as $option) {
                $optionItems = $option->getOptionItems();

                if (!empty($optionItems)) {
                    foreach ($optionItems as $optionItem) {
                        if (!in_array($optionItem->getID(), $excluding)) {
                            $optionItem->delete();
                        }
                    }
                }
            }
        }
    }

    public static function add($option, $name, $sort, $selectorname, $priceAdjust, $weightAdjust, $hidden = false, $persistonly = false)
    {
        $productOptionItem = new self();
        $productOptionItem->setOption($option);
        $productOptionItem->setName($name);
        $productOptionItem->setSelectorName($selectorname);
        $productOptionItem->setPriceAdjustment($priceAdjust);
        $productOptionItem->setWeightAdjustment($weightAdjust);
        $productOptionItem->setSort($sort);
        $productOptionItem->setHidden($hidden);
        $productOptionItem->save($persistonly);

        return $productOptionItem;
    }

    public function update($name, $sort, $selectorname, $priceAdjust, $weightAdjust, $hidden = false, $persistonly = false)
    {
        $this->setName($name);
        $this->setSelectorName($selectorname);
        $this->setPriceAdjustment($priceAdjust);
        $this->setWeightAdjustment($weightAdjust);
        $this->setSort($sort);
        $this->setHidden($hidden);
        $this->save($persistonly);

        return $this;
    }

    public function __clone()
    {
        if ($this->id) {
            $this->setID(null);
            $this->setOption(null);
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
}
