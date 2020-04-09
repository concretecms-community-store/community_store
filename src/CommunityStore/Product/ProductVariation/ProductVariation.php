<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductVariation;

use Concrete\Core\File\File;
use Doctrine\ORM\Mapping as ORM;
use Concrete\Core\Support\Facade\Application;
use Doctrine\Common\Collections\ArrayCollection;
use Concrete\Core\Support\Facade\DatabaseORM as dbORM;
use Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Price as StorePrice;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product as StoreProduct;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductOption\ProductOption as StoreProductOption;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductOption\ProductOptionItem as StoreProductOptionItem;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductVariation\ProductVariationOptionItem as StoreProductVariationOptionItem;

/**
 * @ORM\Entity
 * @ORM\Table(name="CommunityStoreProductVariations")
 */
class ProductVariation
{
    /**
     * @ORM\Id @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $pvID;

    /**
     * @ORM\Column(type="integer")
     */
    protected $pID;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    protected $pvPrice;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    protected $pvWholesalePrice;

    /**
     * @ORM\Column(type="string",nullable=true)
     */
    protected $pvSKU;

    /**
     * @ORM\Column(type="string",nullable=true)
     */
    protected $pvBarcode;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    protected $pvSalePrice;

    /**
     * @ORM\Column(type="integer",nullable=true)
     */
    protected $pvfID;

    /**
     * @ORM\Column(type="decimal", precision=12, scale=4)
     */
    protected $pvQty;

