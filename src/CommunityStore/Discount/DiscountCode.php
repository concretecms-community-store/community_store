<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Discount;

use Concrete\Package\CommunityStore\Src\CommunityStore\Discount\DiscountRule as StoreDiscountRule;
use Doctrine\ORM\Mapping\Column;
use Loader;
use Database;
use Session;

/**
 * @Entity
 * @Table(name="CommunityStoreDiscountCodes")
 */
class DiscountCode
{
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue
     */
    protected $dcID;

    /**
     * @ManyToOne(targetEntity="Concrete\Package\CommunityStore\Src\CommunityStore\Discount\DiscountRule", inversedBy="codes")
     * @JoinColumn(name="drID", referencedColumnName="drID")
     */
    private $discountRule;

    /**
     * @Column(type="string")
     */
    protected $dcCode;

    /**
     * @Column(type="integer", nullable=true)
     */
    protected $oID;

    /**
     * @Column(type="datetime")
     */
    protected $dcDateAdded;

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
    public function getDiscountCodeCode()
    {
        return $this->dcCode;
    }

    /**
     * @param mixed $dcCode
     */
    public function setDiscountCodeCode($dcCode)
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
    public function getDiscountCodeDateAdded()
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
    public function setDiscountCodeDateAdded($dcDateAdded)
    {
        $this->dcDateAdded = $dcDateAdded;
    }

    public static function getByID($dcID)
    {
        $db = Database::connection();
        $em = $db->getEntityManager();

        return $em->find('Concrete\Package\CommunityStore\Src\CommunityStore\Discount\DiscountCode', $dcID);
    }

    public static function getByCode($code)
    {
        $db = Database::connection();
        $em = $db->getEntityManager();

        return $em->find('Concrete\Package\CommunityStore\Src\CommunityStore\Discount\DiscountCode', array('dcCode' => $code));
    }

    public static function add($discountRule, $code)
    {
        $discountCode = new self();
        $discountCode->setDiscountRule($discountRule);
        $discountCode->setDiscountCodeCode($code);
        $discountCode->setDiscountCodeDateAdded(new \DateTime());
        $discountCode->save();

        return $discountCode;
    }

    public function save()
    {
        $em = Database::connection()->getEntityManager();
        $em->persist($this);
        $em->flush();
    }

    public function delete()
    {
        $em = Database::connection()->getEntityManager();
        $em->remove($this);
        $em->flush();
    }

    public static function validate($args)
    {
        $e = Loader::helper('validation/error');

        return $e;
    }

    //TODO: Move to Discounts
    public static function storeCode($code)
    {
        $rule = StoreDiscountRule::findDiscountRuleByCode($code);

        if (!empty($rule)) {
            Session::set('communitystore.code', $code);

            return true;
        }

        return false;
    }

    public static function hasCode()
    {
        return (bool) Session::get('communitystore.code');
    }

    public static function getCode()
    {
        return Session::get('communitystore.code');
    }

    public static function clearCode()
    {
        Session::set('communitystore.code', '');
    }
}
