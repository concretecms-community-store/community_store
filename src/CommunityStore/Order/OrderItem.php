<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Order;

use Database;
use Concrete\Package\CommunityStore\Src\CommunityStore\Order\Order as StoreOrder;
use Concrete\Package\CommunityStore\Src\CommunityStore\Order\OrderItemOption as StoreOrderItemOption;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product as StoreProduct;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductOption\ProductOption as StoreProductOption;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductOption\ProductOptionItem as StoreProductOptionItem;

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
     * @Column(type="integer")
     */
    protected $pID;


    /**
     * @ManyToOne(targetEntity="Concrete\Package\CommunityStore\Src\CommunityStore\Order\Order")
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
    protected $oiTax;

    /**
     * @Column(type="decimal", precision=10, scale=4)
     */
    protected $oiTaxIncluded;

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
    public function getID()
    {
        return $this->oiID;
    }

    /**
     * @return mixed
     */
    public function getProductName()
    {
        return $this->oiProductName;
    }

    /**
     * @param mixed $oiProductName
     */
    public function setProductName($oiProductName)
    {
        $this->oiProductName = $oiProductName;
    }

    /**
     * @return mixed
     */
    public function getSKU()
    {
        return $this->oiSKU;
    }

    /**
     * @param mixed $oiSKU
     */
    public function setSKU($oiSKU)
    {
        $this->oiSKU = $oiSKU;
    }

    /**
     * @return mixed
     */
    public function getPricePaid()
    {
        return $this->oiPricePaid;
    }

    /**
     * @param mixed $oiPricePaid
     */
    public function setPricePaid($oiPricePaid)
    {
        $this->oiPricePaid = $oiPricePaid;
    }

    /**
     * @return mixed
     */
    public function getTax()
    {
        return $this->oiTax;
    }

    /**
     * @param mixed $oiTax
     */
    public function setTax($oitax)
    {
        $this->oiTax = ($oitax ? $oitax : 0);
    }

    /**
     * @return mixed
     */
    public function getTaxIncluded()
    {
        return $this->oiTaxIncluded;
    }

    /**
     * @param mixed $oitaxIncluded
     */
    public function setTaxIncluded($oiTaxIncluded)
    {
        $this->oiTaxIncluded = ($oiTaxIncluded ? $oiTaxIncluded : 0);
    }

    /**
     * @return mixed
     */
    public function getTaxName()
    {
        return $this->oiTaxName;
    }

    /**
     * @param mixed $oiTaxName
     */
    public function setTaxName($oiTaxName)
    {
        $this->oiTaxName = $oiTaxName;
    }

    /**
     * @return mixed
     */
    public function getQty()
    {
        return $this->oiQty;
    }

    /**
     * @param mixed $oiQty
     */
    public function setQty($oiQty)
    {
        $this->oiQty = $oiQty;
    }


    public function setProductID($productid) {
        $this->pID = $productid;
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
        $em = \ORM::entityManager();
        return $em->find(get_class(), $oiID);
    }

    public static function add($data, $oID, $tax = 0, $taxIncluded = 0, $taxName = '', $adjustRatio = 1)
    {
        $product = $data['product']['object'];

        $productName = $product->getName();
        $productPrice = $product->getActivePrice();
        $sku = $product->getSKU();
        $qty = $data['product']['qty'];

        $inStock = $product->getQty();
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
        $orderItem->setProductName($productName);
        $orderItem->setSKU($sku);
        $orderItem->setPricePaid($productPrice * $adjustRatio);
        $orderItem->setTax($tax);
        $orderItem->setTaxIncluded($taxIncluded);
        $orderItem->setTaxName($taxName);
        $orderItem->setQty($qty);
        $orderItem->setOrder($order);

        if ($product) {
            $orderItem->setProductID($product->getID());
        }

        $orderItem->save();

        foreach ($data['productAttributes'] as $groupID => $valID) {

            if (substr($groupID, 0, 2) == 'po') {
                $groupID = str_replace("po", "", $groupID);
                $optionvalue = StoreProductOptionItem::getByID($valID);

                if ($optionvalue) {
                    $optionvalue = $optionvalue->getName();
                }
            } elseif (substr($groupID, 0, 2) == 'pt')  {
                $groupID = str_replace("pt", "", $groupID);
                $optionvalue = $valID;
            } elseif (substr($groupID, 0, 2) == 'pa')  {
                $groupID = str_replace("pa", "", $groupID);
                $optionvalue = $valID;
            } elseif (substr($groupID, 0, 2) == 'ph')  {
                $groupID = str_replace("ph", "", $groupID);
                $optionvalue = $valID;
            }

            $optionGroupName = '';

            $optiongroup = StoreProductOption::getByID($groupID);
            if ($optiongroup) {
                $optionGroupName = $optiongroup->getName();
            }

            $orderItemOption = new StoreOrderItemOption();
            $orderItemOption->setOrderItemOptionKey($optionGroupName);
            $orderItemOption->setOrderItemOptionValue($optionvalue);
            $orderItemOption->setOrderItem($orderItem);
            $orderItemOption->save();
        }

        return $orderItem;
    }

    public function getProductID()
    {
        return $this->pID;
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
        return \Database::connection()->GetAll("SELECT * FROM CommunityStoreOrderItemOptions WHERE oiID=?", $this->oiID);
    }

    public function getProductObject()
    {
        return StoreProduct::getByID($this->getProductID());
    }

    public function save()
    {
        $em = \ORM::entityManager();
        $em->persist($this);
        $em->flush();
    }

    public function delete()
    {
        $em = \ORM::entityManager();
        $em->remove($this);
        $em->flush();
    }
}
