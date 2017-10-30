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
     * @Column(type="string",nullable=true)
     */
    protected $drProductGroups;

    /**
     * @Column(type="string",nullable=true)
     */
    protected $drUserGroups;

    /**
     * @Column(type="datetime")
     */
    protected $drDateAdded;

    /**
     * @Column(type="datetime",nullable=true)
     */
    protected $drDeleted;

    protected $applicableTotal;

    /**
     * @return mixed
     */
    public function getApplicableTotal()
    {
        return $this->applicableTotal;
    }

    /**
     * @param mixed $applicableTotal
     */
    public function setApplicableTotal($applicableTotal)
    {
        $this->applicableTotal = $applicableTotal;
    }


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

    public function __construct()
    {
        $this->codes = new ArrayCollection();
        $this->applicableTotal = false;
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
    public function getName()
    {
        return $this->drName;
    }

    /**
     * @param mixed $drName
     */
    public function setName($drName)
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
        return (bool) $this->drEnabled;
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
    public function getDisplay()
    {
        return $this->drDisplay;
    }

    /**
     * @param mixed $drDisplay
     */
    public function setDisplay($drDisplay)
    {
        $this->drDisplay = $drDisplay;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->drDescription;
    }

    /**
     * @param mixed $drDescription
     */
    public function setDescription($drDescription)
    {
        $this->drDescription = $drDescription;
    }

    /**
     * @return mixed
     */
    public function getDeductType()
    {
        return $this->drDeductType;
    }

    /**
     * @param mixed $drDeductType
     */
    public function setDeductType($drDeductType)
    {
        $this->drDeductType = $drDeductType;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->drValue;
    }

    /**
     * @param mixed $drValue
     */
    public function setValue($drValue)
    {
        $this->drValue = ($drValue ? $drValue : null);
    }

    /**
     * @return mixed
     */
    public function getPercentage()
    {
        return $this->drPercentage;
    }

    /**
     * @param mixed $drPercentage
     */
    public function setPercentage($drPercentage)
    {
        $this->drPercentage = ($drPercentage ? $drPercentage : null);
    }

    /**
     * @return mixed
     */
    public function getDeductFrom()
    {
        return $this->drDeductFrom;
    }

    /**
     * @param mixed $drDeductFrom
     */
    public function setDeductFrom($drDeductFrom)
    {
        $this->drDeductFrom = $drDeductFrom;
    }

    /**
     * @return mixed
     */
    public function getTrigger()
    {
        return $this->drTrigger;
    }

    /**
     * @param mixed $drTrigger
     */
    public function setTrigger($drTrigger)
    {
        $this->drTrigger = $drTrigger;
    }

    public function requiresCode()
    {
        return $this->drTrigger == 'code';
    }

    /**
     * @return mixed
     */
    public function getSingleUseCodes()
    {
        return $this->drSingleUseCodes;
    }

    public function isSingleUse()
    {
        return (bool) $this->drSingleUseCodes;
    }

    /**
     * @param mixed $drSingleUseCodes
     */
    public function setSingleUseCodes($drSingleUseCodes)
    {
        $this->drSingleUseCodes = $drSingleUseCodes;
    }

    /**
     * @return mixed
     */
    public function getCurrency()
    {
        return $this->drCurrency;
    }

    /**
     * @param mixed $drCurrency
     */
    public function setCurrency($drCurrency)
    {
        $this->drCurrency = $drCurrency;
    }

    /**
     * @return mixed
     */
    public function getValidFrom()
    {
        return $this->drValidFrom;
    }

    /**
     * @param mixed $drValidFrom
     */
    public function setValidFrom($drValidFrom)
    {
        $this->drValidFrom = $drValidFrom;
    }

    /**
     * @return mixed
     */
    public function getValidTo()
    {
        return $this->drValidTo;
    }

    /**
     * @param mixed $drValidTo
     */
    public function setValidTo($drValidTo)
    {
        $this->drValidTo = $drValidTo;
    }

    /**
     * @return array
     */
    public function getProductGroups()
    {
        return explode(',', $this->drProductGroups);
    }

    /**
     * @param array $drProductGroups
     */
    public function setProductGroups($drProductGroups)
    {
        $this->drProductGroups = implode(',',$drProductGroups);
    }

    /**
     * @return array
     */
    public function getUserGroups()
    {
        return explode(',', $this->drUserGroups);
    }

    /**
     * @param array $drUserGroups
     */
    public function setUserGroups($drUserGroups)
    {
        if (is_array($drUserGroups)) {
            $this->drUserGroups = implode(',',$drUserGroups);
        }
    }



    /**
     * @return mixed
     */
    public function getDateAdded()
    {
        return $this->drDateAdded;
    }

    /**
     * @param mixed $drDateAdded
     */
    public function setDateAdded($drDateAdded)
    {
        $this->drDateAdded = $drDateAdded;
    }

    /**
     * @return mixed
     */
    public function getDeleted()
    {
        return $this->drDeleted;
    }

    /**
     * @param mixed $drDeleted
     */
    public function setDeleted($drDeleted)
    {
        $this->drDeleted = $drDeleted;
    }

    public function getFullDisplay()
    {
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

    public static function getByID($drID)
    {
        $em = \ORM::entityManager();
        return $em->find(get_class(), $drID);
    }

    public static function discountsWithCodesExist()
    {
        $app = \Concrete\Core\Support\Facade\Application::getFacadeApplication();
        $db = $app->make('database')->connection();
        $data = $db->GetRow("SELECT count(*) as codecount FROM CommunityStoreDiscountRules WHERE drEnabled =1 and drTrigger = 'code' "); // TODO

        return $data['codecount'] > 0;
    }

    public static function findAutomaticDiscounts()
    {
        $app = \Concrete\Core\Support\Facade\Application::getFacadeApplication();
        $db = $app->make('database')->connection();
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
            $include = true;

            if ($row['drUserGroups']) {
                $discountusergroups = explode(',',$row['drUserGroups']);

                $user = new User();
                $usergroups = $user->getUserGroup();

                $matching = array_intersect($usergroups, $discountusergroups);

                if (count($matching) == 0) {
                    $include = false;
                }

            }

            if ($include) {
                $discounts[] = self::getByID($row['drID']);
            }
        }

        return $discounts;
    }

    public function retrieveStatistics()
    {
        $app = \Concrete\Core\Support\Facade\Application::getFacadeApplication();
        $db = $app->make('database')->connection();
        $r = $db->query("select count(*) as total, COUNT(CASE WHEN oID is NULL THEN 1 END) AS available from CommunityStoreDiscountCodes where drID = ?", array($this->drID));
        $r = $r->fetchRow();
        $this->totalCodes = $r['total'];
        $this->availableCodes = $r['available'];

        return $r;
    }

    public static function findDiscountRuleByCode($code, $user = null)
    {
        $app = \Concrete\Core\Support\Facade\Application::getFacadeApplication();
        $db = $app->make('database')->connection();

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
            $include = true;

            if ($row['drUserGroups']) {
                $discountusergroups = explode(',',$row['drUserGroups']);

                $user = new User();
                $usergroups = $user->getUserGroup();

                $matching = array_intersect($usergroups, $discountusergroups);

                if (count($matching) == 0) {
                    $include = false;
                }
            }

            if ($include) {
                $discounts[] = self::getByID($row['drID']);
            }
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

        if ($data['drDeductType'] == 'percentage') {
            $data['drValue'] = null;
        } else {
            $data['drPercentage'] = null;
        }

        $discountRule->setEnabled($data['drEnabled'] ? true : false);
        $discountRule->setName($data['drName']);
        $discountRule->setDisplay($data['drDisplay']);
        $discountRule->setDeductType($data['drDeductType']);
        $discountRule->setDeductFrom($data['drDeductFrom']);
        $discountRule->setPercentage($data['drPercentage']);
        $discountRule->setValue($data['drValue']);
        $discountRule->setSingleUseCodes($data['drSingleUseCodes'] ? true : false);
        $discountRule->setTrigger($data['drTrigger']);
        $discountRule->setDescription($data['drDescription']);
        $discountRule->setDateAdded(new \DateTime());
        $discountRule->setProductGroups($data['drProductGroups']);
        $discountRule->setUserGroups($data['drUserGroups']);

        if ($data['validFrom'] == 1) {
            $from = new \DateTime($data['drValidFrom_dt'] . ' ' . $data['drValidFrom_h'] . ':' . $data['drValidFrom_m']. (isset($data['drValidFrom_a']) ? $data['drValidFrom_a'] : ''));
            $discountRule->setValidFrom($from);
        } else {
            $discountRule->setValidFrom(null);
        }

        if ($data['validTo'] == 1) {
            $to = new \DateTime($data['drValidTo_dt'] . ' ' . $data['drValidTo_h'] . ':' . $data['drValidTo_m']. (isset($data['drValidTo_a']) ? $data['drValidTo_a'] : ''));
            $discountRule->setValidTo($to);
        } else {
            $discountRule->setValidTo(null);
        }

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
        $em = \ORM::entityManager();
        $em->persist($this);
        $em->flush();
    }

    public function delete()
    {
        $em = \ORM::entityManager();
        $em->remove($this);
        $em->flush();
    }
}
