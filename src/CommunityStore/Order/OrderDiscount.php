<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Order;

use Database;

/**
 * @Entity
 * @Table(name="CommunityStoreOrderDiscounts")
 */
class OrderDiscount
{
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue
     */
    protected $odID;

    /**
     * @ManyToOne(targetEntity="Concrete\Package\CommunityStore\Src\CommunityStore\Order\Order", inversedBy="orderDiscounts", cascade={"persist"})
     * @JoinColumn(name="oID", referencedColumnName="oID", onDelete="CASCADE")
     */
    protected $order;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $odName;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $odDisplay;

    /**
     * @Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    protected $odValue;

    /**
     * @Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    protected $odPercentage;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $odDeductFrom;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $odCode;

    /**
     * @return mixed
     */
    public function getOrderDiscountID()
    {
        return $this->odID;
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

    /**
     * @return mixed
     */
    public function getOrderDiscountName()
    {
        return $this->odName;
    }

    /**
     * @param mixed $odName
     */
    public function setOrderDiscountName($odName)
    {
        $this->odName = $odName;
    }

    /**
     * @return mixed
     */
    public function getOrderDiscountDisplay()
    {
        return $this->odDisplay;
    }

    /**
     * @param mixed $odDisplay
     */
    public function setOrderDiscountDisplay($odDisplay)
    {
        $this->odDisplay = $odDisplay;
    }

    /**
     * @return mixed
     */
    public function getOrderDiscountValue()
    {
        return $this->odValue;
    }

    /**
     * @param mixed $odValue
     */
    public function setOrderDiscountValue($odValue)
    {
        $this->odValue = $odValue;
    }

    /**
     * @return mixed
     */
    public function getOrderDiscountPercentage()
    {
        return $this->odPercentage;
    }

    /**
     * @param mixed $odPercentage
     */
    public function setOrderDiscountPercentage($odPercentage)
    {
        $this->odPercentage = $odPercentage;
    }

    /**
     * @return mixed
     */
    public function getOrderDiscountDeductFrom()
    {
        return $this->odDeductFrom;
    }

    /**
     * @param mixed $odDeductFrom
     */
    public function setOrderDiscountDeductFrom($odDeductFrom)
    {
        $this->odDeductFrom = $odDeductFrom;
    }

    /**
     * @return mixed
     */
    public function getOrderDiscountCode()
    {
        return $this->odCode;
    }

    /**
     * @param mixed $odCode
     */
    public function setOrderDiscountCode($odCode)
    {
        $this->odCode = $odCode;
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
