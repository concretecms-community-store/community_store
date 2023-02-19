<?php

namespace Concrete\Package\CommunityStore\Src\CommunityStore\Order;

use Concrete\Core\Support\Facade\DatabaseORM as dbORM;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="CommunityStoreOrderDiscounts")
 */
class OrderDiscount
{
    /**
     * @ORM\Id @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $odID;

    /**
     * @ORM\ManyToOne(targetEntity="Concrete\Package\CommunityStore\Src\CommunityStore\Order\Order")
     * @ORM\JoinColumn(name="oID", referencedColumnName="oID", onDelete="CASCADE")
     */
    protected $order;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $odName;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $odDisplay;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $odDeductType;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    protected $odValue;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    protected $odPercentage;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $odDeductFrom;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $odCode;

    /**
     * @return int
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
    public function getDeductType()
    {
        return $this->odDeductType;
    }

    /**
     * @param mixed $odDeductType
     */
    public function setDeductType($odDeductType)
    {
        $this->odDeductType = $odDeductType;
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
