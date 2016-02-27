<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductOption;

use Database;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product as StoreProduct;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductOption\ProductOptionItem as StoreProductOptionItem;

/**
 * @Entity
 * @Table(name="CommunityStoreProductOptions")
 */
class ProductOption
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
     * @ManyToOne(targetEntity="Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product",inversedBy="options",cascade={"persist"})
     * @JoinColumn(name="pID", referencedColumnName="pID", onDelete="CASCADE")
     */
    protected $product;


    /**
     * @OneToMany(targetEntity="ProductOptionItem", mappedBy="option"))
     */
    protected $optionItems;

    /**
     * @Column(type="string")
     */
    protected $pogName;

    /**
     * @Column(type="integer")
     */
    protected $pogSort;

    private function setID($pogID)
    {
        $this->pogID = $pogID;
    }

    public function setProduct($product)
    {
        return $this->product = $product;
    }

    private function setName($name)
    {
        $this->pogName = $name;
    }
    private function setSort($sort)
    {
        $this->pogSort = $sort;
    }

    public function getID()
    {
        return $this->pogID;
    }
    public function getProductID()
    {
        return $this->pID;
    }
    public function getName()
    {
        return $this->pogName;
    }
    public function getSort()
    {
        return $this->pogSort;
    }


    public function __construct()
    {
        $this->optionItems = new ArrayCollection();
    }

    public function getOptionItems(){
        return $this->optionItems;
    }

    public static function getByID($id)
    {
        $db = Database::connection();
        $em = $db->getEntityManager();

        return $em->find('Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductOption\ProductOption', $id);
    }

    public static function getOptionsForProduct(StoreProduct $product)
    {
        $db = Database::connection();
        $em = $db->getEntityManager();

        return $em->getRepository('Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductOption\ProductOption')->findBy(array('pID' => $product->getID()));
    }

    public static function removeOptionsForProduct(StoreProduct $product, $excluding = array())
    {
        if (!is_array($excluding)) {
            $excluding = array();
        }

        //clear out existing product option groups
        $existingOptions = self::getOptionsForProduct($product);
        foreach ($existingOptions as $optionGroup) {
            if (!in_array($optionGroup->getID(), $excluding)) {
                $optionGroup->delete();
            }
        }
    }

    public static function add($product, $name, $sort)
    {
        $ProductOption = new self();

        return self::addOrUpdate($product, $name, $sort, $ProductOption);
    }
    public function update($product, $name, $sort)
    {
        $ProductOption = $this;

        return self::addOrUpdate($product, $name, $sort, $ProductOption);
    }
    public static function addOrUpdate($product, $name, $sort, $obj)
    {
        $obj->setProduct($product);
        $obj->setName($name);
        $obj->setSort($sort);
        $obj->save();
    }

    public function __clone() {
        if ($this->id) {
            $this->setID(null);
            $this->setProduct(null);

            $optionItems = $this->getOptionItems();
            $this->optionItems = new ArrayCollection();
            if(count($optionItems) > 0){
                foreach ($optionItems as $optionItem) {
                    $cloneOptionItem = clone $optionItem;
                    $this->optionItems->add($cloneOptionItem);
                    $cloneOptionItem->setOption($this);
                    $cloneOptionItem->save();
                }
            }
        }
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

    public static function addProductOptions($data, $product)
    {
        self::removeOptionsForProduct($product, $data['pogID']);

        $count = count($data['pogSort']);
        $ii = 0;//set counter for items
        if ($count > 0) {
            for ($i = 0;$i < count($data['pogSort']);++$i) {
                if (isset($data['pogID'][$i])) {
                    $option = self::getByID($data['pogID'][$i]);

                    if ($option) {
                        $option->update($product, $data['pogName'][$i], $data['pogSort'][$i]);
                    }
                }

                if (!$option) {
                    if ($data['pogName'][$i]) {
                        $option = self::add($product, $data['pogName'][$i], $data['pogSort'][$i]);
                    }
                }

                if ($option) {
                    //add option items
                    $itemsInGroup = count($data['optGroup'.$i]);
                    if ($itemsInGroup > 0) {
                        for ($gi = 0;$gi < $itemsInGroup;$gi++, $ii++) {
                            if ($data['poiID'][$ii] > 0) {
                                $optionItem = StoreProductOptionItem::getByID($data['poiID'][$ii]);
                                if ($optionItem) {
                                    $optionItem->update($product, $data['poiName'][$ii], $data['poiSort'][$ii], $data['poiHidden'][$ii]);
                                }
                            } else {
                                if ($data['poiName'][$ii]) {
                                    StoreProductOptionItem::add($option, $data['poiName'][$ii], $data['poiSort'][$ii], $data['poiHidden'][$ii]);
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}
