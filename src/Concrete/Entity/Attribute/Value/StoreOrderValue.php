<?php

namespace Concrete\Package\CommunityStore\Entity\Attribute\Value;

use Concrete\Core\Entity\Attribute\Value\AbstractValue;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="CommunityStoreOrderAttributeValues"
 * )
 */
class StoreOrderValue extends AbstractValue
{
    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="\Concrete\Package\CommunityStore\Src\CommunityStore\Order\Order")
     * @ORM\JoinColumn(name="oID", referencedColumnName="oID")
     */
    protected $order;

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
}
