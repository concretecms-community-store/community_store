<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Product;

use Doctrine\ORM\Mapping as ORM;
use Concrete\Core\Support\Facade\DatabaseORM as dbORM;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product;

/**
 * @ORM\Entity
 * @ORM\Table(name="CommunityStoreProductPriceTiers")
 */
class ProductPriceTier
{
    /**
     * @ORM\Id @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $ptID;

    /**
     * @ORM\Column(type="integer")
     */
    protected $pID;

    /**
     * @ORM\ManyToOne(targetEntity="Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product", inversedBy="priceTiers", cascade={"persist"})
     * @ORM\JoinColumn(name="pID", referencedColumnName="pID", onDelete="CASCADE")
     */
    protected $product;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    protected $ptPrice;

    /**
     * @ORM\Column(type="integer")
     */
    protected $ptFrom;

    /**
     * @ORM\Column(type="integer")
     */
    protected $ptTo;

    public function setProduct($product)
    {
        return $this->product = $product;
    }

    public function getProductID()
    {
        return $this->pID;
    }

    public function getPrice()
    {
        return $this->ptPrice;
    }

    public function setPrice($ptPrice)
    {
        $this->ptPrice = $ptPrice;
    }

    public function getFrom()
    {
        return $this->ptFrom;
    }

    public function setFrom($ptFrom)
    {
        $this->ptFrom = $ptFrom;
    }

    public function getTo()
    {
        return $this->ptTo;
    }

    public function setTo($ptTo)
    {
        $this->ptTo = $ptTo;
    }

    public static function getByID($ptID)
    {
        $em = dbORM::entityManager();

        return $em->find('Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductPriceTier', $ptID);
    }

    public static function addPriceTiersForProduct(array $data, Product $product)
    {
        //clear out existing groups
        self::removePriceTiersForProduct($product);

        $count = 0;

        //add new ones.
        if (!empty($data['ptFrom'])) {
            foreach ($data['ptFrom'] as $gID) {
                if ('' != $data['ptPrice'][$count] && $data['ptFrom'][$count] && $data['ptTo'][$count]) {
                    self::add($product, $data['ptFrom'][$count], $data['ptTo'][$count], $data['ptPrice'][$count]);
                }
                ++$count;
            }
        }
    }

    public static function removePriceTiersForProduct(Product $product)
    {
        $em = dbORM::entityManager();
        $priceTiers = $em->getRepository('Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductPriceTier')->findBy(['pID' => $product->getID()]);

        foreach ($priceTiers as $tier) {
            $tier->delete();
        }
    }

    public static function add($product, $from, $to, $price)
    {
        $productPriceTier = new self();
        $productPriceTier->setProduct($product);
        $productPriceTier->setFrom($from);
        $productPriceTier->setTo($to);
        $productPriceTier->setPrice($price);
        $productPriceTier->save();

        return $productPriceTier;
    }

    public function __clone()
    {
        if (isset($this->id) && $this->id) {
            $this->setID(null);
            $this->setProductID(null);
        }
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
