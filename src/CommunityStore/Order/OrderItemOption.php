<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Order;

use Doctrine\ORM\Mapping as ORM;

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
     * @ORM\Column(type="text")
     */
    protected $oioValue;

    /**
     * @ORM\return mixed
     */
    public function getID()
    {
        return $this->oioID;
    }

    /**
     * @ORM\return mixed
     */
    public function getOrderItem()
    {
        return $this->orderItem;
    }

    /**
     * @ORM\param mixed $orderItem
     */
    public function setOrderItem(OrderItem $orderItem)
    {
        $this->orderItem = $orderItem;
    }

    /**
     * @ORM\return mixed
     */
    public function getOrderItemOptionKey()
    {
        return $this->oioKey;
    }

    /**
     * @ORM\param mixed $oioKey
     */
    public function setOrderItemOptionKey($oioKey)
    {
        $this->oioKey = $oioKey;
    }

    /**
     * @ORM\return mixed
     */
    public function getOrderItemOptionValue()
    {
        return $this->oioValue;
    }

    /**
     * @ORM\param mixed $oioValue
     */
    public function setOrderItemOptionValue($oioValue)
    {
        $this->oioValue = $oioValue;
    }

    public static function getByID($oioID)
    {
        $em = \ORM::entityManager();

        return $em->find(get_class(), $oioID);
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
