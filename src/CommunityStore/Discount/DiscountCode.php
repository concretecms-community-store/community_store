<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Discount;

use Doctrine\ORM\Mapping as ORM;
use Concrete\Core\Support\Facade\DatabaseORM as dbORM;
use Concrete\Package\CommunityStore\Src\CommunityStore\Discount\DiscountRule as StoreDiscountRule;
use Concrete\Core\Support\Facade\Session;
use Concrete\Core\Support\Facade\Application;

/**
 * @ORM\Entity
 * @ORM\Table(name="CommunityStoreDiscountCodes")
 */
class DiscountCode
{
    /**
     * @ORM\Id @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $dcID;

    /**
     * @ORM\ManyToOne(targetEntity="Concrete\Package\CommunityStore\Src\CommunityStore\Discount\DiscountRule", inversedBy="codes")
     * @ORM\JoinColumn(name="drID", referencedColumnName="drID", onDelete="CASCADE")
     */
    private $discountRule;

    /**
     * @ORM\Column(type="string")
     */
    protected $dcCode;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $oID;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $dcDateAdded;

    /**
     * @ORM\return mixed
     */
    public function getID()
    {
        return $this->dcID;
    }

    /**
     * @ORM\return mixed
     */
    public function getCode()
    {
        return $this->dcCode;
    }

    /**
     * @ORM\param mixed $dcCode
     */
    public function setCode($dcCode)
    {
        $this->dcCode = $dcCode;
    }

    /**
     * @ORM\return mixed
     */
    public function getDiscountRule()
    {
        return $this->discountRule;
    }

    /**
     * @ORM\param mixed $discountRule
     */
    public function setDiscountRule($discountRule)
    {
        $this->discountRule = $discountRule;
    }

    /**
     * @ORM\return mixed
     */
    public function getOID()
    {
        return $this->oID;
    }

    /**
     * @ORM\param mixed $oID
     */
    public function setOID($oID)
    {
        $this->oID = $oID;
    }

    /**
     * @ORM\return mixed
     */
    public function getDateAdded()
    {
        return $this->dcDateAdded;
    }

    public function isUsed()
    {
        return $this->oID > 0;
    }

    /**
     * @ORM\param mixed $dcDateAdded
     */
    public function setDateAdded($dcDateAdded)
    {
        $this->dcDateAdded = $dcDateAdded;
    }

    public static function getByID($dcID)
    {
        $em = dbORM::entityManager();

        return $em->find(get_class(), $dcID);
    }

    public static function getByCode($code)
    {
        $em = dbORM::entityManager();

        return $em->getRepository(get_class())->findOneBy(['dcCode' => $code]);
    }

    public static function add($discountRule, $code)
    {
        $discountCode = new self();
        $discountCode->setDiscountRule($discountRule);
        $discountCode->setCode($code);
        $discountCode->setDateAdded(new \DateTime());
        $discountCode->save();

        return $discountCode;
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

    public static function validate($args)
    {
        $e = Application::getFacadeApplication()->make('helper/validation/error');

        return $e;
    }

    public static function storeCartCode($code)
    {
        $rule = StoreDiscountRule::findDiscountRuleByCode($code);

        if (!empty($rule)) {
            Session::set('communitystore.code', $code);

            return true;
        }

        return false;
    }

    public static function hasCartCode()
    {
        return (bool) Session::get('communitystore.code');
    }

    public static function getCartCode()
    {
        return Session::get('communitystore.code');
    }

    public static function clearCartCode()
    {
        Session::set('communitystore.code', '');
    }
}
