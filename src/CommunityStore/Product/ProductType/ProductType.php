<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductType;


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
    protected $ptName;

    /**
     * @ORM\Column(type="text",nullable=true)
     */
    protected $ptDescription;

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
    public function getName()
    {
        return $this->ptName;
    }

    /**
     * @param mixed $ptName
     */
    public function setName($ptName)
    {
        $this->ptName = $ptName;
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


    public static function add($name, $description)
    {
        $productGroup = new self();
        $productGroup->setName($name);
        $productGroup->setDescription($description);
        $productGroup->save();

        return $productGroup;
    }

    public function update($name, $description)
    {
        $this->setName($name);
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
