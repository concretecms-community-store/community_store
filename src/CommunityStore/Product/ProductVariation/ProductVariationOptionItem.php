<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductVariation;

use Doctrine\ORM\Mapping as ORM;
use Concrete\Core\Support\Facade\DatabaseORM as dbORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="CommunityStoreProductVariationOptionItems")
 */
class ProductVariationOptionItem
{
    /**
     * @ORM\Id @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $pvoiID;

    /**
     * @ORM\ManyToOne(targetEntity="Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductVariation\ProductVariation", inversedBy="options", cascade={"persist"})
     * @ORM\JoinColumn(name="pvID", referencedColumnName="pvID", onDelete="CASCADE")
     */
    protected $variation;

    /**
     * @ORM\ManyToOne(targetEntity="Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductOption\ProductOptionItem", inversedBy="variationoptionitems")
     * @ORM\JoinColumn(name="poiID", referencedColumnName="poiID", onDelete="CASCADE")
     */
    protected $option;

    public function getID()
    {
        return $this->pvoiID;
    }

    public function setVariation($variation)
    {
        $this->variation = $variation;
    }

    public function getVariation()
    {
        return $this->variation;
    }

    public function setOption($option)
    {
        $this->option = $option;
    }

    public function getOption()
    {
        return $this->option;
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
