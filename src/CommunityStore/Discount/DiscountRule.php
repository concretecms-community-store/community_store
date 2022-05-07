<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Discount;

use Concrete\Core\User\User;
use Doctrine\ORM\Mapping as ORM;
use Concrete\Core\Support\Facade\Application;
use Doctrine\Common\Collections\ArrayCollection;
use Concrete\Core\Support\Facade\DatabaseORM as dbORM;
use Concrete\Package\CommunityStore\Src\CommunityStore\Cart\Cart;
use Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Price;

/**
 * @ORM\Entity
 * @ORM\Table(name="CommunityStoreDiscountRules")
 */
class DiscountRule
{
    /**
     * @ORM\Id @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $drID;

    /**
     * @ORM\Column(type="string")
     */
    protected $drName;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $drEnabled;

    /**
     * @ORM\Column(type="string", length=255,nullable=true)
     */
    protected $drDisplay;

    /**
     * @ORM\Column(type="text")
     */
    protected $drDescription;

    /**
     * @ORM\Column(type="string", length=20)
     */
    protected $drDeductType;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2,nullable=true)
     */
    protected $drValue;

    /**
     * @ORM\Column(type="decimal", precision=5, scale=2,nullable=true)
     */
    protected $drPercentage;

    /**
     * @ORM\Column(type="string", length=20)
     */
    protected $drDeductFrom;

    /**
     * @ORM\Column(type="string", length=20)
     */
    protected $drTrigger;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $drSingleUseCodes;

    /**
     * @ORM\Column(type="string",nullable=true)
     */
    protected $drCurrency;

    /**
     * @ORM\Column(type="datetime",nullable=true)
     */
    protected $drValidFrom;

    /**
     * @ORM\Column(type="datetime",nullable=true)
     */
    protected $drValidTo;

    /**
     * @ORM\Column(type="string",nullable=true)
     */
    protected $drProductGroups;

    /**
     * @ORM\Column(type="string",nullable=true)
     */
    protected $drUserGroups;

    /**
     * @ORM\Column(type="decimal", precision=5, scale=2,nullable=true)
     */
    protected $drQuantity;

    /**
     * @ORM\Column(type="decimal", precision=5, scale=2,nullable=true)
     */
    protected $drMaximumQuantity;

    /**
    * @ORM\Column(type="boolean")
    */
    protected $drDiscountSalePrices;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $drDateAdded;

    /**
     * @ORM\Column(type="datetime",nullable=true)
     */
    protected $drDeleted;

    //  Used to temporarily store the calculated total of matching products (when product groups used)
    protected $applicableTotal;

    public function getApplicableTotal()
    {
        return $this->applicableTotal;
    }

    public function setApplicableTotal($applicableTotal)
    {
        $this->applicableTotal = $applicableTotal;
    }

    public function returnDiscountedPrice()
    {
        if ($this->getDeductFrom() == 'subtotal') {
            if ($this->getDeductType() == 'percentage') {
                $applicableTotal = $this->getApplicableTotal();

                if (false != $applicableTotal) {
                    return round($applicableTotal - ($this->getPercentage() / 100 * $applicableTotal), 2);
                }
            }

            if ($this->getDeductType() == 'value_all') {
                $applicableTotal = $this->getApplicableTotal();

                if (false != $applicableTotal) {
                    return $applicableTotal - $this->getValue();
                }
            }

            if ($this->getDeductType() == 'fixed') {
                return $this->getValue();
            }
        }

        return false;
    }

    public function returnFormattedDiscountedPrice()
    {
        return Price::format($this->returnDiscountedPrice());
    }

    /**
     * @ORM\OneToMany(targetEntity="Concrete\Package\CommunityStore\Src\CommunityStore\Discount\DiscountCode", mappedBy="discountRule")
     */
    private $codes;

    /**
     * @ORM\return mixed
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
     * @ORM\return mixed
     */
    public function getID()
    {
        return $this->drID;
    }

    /**
     * @ORM\return mixed
     */
    public function getName()
    {
        return $this->drName;
    }

    /**
     * @ORM\param mixed $drName
     */
    public function setName($drName)
    {
        $this->drName = $drName;
    }

    /**
     * @ORM\return mixed
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
     * @ORM\param mixed $drEnabled
     */
    public function setEnabled($drEnabled)
    {
        $this->drEnabled = $drEnabled;
    }

    /**
     * @ORM\return mixed
     */
    public function getDisplay()
    {
        return $this->drDisplay;
    }

    /**
     * @ORM\param mixed $drDisplay
     */
    public function setDisplay($drDisplay)
    {
        $this->drDisplay = $drDisplay;
    }

    /**
     * @ORM\return mixed
     */
    public function getDescription()
    {
        return $this->drDescription;
    }

    /**
     * @ORM\param mixed $drDescription
     */
    public function setDescription($drDescription)
    {
        $this->drDescription = $drDescription;
    }

    /**
     * @ORM\return mixed
     */
    public function getDeductType()
    {
        return $this->drDeductType;
    }

    /**
     * @ORM\param mixed $drDeductType
     */
    public function setDeductType($drDeductType)
    {
        $this->drDeductType = $drDeductType;
    }

    /**
     * @ORM\return mixed
     */
    public function getValue()
    {
        return $this->drValue;
    }

    /**
     * @ORM\param mixed $drValue
     */
    public function setValue($drValue)
    {
        $this->drValue = ($drValue ? $drValue : null);
    }

    /**
     * @ORM\return mixed
     */
    public function getPercentage()
    {
        return $this->drPercentage;
    }

    /**
     * @ORM\param mixed $drPercentage
     */
    public function setPercentage($drPercentage)
    {
        $this->drPercentage = ($drPercentage ? $drPercentage : null);
    }

    /**
     * @ORM\return mixed
     */
    public function getDeductFrom()
    {
        return $this->drDeductFrom;
    }

    /**
     * @ORM\param mixed $drDeductFrom
     */
    public function setDeductFrom($drDeductFrom)
    {
        $this->drDeductFrom = $drDeductFrom;
    }

    /**
     * @ORM\return mixed
     */
    public function getTrigger()
    {
        return $this->drTrigger;
    }

    /**
     * @ORM\param mixed $drTrigger
     */
    public function setTrigger($drTrigger)
    {
        $this->drTrigger = $drTrigger;
    }

    public function requiresCode()
    {
        return 'code' == $this->drTrigger;
    }

    /**
     * @ORM\return mixed
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
     * @ORM\param mixed $drSingleUseCodes
     */
    public function setSingleUseCodes($drSingleUseCodes)
    {
        $this->drSingleUseCodes = $drSingleUseCodes;
    }

    /**
     * @ORM\return mixed
     */
    public function getCurrency()
    {
        return $this->drCurrency;
    }

    /**
     * @ORM\param mixed $drCurrency
     */
    public function setCurrency($drCurrency)
    {
        $this->drCurrency = $drCurrency;
    }

    /**
     * @ORM\return mixed
     */
    public function getValidFrom()
    {
        return $this->drValidFrom;
    }

    /**
     * @ORM\param mixed $drValidFrom
     */
    public function setValidFrom($drValidFrom)
    {
        $this->drValidFrom = $drValidFrom;
    }

    /**
     * @ORM\return mixed
     */
    public function getValidTo()
    {
        return $this->drValidTo;
    }

    /**
     * @ORM\param mixed $drValidTo
     */
    public function setValidTo($drValidTo)
    {
        $this->drValidTo = $drValidTo;
    }

    /**
     * @ORM\return array
     */
    public function getProductGroups()
    {
        return $this->drProductGroups ? explode(',', $this->drProductGroups) : [];
    }

    /**
     * @ORM\param array $drProductGroups
     */
    public function setProductGroups($drProductGroups)
    {
        if (is_array($drProductGroups)) {
            $this->drProductGroups = implode(',', $drProductGroups);
        } else {
            $this->drProductGroups = '';
        }
    }

    /**
     * @ORM\return array
     */
    public function getUserGroups()
    {
        return $this->drUserGroups ? explode(',', $this->drUserGroups) : [];
    }

    /**
     * @ORM\param array $drUserGroups
     */
    public function setUserGroups($drUserGroups)
    {
        if (is_array($drUserGroups)) {
            $this->drUserGroups = implode(',', $drUserGroups);
        } else {
            $this->drUserGroups = '';
        }
    }

    public function getQuantity()
    {
        return $this->drQuantity;
    }

    public function setQuantity($drQuantity)
    {
        $this->drQuantity = $drQuantity;
    }

    public function getMaximumQuantity()
    {
        return $this->drMaximumQuantity;
    }

    public function setMaximumQuantity($drMaximumQuantity)
    {
        $this->drMaximumQuantity = $drMaximumQuantity;
    }

    public function getDiscountSalePrices()
    {
        return (bool)$this->drDiscountSalePrices;
    }

    public function setDiscountSalePrices($drDiscountSalePrices)
    {
        $this->drDiscountSalePrices = $drDiscountSalePrices;
    }

    /**
     * @ORM\return mixed
     */
    public function getDateAdded()
    {
        return $this->drDateAdded;
    }

    /**
     * @ORM\param mixed $drDateAdded
     */
    public function setDateAdded($drDateAdded)
    {
        $this->drDateAdded = $drDateAdded;
    }

    /**
     * @ORM\return mixed
     */
    public function getDeleted()
    {
        return $this->drDeleted;
    }

    /**
     * @ORM\param mixed $drDeleted
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
                return Price::format($this->drValue) . ' ' . t('off');
            }
        }

        return '';
    }

    public static function getByID($drID)
    {
        $em = dbORM::entityManager();

        return $em->find(get_class(), $drID);
    }

    public static function discountsWithCodesExist()
    {
        $app = Application::getFacadeApplication();
        $db = $app->make('database')->connection();
        $data = $db->GetRow("SELECT count(*) as codecount FROM CommunityStoreDiscountRules WHERE drEnabled =1 and drTrigger = 'code' ");

        return $data['codecount'] > 0;
    }

    public static function findAutomaticDiscounts($cartItems = false)
    {
        $app = Application::getFacadeApplication();
        $db = $app->make('database')->connection();
        $result = $db->query("SELECT drID FROM CommunityStoreDiscountRules
              WHERE drEnabled = 1
              AND drDeleted IS NULL
              AND drTrigger = 'auto'
              AND (drPercentage > 0 or drValue  > 0)
              AND (drValidFrom IS NULL OR drValidFrom <= NOW())
              AND (drValidTo IS NULL OR drValidTo > NOW())
              ORDER BY drPercentage DESC, drValue DESC
              ");

        return self::filterDiscounts($result, $cartItems);
    }


    public static function findDiscountRuleByCode($code, $cartItems = false)
    {
        $app = Application::getFacadeApplication();
        $db = $app->make('database')->connection();

        $result = $db->query("SELECT dr.drID FROM CommunityStoreDiscountCodes as dc, CommunityStoreDiscountRules as dr WHERE 
        dc.drID = dr.drID
        and dcCode = ?
        AND oID IS NULL
        AND drDeleted IS NULL
        AND drEnabled = '1'
        AND drTrigger = 'code'
        AND (drValidFrom IS NULL OR drValidFrom <= NOW())
        AND (drValidTo IS NULL OR drValidTo > NOW()) GROUP BY dr.drID", [$code]);

        return self::filterDiscounts($result, $cartItems);
    }


    private static function filterDiscounts($result, $cartItems = false) {
        $user = new User();

        $discounts = [];

        if (!$cartItems) {
            $cartItems = \Concrete\Core\Support\Facade\Session::get('communitystore.cart');
        }

        while ($row = $result->fetch()) {
            $discountRule = self::getByID($row['drID']);

            if (!$discountRule) {
                continue;
            }

            $include = true;

            $discountUserGroups =  $discountRule->getUserGroups();

            if (count($discountUserGroups) > 0) {
                $userGroups = $user->getUserGroups();

                $matching = array_intersect($userGroups, $discountUserGroups);

                if (0 == count($matching)) {
                    $include = false;
                }
            }

            if ($include) {
                if ($discountRule->getQuantity() > 0 || $discountRule->getMaximumQuantity() > 0) {
                    $include = false;
                    $count = 0;

                    if (is_array($cartItems)) {
                        $discountProductGroups = [];

                        $dpg = trim($row['drProductGroups']);
                        $discountProductGroups = $discountRule->getProductGroups();

                        if (!empty($discountProductGroups)) {
                            foreach ($cartItems as $ci) {
                                if ($ci['product']['object']) {
                                    $groupids = $ci['product']['object']->getGroupIDs();
                                    if (count(array_intersect($discountProductGroups, $groupids)) > 0) {

                                        if ($ci['product']['object']) {
                                            $multiplier = $ci['product']['object']->getNumberItems();
                                        }

                                        if (!$multiplier) {
                                            $multiplier = 1;
                                        }

                                        $count += $ci['product']['qty'] * $multiplier;
                                    }
                                }
                            }
                        } else {
                            foreach ($cartItems as $ci) {

                                if ($ci['product']['object']) {
                                    $multiplier = $ci['product']['object']->getNumberItems();
                                }

                                if (!$multiplier) {
                                    $multiplier = 1;
                                }

                                $count += $ci['product']['qty'] * $multiplier;
                            }
                        }
                    }

                    if ($count >= $discountRule->getQuantity()) {
                        $include = true;
                    }

                    if ($discountRule->getMaximumQuantity() && ($count > $discountRule->getMaximumQuantity())) {
                        $include = false;
                    }
                }
            }

            if ($include) {
                $discounts[] = $discountRule;
            }
        }

        return $discounts;
    }



    public function retrieveStatistics()
    {
        $app = Application::getFacadeApplication();
        $db = $app->make('database')->connection();
        $r = $db->query("select count(*) as total, COUNT(CASE WHEN oID is NULL THEN 1 END) AS available from CommunityStoreDiscountCodes where drID = ?", [$this->drID]);
        $r = $r->fetch();
        $this->totalCodes = $r['total'];
        $this->availableCodes = $r['available'];

        return $r;
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
        if ('percentage' == $data['drDeductType']) {
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
        $discountRule->setSingleUseCodes(isset($data['drSingleUseCodes']) && $data['drSingleUseCodes']  ? true : false);
        $discountRule->setTrigger($data['drTrigger']);
        $discountRule->setDescription($data['drDescription']);
        $discountRule->setDateAdded(new \DateTime());
        $discountRule->setProductGroups(isset($data['drProductGroups']) ? $data['drProductGroups'] : '');
        $discountRule->setUserGroups(isset($data['drUserGroups']) ? $data['drUserGroups'] : '');
        $discountRule->setQuantity($data['drQuantity'] ? $data['drQuantity'] : null);
        $discountRule->setMaximumQuantity($data['drMaximumQuantity'] ? $data['drMaximumQuantity'] : null);
        $discountRule->setDiscountSalePrices($data['drDiscountSalePrices'] ? true : false);

        if (1 == $data['validFrom']) {
            $from = new \DateTime($data['drValidFrom_dt'] . ' ' . $data['drValidFrom_h'] . ':' . $data['drValidFrom_m'] . (isset($data['drValidFrom_a']) ? $data['drValidFrom_a'] : ''));
            $discountRule->setValidFrom($from);
        } else {
            $discountRule->setValidFrom(null);
        }

        if (1 == $data['validTo']) {
            $to = new \DateTime($data['drValidTo_dt'] . ' ' . $data['drValidTo_h'] . ':' . $data['drValidTo_m'] . (isset($data['drValidTo_a']) ? $data['drValidTo_a'] : ''));
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

    public static function getRules()
    {
        $em = dbORM::entityManager();
        $rules = $em->getRepository(get_called_class())->findAll();
        return $rules;
    }
}
