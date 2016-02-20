<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Discount;

use Database;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\Common\Collections\ArrayCollection;

/**
* @Entity
* @Table(name="CommunityStoreDiscountRules")
*/
class DiscountRule
{
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue
     */
    protected $drID;

    /**
     * @Column(type="string")
     */
    protected $drName;

    /**
     * @Column(type="boolean")
     */
    protected $drEnabled;

    /**
     * @Column(type="string", length=255,nullable=true)
     */
    protected $drDisplay;

    /**
     * @Column(type="text")
     */
    protected $drDescription;

    /**
     * @Column(type="string", length=20)
     */
    protected $drDeductType;

    /**
     * @Column(type="decimal", precision=10, scale=2,nullable=true)
     */
    protected $drValue;

    /**
     * @Column(type="decimal", precision=5, scale=2,nullable=true)
     */
    protected $drPercentage;

    /**
     * @Column(type="string", length=20)
     */
    protected $drDeductFrom;

    /**
     * @Column(type="string", length=20)
     */
    protected $drTrigger;

    /**
     * @Column(type="boolean")
     */
    protected $drSingleUseCodes;

    /**
     * @Column(type="string",nullable=true)
     */
    protected $drCurrency;

    /**
     * @Column(type="datetime",nullable=true)
     */
    protected $drValidFrom;

    /**
     * @Column(type="datetime",nullable=true)
     */
    protected $drValidTo;

    /**
     * @Column(type="datetime")
     */
    protected $drDateAdded;

    /**
     * @Column(type="datetime",nullable=true)
     */
    protected $drDeleted;


    /**
     * @OneToMany(targetEntity="Concrete\Package\CommunityStore\Src\CommunityStore\Discount\DiscountCode", mappedBy="discountRule")
     */
    private $codes;

    /**
     * @return mixed
     */
    public function getCodes()
    {
        return $this->codes;
    }

