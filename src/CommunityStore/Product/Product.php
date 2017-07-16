<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Product;

use Package;
use Page;
use PageType;
use PageTemplate;
use Database;
use File;
use Core;
use Config;
use Events;
use Doctrine\Common\Collections\ArrayCollection;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductImage as StoreProductImage;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductGroup as StoreProductGroup;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductUserGroup as StoreProductUserGroup;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductFile as StoreProductFile;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductLocation as StoreProductLocation;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductOption\ProductOption as StoreProductOption;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductOption\ProductOptionItem as StoreProductOptionItem;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductVariation\ProductVariation as StoreProductVariation;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductVariation\ProductVariationOptionItem as StoreProductVariationOptionItem;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductRelated as StoreProductRelated;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductEvent as StoreProductEvent;
use Concrete\Package\CommunityStore\Src\Attribute\Key\StoreProductKey;
use Concrete\Package\CommunityStore\Src\Attribute\Value\StoreProductValue;
use Concrete\Package\CommunityStore\Src\CommunityStore\Tax\TaxClass as StoreTaxClass;
use Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Price as StorePrice;
use Concrete\Core\Support\Facade\Application;

/**
 * @Entity
 * @Table(name="CommunityStoreProducts")
 */
class Product
{
    /** 
     * @Id @Column(type="integer") 
     * @GeneratedValue 
     */
    protected $pID;

    /**
     * @Column(type="integer",nullable=true)
     */
    protected $cID;

    /**
     * @Column(type="string")
     */
    protected $pName;

    /**
     * @Column(type="string",nullable=true)
     */
    protected $pSKU;

    /**
     * @Column(type="text",nullable=true)
     */
    protected $pDesc;

    /**
     * @Column(type="text",nullable=true)
     */
    protected $pDetail;

    /**
     * @Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    protected $pPrice;

    /**
     * @Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    protected $pSalePrice;

    /**
     * @Column(type="boolean")
     */
    protected $pCustomerPrice;

    /**
     * @Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    protected $pPriceMaximum;

    /**
     * @Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    protected $pPriceMinimum;

    /**
     * @Column(type="text",nullable=true)
     */
    protected $pPriceSuggestions;

    /**
     * @Column(type="boolean")
     */
    protected $pFeatured;

    /**
     * @Column(type="integer")
     */
    protected $pQty;

    /**
     * @Column(type="boolean",nullable=true)
     */
    protected $pQtyUnlim;
    /**
     * @Column(type="boolean",nullable=true)
     */
    protected $pBackOrder;
    /**
     * @Column(type="boolean")
     */
    protected $pNoQty;

    /**
     * @Column(type="integer",nullable=true)
     */
    protected $pTaxClass;

    /**
     * @Column(type="boolean")
     */
    protected $pTaxable;

    /**
     * @Column(type="integer")
     */
    protected $pfID;

    /**
     * @Column(type="boolean")
     */
    protected $pActive;

    /**
     * @Column(type="datetime")
     */
    protected $pDateAdded;

    /**
     * @Column(type="boolean")
     */
    protected $pShippable;

    /**
     * @Column(type="decimal", precision=10, scale=2,nullable=true)
     */
    protected $pWidth;

    /**
     * @Column(type="decimal", precision=10, scale=2,nullable=true)
     */
    protected $pHeight;

    /**
     * @Column(type="decimal", precision=10, scale=2,nullable=true)
     */
    protected $pLength;

    /**
     * @Column(type="decimal", precision=10, scale=2,nullable=true)
     */
    protected $pWeight;

    /**
     * @Column(type="integer",nullable=true)
     */
    protected $pNumberItems;

    /**
     * @Column(type="boolean")
     */
    protected $pCreateUserAccount;

    /**
     * @Column(type="boolean")
     */
    protected $pAutoCheckout;

    /**
     * @Column(type="integer")
     */
    protected $pExclusive;

    /**
     * @Column(type="boolean")
     */
    protected $pVariations;

    // not stored, used for price/sku/etc lookup purposes
    protected $variation;

