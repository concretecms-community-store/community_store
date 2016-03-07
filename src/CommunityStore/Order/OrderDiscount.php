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
     * @ManyToOne(targetEntity="Concrete\Package\CommunityStore\Src\CommunityStore\Order\Order")
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
     * @return integer
     */
    public function getID()
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
    public function getName()
    {
        return $this->odName;
    }

    /**
     * @param mixed $odName
     */
    public function setName($odName)
    {
        $this->odName = $odName;
    }

    /**
     * @return mixed
     */
    public function getDisplay()
    {
        return $this->odDisplay;
    }

    /**
     * @param mixed $odDisplay
     */
    public function setDisplay($odDisplay)
    {
        $this->odDisplay = $odDisplay;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->odValue;
    }

    /**
     * @param mixed $odValue
     */
    public function setValue($odValue)
    {
        $this->odValue = $odValue;
    }

    /**
     * @return mixed
     */
    public function getPercentage()
    {
        return $this->odPercentage;
    }

    /**
     * @param mixed $odPercentage
     */
    public function setPercentage($odPercentage)
    {
        $this->odPercentage = $odPercentage;
    }

    /**
     * @return mixed
     */
    public function getDeductFrom()
    {
        return $this->odDeductFrom;
    }

    /**
     * @param mixed $odDeductFrom
     */
    public function setDeductFrom($odDeductFrom)
    {
        $this->odDeductFrom = $odDeductFrom;
    }

    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->odCode;
    }

    /**
     * @param mixed $odCode
     */
    public function setCode($odCode)
    {
        $this->odCode = $odCode;
    }

    public function save()
    {
        $em = \Database::connection()->getEntityManager();
        $em->persist($this);
        $em->flush();
    }

    public function delete()
    {
        $em = \Database::connection()->getEntityManager();
        $em->remove($this);
        $em->flush();
    }
}