    public function __construct() {
        $this->codes = new ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function getID()
    {
        return $this->drID;
    }


    /**
     * @return mixed
     */
    public function getDiscountName()
    {
        return $this->drName;
    }

    /**
     * @param mixed $drName
     */
    public function setDiscountName($drName)
    {
        $this->drName = $drName;
    }

    /**
     * @return mixed
     */
    public function getEnabled()
    {
        return $this->drEnabled;
    }

    public function isEnabled()
    {
        return (bool)$this->drEnabled;
    }

    /**
     * @param mixed $drEnabled
     */
    public function setEnabled($drEnabled)
    {
        $this->drEnabled = $drEnabled;
    }

    /**
     * @return mixed
     */
    public function getDiscountDisplay()
    {
        return $this->drDisplay;
    }

    /**
     * @param mixed $drDisplay
     */
    public function setDiscountDisplay($drDisplay)
    {
        $this->drDisplay = $drDisplay;
    }

    /**
     * @return mixed
     */
    public function getDiscountDescription()
    {
        return $this->drDescription;
    }

    /**
     * @param mixed $drDescription
     */
    public function setDiscountDescription($drDescription)
    {
        $this->drDescription = $drDescription;
    }

    /**
     * @return mixed
     */
    public function getDiscountDeductType()
    {
        return $this->drDeductType;
    }

    /**
     * @param mixed $drDeductType
     */
    public function setDiscountDeductType($drDeductType)
    {
        $this->drDeductType = $drDeductType;
    }

    /**
     * @return mixed
     */
    public function getDiscountValue()
    {
        return $this->drValue;
    }

    /**
     * @param mixed $drValue
     */
    public function setDiscountValue($drValue)
    {
        $this->drValue = $drValue;
    }

    /**
     * @return mixed
     */
    public function getDiscountPercentage()
    {
        return $this->drPercentage;
    }

    /**
     * @param mixed $drPercentage
     */
    public function setDiscountPercentage($drPercentage)
    {
        $this->drPercentage = $drPercentage;
    }

    /**
     * @return mixed
     */
    public function getDiscountDeductFrom()
    {
        return $this->drDeductFrom;
    }

    /**
     * @param mixed $drDeductFrom
     */
    public function setDiscountDeductFrom($drDeductFrom)
    {
        $this->drDeductFrom = $drDeductFrom;
    }

    /**
     * @return mixed
     */
    public function getDiscountTrigger()
    {
        return $this->drTrigger;
    }

    /**
     * @param mixed $drTrigger
     */
    public function setDiscountTrigger($drTrigger)
    {
        $this->drTrigger = $drTrigger;
    }

    public function requiresCode() {
        return ($this->drTrigger == 'code');
    }

    /**
     * @return mixed
     */
    public function getDiscountSingleUseCodes()
    {
        return $this->drSingleUseCodes;
    }

    public function isSingleUse() {
        return (bool)$this->drSingleUseCodes;
    }


    /**
     * @param mixed $drSingleUseCodes
     */
    public function setDiscountSingleUseCodes($drSingleUseCodes)
    {
        $this->drSingleUseCodes = $drSingleUseCodes;
    }

    /**
     * @return mixed
     */
    public function getDiscountCurrency()
    {
        return $this->drCurrency;
    }

    /**
     * @param mixed $drCurrency
     */
    public function setDiscountCurrency($drCurrency)
    {
        $this->drCurrency = $drCurrency;
    }

    /**
     * @return mixed
     */
    public function getDiscountValidFrom()
    {
        return $this->drValidFrom;
    }

    /**
     * @param mixed $drValidFrom
     */
    public function setDiscountValidFrom($drValidFrom)
    {
        $this->drValidFrom = $drValidFrom;
    }

    /**
     * @return mixed
     */
    public function getDiscountValidTo()
    {
        return $this->drValidTo;
    }

    /**
     * @param mixed $drValidTo
     */
    public function setDiscountValidTo($drValidTo)
    {
        $this->drValidTo = $drValidTo;
    }

    /**
     * @return mixed
     */
    public function getDiscountDateAdded()
    {
        return $this->drDateAdded;
    }

    /**
     * @param mixed $drDateAdded
     */
    public function setDiscountDateAdded($drDateAdded)
    {
        $this->drDateAdded = $drDateAdded;
    }

    /**
     * @return mixed
     */
    public function getDiscountDeleted()
    {
        return $this->drDeleted;
    }

    /**
     * @param mixed $drDeleted
     */
    public function setDiscountDeleted($drDeleted)
    {
        $this->drDeleted = $drDeleted;
    }

    public function getDisplay() {
        $display = trim($this->drDisplay);

        if ($display) {
            return $display;
        } else {
            if ($this->drDeductType == 'percentage') {
                return $this->drPercentage . ' ' . t('off');
            }

            if ($this->drDeductType == 'value') {
                return StorePrice::format($this->drValue) . ' ' . t('off');
            }
        }
    }


    public static function getByID($drID) {
        $db = Database::connection();
        $em = $db->getEntityManager();
        return $em->find('Concrete\Package\CommunityStore\Src\CommunityStore\Discount\DiscountRule', $drID);
    }

    public static function discountsWithCodesExist() {
        $db = Database::connection();
        $data = $db->GetRow("SELECT count(*) as codecount FROM CommunityStoreDiscountRules WHERE drEnabled =1 "); // TODO

        return ($data['codecount'] > 0);
    }

    public static function findAutomaticDiscounts($user = null, $productlist = array()) {
        $db = Database::connection();
        $result = $db->query("SELECT * FROM CommunityStoreDiscountRules
              WHERE drEnabled = 1
              AND drDeleted IS NULL
              AND drTrigger = 'auto'
              AND (drPercentage > 0 or drValue  > 0)
              AND (drValidFrom IS NULL OR drValidFrom <= NOW())
              AND (drValidTo IS NULL OR drValidTo > NOW())
              ");

        $discounts = array();
        while ($row = $result->fetchRow()) {
            $discounts[] =  self::getByID($row['drID']);
        }
        return $discounts;
    }

    public function retrieveStatistics() {
        $db = Database::connection();
        $r = $db->query("select count(*) as total, COUNT(CASE WHEN oID is NULL THEN 1 END) AS available from CommunityStoreDiscountCodes where drID = ?", array($this->drID));
        $r = $r->fetchRow();
        $this->totalCodes = $r['total'];
        $this->availableCodes = $r['available'];
        return $r;
    }

    public static function findDiscountRuleByCode($code, $user = null) {
        $db = Database::connection();

        $result = $db->query("SELECT * FROM CommunityStoreDiscountCodes as dc
        LEFT JOIN CommunityStoreDiscountRules as dr on dc.drID = dr.drID
        WHERE dcCode = ?
        AND oID IS NULL
        AND drDeleted IS NULL
        AND  drEnabled = '1'
        AND drTrigger = 'code'
        AND (drValidFrom IS NULL OR drValidFrom <= NOW())
        AND (drValidTo IS NULL OR drValidTo > NOW())", array($code));

        $discounts = array();

        while ($row = $result->fetchRow()) {
            $discounts[] =  self::getByID($row['drID']);
        }

        return $discounts;
    }

    public static function add($data)
    {
        $discountRule = new self();
        self::loadData($discountRule, $data);
        $discountRule->save();
        return $discountRule;
    }

    public static function loadData($discountRule, $data)
    {
        $discountRule->setEnabled($data['drEnabled'] ? true : false);
        $discountRule->setDiscountName($data['drName']);
        $discountRule->setDiscountDisplay($data['drDisplay']);
        $discountRule->setDiscountDeductType($data['drDeductType']);
        $discountRule->setDiscountDeductFrom($data['drDeductFrom']);
        $discountRule->setDiscountPercentage($data['drPercentage']);
        $discountRule->setDiscountValue($data['drValue']);
        $discountRule->setDiscountSingleUseCodes($data['drPercentage'] ? true : false );
        $discountRule->setDiscountTrigger($data['drTrigger']);
        $discountRule->setDiscountDescription($data['drDescription']);
        $discountRule->setDiscountDateAdded(new \DateTime());
    }

    public static function edit($drID, $data)
    {
        $discountRule = self::getByID($drID);

        if ($discountRule) {
            self::loadData($discountRule, $data);
            $discountRule->save();
            return $discountRule;
        } else {
            return false;
        }
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

}



?>