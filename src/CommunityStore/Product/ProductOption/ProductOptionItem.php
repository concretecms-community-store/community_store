<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductOption;

use Database;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product as StoreProduct;

/**
 * @Entity
 * @Table(name="CommunityStoreProductOptionItems")
 */
class ProductOptionItem
{
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue
     */
    protected $poiID;

    /**
     * @Column(type="integer")
     */
    protected $poID;

    /**
     * @ManyToOne(targetEntity="ProductOption",inversedBy="optionItems",cascade={"persist"})
     * @JoinColumn(name="poID", referencedColumnName="poID", onDelete="CASCADE")
     */
    protected $option;

    public function setOption($option)
    {
        return $this->option = $option;
    }

    /**
     * @Column(type="string")
     */
    protected $poiName;

    /**
     * @Column(type="integer")
     */
    protected $poiSort;

    /**
     * @Column(type="boolean")
     */
    protected $poiHidden = 0;

    /** @OneToMany(targetEntity="Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductVariation\ProductVariationOptionItem", mappedBy="option", cascade={"persist", "remove"})
     * @JoinColumn(name="poiID", referencedColumnName="poiID", onDelete="CASCADE")
     */
    private $variationoptionitems;

    private function setName($name)
    {
        $this->poiName = $name;
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
        $em = \ORM::entityManager();
        return $em->find(get_class(), $id);
    }

    public static function getOptionItemsForProductOption(ProductOption $po)
    {
        $em = \ORM::entityManager();
        return $em->getRepository(get_class())->findBy(array('poID' => $po->getID()), array('poiSort' => 'asc'));
    }

    public static function removeOptionItemsForProduct(StoreProduct $product, $excluding = array())
    {
        if (!is_array($excluding)) {
            $excluding = array();
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

    public static function add($option, $name, $sort, $hidden = false, $persistonly = false)
    {
        $productOptionItem = new self();;
        $productOptionItem->setOption($option);
        $productOptionItem->setName($name);
        $productOptionItem->setSort($sort);
        $productOptionItem->setHidden($hidden);
        $productOptionItem->save($persistonly);

        return $productOptionItem;
    }

    public function update($name, $sort, $hidden = false, $persistonly = false)
    {
        $this->setName($name);
        $this->setSort($sort);
        $this->setHidden($hidden);
        $this->save($persistonly);

        return $this;
    }

    public function __clone() {
        if ($this->id) {
            $this->setID(null);
            $this->setOption(null);
        }
    }

    public function save($persistonly = false)
    {
        $em = \ORM::entityManager();
        $em->persist($this);

        if (!$persistonly) {
            $em->flush();
        }
    }

    public function delete()
    {
        $em = \ORM::entityManager();
        $em->remove($this);
        $em->flush();
    }
}
