<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductType;

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
    protected $type;

    /**
     * @ORM\OneToMany(targetEntity="Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductType\ProductTypeLayoutSetControl", mappedBy="set",cascade={"persist"}))
     * @ORM\OrderBy({"sortOrder" = "ASC"})
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




}
