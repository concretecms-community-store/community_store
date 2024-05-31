<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Order;

use Doctrine\ORM\Mapping as ORM;
use Concrete\Core\Support\Facade\DatabaseORM as dbORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="CommunityStoreOrderItemOptions")
 */
class OrderItemOption
{
    /**
     * @ORM\Id @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $oioID;

    /**
     * @ORM\ManyToOne(targetEntity="Concrete\Package\CommunityStore\Src\CommunityStore\Order\OrderItem")
     * @ORM\JoinColumn(name="oiID", referencedColumnName="oiID", onDelete="CASCADE")
     */
    protected $orderItem;

    /**
     * @ORM\Column(type="string")
     */
    protected $oioKey;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $oioHandle;

    /**
     * @ORM\Column(type="text")
     */
    protected $oioValue;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    protected $oioPriceAdjust;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    protected $oioWeightAdjust;


    /**
     * @return mixed
     */
    public function getID()
    {
        return $this->oioID;
    }

    /**
     * @return mixed
     */
    public function getOrderItem()
    {
        return $this->orderItem;
    }

    /**
     * @param mixed $orderItem
     */
    public function setOrderItem(OrderItem $orderItem)
    {
        $this->orderItem = $orderItem;
    }

    /**
     * @return mixed
     */
    public function getOrderItemOptionKey()
    {
        return $this->oioKey;
    }

    /**
     * @param mixed $oioKey
     */
    public function setOrderItemOptionKey($oioKey)
    {
        $this->oioKey = $oioKey;
    }

    /**
     * @param mixed $oioHandle
     */
    public function setOrderItemOptionHandle($oioHandle)
    {
        $this->oioHandle = $oioHandle;
    }

    /**
     * @return mixed
     */
    public function getOrderItemOptionValue()
    {
        return $this->oioValue;
    }

    /**
     * @param mixed $oioValue
     */
    public function setOrderItemOptionValue($oioValue)
    {
        $this->oioValue = $oioValue;
    }

    /**
     * @return mixed
     */
    public function getOrderItemOptionHandle()
    {
        return $this->oioHandle;
    }

    /**
     * @return mixed
     */
    public function getOrderItemOptionPriceAdjust()
    {
        return $this->oioPriceAdjust;
    }

    /**
     * @param mixed $oioPriceAdjust
     */
    public function setOrderItemOptionPriceAdjust($priceAdjust)
    {
        $this->oioPriceAdjust = $priceAdjust;
    }

    /**
     * @return mixed
     */
    public function getOrderItemOptionWeightAdjust()
    {
        return $this->oioWeightAdjust;
    }

    /**
     * @param mixed $oioWeightAdjust
     */
    public function setOrderItemOptionWeightAdjust($weightAdjust)
    {
        $this->oioWeightAdjust = $weightAdjust;
    }



    public static function getByID($oioID)
    {
        $em = dbORM::entityManager();

        return $em->find(__CLASS__, $oioID);
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