    /**
     * @ORM\Column(type="boolean",nullable=true)
     */
    protected $pvQtyUnlim;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2,nullable=true)
     */
    protected $pvWidth;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2,nullable=true)
     */
    protected $pvHeight;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2,nullable=true)
     */
    protected $pvLength;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2,nullable=true)
     */
    protected $pvWeight;

    /**
     * @ORM\Column(type="integer",nullable=true)
     */
    protected $pvNumberItems;

    /**
     * @ORM\Column(type="text",nullable=true)
     */
    protected $pvPackageData;

    /**
     * @ORM\Column(type="integer")
     */
    protected $pvSort;

    /**
     * @ORM\OneToMany(targetEntity="Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductVariation\ProductVariationOptionItem", mappedBy="variation", cascade={"persist"}))
     */
    protected $options;


    /**
     * @ORM\return mixed
     */
    public function getVariationFID()
    {
        return $this->pvfID;
    }

    /**
     * @ORM\param mixed $pvfID
     */
    public function setVariationFID($pvfID)
    {
        $this->pvfID = $pvfID;
    }

    public function getVariationImageID()
    {
        return $this->pvfID;
    }

    public function getVariationImageObj()
    {
        if ($this->pvfID) {
            $fileObj = File::getByID($this->pvfID);

            return $fileObj;
        }
    }

    public function __construct()
    {
        $this->options = new ArrayCollection();
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function getOptionItemIDs()
    {
        $options = $this->getOptions();

        $optionids = [];

        foreach ($options as $opt) {
            $optionids[] = $opt->getOptionItem()->getID();
        }

        sort($optionids);

        return $optionids;
    }

    /**
     * @ORM\return mixed
     */
    public function getID()
    {
        return $this->pvID;
    }

    /**
     * @ORM\return mixed
     */
    public function getProductID()
    {
        return $this->pID;
    }

    /**
     * @ORM\return mixed
     */
    public function getVariationSKU()
    {
        return $this->pvSKU;
    }

    /**
     * @ORM\param mixed $pvSKU
     */
    public function setVariationSKU($pvSKU)
    {
        $this->pvSKU = $pvSKU;
    }

    /**
     * @ORM\return mixed
     */
    public function getVariationBarcode()
    {
        return $this->pvBarcode;
    }

    /**
     * @ORM\param mixed $pvBarcode
     */
    public function setVariationBarcode($pvBarcode)
    {
        $this->pvBarcode = $pvBarcode;
    }

    /**
     * @ORM\param mixed $pID
     */
    public function setProductID($pID)
    {
        $this->pID = $pID;
    }

    /**
     * @ORM\return mixed
     */
    public function getVariationPrice()
    {
        return $this->pvPrice;
    }

    /**
     * @return mixed
     */
    public function getVariationWholesalePrice()
    {
        return $this->pvWholesalePrice;
    }

    public function getFormattedVariationPrice()
    {
        return StorePrice::format($this->pvPrice);
    }

    /**
     * @ORM\param mixed $pvPrice
     */
    public function setVariationPrice($pvPrice)
    {
        if ('' != $pvPrice) {
            $this->pvPrice = (float) $pvPrice;
        } else {
            $this->pvPrice = null;
        }
    }

    /**
     * @param mixed $pvWholesalePrice
     */
    public function setVariationWholesalePrice($pvWholesalePrice)
    {
        if ($pvWholesalePrice != '') {
            $this->pvWholesalePrice = (float)$pvWholesalePrice;
        } else {
            $this->pvWholesalePrice = null;
        }
    }

    /**
     * @ORM\return mixed
     */
    public function getVariationSalePrice()
    {
        return $this->pvSalePrice;
    }

    public function getFormattedVariationSalePrice()
    {
        return StorePrice::format($this->pvSalePrice);
    }

    /**
     * @ORM\param mixed $pvSalePrice
     */
    public function setVariationSalePrice($pvSalePrice)
    {
        if ('' != $pvSalePrice) {
            $this->pvSalePrice = (float) $pvSalePrice;
        } else {
            $this->pvSalePrice = null;
        }
    }

    /**
     * @ORM\return mixed
     */
    public function getStockLevel()
    {
        return $this->pvQty;
    }

    /**
     * @deprecated
     */
    public function getVariationQty()
    {
        return $this->getStockLevel();
    }

    /**
     * @ORM\param mixed $pvQty
     */
    public function setStockLevel($pvQty)
    {
        $this->pvQty = $pvQty ? $pvQty : 0;
    }

    /**
     * @deprecated
     */
    public function setVariationQty($pvQty) {
        $this->setStockLevel($pvQty);
    }

    /**
     * @ORM\return mixed
     */
    public function getVariationQtyUnlim()
    {
        return $this->pvQtyUnlim;
    }

    /**
     * @ORM\param mixed $pvQtyUnlim
     */
    public function setVariationQtyUnlim($pvQtyUnlim)
    {
        $this->pvQtyUnlim = $pvQtyUnlim;
    }

    /**
     * @ORM\return mixed
     */
    public function getVariationWidth()
    {
        return $this->pvWidth;
    }

    /**
     * @ORM\param mixed $pWidth
     */
    public function setVariationWidth($pvWidth)
    {
        if ('' != $pvWidth) {
            $this->pvWidth = (float) $pvWidth;
        } else {
            $this->pvWidth = null;
        }
    }

    /**
     * @ORM\return mixed
     */
    public function getVariationHeight()
    {
        return $this->pvHeight;
    }

    /**
     * @ORM\param mixed $pvHeight
     */
    public function setVariationHeight($pvHeight)
    {
        if ('' != $pvHeight) {
            $this->pvHeight = (float) $pvHeight;
        } else {
            $this->pvHeight = null;
        }
    }

    /**
     * @ORM\return mixed
     */
    public function getVariationLength()
    {
        return $this->pvLength;
    }

    /**
     * @ORM\param mixed $pvLength
     */
    public function setVariationLength($pvLength)
    {
        if ('' != $pvLength) {
            $this->pvLength = (float) $pvLength;
        } else {
            $this->pvLength = null;
        }
    }

    /**
     * @ORM\return mixed
     */
    public function getVariationWeight()
    {
        return $this->pvWeight;
    }

    /**
     * @ORM\param mixed $pvWeight
     */
    public function setVariationWeight($pvWeight)
    {
        if ('' != $pvWeight) {
            $this->pvWeight = (float) $pvWeight;
        } else {
            $this->pvWeight = null;
        }
    }

    /**
     * @ORM\return mixed
     */
    public function getVariationNumberItems()
    {
        return $this->pvNumberItems;
    }

    public function getVariationPackageData()
    {
        return $this->pvPackageData;
    }

    public function setVariationPackageData($data)
    {
        $this->pvPackageData = trim($data);
    }

    /**
     * @ORM\param mixed $pvNumberItems
     */
    public function setVariationNumberItems($pvNumberItems)
    {
        if ('' != $pvNumberItems) {
            $this->pvNumberItems = (int) $pvNumberItems;
        } else {
            $this->pvNumberItems = null;
        }
    }

    /**
     * @ORM\return mixed
     */
    public function getVariationSort()
    {
        return $this->pvSort;
    }

    /**
     * @ORM\param mixed $pvSort
     */
    public function setVariationSort($pvSort)
    {
        $this->pvSort = $pvSort;
    }

    public function isUnlimited()
    {
        return $this->getVariationQtyUnlim();
    }

    public function isSellable()
    {
        if ($this->isUnlimited() || $this->getVariationQty() > 0) {
            return true;
        } else {
            return false;
        }
    }

    public static function addVariations(array $data, StoreProduct $product)
    {
        $options = $product->getOptions();

        $optionArrays = [];

        if (!empty($options)) {
            foreach ($options as $option) {
                if ($option->getIncludeVariations()) {

                    $optionItems = $option->getOptionItems();
                    $sortedItems = [];
                    foreach($optionItems as $optionItem) {
                        $sortedItems[$optionItem->getSort()] = $optionItem;
                    }

                    foreach ($sortedItems as $optItem) {
                        $optionArrays[$option->getID()][] = $optItem->getID();
                        $tempoptionArrays[] = ['id'=>$optItem->getID(), 'order'=>$optItem->getSort()];
                    }
                }
            }
        }

        $comboOptions = self::combinations(array_values($optionArrays));

        $variationIDs = [];

        if (!empty($comboOptions)) {
            $sort = 0;

            foreach ($comboOptions as $key => $optioncombo) {
                if (!is_array($optioncombo)) {
                    $optioncomboarray = [];
                    $optioncomboarray[] = $optioncombo;
                    $optioncombo = $optioncomboarray;
                }

                $variation = self::getByOptionItemIDs($optioncombo);

                if (!$variation) {
                    $variation = self::add(
                        $product->getID(),
                        [
                        'pvSKU' => '',
                        'pvBarcode' => '',
                        'pvPrice' => '',
                        'pvWholesalePrice'=>'',
                        'pvSalePrice' => '',
                        'pvQty' => 0,
                        'pvQtyUnlim' => null,
                        'pvfID' => null,
                        'pvWeight' => '',
                        'pvNumberItems' => '',
                        'pvWidth' => '',
                        'pvHeight' => '',
                        'pvLength' => '',
                        'pvSort' => $sort,
                         true, ]
                    );

                    foreach ($optioncombo as $optionvalue) {
                        $option = StoreProductOptionItem::getByID($optionvalue);

                        if ($option) {
                            $variationoption = new StoreProductVariationOptionItem();
                            $variationoption->setOptionItem($option);
                            $variationoption->setVariation($variation);
                            $variationoption->save(true);
                        }
                    }
                } else {
                    $key = $variation->getID();

                    $variation->setVariationSKU($data['pvSKU'][$key]);
                    $variation->setVariationBarcode($data['pvBarcode'][$key]);
                    $variation->setVariationPrice($data['pvPrice'][$key]);
                    $variation->setVariationWholesalePrice($data['pvWholesalePrice'][$key]);
                    $variation->setVariationSalePrice($data['pvSalePrice'][$key]);
                    $variation->setVariationQty($data['pvQty'][$key]);
                    $variation->setVariationQtyUnlim($data['pvQtyUnlim'][$key]);
                    $variation->setVariationFID($data['pvfID'][$key] ? $data['pvfID'][$key] : null);
                    $variation->setVariationWeight($data['pvWeight'][$key]);
                    $variation->setVariationNumberItems($data['pvNumberItems'][$key]);
                    $variation->setVariationWidth($data['pvWidth'][$key]);
                    $variation->setVariationHeight($data['pvHeight'][$key]);
                    $variation->setVariationLength($data['pvLength'][$key]);
                    $variation->setVariationPackageData($data['pvPackageData'][$key]);
                    $variation->setVariationSort($sort);
                    $variation->save(true);

                    $options = $variation->getOptions();

                    foreach ($options as $opt) {
                        if (!in_array($opt->getOptionItem()->getID(), $optioncombo)) {
                            $opt->delete();
                        }
                    }
                }

                $variationIDs[] = $variation->getID();
                ++$sort;
            }

            $em = dbORM::entityManager();
            $em->flush();
        }

        $app = Application::getFacadeApplication();
        $db = $app->make('database')->connection();

        if (!empty($variationIDs)) {
            $options = implode(',', $variationIDs);
            $pvIDstoDelete = $db->getAll("SELECT pvID FROM CommunityStoreProductVariations WHERE pID = ? and pvID not in ($options)", [$product->getID()]);
        } else {
            $pvIDstoDelete = $db->getAll("SELECT pvID FROM CommunityStoreProductVariations WHERE pID = ?", [$product->getID()]);
        }

        if (!empty($pvIDstoDelete)) {
            foreach ($pvIDstoDelete as $pvID) {
                $variation = self::getByID($pvID);
                if ($variation) {
                    $variation->delete();
                }
            }
        }
    }

    public static function getByID($pvID)
    {
        $em = dbORM::entityManager();

        return $em->find(get_class(), $pvID);
    }

    public static function getBySKU($pvSKU)
    {
        $em = dbORM::entityManager();

        return $em->getRepository(get_class())->findOneBy(['pvSKU' => $pvSKU]);
    }

    public static function add($productID, $data, $persistonly = false)
    {
        $variation = new self();
        $variation->setProductID($productID);
        $variation->setVariationSKU($data['pvSKU']);
        $variation->setVariationBarcode($data['pvBarcode']);
        $variation->setVariationPrice($data['pvPrice']);
        $variation->setVariationWholesalePrice($data['pvWholesalePrice']);
        $variation->setVariationSalePrice($data['pvSalePrice']);
        $variation->setVariationQty($data['pvQty']);
        $variation->setVariationQtyUnlim($data['pvQtyUnlim']);
        $variation->setVariationFID($data['pvfID']);
        $variation->setVariationWeight($data['pvWidth']);
        $variation->setVariationNumberItems($data['pvNumberItems']);
        $variation->setVariationHeight($data['pvHeight']);
        $variation->setVariationLength($data['pvLength']);
        $variation->setVariationWidth($data['pvWeight']);
        $variation->setVariationPackageData($data['pvPackageData']);
        $variation->setVariationSort($data['pvSort']);
        $variation->save($persistonly);

        return $variation;
    }

    public static function getByOptionItemIDs(array $optionids)
    {
        $app = Application::getFacadeApplication();
        $db = $app->make('database')->connection();

        if (is_array($optionids) && !empty($optionids)) {
            $options = implode(',', $optionids);

            $pvID = $db->fetchColumn("SELECT pvID FROM CommunityStoreProductVariationOptionItems WHERE poiID in ($options)
                                 group by pvID having count(*) = ? ", [count($optionids)]);

            return self::getByID($pvID);
        }

        return false;
    }

    public function save($persistonly = false)
    {
        $em = dbORM::entityManager();
        $em->persist($this);

        if (!$persistonly) {
            $em->flush();
        }
    }

    public static function getVariationsForProduct(StoreProduct $product)
    {
        $em = dbORM::entityManager();

        return $em->getRepository(get_class())->findBy(['pID' => $product->getID()], ['pvSort' => 'asc']);
    }

    public function delete()
    {
        $em = dbORM::entityManager();
        $em->remove($this);
        $em->flush();
    }

    public static function removeVariationsForProduct(StoreProduct $product, $excluding = [])
    {
        if (!is_array($excluding)) {
            $excluding = [];
        }

        //clear out existing product option groups
        $existingVariations = self::getVariationsForProduct($product);
        foreach ($existingVariations as $variation) {
            if (!in_array($variation->getID(), $excluding)) {
                $variation->delete();
            }
        }
    }

    public static function combinations($arrays, $i = 0)
    {
        if (!isset($arrays[$i])) {
            return [];
        }
        if ($i == count($arrays) - 1) {
            return $arrays[$i];
        }

        // get combinations from subsequent arrays
        $tmp = self::combinations($arrays, $i + 1);

        $result = [];

        // concat each array from tmp with each element from $arrays[$i]
        foreach ($arrays[$i] as $v) {
            foreach ($tmp as $t) {
                $result[] = is_array($t) ?
                    array_merge([$v], $t) :
                    [$v, $t];
            }
        }

        if (count($result) > 50) {
            return array_slice($result, 0, 50);
        }

        return $result;
    }

    public function __clone()
    {
        if ($this->id) {
            $this->setID(null);
            $this->setProductID(null);
        }

        $options = $this->getOptions();
        $this->options = new ArrayCollection();
        if (count($options) > 0) {
            foreach ($options as $option) {
                $cloneOption = clone $option;
                $cloneOption->setVariation($this);
                $this->options->add($cloneOption);
            }
        }
    }
}
