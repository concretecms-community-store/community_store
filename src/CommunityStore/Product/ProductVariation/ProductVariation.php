<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductVariation;

use Concrete\Core\File\File;
use Concrete\Core\Support\Facade\Config;
use Doctrine\ORM\Mapping as ORM;
use Concrete\Core\Support\Facade\Application;
use Doctrine\Common\Collections\ArrayCollection;
use Concrete\Core\Support\Facade\DatabaseORM as dbORM;
use Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Price;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductOption\ProductOptionItem;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductVariation\ProductVariationOptionItem;

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
     * @ORM\ManyToOne(targetEntity="Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product", inversedBy="variations", cascade={"persist"})
     * @ORM\JoinColumn(name="pID", referencedColumnName="pID", onDelete="CASCADE")
     */
    protected $product;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    protected $pvPrice;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    protected $pvWholesalePrice;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    protected $pvCostPrice;

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
	 * @ORM\Column(type="boolean",nullable=true)
	 */
	protected $pvDisabled;

    /**
     * @ORM\OneToMany(targetEntity="Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductVariation\ProductVariationOptionItem", mappedBy="variation", cascade={"persist"}))
     */
    protected $options;

	public function getVariationDisabled()
	{
		return $this->pvDisabled ? 1 : 0;
	}

	public function setVariationDisabled($pvDisabled)
	{
		$this->pvDisabled = $pvDisabled ? 1 : 0;
	}

    public function getVariationFID()
    {
        return $this->pvfID;
    }

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

	/**
	 * @return ArrayCollection | ProductVariationOptionItem[]
	 */
    public function getOptions()
    {
        return $this->options;
    }

    public function getOptionItemIDs()
    {
        $options = $this->getOptions();

        $optionids = [];

        foreach ($options as $opt) {
            $optItem = $opt->getOptionItem();

            if ($optItem) {
                $optionids[] = $optItem->getID();
            }
        }

        sort($optionids);

        return $optionids;
    }

    public function getID()
    {
        return $this->pvID;
    }

    public function getProductID()
    {
        return $this->pID;
    }

    /**
     * @return \Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product
     */
    public function getProduct() {
        return $this->product;
    }

    public function setProduct($product) {
        $this->product = $product;
    }

    public function getVariationSKU()
    {
        return $this->pvSKU;
    }

    public function setVariationSKU($pvSKU)
    {
        $this->pvSKU = $pvSKU;
    }

    public function getVariationBarcode()
    {
        return $this->pvBarcode;
    }

    public function setVariationBarcode($pvBarcode)
    {
        $this->pvBarcode = $pvBarcode;
    }

    public function setProductID($pID)
    {
        $this->pID = $pID;
    }

    public function getVariationPrice()
    {
        return $this->pvPrice;
    }

    public function getVariationWholesalePrice()
    {
        return $this->pvWholesalePrice;
    }

    public function getVariationCostPrice()
    {
        return $this->pvCostPrice;
    }

    public function getFormattedVariationPrice()
    {
        return Price::format($this->pvPrice);
    }

    public function setVariationPrice($pvPrice)
    {
        if ('' != $pvPrice) {
            $this->pvPrice = (float) $pvPrice;
        } else {
            $this->pvPrice = null;
        }
    }

    public function setVariationWholesalePrice($pvWholesalePrice)
    {
        if ($pvWholesalePrice != '') {
            $this->pvWholesalePrice = (float)$pvWholesalePrice;
        } else {
            $this->pvWholesalePrice = null;
        }
    }

    public function setVariationCostPrice($pvCostPrice)
    {
        if ($pvCostPrice != '') {
            $this->pvCostPrice = (float)$pvCostPrice;
        } else {
            $this->pvCostPrice = null;
        }
    }

    public function getVariationSalePrice()
    {
        return $this->pvSalePrice;
    }

    public function getFormattedVariationSalePrice()
    {
        return Price::format($this->pvSalePrice);
    }

    public function setVariationSalePrice($pvSalePrice)
    {
        if ('' != $pvSalePrice) {
            $this->pvSalePrice = (float) $pvSalePrice;
        } else {
            $this->pvSalePrice = null;
        }
    }

    public function getStockLevel()
    {
        return $this->pvQty;
    }

    public function getMaxCartQty()
    {
        if ($this->isUnlimited()) {
            $available = false;
        } else {
            $available = $this->getStockLevel();
        }

        $maxcart = $this->product->getMaxQty();

        if ($maxcart > 0) {
            if ($available > 0) {
                return min($maxcart, $available);
            } else {
                return $maxcart;
            }
        } else {
            return $available;
        }
    }

    /**
     * @deprecated
     */
    public function getVariationQty()
    {
        return $this->getStockLevel();
    }

    public function setVariationStockLevel($pvQty)
    {
        $this->pvQty = $pvQty ? $pvQty : 0;
    }

    /**
     * @deprecated
     */
    public function setVariationQty($pvQty) {
        $this->setVariationStockLevel($pvQty);
    }

    /**
     * @return mixed
     */
    public function getVariationQtyUnlim()
    {
        return $this->pvQtyUnlim;
    }

    /**
     * @deprecated
     */
    public function setVariationQtyUnlim($pvQtyUnlim)
    {
        $this->pvQtyUnlim = $pvQtyUnlim;
    }

    public function setVariationIsUnlimited($bool)
    {
        $this->pvQtyUnlim = (!is_null($bool) ? $bool : false);
    }

    public function getVariationWidth()
    {
        return $this->pvWidth;
    }

    public function setVariationWidth($pvWidth)
    {
        if ('' != $pvWidth) {
            $this->pvWidth = (float) $pvWidth;
        } else {
            $this->pvWidth = null;
        }
    }

    public function getVariationHeight()
    {
        return $this->pvHeight;
    }

    public function setVariationHeight($pvHeight)
    {
        if ('' != $pvHeight) {
            $this->pvHeight = (float) $pvHeight;
        } else {
            $this->pvHeight = null;
        }
    }

    public function getVariationLength()
    {
        return $this->pvLength;
    }

    public function setVariationLength($pvLength)
    {
        if ('' != $pvLength) {
            $this->pvLength = (float) $pvLength;
        } else {
            $this->pvLength = null;
        }
    }

    public function getVariationWeight()
    {
        return $this->pvWeight;
    }

    public function setVariationWeight($pvWeight)
    {
        if ('' != $pvWeight) {
            $this->pvWeight = (float) $pvWeight;
        } else {
            $this->pvWeight = null;
        }
    }

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

    public function setVariationNumberItems($pvNumberItems)
    {
        if ('' != $pvNumberItems) {
            $this->pvNumberItems = (int) $pvNumberItems;
        } else {
            $this->pvNumberItems = null;
        }
    }

    public function getVariationSort()
    {
        return $this->pvSort;
    }

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
        if (!$this->product->isActive()) {
            return false;
        }

        $now = new \DateTime();
        $startAvailable = $this->product->getDateAvailableStart();
        $endAvailable = $this->product->getDateAvailableEnd();

        if ($startAvailable && $startAvailable >= $now) {
            return false;
        }

        if ($endAvailable && $now > $endAvailable) {
            return false;
        }

        if ($this->getVariationDisabled()) {
        	return false;
		}

        if ($this->isUnlimited() || $this->getStockLevel() > 0) {
            return true;
        } else {
            return false;
        }
    }

    public static function addVariations(array $data, Product $product)
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

        $variationDefaultUnlimited = Config::get('community_store.variationDefaultUnlimited');

        if (!empty($comboOptions) && isset($data['pVariations'])) {
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
                        $product,
                        [
                        'pvSKU' => '',
                        'pvBarcode' => '',
                        'pvPrice' => '',
                        'pvWholesalePrice'=>'',
                        'pvSalePrice' => '',
                        'pvQty' => 0,
                        'pvQtyUnlim' => ($variationDefaultUnlimited ? 1 : null) ,
                        'pvfID' => null,
                        'pvWeight' => '',
                        'pvNumberItems' => '',
                        'pvWidth' => '',
                        'pvHeight' => '',
                        'pvLength' => '',
                        'pvSort' => $sort,
                        'pvDisabled' => Config::get('community_store::products.newVariantDisable'),
                         true, ]
                    );

                    foreach ($optioncombo as $optionvalue) {
                        $option = ProductOptionItem::getByID($optionvalue);

                        if ($option) {
                            $variationoption = new ProductVariationOptionItem();
                            $variationoption->setOptionItem($option);
                            $variationoption->setVariation($variation);
                            $variationoption->save(true);
                        }
                    }
                } else {
                    $key = $variation->getID();

                    if (isset($data['pvSKU'][$key])) {
                        $variation->setVariationSKU($data['pvSKU'][$key]);
                        $variation->setVariationBarcode($data['pvBarcode'][$key]);
                        $variation->setVariationPrice($data['pvPrice'][$key]);
                        $variation->setVariationWholesalePrice($data['pvWholesalePrice'][$key]);
                        $variation->setVariationCostPrice($data['pvCostPrice'][$key]);
                        $variation->setVariationSalePrice($data['pvSalePrice'][$key]);
                        $variation->setVariationStockLevel($data['pvQty'][$key]);
                        $variation->setVariationIsUnlimited(isset($data['pvQtyUnlim'][$key]));
                        $variation->setVariationFID($data['pvfID'][$key] ? $data['pvfID'][$key] : null);
                        $variation->setVariationWeight($data['pvWeight'][$key]);
                        $variation->setVariationNumberItems($data['pvNumberItems'][$key]);
                        $variation->setVariationWidth($data['pvWidth'][$key]);
                        $variation->setVariationHeight($data['pvHeight'][$key]);
                        $variation->setVariationLength($data['pvLength'][$key]);
                        $variation->setVariationPackageData($data['pvPackageData'][$key]);
                        $variation->setVariationSort($sort);
                        if (isset($data['pvDisabled'][$key])) {
                            $variation->setVariationDisabled(1);
                        } else {
                            $variation->setVariationDisabled(0);
                        }
                        $variation->setProduct($product);
                        $variation->save(true);

                        $options = $variation->getOptions();

                        foreach ($options as $opt) {
                            if (!in_array($opt->getOptionItem()->getID(), $optioncombo)) {
                                $opt->delete();
                            }
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
            $options = implode(',', array_map('intval', $variationIDs));

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

    /**
	 * @return ProductVariation | null
	 */
    public static function getByID($pvID)
    {
        $em = dbORM::entityManager();

        return $em->find(__CLASS__, $pvID);
    }

    /**
     * @return ProductVariation | null
     */
    public static function getBySKU($pvSKU)
    {
        $em = dbORM::entityManager();

        return $em->getRepository(__CLASS__)->findOneBy(['pvSKU' => $pvSKU]);
    }

    public static function add($product, $data, $persistonly = false)
    {
        $variation = new self();
        $variation->setProduct($product);
        $variation->setVariationSKU($data['pvSKU']);
        $variation->setVariationBarcode($data['pvBarcode']);
        $variation->setVariationPrice($data['pvPrice']);
        $variation->setVariationWholesalePrice($data['pvWholesalePrice']);
        $variation->setVariationSalePrice($data['pvSalePrice']);
        $variation->setVariationStockLevel($data['pvQty']);
        $variation->setVariationIsUnlimited($data['pvQtyUnlim']);
        $variation->setVariationFID($data['pvfID']);
        $variation->setVariationWeight($data['pvWidth']);
        $variation->setVariationNumberItems($data['pvNumberItems']);
        $variation->setVariationHeight($data['pvHeight']);
        $variation->setVariationLength($data['pvLength']);
        $variation->setVariationWidth($data['pvWeight']);
        $variation->setVariationPackageData(isset($data['pvPackageData']) ? $data['pvPackageData'] : '');
        $variation->setVariationSort($data['pvSort']);
        $variation->setVariationDisabled(isset($data['pvDisabled']) ? $data['pvDisabled'] : Config::get('community_store::products.newVariantDisable'));
        $product->getVariations()->add($variation);
        $variation->save($persistonly);

        return $variation;
    }

    public static function getByOptionItemIDs(array $optionids)
    {
        $app = Application::getFacadeApplication();
        $db = $app->make('database')->connection();

        if (is_array($optionids) && !empty($optionids)) {
            $options = implode(',', array_map('intval', $optionids));

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
            $product = $this->getProduct();
            $product->setDateUpdated(new \DateTime());
            $product->save();

            $em->flush();
        }
    }

    public function delete()
    {
        $product = $this->getProduct();
        $product->getVariations()->removeElement($this);
        $em = dbORM::entityManager();
        $em->remove($this);
        $em->flush();
    }

    public static function removeVariationsForProduct(Product $product, $excluding = [])
    {
        if (!is_array($excluding)) {
            $excluding = [];
        }

        //clear out existing product option groups
        $existingVariations = $product->getVariations();
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

        $max = Config::get('community_store.variationMaxVariations') ?: 50;
        if (count($result) > $max) {
            return array_slice($result, 0, $max);
        }

        return $result;
    }

    public function __clone()
    {
        if (isset($this->id) && $this->id) {
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
