<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Order;

use Concrete\Core\Foundation\Object as Object;
use Database;
use User;
use UserInfo;
use Concrete\Package\CommunityStore\Src\CommunityStore\Order\Order as StoreOrder;
use Concrete\Package\CommunityStore\Src\CommunityStore\Order\OrderItemOption as StoreOrderItemOption;

/**
 * @Entity
 * @Table(name="CommunityStoreOrderItems")
 */
class OrderItem
{
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue
     */
    protected $oiID;

    /**
     * @ManyToOne(targetEntity="Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product", inversedBy="orderItemDiscounts", cascade={"persist"})
     * @JoinColumn(name="pID", referencedColumnName="pID", onDelete="CASCADE")
     */
    protected $product;

    /**
     * @ManyToOne(targetEntity="Concrete\Package\CommunityStore\Src\CommunityStore\Order\Order", inversedBy="orderItemDiscounts", cascade={"persist"})
     * @JoinColumn(name="oID", referencedColumnName="oID", onDelete="CASCADE")
     */
    protected $order;

    /**
     * @Column(type="string")
     */
    protected $oiProductName;

    /**
     * @Column(type="string")
     */
    protected $oiSKU;

    /**
     * @Column(type="decimal", precision=10, scale=4)
     */
    protected $oiPricePaid;

    /**
     * @Column(type="decimal", precision=10, scale=4)
     */
    protected $oitax;

    /**
     * @Column(type="decimal", precision=10, scale=4)
     */
    protected $oitaxIncluded;

    /**
     * @Column(type="string")
     */
    protected $oiTaxName;

    /**
     * @Column(type="integer")
     */
    protected $oiQty;

    /**
     * @return mixed
     */
    public function getOrderItemID()
    {
        return $this->oiID;
    }

    /**
     * @return mixed
     */
    public function getOrderItemProductName()
    {
        return $this->oiProductName;
    }

    /**
     * @param mixed $oiProductName
     */
    public function setOrderItemProductName($oiProductName)
    {
        $this->oiProductName = $oiProductName;
    }

    /**
     * @return mixed
     */
    public function getOrderItemSKU()
    {
        return $this->oiSKU;
    }

    /**
     * @param mixed $oiSKU
     */
    public function setOrderItemSKU($oiSKU)
    {
        $this->oiSKU = $oiSKU;
    }

    /**
     * @return mixed
     */
    public function getOrderItemPricePaid()
    {
        return $this->oiPricePaid;
    }

    /**
     * @param mixed $oiPricePaid
     */
    public function setOrderItemPricePaid($oiPricePaid)
    {
        $this->oiPricePaid = $oiPricePaid;
    }

    /**
     * @return mixed
     */
    public function getOrderItemTax()
    {
        return $this->oitax;
    }

    /**
     * @param mixed $oitax
     */
    public function setOrderItemTax($oitax)
    {
        $this->oitax = $oitax;
    }

    /**
     * @return mixed
     */
    public function getOrderItemtaxIncluded()
    {
        return $this->oitaxIncluded;
    }

    /**
     * @param mixed $oitaxIncluded
     */
    public function setOrderItemTaxIncluded($oitaxIncluded)
    {
        $this->oitaxIncluded = $oitaxIncluded;
    }

    /**
     * @return mixed
     */
    public function getOrderItemTaxName()
    {
        return $this->oiTaxName;
    }

    /**
     * @param mixed $oiTaxName
     */
    public function setOrderItemTaxName($oiTaxName)
    {
        $this->oiTaxName = $oiTaxName;
    }

    /**
     * @return mixed
     */
    public function getOrderItemQty()
    {
        return $this->oiQty;
    }

    /**
     * @param mixed $oiQty
     */
    public function setOrderItemQty($oiQty)
    {
        $this->oiQty = $oiQty;
    }

    /**
     * @return mixed
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @param mixed $product
     */
    public function setProduct($product)
    {
        $this->product = $product;
    }

    /**
     * @return mixed
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param mixed $order
     */
    public function setOrder($order)
    {
        $this->order = $order;
    }