    /**
     * @OneToMany(targetEntity="Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductLocation", mappedBy="product",cascade={"persist"}))
     */
    protected $locations;

    public function getLocations(){
        return $this->locations;
    }

    /**
     * @OneToMany(targetEntity="Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductGroup", mappedBy="product",cascade={"persist"})
     */
    protected $groups;

    public function getGroups(){
        return $this->groups;
    }

    /**
     * @OneToMany(targetEntity="Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductFile", mappedBy="product",cascade={"persist"}))
     */
    protected $files;

    public function getFiles(){
        return $this->files;
    }

    /**
     * @OneToMany(targetEntity="Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductImage", mappedBy="product",cascade={"persist"}))
     */
    protected $images;

    public function getImages(){
        return $this->images;
    }

    /**
     * @OneToMany(targetEntity="Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductUserGroup", mappedBy="product",cascade={"persist"}))
     */
    protected $userGroups;

    public function getUserGroups(){
        return $this->userGroups;
    }

    /**
     * @OneToMany(targetEntity="Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductOption\ProductOption", mappedBy="product",cascade={"persist"}))
     * @OrderBy({"poSort" = "ASC"})
     */
    protected $options;

    public function getOptions(){
        return $this->options;
    }

    /**
     * @OneToMany(targetEntity="Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductRelated", mappedBy="product",cascade={"persist"}))
     */
    protected $related;

