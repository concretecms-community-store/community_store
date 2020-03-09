<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Product;

use Concrete\Package\CommunityStore\Src\CommunityStore\Manufacturer\Manufacturer;
use Doctrine\ORM\Mapping as ORM;
use Concrete\Core\Support\Facade\DatabaseORM as dbORM;
use Concrete\Core\Package\Package;
use Concrete\Core\Page\Page;
use Concrete\Core\Page\Type\Type as PageType;
use Concrete\Core\Page\Template as PageTemplate;
use Concrete\Core\File\File;
use Concrete\Core\Support\Facade\Config;
use Concrete\Core\Support\Facade\Events;
use Doctrine\Common\Collections\ArrayCollection;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductImage as StoreProductImage;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductGroup as StoreProductGroup;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductUserGroup as StoreProductUserGroup;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductFile as StoreProductFile;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductLocation as StoreProductLocation;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductOption\ProductOption as StoreProductOption;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductOption\ProductOptionItem as StoreProductOptionItem;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductVariation\ProductVariation as StoreProductVariation;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductRelated as StoreProductRelated;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductEvent as StoreProductEvent;
use Concrete\Package\CommunityStore\Src\CommunityStore\Tax\TaxClass as StoreTaxClass;
use Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Price as StorePrice;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Wholesale;
use Concrete\Core\Support\Facade\Application;
use Concrete\Package\CommunityStore\Src\CommunityStore\Shipping\Package as StorePackage;
use Concrete\Package\CommunityStore\Entity\Attribute\Value\StoreProductValue;
use Concrete\Core\Multilingual\Page\Section\Section;

use \Concrete\Core\Attribute\ObjectTrait;

/**
 * @ORM\Entity
 * @ORM\Table(name="CommunityStoreProducts")
 */
class Product
{
    // not stored, used for price/sku/etc lookup purposes
    public $adjustment = 0;
    public $variation;

    use ObjectTrait;
    /**
     * @ORM\Id @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $pID;

    /**
     * @ORM\Column(type="integer",nullable=true)
     */
    protected $cID;

    /**
     * @ORM\Column(type="string")
     */
    protected $pName;

    /**
     * @ORM\Column(type="string",nullable=true)
     */
    protected $pSKU;

    /**
     * @ORM\Column(type="text",nullable=true)
     */
    protected $pBarcode;

    /**
     * @ORM\Column(type="text",nullable=true)
     */
    protected $pDesc;

    /**
     * @ORM\Column(type="text",nullable=true)
     */
    protected $pDetail;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    protected $pPrice;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    protected $pWholesalePrice;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    protected $pSalePrice;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $pCustomerPrice;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    protected $pPriceMaximum;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    protected $pPriceMinimum;

    /**
     * @ORM\Column(type="text",nullable=true)
     */
    protected $pPriceSuggestions;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $pQuantityPrice;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $pFeatured;

    /**
     * @ORM\Column(type="decimal", precision=12, scale=4)
     */
    protected $pQty;

    /**
     * @ORM\Column(type="boolean",nullable=true)
     */
    protected $pQtyUnlim;

    /**
     * @ORM\Column(type="boolean",nullable=true)
     */
    protected $pBackOrder;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $pNoQty;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $pAllowDecimalQty;

    /**
     * @ORM\Column(type="decimal", precision=5, scale=4, nullable=true)
     */
    protected $pQtySteps;

    /**
     * @ORM\Column(type="string")
     */
    protected $pQtyLabel;

    /**
     * @ORM\Column(type="string")
     */
    protected $pMaxQty;

    /**
     * @ORM\Column(type="integer",nullable=true)
     */
    protected $pTaxClass;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $pTaxable;

