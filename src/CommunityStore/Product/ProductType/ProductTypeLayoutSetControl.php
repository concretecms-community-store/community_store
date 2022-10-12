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


}