    public function getRelatedProducts(){
        return $this->related;
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
            $optionkeys = array();

            foreach ($options as $option) {
                $optionItems = $option->getOptionItems();
                foreach ($optionItems as $optionItem) {
                    if (!$optionItem->isHidden()) {
                        $optionkeys[] = $optionItem->getID();
                        break;
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
        $this->pPrice = ($price != '' ? $price : 0);
    }
    public function setSalePrice($price)
    {
        $this->pSalePrice = ($price != '' ? $price : null);
    }
    public function setCustomerPrice($bool) {
        $this->pCustomerPrice = (!is_null($bool) ? $bool : false);
    }
    public function getPriceMaximum()
    {
        return $this->pPriceMaximum;
    }
    public function setPriceMaximum($pPriceMaximum)
    {
        $this->pPriceMaximum = $pPriceMaximum != '' ? $pPriceMaximum : null;
    }
    public function getPriceMinimum()
    {
        return $this->pPriceMinimum;
    }
    public function setPriceMinimum($pPriceMinimum)
    {
        $this->pPriceMinimum = $pPriceMinimum != '' ? $pPriceMinimum : null;
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
    public function setWidth($width)
    {
        $this->pWidth = (float)$width;
    }
    public function setHeight($height)
    {
        $this->pHeight = (float)$height;
    }
    public function setLength($length)
    {
        $this->pLength = (float)$length;
    }
    public function setWeight($weight)
    {
        $this->pWeight = (float)$weight;
    }
    public function setNumberItems($number)
    {
        $this->pNumberItems = ($number != '' ? $number : null);
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

    public static function getByID($pID)
    {
        $em = \ORM::entityManager();
        return $em->find(get_class(), $pID);
    }

    public static function getBySKU($pSKU)
    {
        $em = \ORM::entityManager();
        return $em->getRepository(get_class())->findOneBy(array('pSKU' => $pSKU));
    }

    public static function getByCollectionID($cID)
    {
        $em = \ORM::entityManager();
        return $em->getRepository(get_class())->findOneBy(array('cID' => $cID));
    }

    public static function saveProduct($data)
    {
        if ($data['pID']) {
            //if we know the pID, we're updating.
            $product = self::getByID($data['pID']);
            $originalProduct = clone $product;

            $product->setPageDescription($data['pDesc']);
            $newproduct = false;
            if ($data['pDateAdded_dt']) {
                $product->setDateAdded(new \DateTime($data['pDateAdded_dt'] . ' ' . $data['pDateAdded_h'] . ':' . $data['pDateAdded_m']));
            }
        } else {
            //else, we don't know it and we're adding a new product
            $product = new self();
            $dt = Core::make('helper/date');
            $product->setDateAdded(new \DateTime());
            $newproduct = true;
        }
        $product->setName($data['pName']);
        $product->setSKU($data['pSKU']);
        $product->setDescription($data['pDesc']);
        $product->setDetail($data['pDetail']);
        $product->setPrice($data['pPrice']);
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
        $product->setLength($data['pLength']);
        $product->setWeight($data['pWeight']);
        $product->setNumberItems($data['pNumberItems']);
        $product->setAutoCheckout($data['pAutoCheckout']);
        $product->setIsExclusive($data['pExclusive']);
        $product->setCustomerPrice($data['pCustomerPrice']);
        $product->setPriceSuggestions($data['pPriceSuggestions']);
        $product->setPriceMaximum($data['pPriceMaximum']);
        $product->setPriceMinimum($data['pPriceMinimum']);

        // if we have no product groups, we don't have variations to offer
        if (empty($data['poName'])) {
            $product->setHasVariations(0);
        } else {
            $product->setHasVariations($data['pVariations']);
        }

        $product->save();
        if (!$data['pID']) {
            $product->generatePage($data['selectPageTemplate']);
        }

        // create product event and dispatch
        if ($newproduct) {
            $event = new StoreProductEvent($product);
            Events::dispatch('on_community_store_product_add', $event);
        } else {
            $event = new StoreProductEvent($originalProduct, $product);
            Events::dispatch('on_community_store_product_update', $event);
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
    public function getDesc()
    {
        return $this->pDesc;
    }
    public function getDetail()
    {
        return $this->pDetail;
    }
    public function getPrice()
    {
        if ($this->hasVariations() && $variation = $this->getVariation()) {
            if ($variation) {
                $varprice = $variation->getVariationPrice();

                if ($varprice) {
                    return $varprice;
                } else {
                    return $this->pPrice;
                }
            }
        } else {
            return $this->pPrice;
        }
    }
    public function getFormattedOriginalPrice()
    {
        return StorePrice::format($this->getPrice());
    }
    public function getFormattedPrice()
    {
        return StorePrice::format($this->getActivePrice());
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

        if ($saleprice != '') {
            return StorePrice::format($saleprice);
        }
    }

    public function getActivePrice()
    {
        $salePrice = $this->getSalePrice();
        if ($salePrice != "") {
            return $salePrice;
        } else {
            return $this->getPrice();
        }
    }
    public function getFormattedActivePrice()
    {
        return StorePrice::format($this->getActivePrice());
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
    public function allowCustomerPrice() {
        return (bool) $this->pCustomerPrice;
    }

    public function getDimensions($whl = null)
    {
        $width = $this->getWidth();
        $height = $this->getHeight();
        $length = $this->getWidth();

        if ($this->hasVariations() && $variation = $this->getVariation()) {

            $varWidth = $variation->getVariationWidth();
            $varHeight = $variation->getVariationHeight();
            $varLength = $variation->getVariationLength();

            if ($varWidth != '') {
                $width = $varWidth;
            }

            if ($varHeight != '') {
                $height = $varHeight;
            }

            if ($varLength != '') {
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
                return $length."x".$width."x".$height;
                break;
        }
    }

    public function getWidth() {
        if ($this->hasVariations() && $variation = $this->getVariation()) {
          $width = $variation->getVariationWidth();

            if ($width) {
                return $width;
            }
        }

        return $this->pWidth;
    }

    public function getHeight() {
        if ($this->hasVariations() && $variation = $this->getVariation()) {
            $height = $variation->getVariationHeight();

            if ($height) {
                return $height;
            }
        }

        return $this->pHeight;
    }

    public function getLength() {
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
            return "<img src='".$fileObj->getRelativePath()."'>";
        }
    }
    public function getImageThumb()
    {
        $fileObj = $this->getImageObj();
        if (is_object($fileObj)) {
            return "<img src='".$fileObj->getThumbnailURL('file_manager_listing')."'>";
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
    public function getDateAdded(){
        return $this->pDateAdded;
    }

    public function save()
    {
        $em = \ORM::entityManager();
        $em->persist($this);
        $em->flush();
    }

    public function delete() {
        $em = \ORM::entityManager();
        $em->remove($this);
        $em->flush();
    }

    public function remove()
    {
        StoreProductImage::removeImagesForProduct($this);
        StoreProductOption::removeOptionsForProduct($this);
        StoreProductOptionItem::removeOptionItemsForProduct($this);
        StoreProductFile::removeFilesForProduct($this);
        StoreProductGroup::removeGroupsForProduct($this);
        StoreProductLocation::removeLocationsForProduct($this);
        StoreProductUserGroup::removeUserGroupsForProduct($this);
        StoreProductVariation::removeVariationsForProduct($this);

        // create product event and dispatch
        $event = new StoreProductEvent($this);
        Events::dispatch('on_community_store_product_delete', $event);

        $this->delete();
        $page = Page::getByID($this->cID);
        if (is_object($page)) {
            $page->delete();
        }
    }

    public function __clone() {
        if ($this->shallowClone) {
            return;
        }

        if ($this->pID) {
            $this->setId(null);
            $this->setPageID(null);

            $locations = $this->getLocations();
            $this->locations = new ArrayCollection();
            if(count($locations) > 0){
                foreach ($locations as $loc) {
                    $cloneLocation = clone $loc;
                    $this->locations->add($cloneLocation);
                    $cloneLocation->setProduct($this);
                }
            }

            $groups = $this->getGroups();
            $this->groups = new ArrayCollection();
            if(count($groups) > 0){
                foreach ($groups as $group) {
                    $cloneGroup = clone $group;
                    $this->groups->add($cloneGroup);
                    $cloneGroup->setProduct($this);
                }
            }

            $images = $this->getImages();
            $this->images = new ArrayCollection();
            if(count($images) > 0){
                foreach ($images as $image) {
                    $cloneImage = clone $image;
                    $this->images->add($cloneImage);
                    $cloneImage->setProduct($this);
                }
            }

            $files = $this->getFiles();
            $this->files = new ArrayCollection();
            if(count($files) > 0){
                foreach ($files as $file) {
                    $cloneFile = clone $file;
                    $this->files->add($cloneFile);
                    $cloneFile->setProduct($this);
                }
            }

            $userGroups = $this->getUserGroups();
            $this->userGroups = new ArrayCollection();
            if(count($userGroups) > 0){
                foreach ($userGroups as $userGroup) {
                    $cloneUserGroup = clone $userGroup;
                    $this->userGroups->add($cloneUserGroup);
                    $cloneUserGroup->setProduct($this);
                }
            }

            $options = $this->getOptions();
            $this->options = new ArrayCollection();
            if(count($options) > 0){
                foreach ($options as $option) {
                    $cloneOption = clone $option;
                    $this->options->add($cloneOption);
                    $cloneOption->setProduct($this);
                }
            }
        }
    }

    public  function duplicate($newName, $newSKU = '') {
        $newproduct = clone $this;
        $newproduct->setIsActive(false);
        $newproduct->setQty(0);
        $newproduct->setName($newName);
        $newproduct->setSKU($newSKU);

        $existingPageID = $this->getPageID();
        if ($existingPageID) {
            $existinPage= Page::getByID($existingPageID);
            $pageTemplateID = $existinPage->getPageTemplateID();
            $newproduct->generatePage($pageTemplateID);
        }

        $newproduct->setDateAdded(new \DateTime());
        $newproduct->save();

        $attributes = StoreProductKey::getAttributes($this->getID());
        foreach($attributes as $handle=>$value) {
            $spk = StoreProductKey::getByHandle($handle);
            $spk->saveAttribute($newproduct, $value);
        }


        $variations = $this->getVariations();
        $newvariations = array();

        if(count($variations) > 0){
            foreach ($variations as $variation) {
                $cloneVariation = clone $variation;
                $cloneVariation->setProductID($newproduct->getID());
                $cloneVariation->save();
                $newvariations[] = $cloneVariation;
            }
        }

        $optionMap = array();

        foreach($newproduct->getOptions() as $newoption) {
            foreach($newoption->getOptionItems() as $optionItem) {
                $optionMap[$optionItem->originalID] = $optionItem;
            }
        }

        foreach($newvariations as $variation) {
            foreach($variation->getOptions() as $option) {
                $optionid = $option->getOption()->getID();
                $option->setOption($optionMap[$optionid]);
                $option->save();
            }
        }

        $relatedProducts = $this->getRelatedProducts();
        if (count($relatedProducts)) {
            $related = array();
            foreach ($relatedProducts as $relatedProduct) {
                $related[] = $relatedProduct->getRelatedProductID();
            }
            StoreProductRelated::addRelatedProducts(array('pRelatedProducts' => $related), $newproduct);
        }
        
        // create product event and dispatch
        $event = new StoreProductEvent($this, $newproduct);
        Events::dispatch('on_community_store_product_duplicate', $event);

        return $newproduct;
    }

    public function generatePage($templateID = null)
    {
        $pkg = Package::getByHandle('community_store');
        $targetCID = Config::get('community_store.productPublishTarget');

        if ($targetCID > 0) {
            $parentPage = Page::getByID($targetCID);
            $pageType = PageType::getByHandle('store_product');
            $pageTemplate = $pageType->getPageTypeDefaultPageTemplateObject();

            if ($parentPage && $pageType && $pageTemplate) {
                if ($templateID) {
                    $pt = PageTemplate::getByID($templateID);
                    if (is_object($pt)) {
                        $pageTemplate = $pt;
                    }
                }
                $newProductPage = $parentPage->add(
                    $pageType,
                    array(
                        'cName' => $this->getName(),
                        'pkgID' => $pkg->getPackageID(),
                    ),
                    $pageTemplate
                );
                $newProductPage->setAttribute('exclude_nav', 1);

                $this->savePageID($newProductPage->getCollectionID());
                $this->setPageDescription($this->getDesc());
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
                if ($pageDescription == '' || $productDescription == $pageDescription) {
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

    public function setAttribute($ak, $value)
    {
        if (!is_object($ak)) {
            $ak = StoreProductKey::getByHandle($ak);
        }
        $ak->setAttribute($this, $value);
    }
    public function getAttribute($ak, $displayMode = false)
    {
        if (!is_object($ak)) {
            $ak = StoreProductKey::getByHandle($ak);
        }
        if (is_object($ak)) {
            $av = $this->getAttributeValueObject($ak);
            if (is_object($av)) {
                return $av->getValue($displayMode);
            }
        }
    }
    public function getAttributeValueObject($ak, $createIfNotFound = false)
    {
        $app = Application::getFacadeApplication();
        $db = $app->make('database')->connection();
        $av = false;
        $v = array($this->getID(), $ak->getAttributeKeyID());
        $avID = $db->GetOne("SELECT avID FROM CommunityStoreProductAttributeValues WHERE pID=? AND akID=?", $v);
        if ($avID > 0) {
            $av = StoreProductValue::getByID($avID);
            if (is_object($av)) {
                $av->setProduct($this);
                $av->setAttributeKey($ak);
            }
        }

        if ($createIfNotFound) {
            $cnt = 0;

            // Is this avID in use ?
            if (is_object($av)) {
                $cnt = $db->GetOne("SELECT COUNT(avID) FROM CommunityStoreProductAttributeValues WHERE avID=?", $av->getAttributeValueID());
            }

            if ((!is_object($av)) || ($cnt > 1)) {
                $av = $ak->addAttributeValue();
            }
        }

        return $av;
    }

    public function getVariationData() {
        $firstAvailableVariation = false;

        if ($this->hasVariations()) {
            $availableOptionsids = false;
            foreach ($this->getVariations() as $variation) {
                $isAvailable = false;

                if ($variation->isSellable()) {
                    $variationOptions = $variation->getOptions();

                    foreach ($variationOptions as $variationOption) {
                        $opt = $variationOption->getOption();
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

        return array('firstAvailableVariation'=>$firstAvailableVariation, 'availableOptionsids'=>$availableOptionsids);
    }

    // helper function for working with variation options
    public function getVariationLookup() {
        $variationLookup = array();

        if ($this->hasVariations()) {
            $variations = StoreProductVariation::getVariationsForProduct($this);

            $variationLookup = array();

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
