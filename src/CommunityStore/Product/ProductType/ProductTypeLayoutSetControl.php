<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductType;

use Doctrine\ORM\Mapping as ORM;
use Concrete\Core\Support\Facade\DatabaseORM as dbORM;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product;

/**
 * @ORM\Entity
 * @ORM\Table(name="CommunityStoreProductTypeLayoutSetControl")
 */
class ProductTypeLayoutSetControl
{
    /**
     * @ORM\Id @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $ptlscID;

    /**
     * @ORM\ManyToOne(targetEntity="Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductType\ProductTypeLayoutSet", inversedBy="controls")
     * @ORM\JoinColumn(name="ptlsID", referencedColumnName="ptlsID", onDelete="CASCADE")
     */
    protected $set;

    /**
     * @ORM\ManyToOne(targetEntity="\Concrete\Core\Entity\Attribute\Key\Key")
     * @ORM\JoinColumn(name="akID", referencedColumnName="akID", onDelete="CASCADE")
     **/
    protected $attribute_key;

    /**
     * @ORM\Column(type="text",nullable=true)
     */
    protected $ptlsDescription;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $customLabel;

    /**
     * @ORM\Column(type="integer")
     */
    protected $displayOrder;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $hidden;


    public function getDisplayLabel($admin = false) {
        if ($this->customLabel) {
            if ($admin) {
                return $this->customLabel . ' (' . $this->getAttributeKey()->getAttributeKeyName() . ')';
            } else {
                return $this->customLabel;
            }
        } else {
            return $this->getAttributeKey()->getAttributeKeyName();
        }
    }

    public function getProductTypeLayoutSetControlID() {
        return $this->ptlscID;
    }


    public function getLayoutSet()
    {
        return $this->set;
    }

    public function setLayoutSet($set)
    {
        $this->set = $set;
    }

    public function getAttributeKey()
    {
        return $this->attribute_key;
    }


    public function setAttributeKey($attribute_key)
    {
        $this->attribute_key = $attribute_key;
    }


    public function getDescription()
    {
        return $this->ptlsDescription;
    }


    public function setPtlsDescription($ptlsDescription)
    {
        $this->ptlsDescription = $ptlsDescription;
    }


    public function getCustomLabel()
    {
        return $this->customLabel;
    }


    public function setCustomLabel($customLabel)
    {
        $this->customLabel = trim($customLabel);
    }

    public function getDisplayOrder()
    {
        return $this->displayOrder;
    }

    public function setDisplayOrder($displayOrder)
    {
        $this->displayOrder = $displayOrder;
    }

    public function getHidden()
    {
        return $this->hidden;
    }

    public function setHidden($hidden)
    {
        $this->hidden = $hidden;
    }

    public static function getByID($ptID)
    {
        $em = dbORM::entityManager();
        return $em->find(get_called_class(), $ptID);
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
