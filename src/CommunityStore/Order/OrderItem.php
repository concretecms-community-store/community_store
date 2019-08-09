<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Order;

use Doctrine\ORM\Mapping as ORM;
use Concrete\Core\Support\Facade\DatabaseORM as dbORM;
use Concrete\Core\Support\Facade\Database;
use Concrete\Package\CommunityStore\Src\CommunityStore\Order\Order as StoreOrder;
use Concrete\Package\CommunityStore\Src\CommunityStore\Order\OrderItemOption as StoreOrderItemOption;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product as StoreProduct;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductOption\ProductOption as StoreProductOption;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductOption\ProductOptionItem as StoreProductOptionItem;
use Concrete\Core\Support\Facade\Application;

/**
 * @ORM\Entity
 * @ORM\Table(name="CommunityStoreOrderItems")
 */
class OrderItem
{
    /**
     * @ORM\Id @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $oiID;

    /**
     * @ORM\Column(type="integer")
     */
    protected $pID;

    /**
     * @ORM\ManyToOne(targetEntity="Concrete\Package\CommunityStore\Src\CommunityStore\Order\Order", inversedBy="orderItems")
     * @ORM\JoinColumn(name="oID", referencedColumnName="oID", onDelete="CASCADE")
     */
    protected $order;

    /**
     * @ORM\Column(type="string")
     */
    protected $oiProductName;

    /**
     * @ORM\Column(type="string")
     */
    protected $oiSKU;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=4)
     */
    protected $oiPricePaid;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $oiTax;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $oiTaxIncluded;

    /**
     * @ORM\Column(type="string")
     */
    protected $oiTaxName;

    /**
     * @ORM\Column(type="decimal", precision=12, scale=4)
     */
    protected $oiQty;

    /**
     * @ORM\Column(type="string",nullable=true)
     */
    protected $oiQtyLabel;

    /**
     * @ORM\return mixed
     */
    public function getID()
    {
        return $this->oiID;
    }

    /**
     * @ORM\return mixed
     */
    public function getProductName()
    {
        return $this->oiProductName;
    }

    /**
     * @ORM\param mixed $oiProductName
     */
    public function setProductName($oiProductName)
    {
        $this->oiProductName = $oiProductName;
    }

    /**
     * @ORM\return mixed
     */
    public function getSKU()
    {
        return $this->oiSKU;
    }

    /**
     * @ORM\param mixed $oiSKU
     */
    public function setSKU($oiSKU)
    {
        $this->oiSKU = $oiSKU;
    }

    /**
     * @ORM\return mixed
     */
    public function getPricePaid()
    {
        return $this->oiPricePaid;
    }

    /**
     * @ORM\param mixed $oiPricePaid
     */
    public function setPricePaid($oiPricePaid)
    {
        $this->oiPricePaid = $oiPricePaid;
    }

    /**
     * @ORM\return mixed
     */
    public function getTax()
    {
        return $this->oiTax;
    }

    /**
     * @ORM\param mixed $oiTax
     */
    public function setTax($oitax)
    {
        $this->oiTax = ($oitax ? $oitax : 0);
    }

    /**
     * @ORM\return mixed
     */
    public function getTaxIncluded()
    {
        return $this->oiTaxIncluded;
    }

    /**
     * @ORM\param mixed $oitaxIncluded
     */
    public function setTaxIncluded($oiTaxIncluded)
    {
        $this->oiTaxIncluded = ($oiTaxIncluded ? $oiTaxIncluded : 0);
    }

    /**
     * @ORM\return mixed
     */
    public function getTaxName()
    {
        return $this->oiTaxName;
    }

    /**
     * @ORM\param mixed $oiTaxName
     */
    public function setTaxName($oiTaxName)
    {
        $this->oiTaxName = $oiTaxName;
    }

    /**
     * @ORM\return mixed
     */
    public function getQty()
    {
        return round($this->oiQty, 4);
    }

    /**
     * @ORM\param mixed $oiQty
     */
    public function setQty($oiQty)
    {
        $this->oiQty = $oiQty;
    }

    /**
     * @ORM\return mixed
     */
    public function getQtyLabel()
    {
        return $this->oiQtyLabel;
    }

    /**
     * @ORM\param mixed $oiQtyLabel
     */
    public function setQtyLabel($oiQtyLabel)
    {
        $this->oiQtyLabel = $oiQtyLabel;
    }

    public function setProductID($productid)
    {
        $this->pID = $productid;
    }

    /**
     * @ORM\return mixed
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @ORM\param mixed $order
     */
    public function setOrder($order)
    {
        $this->order = $order;
    }

    public static function getByID($oiID)
    {
        $em = dbORM::entityManager();

        return $em->find(get_class(), $oiID);
    }

    public static function add($data, $oID, $tax = 0, $taxIncluded = 0, $taxName = '', $adjustRatio = 1)
    {
        $app = Application::getFacadeApplication();
        $csm = $app->make('cs/helper/multilingual');

        $product = $data['product']['object'];

        $productName = $csm->t($product->getName(), 'productName', $product->getID());
        $qty = $data['product']['qty'];

        if (isset($data['product']['customerPrice'])) {
            $productPrice = $data['product']['customerPrice'];
        } else {
            $productPrice = $product->getActivePrice($qty);
        }

        $qtyLabel = $csm->t($product->getQtyLabel(), 'productQuantityLabel', $product->getID());

        $sku = $product->getSKU();

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
        $orderItem->setQtyLabel($qtyLabel);
        $orderItem->setOrder($order);

        if ($product) {
            $orderItem->setProductID($product->getID());
        }

        $orderItem->save();

        foreach ($data['productAttributes'] as $groupID => $valID) {
            if ('po' == substr($groupID, 0, 2)) {
                $groupID = str_replace("po", "", $groupID);
                $optionvalue = StoreProductOptionItem::getByID($valID);

                if ($optionvalue) {
                    $optionvalue = $csm->t($optionvalue->getName(), 'optionValue');
                }
            } elseif ('pt' == substr($groupID, 0, 2)) {
                $groupID = str_replace("pt", "", $groupID);
                $optionvalue = $valID;
            } elseif ('pa' == substr($groupID, 0, 2)) {
                $groupID = str_replace("pa", "", $groupID);
                $optionvalue = $valID;
            } elseif ('ph' == substr($groupID, 0, 2)) {
                $groupID = str_replace("ph", "", $groupID);
                $optionvalue = $valID;
            } elseif ('pc' == substr($groupID, 0, 2)) {
                $groupID = str_replace("pc", "", $groupID);
                $optionvalue = $valID;
            }

            $optionGroupName = '';

            $optiongroup = StoreProductOption::getByID($groupID);
            if ($optiongroup) {
                $optionGroupName = $csm->t($optiongroup->getName(), 'optionName', null, $groupID);
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
        return Database::connection()->GetAll("SELECT * FROM CommunityStoreOrderItemOptions WHERE oiID=?", $this->oiID);
    }

    public function getProductObject()
    {
        return StoreProduct::getByID($this->getProductID());
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
}
