<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductType;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Concrete\Core\Support\Facade\DatabaseORM as dbORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="CommunityStoreProductTypeLayoutSets")
 */
class ProductTypeLayoutSet
{
    /**
     * @ORM\Id @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $ptlsID;

    /**
     * @ORM\ManyToOne(targetEntity="Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductType\ProductType", inversedBy="products")
     * @ORM\JoinColumn(name="ptID", referencedColumnName="ptID", onDelete="CASCADE")
     */
    protected $productType;

    /**
     * @ORM\OneToMany(targetEntity="Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductType\ProductTypeLayoutSetControl", mappedBy="set",cascade={"persist"}))
     * @ORM\OrderBy({"displayOrder" = "ASC"})
     */
    protected $controls;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $ptlsName;

    /**
     * @ORM\Column(type="text",nullable=true)
     */
    protected $ptlsDescription;

    /**
     * @ORM\Column(type="integer")
     */
    protected $ptlsDisplayOrder;

    public function getLayoutSetID() {
        return $this->ptlsID;
    }


    public function getProductType()
    {
        return $this->productType;
    }

    public function setProductType($type)
    {
        $this->productType = $type;
    }

    public function getLayoutSetName()
    {
        return $this->ptlsName;
    }

    public function setLayoutSetName($ptlsName)
    {
        $this->ptlsName = $ptlsName;
    }

    public function getLayoutSetDescription()
    {
        return $this->ptlsDescription;
    }

    public function setLayoutSetDescription($ptlsDescription)
    {
        $this->ptlsDescription = $ptlsDescription;
    }

    public function getLayoutSetDisplayOrder()
    {
        return $this->ptlsDisplayOrder;
    }

    public function setLayoutSetDisplayOrder($ptlsDisplayOrder)
    {
        $this->ptlsDisplayOrder = $ptlsDisplayOrder;
    }

    public function getLayoutSetControls() {
        return $this->controls;
    }


    public function __construct()
    {
        $this->controls = new ArrayCollection();
    }

    public static function add($productType, $name, $description)
    {
        $typeSet = new self();
        $typeSet->setProductType($productType);
        $typeSet->setLayoutSetName($name);
        $typeSet->setLayoutSetDescription($description);
        $typeSet->setLayoutSetDisplayOrder(0);
        $typeSet->save();

        return $typeSet;
    }

    public static function getByID($ptID)
    {
        $em = dbORM::entityManager();
        return $em->find(get_called_class(), $ptID);
    }


    public function update($name, $description)
    {
        $this->setLayoutSetName($name);
        $this->setLayoutSetDescription($description);
        $this->save();

        return $this;
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
