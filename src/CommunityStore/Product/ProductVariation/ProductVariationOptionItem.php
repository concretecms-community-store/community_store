<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductVariation;

use Database;

/**
 * @Entity
 * @Table(name="CommunityStoreProductVariationOptionItems")
 */
class ProductVariationOptionItem
{
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue
     */
    protected $pvoiID;

    /**
     * @ManyToOne(targetEntity="Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductVariation\ProductVariation", inversedBy="options", cascade={"persist"})
     * @JoinColumn(name="pvID", referencedColumnName="pvID", onDelete="CASCADE")
     */
    protected $variation;

    /**
     * @ManyToOne(targetEntity="Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductOption\ProductOptionItem")
     * @JoinColumn(name="poiID", referencedColumnName="poiID", onDelete="CASCADE")
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
