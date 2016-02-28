<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Order;

use Database;

/**
 * @Entity
 * @Table(name="CommunityStoreOrderItemDiscounts")
 */
class OrderItemDiscount
{
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue
     */
    protected $oidID;

    /**
     * @ManyToOne(targetEntity="Concrete\Package\CommunityStore\Src\CommunityStore\Order\OrderItem")
     * @JoinColumn(name="oiID", referencedColumnName="oiID", onDelete="CASCADE")
     */
    protected $orderItem;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $oidName;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $oidDisplay;

    /**
     * @Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    protected $oidValue;

    /**
     * @Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    protected $oidPercentage;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $oidDeductFrom;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $oidCode;

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