    /**
     * @ORM\Column(type="integer")
     */
    protected $pfID;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $pActive;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $pDateAdded;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $pShippable;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2,nullable=true)
     */
    protected $pWidth;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2,nullable=true)
     */
    protected $pHeight;

	/**
	 * @ORM\Column(type="decimal", precision=10, scale=2,nullable=true)
	 */
	protected $pStackedHeight;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2,nullable=true)
     */
    protected $pLength;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2,nullable=true)
     */
    protected $pWeight;

    /**
     * @ORM\Column(type="integer",nullable=true)
     */
    protected $pNumberItems;

    /**
     * @ORM\Column(type="boolean",nullable=true)
     */
    protected $pSeperateShip;

    /**
     * @ORM\Column(type="text",nullable=true)
     */
    protected $pPackageData;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $pCreateUserAccount;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $pAutoCheckout;

    /**
     * @ORM\Column(type="integer")
     */
    protected $pExclusive;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $pVariations;

    /**
     * @ORM\Column(type="text")
     */
    protected $pNotificationEmails;

    /**
     * @ORM\ManyToOne(targetEntity="Concrete\Package\CommunityStore\Src\CommunityStore\Manufacturer\Manufacturer",inversedBy="products",cascade={"persist"})
     * @ORM\JoinColumn(name="pManufacturer", referencedColumnName="mID", onDelete="CASCADE")
     */
    protected $manufacturer;


    /**
     * @ORM\OneToMany(targetEntity="Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductLocation", mappedBy="product",cascade={"persist"}))
     */
    protected $locations;

    public function getLocations()
    {
        return $this->locations;
    }

    /**
     * @ORM\OneToMany(targetEntity="Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductGroup", mappedBy="product",cascade={"persist"})
     */
    protected $groups;

    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * @ORM\OneToMany(targetEntity="Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductFile", mappedBy="product",cascade={"persist"}))
     */
    protected $files;

    public function getFiles()
    {
        return $this->files;
    }

    /**
     * @ORM\OneToMany(targetEntity="Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductImage", mappedBy="product",cascade={"persist"}))
     */
    protected $images;

    public function getImages()
    {
        return $this->images;
    }

    /**
     * @ORM\OneToMany(targetEntity="Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductUserGroup", mappedBy="product",cascade={"persist"}))
     */
    protected $userGroups;

    public function getUserGroups()
    {
        return $this->userGroups;
    }

    /**
     * @ORM\OneToMany(targetEntity="Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductOption\ProductOption", mappedBy="product",cascade={"persist"}))
     * @ORM\OrderBy({"poSort" = "ASC"})
     */
    protected $options;

    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @ORM\OneToMany(targetEntity="Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductRelated", mappedBy="product",cascade={"persist"}))
     */
    protected $related;

    public function getRelatedProducts()
    {
        return $this->related;
    }

    /**
     * @ORM\OneToMany(targetEntity="Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductPriceTier", mappedBy="product", cascade={"persist"}))
     * @ORM\OrderBy({"ptFrom" = "ASC"})
     */
    protected $priceTiers;

    public function getPriceTiers()
    {
        return $this->priceTiers;
    }

    protected $discountRules;

    protected $discountRuleIDs;


    public function clearDiscountRules() {
        $this->discountRules = [];
        $this->discountRuleIDs = [];
    }

    public function addDiscountRules($rules)
    {
        foreach ($rules as $rule) {
            $this->addDiscountRule($rule);
        }
    }

    public function addDiscountRule($discountRule)
    {
        if (!is_array($this->discountRules)) {
            $this->discountRules = [];
            $this->discountRuleIDs = [];
        }

        //add only if rule hasn't been added before
        if (!in_array($discountRule->getID(), $this->discountRuleIDs)) {
            $discountProductGroups = $discountRule->getProductGroups();
            $include = false;

            if (!empty($discountProductGroups)) {
                $groupids = $this->getGroupIDs();
                if (count(array_intersect($discountProductGroups, $groupids)) > 0) {
                    $include = true;
                }
            } else {
                $include = true;
            }

            if ($include) {
                $this->discountRules[] = $discountRule;
                $this->discountRuleIDs[] = $discountRule->getID();
            }
        }
    }

    public function getDiscountRules()
    {
        return is_array($this->discountRules) ? $this->discountRules : [];
    }

    public function __construct()
    {
        $this->locations = new ArrayCollection();
        $this->groups = new ArrayCollection();
        $this->files = new ArrayCollection();
        $this->images = new ArrayCollection();
        $this->userGroups = new ArrayCollection();
        $this->options = new ArrayCollection();
        $this->related = new ArrayCollection();
        $this->priceTiers = new ArrayCollection();
    }

    public function setAdjustment($adjustment){
        $this->adjustment = $adjustment;
    }

    public function getAdjustment(){
        return $this->adjustment;
    }

    public function setVariation($variation)
    {
        if (is_object($variation)) {
            $this->variation = $variation;
        } elseif (is_integer($variation)) {
            $variation = StoreProductVariation::getByID($variation);

            if ($variation) {
                $this->variation = $variation;
            } else {
                $this->variation = null;
            }
        }
    }

    public function removeVariation()
    {
        $this->variation = null;
    }

    public function setInitialVariation()
    {
        if ($this->hasVariations()) {
            $options = $this->getOptions();
            $optionkeys = [];

            foreach ($options as $option) {
                if ($option->getIncludeVariations()) {
                    $optionItems = $option->getOptionItems();
                    foreach ($optionItems as $optionItem) {
                        if (!$optionItem->isHidden()) {
                            $optionkeys[] = $optionItem->getID();
                            break;
                        }
                    }
                }
            }

            $this->setVariation(StoreProductVariation::getByOptionItemIDs($optionkeys));
        }
    }

    public function getVariation()
    {
        return $this->variation;
    }

    public function setCollectionID($cID)
    {
        $this->cID = $cID;
    }

    public function setName($name)
    {
        $this->pName = $name;
    }

    public function setSKU($sku)
    {
        $this->pSKU = $sku;
    }

    public function getBarcode()
    {
        if ($this->hasVariations() && $variation = $this->getVariation()) {
            if ($variation) {
                $vbarcode = $variation->getVariationBarcode();

                if ($vbarcode) {
                    return $vbarcode;
                } else {
                    return $this->pBarcode;
                }
            }
        } else {
            return $this->pBarcode;
        }
    }

    public function setBarcode($barcode)
    {
        $this->pBarcode = $barcode;
    }

    public function setDescription($description)
    {
        $this->pDesc = $description;
    }

    public function setDetail($detail)
    {
        $this->pDetail = $detail;
    }

    public function setPrice($price)
    {
        $this->pPrice = ('' != $price ? $price : 0);
    }
    public function setWholesalePrice($price)
    {
        $this->pWholesalePrice = ($price != '' ? $price : 0);
    }
    public function setSalePrice($price)
    {
        $this->pSalePrice = ('' != $price ? $price : null);
    }

    public function setCustomerPrice($bool)
    {
        $this->pCustomerPrice = (!is_null($bool) ? $bool : false);
    }

    public function getPriceMaximum()
    {
        return $this->pPriceMaximum;
    }

    public function setPriceMaximum($pPriceMaximum)
    {
        $this->pPriceMaximum = '' != $pPriceMaximum ? $pPriceMaximum : null;
    }

    public function getPriceMinimum()
    {
        return $this->pPriceMinimum;
    }

    public function setPriceMinimum($pPriceMinimum)
    {
        $this->pPriceMinimum = '' != $pPriceMinimum ? $pPriceMinimum : null;
    }

    public function getPriceSuggestions()
    {
        return $this->pPriceSuggestions;
    }

    public function getPriceSuggestionsArray()
    {
        return array_filter(array_map('trim', explode(',', trim($this->pPriceSuggestions))));
    }

    public function setPriceSuggestions($priceSuggestions)
    {
        $this->pPriceSuggestions = $priceSuggestions;
    }

    public function setIsFeatured($bool)
    {
        $this->pFeatured = (!is_null($bool) ? $bool : false);
    }

    public function setQty($qty)
    {
        if ($qty > 99999999.9999) {
            $qty = 99999999.9999;
        }

        $this->pQty = ($qty ? $qty : 0);
    }

    public function setIsUnlimited($bool)
    {
        $this->pQtyUnlim = (!is_null($bool) ? $bool : false);
    }

    public function setAllowBackOrder($bool)
    {
        $this->pBackOrder = (!is_null($bool) ? $bool : false);
    }

    public function setNoQty($bool)
    {
        $this->pNoQty = $bool;
    }

    public function getPID()
    {
        return $this->pID;
    }

    public function setPID($pID)
    {
        $this->pID = $pID;
    }

    public function getAllowDecimalQty()
    {
        return '1' == $this->pAllowDecimalQty;
    }

    public function allowDecimalQuantity()
    {
        return $this->getAllowDecimalQty();
    }

    public function setAllowDecimalQty($pAllowDecimalQty)
    {
        $this->pAllowDecimalQty = $pAllowDecimalQty;
    }

    public function getQtySteps()
    {
        return round($this->pQtySteps, 4);
    }

    public function setQtySteps($pQtySteps)
    {
        $this->pQtySteps = $pQtySteps;
    }

    public function getQtyLabel()
    {
        return $this->pQtyLabel;
    }

    public function setQtyLabel($pQtyLabel)
    {
        $this->pQtyLabel = $pQtyLabel;
    }

    public function getMaxQty()
    {
        return $this->pMaxQty;
    }

    public function setMaxQty($pMaxQty)
    {
        $this->pMaxQty = $pMaxQty;
    }

    public function setTaxClass($taxClass)
    {
        $this->pTaxClass = $taxClass;
    }

    public function setIsTaxable($bool)
    {
        $this->pTaxable = (!is_null($bool) ? $bool : false);
    }

    public function setImageID($fID)
    {
        $this->pfID = $fID;
    }

    public function setIsActive($bool)
    {
        $this->pActive = $bool;
    }

    public function setDateAdded($date)
    {
        $this->pDateAdded = $date;
    }

    public function setIsShippable($bool)
    {
        $this->pShippable = (!is_null($bool) ? $bool : false);
    }

    public function setSeperateShip($bool)
    {
        $this->pSeperateShip = (!is_null($bool) ? $bool : false);
    }

    public function getPackageData()
    {
        if ($this->hasVariations() && $variation = $this->getVariation()) {
            return $variation->getVariationPackageData();
        } else {
            return $this->pPackageData;
        }
    }

    public function setPackageData($pPackageData)
    {
        $this->pPackageData = trim($pPackageData);
    }

    public function getSeperateShip()
    {
        return $this->pSeperateShip;
    }

    public function isSeperateShip()
    {
        return (bool) $this->getSeperateShip();
    }

    public function setWidth($width)
    {
        $this->pWidth = (float) $width;
    }

    public function setHeight($height)
    {
        $this->pHeight = (float) $height;
    }

	public function setStackedHeight($height)
	{
		$this->pStackedHeight = (float) $height;
	}

    public function setLength($length)
    {
        $this->pLength = (float) $length;
    }

    public function setWeight($weight)
    {
        $this->pWeight = (float) $weight;
    }

    public function setNumberItems($number)
    {
        $this->pNumberItems = ('' != $number ? $number : null);
    }

    public function setCreatesUserAccount($bool)
    {
        $this->pCreateUserAccount = (!is_null($bool) ? $bool : false);
    }

    public function setAutoCheckout($bool)
    {
        $this->pAutoCheckout = (!is_null($bool) ? $bool : false);
    }

    public function setIsExclusive($bool)
    {
        $this->pExclusive = (!is_null($bool) ? $bool : false);
    }

    public function setHasVariations($bool)
    {
        $this->pVariations = (!is_null($bool) ? $bool : false);
    }

    public function getNotificationEmails()
    {
        return $this->pNotificationEmails;
    }

    public function getNotificationEmailsArray() {
        if ($this->pNotificationEmails) {
            $notificationEmails = explode(',', $this->pNotificationEmails);
            return array_map('trim', $notificationEmails);
        } else {
            return array();
        }
    }

    public function setNotificationEmails($pNotificationEmails)
    {
        $this->pNotificationEmails = trim($pNotificationEmails);
    }

    public function getManufacturer()
    {
        return $this->manufacturer;
    }

    public function setManufacturer($manufacturer)
    {
        $this->manufacturer = $manufacturer;
    }

    public function updateProductQty($qty)
    {
        if ($this->hasVariations() && $variation = $this->getVariation()) {
            if ($variation) {
                $variation->setVariationQty($qty);
                $variation->save();
            }
        } else {
            $this->setQty($qty);
            $this->save();
        }
    }

	/**
	 * @return \Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product
	 */
    public static function getByID($pID)
    {
        $em = dbORM::entityManager();

        return $em->find(get_class(), $pID);
    }

	/**
	 * @return \Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product
	 */
    public static function getBySKU($pSKU)
    {
        $em = dbORM::entityManager();

        return $em->getRepository(get_class())->findOneBy(['pSKU' => $pSKU]);
    }

	/**
	 * @return \Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product
	 */
    public static function getByCollectionID($cID)
    {
        $em = dbORM::entityManager();

        return $em->getRepository(get_class())->findOneBy(['cID' => $cID]);
    }

    public function getAttributes()
    {
        return $this->getObjectAttributeCategory()->getAttributeValues($this);
    }

    public static function saveProduct($data)
    {
        if ($data['pID']) {
            //if we know the pID, we're updating.
            $product = self::getByID($data['pID']);
            $product->setPageDescription($data['pDesc']);

            if ($data['pDateAdded_dt']) {
                $product->setDateAdded(new \DateTime($data['pDateAdded_dt'] . ' ' . $data['pDateAdded_h'] . ':' . $data['pDateAdded_m']));
            }
        } else {
            //else, we don't know it and we're adding a new product
            $product = new self();
            $product->setDateAdded(new \DateTime());
        }
        $product->setName($data['pName']);
        $product->setSKU($data['pSKU']);
        $product->setBarCode($data['pBarcode']);
        $product->setDescription($data['pDesc']);
        $product->setDetail($data['pDetail']);
        $product->setPrice($data['pPrice']);
        $product->setWholesalePrice($data['pWholesalePrice']);
        $product->setSalePrice($data['pSalePrice']);
        $product->setIsFeatured($data['pFeatured']);
        $product->setQty($data['pQty']);
        $product->setIsUnlimited($data['pQtyUnlim']);
        $product->setAllowBackOrder($data['pBackOrder']);
        $product->setNoQty($data['pNoQty']);
        $product->setTaxClass($data['pTaxClass']);
        $product->setIsTaxable($data['pTaxable']);
        $product->setImageID($data['pfID']);
        $product->setIsActive($data['pActive']);
        $product->setCreatesUserAccount($data['pCreateUserAccount']);
        $product->setIsShippable($data['pShippable']);
        $product->setWidth($data['pWidth']);
        $product->setHeight($data['pHeight']);
        $product->setStackedHeight($data['pStackedHeight']);
        $product->setLength($data['pLength']);
        $product->setWeight($data['pWeight']);
        $product->setPackageData($data['pPackageData']);
        $product->setNumberItems($data['pNumberItems']);
        $product->setSeperateShip($data['pSeperateShip']);
        $product->setAutoCheckout($data['pAutoCheckout']);
        $product->setIsExclusive($data['pExclusive']);
        $product->setCustomerPrice($data['pCustomerPrice']);
        $product->setPriceSuggestions($data['pPriceSuggestions']);
        $product->setPriceMaximum($data['pPriceMaximum']);
        $product->setPriceMinimum($data['pPriceMinimum']);
        $product->setQuantityPrice($data['pQuantityPrice']);
        $product->setAllowDecimalQty($data['pAllowDecimalQty']);
        $product->setQtySteps($data['pQtySteps'] > 0 ? $data['pQtySteps'] : null);
        $product->setQtyLabel($data['pQtyLabel']);
        $product->setMaxQty($data['pMaxQty']);
        $product->setPageID($data['pageCID']);
        $product->setNotificationEmails($data['pNotificationEmails']);

        if ($data['pManufacturer']) {
            $manufacturer = Manufacturer::getByID($data['pManufacturer']);
        } else {
            $manufacturer = null;
        }

        $product->setManufacturer($manufacturer);


        // if we have no product groups, we don't have variations to offer
        if (empty($data['poName'])) {
            $product->setHasVariations(0);
        } else {
            $product->setHasVariations($data['pVariations']);
        }

        $product->save();
        if (!$data['pID']) {
            $product->generatePage($data['selectPageTemplate']);
        } else {
            $product->updatePage();
        }

        return $product;
    }

    public function getID()
    {
        return $this->pID;
    }

    public function setID($id)
    {
        $this->pID = $id;
    }

    public function getName()
    {
        return $this->pName;
    }

    public function getSKU()
    {
        if ($this->hasVariations() && $variation = $this->getVariation()) {
            if ($variation) {
                $varsku = $variation->getVariationSKU();

                if ($varsku) {
                    return $varsku;
                } else {
                    return $this->pSKU;
                }
            }
        } else {
            return $this->pSKU;
        }
    }

    public function getPageID()
    {
        return $this->cID;
    }

    public function getProductPage()
    {
        if ($this->getPageID()) {
            $pageID = $this->getPageID();
            $productPage = Page::getByID($pageID);
            if ($productPage && !$productPage->isError() && !$productPage->isInTrash()) {

                $c = Page::getCurrentPage();
                $lang = Section::getBySectionOfSite($c);

                if (is_object($lang)) {
                    $relatedID = $lang->getTranslatedPageID($productPage);

                    if ($relatedID && $relatedID != $pageID) {
                        $translatedPage = Page::getByID($relatedID);

                        if ($translatedPage && !$translatedPage->isError() && !$translatedPage->isInTrash()) {
                            $productPage = $translatedPage;
                        }
                    }
                }

                return $productPage;
            }
        }

        return false;
    }

    public function getDescription()
    {
        return $this->pDesc;
    }

    public function getDesc()
    {
        return $this->pDesc;
    }

    public function getDetail()
    {
        return $this->pDetail;
    }

    public function getBasePrice()
    {
        return $this->pPrice;
    }

    // set ignoreDiscounts to true to get the undiscounted price
    public function getPrice($qty = 1, $ignoreDiscounts = false)
    {
        if ($this->hasVariations() && $variation = $this->getVariation()) {
            if ($variation) {
                $varprice = $variation->getVariationPrice();

                if ($varprice) {
                    $price = $varprice;
                } else {
                    $price = $this->getQuantityAdjustedPrice($qty);
                }
            }
        } else {
            $price = $this->getQuantityAdjustedPrice($qty);
        }

        $discounts = $this->getDiscountRules();

        if (!$ignoreDiscounts) {
            if (!empty($discounts)) {
                foreach ($discounts as $discount) {
                    $discount->setApplicableTotal($price);
                    $discountedprice = $discount->returnDiscountedPrice();

                    if (false !== $discountedprice) {
                        $price = $discountedprice;
                    }
                }
            }
        }

        return $price + $this->getAdjustment();
    }

    public function getWholesalePrice($qty = 1)
    {
        if ($this->hasVariations() && $variation = $this->getVariation()) {
            if ($variation) {
                $varWholesalePrice = $variation->getVariationWholesalePrice();

                if ($varWholesalePrice) {
                    return $varWholesalePrice;
                }
            }
        }
        return $this->pWholesalePrice;
    }

    private function getQuantityAdjustedPrice($qty = 1) {
        if ($this->hasQuantityPrice()) {
            $priceTiers = $this->getPriceTiers();

            if (count($priceTiers) > 0) {
                foreach ($priceTiers as $pt) {
                    if ($qty >= $pt->getFrom() && $qty <= $pt->getTo()) {
                        return $pt->getPrice();
                    }
                }

                if ($qty >= $pt->getFrom()) {
                    return $pt->getPrice();
                }
            }
        }

        return $this->pPrice;
    }

    public function getFormattedOriginalPrice()
    {
        return StorePrice::format($this->getPrice(1));
    }

    public function getFormattedPrice()
    {
        return StorePrice::format($this->getActivePrice());
    }

    public function getFormattedWholesalePrice()
    {
        return StorePrice::format($this->getWholesalePrice());
    }

    public function getSalePrice()
    {
        if ($this->hasVariations() && $variation = $this->getVariation()) {
            if ($variation) {
                $varprice = $variation->getVariationSalePrice();
                if ($varprice) {
                    return $varprice;
                } else {
                    return $this->pSalePrice;
                }
            }
        } else {
            return $this->pSalePrice;
        }
    }

    public function getFormattedSalePrice()
    {
        $saleprice = $this->getSalePrice();

        if ('' != $saleprice) {
            return StorePrice::format($saleprice);
        }
    }

    public function getActivePrice($qty = 1)
    {
        if(Wholesale::isUserWholesale()){
            return $this->getWholesalePrice();
        } else {
            $salePrice = $this->getSalePrice();
            if ($salePrice != "") {
                return $salePrice;
            }
            return $this->getPrice($qty);
        }
    }

    public function getFormattedActivePrice($qty = 1)
    {
        return StorePrice::format($this->getActivePrice($qty));
    }

    public function getTaxClassID()
    {
        return $this->pTaxClass;
    }

    public function getTaxClass()
    {
        return StoreTaxClass::getByID($this->pTaxClass);
    }

    public function isTaxable()
    {
        return (bool) $this->pTaxable;
    }

    public function isFeatured()
    {
        return (bool) $this->pFeatured;
    }

    public function isActive()
    {
        return (bool) $this->pActive;
    }

    public function isShippable()
    {
        return (bool) $this->pShippable;
    }

    public function allowCustomerPrice()
    {
        return (bool) $this->pCustomerPrice;
    }

    public function hasQuantityPrice()
    {
        return (bool) $this->pQuantityPrice;
    }

    public function getQuantityPrice()
    {
        return $this->pQuantityPrice;
    }

    public function setQuantityPrice($bool)
    {
        $this->pQuantityPrice = (!is_null($bool) ? $bool : false);
    }

    public function getDimensions($whl = null)
    {
        $width = $this->getWidth();
        $height = $this->getHeight();
        $length = $this->getLength();

        if ($this->hasVariations() && $variation = $this->getVariation()) {
            $varWidth = $variation->getVariationWidth();
            $varHeight = $variation->getVariationHeight();
            $varLength = $variation->getVariationLength();

            if ('' != $varWidth) {
                $width = $varWidth;
            }

            if ('' != $varHeight) {
                $height = $varHeight;
            }

            if ('' != $varLength) {
                $length = $varLength;
            }
        }

        switch ($whl) {
            case "w":
                return $width;
                break;
            case "h":
                return $height;
                break;
            case "l":
                return $length;
                break;
            default:

                $dimensions = [];

                if ($length > 0) {
                    $dimensions[] = $length;
                }

                if ($width > 0) {
                    $dimensions[] = $width;
                }

                if ($height > 0) {
                    $dimensions[] = $height;
                }

                return implode('&times;', $dimensions);
                break;
        }
    }

    public function getWidth()
    {
        if ($this->hasVariations() && $variation = $this->getVariation()) {
            $width = $variation->getVariationWidth();

            if ($width) {
                return $width;
            }
        }

        return $this->pWidth;
    }

    public function getHeight()
    {
        if ($this->hasVariations() && $variation = $this->getVariation()) {
            $height = $variation->getVariationHeight();

            if ($height) {
                return $height;
            }
        }

        return $this->pHeight;
    }


	public function getStackedHeight()
	{
		return $this->pStackedHeight;
	}

    public function getLength()
    {
        if ($this->hasVariations() && $variation = $this->getVariation()) {
            $length = $variation->getVariationLength();

            if ($length) {
                return $length;
            }
        }

        return $this->pLength;
    }

    public function getWeight()
    {
        $weight = $this->pWeight;

        if ($this->hasVariations() && $variation = $this->getVariation()) {
            $varWeight = $variation->getVariationWeight();
            if ($varWeight) {
                return $varWeight;
            }
        }

        return $weight;
    }

    public function getNumberItems()
    {
        $numberItems = $this->pNumberItems;

        if ($this->hasVariations() && $variation = $this->getVariation()) {
            $varNumberItems = $variation->getVariationNumberItems();

            if ($varNumberItems) {
                return $varNumberItems;
            } else {
                return $numberItems;
            }
        } else {
            return $numberItems;
        }
    }

    public function getPackages()
    {
        $packages = [];

        $packagedata = $this->getPackageData();

        if ($packagedata) {
            $lines = explode("\n", $packagedata);

            foreach ($lines as $line) {
                $line = strtolower($line);
                $line = str_replace('x', ' ', $line);
                $line = str_replace('-', ' ', $line);
                $values = preg_split('/[\s]+/', $line);

                $package = new StorePackage();
                $package->setWeight($values[0]);
                $package->setWidth($values[1]);
                $package->setHeight($values[2]);
                $package->setLength($values[3]);

                $packages[] = $package;
            }
        } else {
            $package = new StorePackage();
            $package->setWeight($this->getWeight());
            $package->setWidth($this->getLength());
            $package->setHeight($this->getWidth());
            $package->setLength($this->getHeight());

            $packages[] = $package;
        }

        return $packages;
    }

    public function getImageID()
    {
        if ($this->hasVariations() && $variation = $this->getVariation()) {
            $id = $variation->getVariationImageID();
            if (!$id) {
                return $this->pfID;
            } else {
                return $id;
            }
        } else {
            return $this->pfID;
        }
    }

    public function getImageObj()
    {
        if ($this->getImageID()) {
            $fileObj = File::getByID($this->getImageID());

            return $fileObj;
        }
    }

    public function getBaseProductImageID()
    {
        return $this->pfID;
    }

    public function getBaseProductImageObj()
    {
        if ($this->getBaseProductImageID()) {
            $fileObj = File::getByID($this->getBaseProductImageID());

            return $fileObj;
        }
    }

    public function hasDigitalDownload()
    {
        return count($this->getDownloadFiles()) > 0 ? true : false;
    }

    public function getDownloadFiles()
    {
        return StoreProductFile::getFilesForProduct($this);
    }

    public function getDownloadFileObjects()
    {
        return StoreProductFile::getFileObjectsForProduct($this);
    }

    public function createsLogin()
    {
        return (bool) $this->pCreateUserAccount;
    }

    public function allowQuantity()
    {
        return !(bool) $this->pNoQty;
    }

    public function isExclusive()
    {
        return (bool) $this->pExclusive;
    }

    public function hasVariations()
    {
        return (bool) $this->pVariations;
    }

    public function isUnlimited()
    {
        if ($this->hasVariations() && $variation = $this->getVariation()) {
            return $variation->isUnlimited();
        } else {
            return (bool) $this->pQtyUnlim;
        }
    }

    public function autoCheckout()
    {
        return (bool) $this->pAutoCheckout;
    }

    public function allowBackOrders()
    {
        return (bool) $this->pBackOrder;
    }

    public function hasUserGroups()
    {
        return count($this->getUserGroups()) > 0 ? true : false;
    }

    public function getUserGroupIDs()
    {
        return StoreProductUserGroup::getUserGroupIDsForProduct($this);
    }

    public function getImage()
    {
        $fileObj = $this->getImageObj();
        if (is_object($fileObj)) {
            return "<img src='" . $fileObj->getRelativePath() . "'>";
        }
    }

    public function getImageThumb()
    {
        $fileObj = $this->getImageObj();
        if (is_object($fileObj)) {
            return "<img src='" . $fileObj->getThumbnailURL('file_manager_listing') . "'>";
        }
    }

    public function getQty()
    {
        if ($this->hasVariations() && $variation = $this->getVariation()) {
            return $variation->getVariationQty();
        } else {
            return $this->pQty;
        }
    }

    public function getMaxCartQty()
    {
        if ($this->allowBackOrders() || $this->isUnlimited()) {
            $available = false;
        } else {
            $available = $this->getQty();
        }

        $maxcart = $this->getMaxQty();

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

    public function isSellable()
    {
        if (!$this->isActive()) {
            return false;
        }

        if ($this->hasVariations() && $variation = $this->getVariation()) {
            return $variation->isSellable();
        } else {
            if ($this->getQty() > 0 || $this->isUnlimited()) {
                return true;
            } else {
                if ($this->allowBackOrders()) {
                    return true;
                } else {
                    return false;
                }
            }
        }
    }

    public function getimagesobjects()
    {
        return StoreProductImage::getImageObjectsForProduct($this);
    }

    public function getLocationPages()
    {
        return StoreProductLocation::getLocationsForProduct($this);
    }

    public function getGroupIDs()
    {
        return StoreProductGroup::getGroupIDsForProduct($this);
    }

    public function getVariations()
    {
        return StoreProductVariation::getVariationsForProduct($this);
    }

    public function getDateAdded()
    {
        return $this->pDateAdded;
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

    public function remove()
    {
        // create product event and dispatch
        $event = new StoreProductEvent($this);
        Events::dispatch(StoreProductEvent::PRODUCT_DELETE, $event);

        StoreProductImage::removeImagesForProduct($this);
        StoreProductOption::removeOptionsForProduct($this);
        StoreProductOptionItem::removeOptionItemsForProduct($this);
        StoreProductFile::removeFilesForProduct($this);
        StoreProductGroup::removeGroupsForProduct($this);
        StoreProductLocation::removeLocationsForProduct($this);
        StoreProductUserGroup::removeUserGroupsForProduct($this);
        StoreProductVariation::removeVariationsForProduct($this);

        $em = dbORM::entityManager();
        $attributes = $this->getAttributes();

        foreach($attributes as $attribute) {
            $em->remove($attribute);
        }

        $em->remove($this);
        $em->flush();

        $page = Page::getByID($this->cID);
        if (is_object($page)) {
            $page->delete();
        }
    }

    public function __clone()
    {
        if ($this->shallowClone) {
            return;
        }

        if ($this->pID) {
            $this->setId(null);
            $this->setPageID(null);

            $locations = $this->getLocations();
            $this->locations = new ArrayCollection();
            if (count($locations) > 0) {
                foreach ($locations as $loc) {
                    $cloneLocation = clone $loc;
                    $this->locations->add($cloneLocation);
                    $cloneLocation->setProduct($this);
                }
            }

            $groups = $this->getGroups();
            $this->groups = new ArrayCollection();
            if (count($groups) > 0) {
                foreach ($groups as $group) {
                    $cloneGroup = clone $group;
                    $this->groups->add($cloneGroup);
                    $cloneGroup->setProduct($this);
                }
            }

            $images = $this->getImages();
            $this->images = new ArrayCollection();
            if (count($images) > 0) {
                foreach ($images as $image) {
                    $cloneImage = clone $image;
                    $this->images->add($cloneImage);
                    $cloneImage->setProduct($this);
                }
            }

            $files = $this->getFiles();
            $this->files = new ArrayCollection();
            if (count($files) > 0) {
                foreach ($files as $file) {
                    $cloneFile = clone $file;
                    $this->files->add($cloneFile);
                    $cloneFile->setProduct($this);
                }
            }

            $userGroups = $this->getUserGroups();
            $this->userGroups = new ArrayCollection();
            if (count($userGroups) > 0) {
                foreach ($userGroups as $userGroup) {
                    $cloneUserGroup = clone $userGroup;
                    $this->userGroups->add($cloneUserGroup);
                    $cloneUserGroup->setProduct($this);
                }
            }

            $options = $this->getOptions();
            $this->options = new ArrayCollection();
            if (count($options) > 0) {
                foreach ($options as $option) {
                    $cloneOption = clone $option;
                    $this->options->add($cloneOption);
                    $cloneOption->setProduct($this);
                }
            }
        }
    }

    public function duplicate($newName, $newSKU = '')
    {
        $newproduct = clone $this;
        $newproduct->setIsActive(false);
        $newproduct->setQty(0);
        $newproduct->setName($newName);
        $newproduct->setSKU($newSKU);

        $existingPageID = $this->getPageID();
        if ($existingPageID) {
            $existinPage = Page::getByID($existingPageID);
            $pageTemplateID = $existinPage->getPageTemplateID();
            $newproduct->generatePage($pageTemplateID);
        }

        $newproduct->setDateAdded(new \DateTime());
        $newproduct->save();

        $attributes = $this->getAttributes();
        if (count($attributes)) {
            foreach ($attributes as $att) {
                $ak = $att->getAttributeKey();
                if ($ak && is_object($ak)) {
                    $value = $att->getValue();

                    if (is_object($value) && !is_subclass_of($value,  'Concrete\Core\Entity\File\File')) {
                        $newvalue = clone $value;
                    } else {
                        $newvalue = $value;
                    }
                    $newproduct->setAttribute($ak->getAttributeKeyHandle(), $newvalue);
                }
            }
        }

        $variations = $this->getVariations();
        $newvariations = [];

        if (count($variations) > 0) {
            foreach ($variations as $variation) {
                $cloneVariation = clone $variation;
                $cloneVariation->setProductID($newproduct->getID());
                $cloneVariation->save(true);
                $newvariations[] = $cloneVariation;
            }
        }

        $optionMap = [];

        foreach ($newproduct->getOptions() as $newoption) {
            foreach ($newoption->getOptionItems() as $optionItem) {
                $optionMap[$optionItem->originalID] = $optionItem;
            }
        }

        foreach ($newvariations as $variation) {
            foreach ($variation->getOptions() as $option) {
                $optionid = $option->getOptionItem()->getID();
                $option->setOptionItem($optionMap[$optionid]);
                $option->save(true);
            }
        }

        $relatedProducts = $this->getRelatedProducts();
        if (count($relatedProducts)) {
            $related = [];
            foreach ($relatedProducts as $relatedProduct) {
                $related[] = $relatedProduct->getRelatedProductID();
            }
            StoreProductRelated::addRelatedProducts(['pRelatedProducts' => $related], $newproduct);
        }

        $em = dbORM::entityManager();
        $em->flush();

        // create product event and dispatch
        $event = new StoreProductEvent($this, $newproduct);
        Events::dispatch(StoreProductEvent::PRODUCT_DUPLICATE, $event);

        return $newproduct;
    }

    public function generatePage($templateID = null)
    {
        $app = Application::getFacadeApplication();
        $pkg = $app->make('Concrete\Core\Package\PackageService')->getByHandle('community_store');
        $targetCID = Config::get('community_store.productPublishTarget');

        if ($targetCID > 0) {
            $parentPage = Page::getByID($targetCID);
            $pageType = PageType::getByHandle('store_product');

            if ($pageType && $parentPage && !$parentPage->isError() && !$parentPage->isInTrash()) {
                $pageTemplate = $pageType->getPageTypeDefaultPageTemplateObject();

                if ($pageTemplate) {
                    if ($templateID) {
                        $pt = PageTemplate::getByID($templateID);
                        if (is_object($pt)) {
                            $pageTemplate = $pt;
                        }
                    }
                    $newProductPage = $parentPage->add(
                        $pageType,
                        [
                            'cName' => $this->getName(),
                            'pkgID' => $pkg->getPackageID(),
                        ],
                        $pageTemplate
                    );
                    $newProductPage->setAttribute('exclude_nav', 1);

                    $this->savePageID($newProductPage->getCollectionID());
                    $this->setPageDescription($this->getDesc());

                    $csm = $app->make('cs/helper/multilingual');
                    $mlist = Section::getList();

                    // if we have multilingual pages to also create
                    if (count($mlist) > 1) {
                        foreach ($mlist as $m) {
                            $relatedID = $m->getTranslatedPageID($parentPage);

                            if (!empty($relatedID) && $targetCID != $relatedID) {
                                $parentPage = Page::getByID($relatedID);
                                $translatedPage = $newProductPage->duplicate($parentPage);

                                $productName = $csm->t(null, 'productName', $this->getID(), false, $m->getLocale());

                                if ($productName) {
                                    $translatedPage->update(['cName' => $productName]);
                                }

                                $pageDescription = trim($translatedPage->getAttribute('meta_description'));
                                $newDescription = $csm->t(null, 'productDescription', $this->getID(), false, $m->getLocale());

                                if ($newDescription && !$pageDescription) {
                                    $translatedPage->setAttribute('meta_description', strip_tags($newDescription));
                                }
                            }
                        }
                    }

                    return true;
                }
            }
        }

        return false;
    }

    public function updatePage()
    {
        $pageID = $this->getPageID();

        if ($pageID) {
            $page = Page::getByID($pageID);

            if ($page && !$page->isError() && $page->getCollectionName() != $this->getName()) {
                $page->updateCollectionName($this->getName());
            }
        }
    }

    public function setPageDescription($newDescription)
    {
        $productDescription = strip_tags(trim($this->getDesc()));
        $pageID = $this->getPageID();
        if ($pageID) {
            $productPage = Page::getByID($pageID);
            if (is_object($productPage) && $productPage->getCollectionID() > 0) {
                $pageDescription = trim($productPage->getAttribute('meta_description'));
                // if it's the same as the current product description, it hasn't been updated independently of the product
                if ('' == $pageDescription || $productDescription == $pageDescription) {
                    $productPage->setAttribute('meta_description', strip_tags($newDescription));
                }
            }
        }
    }

    public function setPageID($cID)
    {
        $this->setCollectionID($cID);
    }

    public function savePageID($cID)
    {
        $this->setCollectionID($cID);
        $this->save();
    }

    /* TO-DO
     * This isn't completely accurate as an order status may be incomplete and never change,
     * or an order may be canceled. So at somepoint, circle back to this to check for certain status's
     */
    public function getTotalSold()
    {
        $app = Application::getFacadeApplication();
        $db = $app->make('database')->connection();
        $results = $db->GetAll("SELECT * FROM CommunityStoreOrderItems WHERE pID = ?", $this->pID);

        return count($results);
    }

    public function getObjectAttributeCategory()
    {
        return Application::getFacadeApplication()->make('\Concrete\Package\CommunityStore\Attribute\Category\ProductCategory');
    }

    public function getAttributeValueObject($ak, $createIfNotExists = false)
    {
        $category = $this->getObjectAttributeCategory();

        if (!is_object($ak)) {
            $ak = $category->getByHandle($ak);
        }

        $value = false;
        if (is_object($ak)) {
            $value = $category->getAttributeValue($ak, $this);
        }

        if ($value) {
            return $value;
        } elseif ($createIfNotExists) {
            $attributeValue = new StoreProductValue();
            $attributeValue->setProduct($this);
            $attributeValue->setAttributeKey($ak);
            return $attributeValue;
        }
    }


    public function getVariationData()
    {
        $firstAvailableVariation = false;

        if ($this->hasVariations()) {
            $availableOptionsids = false;
            foreach ($this->getVariations() as $variation) {
                $isAvailable = false;

                if ($variation->isSellable()) {
                    $variationOptions = $variation->getOptions();

                    foreach ($variationOptions as $variationOption) {
                        $opt = $variationOption->getOptionItem();
                        if ($opt->isHidden()) {
                            $isAvailable = false;
                            break;
                        } else {
                            $isAvailable = true;
                        }
                    }
                    if ($isAvailable) {
                        $availableOptionsids = $variation->getOptionItemIDs();
                        $this->shallowClone = true;
                        $firstAvailableVariation = clone $this;
                        $firstAvailableVariation->setVariation($variation);

                        break;
                    }
                }
            }
        }

        $adjustment = 0;

        foreach($this->getOptions() as $option) {
           $optionItems = $option->getOptionItems();

           foreach($optionItems as $optionItem) {
               if (!$optionItem->isHidden()) {
                   $adjustment += $optionItem->getPriceAdjustment();
                   break;
               }
           }
        }

        return ['firstAvailableVariation' => $firstAvailableVariation, 'availableOptionsids' => $availableOptionsids, 'priceAdjustment'=>$adjustment];
    }

    // helper function for working with variation options
    public function getVariationLookup()
    {
        $variationLookup = [];

        if ($this->hasVariations()) {
            $variations = StoreProductVariation::getVariationsForProduct($this);

            $variationLookup = [];

            if (!empty($variations)) {
                foreach ($variations as $variation) {
                    // returned pre-sorted
                    $ids = $variation->getOptionItemIDs();
                    $variationLookup[implode('_', $ids)] = $variation;
                }
            }
        }

        return $variationLookup;
    }
}
