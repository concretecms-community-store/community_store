<?php
namespace Concrete\Package\CommunityStore\Attribute\Value;

use Concrete\Core\Entity\Attribute\Value\AbstractValue;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="CommunityStoreProductAttributeValues"
 * )
 */
class StoreProductValue extends AbstractValue
{
    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="\Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product")
     * @ORM\JoinColumn(name="pID", referencedColumnName="pID")
     */
    protected $product;

    /**
     * @return mixed
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @param mixed $order
     */
    public function setProduct($product)
    {
        $this->product = $product;
    }
}