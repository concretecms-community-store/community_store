<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Order;

use Doctrine\ORM\Mapping as ORM;
use Concrete\Core\Support\Facade\Database;
use Concrete\Core\Support\Facade\Application;
use Concrete\Core\Support\Facade\DatabaseORM as dbORM;
use Concrete\Package\CommunityStore\Src\CommunityStore\Order\Order;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product;
use Concrete\Package\CommunityStore\Src\CommunityStore\Order\OrderItemOption;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductOption\ProductOption;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductOption\ProductOptionItem;

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
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $pvID;

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
     * @ORM\Column(type="string", nullable=true)
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
     * @ORM\Column(type="string", nullable=true)
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
    public function getQuantity() {
        return round($this->oiQty, 4);
    }

    /**
     * @deprecated
     */
    public function getQty()
    {
       return $this->getQuantity();
    }

    /**
     * @param mixed $oiQty
     */
    public function setQuantity($oiQty)
    {
        $this->oiQty = $oiQty;
    }

    /**
     * @deprecated
     */
    public function setQty($oiQty)
    {
        $this->setQuantity($oiQty);
    }

    /**
     * @return mixed
     */
    public function getQuantityLabel()
    {
        return $this->oiQtyLabel;
    }

    /**
     * @deprecated
     */
    public function getQtyLabel()
    {
        return $this->getQuantityLabel();
    }

    /**
     * @param mixed $oiQtyLabel
     */
    public function setQuantityLabel($oiQtyLabel)
    {
        $this->oiQtyLabel = $oiQtyLabel;
    }

    /**
     * @deprecated
     */
    public function setQtyLabel($oiQtyLabel)
    {
        $this->setQuantityLabel($oiQtyLabel);
    }

    public function setProductID($productid)
    {
        $this->pID = $productid;
    }

    public function getProductID()
    {
        return $this->pID;
    }

    public function setVariationID($variationID)
    {
        $this->pvID = $variationID;
    }

    public function getVariationID()
    {
        return $this->pvID;
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
        $em = dbORM::entityManager();

        return $em->find(__CLASS__, $oiID);
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
        $variation = $product->getVariation();

        $order = Order::getByID($oID);

        $orderItem = new self();
        $orderItem->setProductName($productName);
        $orderItem->setSKU($sku);
        $orderItem->setPricePaid($productPrice * $adjustRatio);
        $orderItem->setTax($tax);
        $orderItem->setTaxIncluded($taxIncluded);
        $orderItem->setTaxName($taxName);
        $orderItem->setQuantity($qty);
        $orderItem->setQuantityLabel($qtyLabel);
        $orderItem->setOrder($order);

        $orderItem->setProductID($data['product']['pID']);

        if ($variation) {
            $orderItem->setVariationID($variation->getID());
        }


        $orderItem->save();

        foreach ($data['productAttributes'] as $groupID => $valID) {
            if ('po' == substr($groupID, 0, 2)) {
                $groupID = str_replace("po", "", $groupID);
                $optionvalue = ProductOptionItem::getByID($valID);

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

            $optiongroup = ProductOption::getByID($groupID);
            if ($optiongroup) {
                $optionGroupName = $csm->t($optiongroup->getName(), 'optionName', null, $groupID);
                $orderItemOption = new OrderItemOption();
                $orderItemOption->setOrderItemOptionKey($optionGroupName);
                $orderItemOption->setOrderItemOptionHandle($optiongroup->getHandle());
                $orderItemOption->setOrderItemOptionValue($optionvalue);
                $orderItemOption->setOrderItem($orderItem);
                $orderItemOption->save();
            }
        }

        return $orderItem;
    }

    public function getSubTotal()
    {
        $price = $this->getPricePaid();
        $qty = $this->getQuantity();
        $subtotal = $qty * $price;

        return $subtotal;
    }

    public function getProductOptions()
    {
        return Database::connection()->GetAll("SELECT * FROM CommunityStoreOrderItemOptions WHERE oiID=?", $this->oiID);
    }

    public function getProductObject()
    {
        return Product::getByID($this->getProductID());
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