    public static function getByID($oiID)
    {
        $db = Database::connection();
        $em = $db->getEntityManager();

        return $em->find('Concrete\Package\CommunityStore\Src\CommunityStore\Order\OrderItem', $oiID);
    }

    public function add($data, $oID, $tax = 0, $taxIncluded = 0, $taxName = '')
    {
        $product = $data['product']['object'];

        $productName = $product->getProductName();
        $productPrice = $product->getActivePrice();
        $sku = $product->getProductSKU();
        $qty = $data['product']['qty'];

        $inStock = $product->getProductQty();
        $newStock = $inStock - $qty;

        $variation = $product->getVariation();

        if ($variation) {
            if (!$variation->isUnlimited()) {
                $product->updateProductQty($newStock);
            }
        } elseif (!$product->isUnlimited()) {
            $product->updateProductQty($newStock);
        }

        $order = StoreOrder::getByID($oID);

        $orderItem = new self();
        $orderItem->setOrderItemProductName($productName);
        $orderItem->setOrderItemSKU($sku);
        $orderItem->setOrderItemPricePaid($productPrice);
        $orderItem->setOrderItemTax($tax);
        $orderItem->setOrderItemTaxIncluded($taxIncluded);
        $orderItem->setOrderItemTaxName($taxName);
        $orderItem->setOrderItemQty($qty);
        $orderItem->setOrder($order);

        if ($product) {
            $orderItem->setProduct($product);
        }

        $orderItem->save();

        foreach ($data['productAttributes'] as $optionGroup => $selectedOption) {
            $optionGroupID = str_replace("pog", "", $optionGroup);
            $optionGroupName = self::getProductOptionGroupNameByID($optionGroupID);
            $optionValue = self::getProductOptionValueByID($selectedOption);

            $orderItemOption = new StoreOrderItemOption();
            $orderItemOption->setOrderItemOptionKey($optionGroupName);
            $orderItemOption->setOrderItemOptionValue($optionValue);
            $orderItemOption->setOrderItem($orderItem);
            $orderItemOption->save();
        }

        if ($product->hasDigitalDownload()) {
            $fileObjs = $product->getProductDownloadFileObjects();
            $fileObj = $fileObjs[0];
            $pk = \Concrete\Core\Permission\Key\FileKey::getByHandle('view_file');
            $pk->setPermissionObject($fileObj);
            $pao = $pk->getPermissionAssignmentObject();
            $u = new User();
            $uID = $u->getUserID();
            $ui = UserInfo::getByID($uID);
            $user = \Concrete\Core\Permission\Access\Entity\UserEntity::getOrCreate($ui);
            $pa = $pk->getPermissionAccessObject();
            if ($pa) {
                $pa->addListItem($user);
                $pao->assignPermissionAccess($pa);
            }
        }
    }

    public function getProductID()
    {
        return $this->pID;
    }
    public function getProductName()
    {
        return $this->oiProductName;
    }
    public function getSKU()
    {
        return $this->oiSKU;
    }
    public function getPricePaid()
    {
        return $this->oiPricePaid;
    }
    public function getQty()
    {
        return $this->oiQty;
    }
    public function getSubTotal()
    {
        $price = $this->getPricePaid();
        $qty = $this->getQty();
        $subtotal = $qty * $price;

        return $subtotal;
    }

    public function getProductOptions()
    {
        return Database::connection()->GetAll("SELECT * FROM CommunityStoreOrderItemOptions WHERE oiID=?", $this->oiID);
    }
    public function getProductOptionGroupNameByID($id)
    {
        $db = Database::connection();
        $optionGroup = $db->GetRow("SELECT * FROM CommunityStoreProductOptionGroups WHERE pogID=?", $id);

        return $optionGroup['pogName'];
    }
    public function getProductOptionValueByID($id)
    {
        $db = Database::connection();
        $optionItem = $db->GetRow("SELECT * FROM CommunityStoreProductOptionItems WHERE poiID=?", $id);

        return $optionItem['poiName'];
    }
    public function getProductObject()
    {
        return $this->product;
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
}
