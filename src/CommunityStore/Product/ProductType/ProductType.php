<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductType;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Concrete\Core\Support\Facade\DatabaseORM as dbORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="CommunityStoreProductTypes")
 */
class ProductType
{
    /**
     * @ORM\Id @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $ptID;

    /**
     * @ORM\OneToMany(targetEntity="Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product", mappedBy="type",cascade={"persist"}))
     * @ORM\OrderBy({"sortOrder" = "ASC"})
     */
    protected $products;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $ptHandle;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $ptName;

    /**
     * @ORM\Column(type="text",nullable=true)
     */
    protected $ptDescription;

    /**
     * @ORM\OneToMany(targetEntity="Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductType\ProductTypeLayoutSet", mappedBy="productType",cascade={"persist"}))
     * @ORM\OrderBy({"ptlsDisplayOrder" = "ASC"})
     */
    protected $layoutSets;


    public function __construct()
    {
        $this->layoutSets = new ArrayCollection();
    }



    public function getLayoutSets()
    {
        return $this->layoutSets;
    }

    /**
     * @return mixed
     */
    public function getTypeID()
    {
        return $this->ptID;
    }

    /**
     * @return mixed
     */
    public function getTypeName()
    {
        return $this->ptName;
    }

    /**
     * @param mixed $ptName
     */
    public function setTypeName($ptName)
    {
        $this->ptName = $ptName;
    }

    /**
     * @return mixed
     */
    public function getHandle()
    {
        return $this->ptHandle;
    }

    /**
     * @param mixed $ptName
     */
    public function setHandle($ptHandle)
    {
        $this->ptHandle = $ptHandle;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->ptDescription;
    }

    /**
     * @param mixed $ptDescription
     */
    public function setDescription($ptDescription)
    {
        $this->ptDescription = $ptDescription;
    }



    public static function getByID($ptID)
    {
        $em = dbORM::entityManager();
        return $em->find(get_called_class(), $ptID);
    }


    public static function add($name, $handle, $description)
    {
        $productType = new self();
        $productType->setTypeName($name);
        $productType->setHandle($handle);
        $productType->setDescription($description);
        $productType->save();

        return $productType;
    }

    public function update($name, $handle, $description)
    {
        $this->setTypeName($name);
        $this->setHandle($handle);
        $this->setDescription($description);
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
