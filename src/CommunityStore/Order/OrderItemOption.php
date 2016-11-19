<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Order;

use Database;

/**
 * @Entity
 * @Table(name="CommunityStoreOrderItemOptions")
 */
class OrderItemOption
{
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue
     */
    protected $oioID;

    /**
     * @ManyToOne(targetEntity="Concrete\Package\CommunityStore\Src\CommunityStore\Order\OrderItem")
     * @JoinColumn(name="oiID", referencedColumnName="oiID", onDelete="CASCADE")
     */
    protected $orderItem;

    /**
     * @Column(type="string")
     */
    protected $oioKey;

    /**
     * @Column(type="text")
     */
    protected $oioValue;

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
