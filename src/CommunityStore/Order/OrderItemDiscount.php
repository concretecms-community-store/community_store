<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Order;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="CommunityStoreOrderItemDiscounts")
 */
class OrderItemDiscount
{
    /**
     * @ORM\Id @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $oidID;

    /**
     * @ORM\ManyToOne(targetEntity="Concrete\Package\CommunityStore\Src\CommunityStore\Order\OrderItem")
     * @ORM\JoinColumn(name="oiID", referencedColumnName="oiID", onDelete="CASCADE")
     */
    protected $orderItem;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $oidName;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $oidDisplay;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    protected $oidValue;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    protected $oidPercentage;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $oidDeductFrom;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $oidCode;

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
