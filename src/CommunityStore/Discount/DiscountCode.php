<?php

namespace Concrete\Package\CommunityStore\Src\CommunityStore\Discount;

use Concrete\Core\Support\Facade\Application;
use Concrete\Core\Support\Facade\DatabaseORM as dbORM;
use Concrete\Core\Support\Facade\Session;
use Doctrine\ORM\Mapping as ORM;

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
     * @ORM\ManyToOne(targetEntity="Concrete\Package\CommunityStore\Src\CommunityStore\Discount\DiscountRule", inversedBy="codes")
     * @ORM\JoinColumn(name="drID", referencedColumnName="drID", onDelete="CASCADE")
     */
    private $discountRule;

    /**
     * @return mixed
     */
    public function getID()
    {
        return $this->dcID;
    }

    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->dcCode;
    }

    /**
     * @param mixed $dcCode
     */
    public function setCode($dcCode)
    {
        $this->dcCode = $dcCode;
    }

    /**
     * @return mixed
     */
    public function getDiscountRule()
    {
        return $this->discountRule;
    }

    /**
     * @param mixed $discountRule
     */
    public function setDiscountRule($discountRule)
    {
        $this->discountRule = $discountRule;
    }

    /**
     * @return mixed
     */
    public function getOID()
    {
        return $this->oID;
    }

    /**
     * @param mixed $oID
     */
    public function setOID($oID)
    {
        $this->oID = $oID;
    }

    /**
     * @return mixed
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
     * @param mixed $dcDateAdded
     */
    public function setDateAdded($dcDateAdded)
    {
        $this->dcDateAdded = $dcDateAdded;
    }

    public static function getByID($dcID)
    {
        $em = dbORM::entityManager();

        return $em->find(__CLASS__, $dcID);
    }

    public static function getByCode($code)
    {
        $em = dbORM::entityManager();

        return $em->getRepository(__CLASS__)->findOneBy(['dcCode' => $code]);
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
        return Application::getFacadeApplication()->make('helper/validation/error');
    }

    public static function storeCartCode($code)
    {
        $rule = DiscountRule::findDiscountRuleByCode($code);

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
