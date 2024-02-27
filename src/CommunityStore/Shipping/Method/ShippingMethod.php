<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Shipping\Method;

use Concrete\Core\Support\Facade\Application;
use Concrete\Core\Support\Facade\DatabaseORM as dbORM;
use Concrete\Core\Support\Facade\Session;
use Concrete\Core\User\User;
use Concrete\Core\View\View;
use Concrete\Package\CommunityStore\Src\CommunityStore\Cart\Cart;
use Concrete\Package\CommunityStore\Src\CommunityStore\Group\Group;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductGroup\Criteria;
use Concrete\Package\CommunityStore\Src\CommunityStore\Shipping\Method\ShippingMethodType as ShippingMethodType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping as ORM;
use Illuminate\Filesystem\Filesystem;

/**
 * @ORM\Entity
 * @ORM\Table(name="CommunityStoreShippingMethods")
 */
class ShippingMethod
{
    /** @ORM\Id @ORM\Column(type="integer") @ORM\GeneratedValue **/
    protected $smID;

    /**
     * @ORM\Column(type="integer")
     */
    protected $smtID;

    /**
     * @ORM\Column(type="integer")
     */
    protected $smtmID;

    /**
     * @ORM\Column(type="string")
     */
    protected $smName;

    /**
     * @ORM\Column(type="text",nullable=true)
     */
    protected $smDetails;

    /**
     * @ORM\Column(type="string",nullable=true)
     */
    protected $smUserGroups;

    /**
     * @ORM\Column(type="string",nullable=true)
     */
    protected $smExcludedUserGroups;

    /**
     * @ORM\Column(type="integer")
     */
    protected $smEnabled;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $smSortOrder;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $smProductGroupsCriteria;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $smProductGroups;

    protected $smOfferKey;

    public function setOfferKey($key)
    {
        $this->smOfferKey = $key;
    }

    public function getOfferKey()
    {
        if ($this->smOfferKey) {
            return $this->smOfferKey;
        } else {
            return 0;
        }
    }

    public function setShippingMethodTypeID($smt)
    {
        $this->smtID = $smt->getShippingMethodTypeID();
    }

    public function setShippingMethodTypeMethodID($smtm)
    {
        $this->smtmID = $smtm->getShippingMethodTypeMethodID();
    }

    public function setName($name)
    {
        $this->smName = $name;
    }

    public function setEnabled($status)
    {
        $this->smEnabled = $status;
    }

    public function setDetails($details)
    {
        $this->smDetails = $details;
    }

    public function setSortOrder($smSortOrder)
    {
        $this->smSortOrder = $smSortOrder;
    }


    public function getUserGroups()
    {
        return $this->smUserGroups ? explode(',', $this->smUserGroups) : [];
    }

    public function setUserGroups($userGroups)
    {
        if (is_array($userGroups)) {
            $this->smUserGroups = implode(',', $userGroups);
        } else {
            $this->smUserGroups = '';
        }
    }

    public function getExcludedUserGroups()
    {
        return $this->smExcludedUserGroups ? explode(',', $this->smExcludedUserGroups) : [];
    }

    public function setExcludedUserGroups($userGroups)
    {
        if (is_array($userGroups)) {
            $this->smExcludedUserGroups = implode(',', $userGroups);
        } else {
            $this->smExcludedUserGroups = '';
        }
    }

    public function getID()
    {
        return $this->smID;
    }

    public function getShippingMethodType()
    {
        return ShippingMethodType::getByID($this->smtID);
    }

    public function getShippingMethodTypeMethod()
    {
        $methodTypeController = $this->getShippingMethodType()->getMethodTypeController();
        $methodTypeMethod = $methodTypeController->getByID($this->smtmID);

        return $methodTypeMethod;
    }

    public function getOffers()
    {
        $offers = $this->getShippingMethodTypeMethod()->getOffers();
        $count = 0;

        foreach ($offers as $offer) {
            $offer->setMethodLabel($this->getName());
            $offer->setKey($this->getID() . '_' . $count++);
        }

        return $offers;
    }

    public function getCurrentOffer()
    {
        $currentOffers = $this->getOffers();

        if ($currentOffers && isset($currentOffers[$this->getOfferKey()])) {
            return $this->getOffers()[$this->getOfferKey()];
        } else {
            return null;
        }
    }

    public function getName()
    {
        return $this->smName;
    }

    public function getDetails()
    {
        return $this->smDetails;
    }


    public function isEnabled()
    {
        return $this->smEnabled;
    }

    public function getSortOrder()
    {
        return $this->smSortOrder;
    }

    public static function getByID($smID)
    {
        $ident = explode('_', $smID);
        $smID = $ident[0];

        $em = dbORM::entityManager();
        $method = $em->find(get_called_class(), $smID);

        if ($method) {
            if (isset($ident[1])) {
                $method->setOfferKey($ident[1]);
            }

            return $method;
        }

        return false;
    }

    /**
     * @return \Concrete\Package\CommunityStore\Src\CommunityStore\Shipping\Method\ShippingMethod[]
     */
    public static function getAvailableMethods($methodTypeID = null)
    {
        $em = dbORM::entityManager();
        if ($methodTypeID) {
            $methods = $em->getRepository(get_called_class())->findBy(['smtID' => $methodTypeID, 'smEnabled' => '1']);
        } else {
            $methods = $em->createQuery('select sm from \Concrete\Package\CommunityStore\Src\CommunityStore\Shipping\Method\ShippingMethod sm where sm.smEnabled = 1 order by sm.smSortOrder')->getResult();
        }

        return $methods;
    }

    public static function getMethods($methodTypeID = null)
    {
        $em = dbORM::entityManager();
        if ($methodTypeID) {
            $methods = $em->getRepository(get_called_class())->findBy(['smtID' => $methodTypeID]);
        } else {
            $methods = $em->createQuery('select sm from \Concrete\Package\CommunityStore\Src\CommunityStore\Shipping\Method\ShippingMethod sm')->getResult();
        }

        return $methods;
    }

    /**
     * @param StoreShippingMethodTypeMethod $smtm
     * @param StoreShippingMethodType $smt
     * @param string $smName
     * @param bool $smEnabled
     *
     * @return ShippingMethod
     */
    public static function add($smtm, $smt, $smName, $smEnabled, $smDetails, $smSortOrder, $userGroups, $excludedUserGroups)
    {
        $sm = new self();
        $sm->setShippingMethodTypeMethodID($smtm);
        $sm->setShippingMethodTypeID($smt);
        $sm->setName($smName);
        $sm->setEnabled($smEnabled);
        $sm->setDetails($smDetails);
        $sm->setSortOrder($smSortOrder);
        $sm->setUserGroups($userGroups);
        $sm->setExcludedUserGroups($excludedUserGroups);
        $sm->save();
        $smtm->setShippingMethodID($sm->getID());
        $smtm->save();

        return $sm;
    }

    public function update($smName, $smEnabled, $smDetails, $smSortOrder, $userGroups, $excludedUserGroups)
    {
        $this->setName($smName);
        $this->setEnabled($smEnabled);
        $this->setSortOrder($smSortOrder);
        $this->setDetails($smDetails);
        $this->setUserGroups($userGroups);
        $this->setExcludedUserGroups($excludedUserGroups);
        $this->save();

        return $this;
    }

    public function save()
    {
        $em = dbORM::entityManager();
        $em->persist($this);
        $em->flush();
    }

    public function delete()
    {
        $this->getShippingMethodTypeMethod()->delete();
        $em = dbORM::entityManager();
        $em->remove($this);
        $em->flush();
    }

    /**
     * @return \Concrete\Package\CommunityStore\Src\CommunityStore\Shipping\Method\ShippingMethod[]
     */
    public static function getEligibleMethods()
    {
        $allMethods = self::getAvailableMethods();
        $eligibleMethods = [];

        $u = app(User::class);
        $userGroups = $u->getUserGroups();
        $criteriaService = null;
        $products = null;

        foreach ($allMethods as $method) {
            $includedGroups = $method->getUserGroups();
            $excludedGroups = $method->getExcludedUserGroups();

            if (count($includedGroups) > 0 && count(array_intersect($includedGroups, $userGroups)) == 0) {
                continue;
            }

            if (count($excludedGroups) > 0 && count(array_intersect($excludedGroups, $userGroups)) > 0) {
                continue;
            }

            if ($method->getShippingMethodTypeMethod()->isEligible()) {
                $criteria = $method->getProductGroupsCriteria();
                if ($criteria) {
                    if ($criteriaService === null) {
                        $criteriaService = app(Criteria::class);
                    }
                    if ($products === null) {
                        $em = app(EntityManagerInterface::class);
                        $products = [];
                        foreach (Cart::getCart() as $entry) {
                            $products[] = $em->find(Product::class, $entry['product']['pID']);
                        }
                    }
                    if (!$criteriaService->check($criteria, $products, $method->getProductGroupIDs())) {
                        continue;
                    }
                }
                $eligibleMethods[] = $method;
            }
        }

        return $eligibleMethods;
    }

    public function getShippingMethodSelector()
    {
        if (file_exists(DIR_BASE . "/application/elements/checkout/shipping_methods.php")) {
            View::element("checkout/shipping_methods");
        } elseif (file_exists(DIR_BASE . "/packages/" . $this->getPackageHandle() . "/elements/checkout/shipping_methods.php")) {
            View::element("checkout/shipping_methods", $this, $this->getPackageHandle());
        } else {
            View::element("checkout/shipping_methods", "community_store");
        }
    }

    public static function getActiveShippingMethod()
    {
        $smID = Session::get('community_store.smID');
        if ($smID) {
            $sm = self::getByID($smID);

            return $sm;
        }
    }

    public static function getActiveShippingLabel()
    {
        $activeShippingMethod = self::getActiveShippingMethod();

        if ($activeShippingMethod) {
            $currentOffer = $activeShippingMethod->getCurrentOffer();
            if ($currentOffer) {
                return $currentOffer->getLabel();
            }
        }

        return '';
    }

    public static function getActiveShipmentID()
    {
        $activeShippingMethod = self::getActiveShippingMethod();

        if ($activeShippingMethod) {
            $currentOffer = $activeShippingMethod->getCurrentOffer();
            if ($currentOffer) {
                return $currentOffer->getShipmentID();
            }
        }

        return '';
    }

    public static function getActiveRateID()
    {
        $activeShippingMethod = self::getActiveShippingMethod();

        if ($activeShippingMethod) {
            $currentOffer = $activeShippingMethod->getCurrentOffer();
            if ($currentOffer) {
                return $currentOffer->getRateID();
            }
        }

        return '';
    }

    public function getPackageHandle()
    {
        return Application::getFacadeApplication()->make('Concrete\Core\Package\PackageService')->getByID($this->getShippingMethodType()->getPackageID())->getPackageHandle();
    }

    /**
     * @param int|null $value
     *
     * @return $this
     *
     * @see \Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductGroup\Criteria and its constants
     */
    public function setProductGroupsCriteria($value)
    {
        $this->smProductGroupsCriteria = empty($value) ? null : (int) $value;

        return $this;
    }

    /**
     * @return int|null
     *
     * @see \Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductGroup\Criteria and its constants
     */
    public function getProductGroupsCriteria()
    {
        return $this->smProductGroupsCriteria? (int) $this->smProductGroupsCriteria : null;
    }

    /**
     * @param int[]|\Concrete\Package\CommunityStore\Src\CommunityStore\Group\Group[] $value
     *
     * @return $this
     */
    public function setProductGroups(array $value)
    {
        $ids = [];
        foreach ($value as $item) {
            if ($item instanceof Group) {
                $ids[] = (int) $item->getID();
            } elseif (is_numeric($item)) {
                $ids[] = (int) $item;
            }
        }
        $this->smProductGroups = implode(',', array_filter(array_unique($ids, SORT_NUMERIC)));

        return $this;
    }

    /**
     * @return int[]
     */
    public function getProductGroupIDs()
    {
        if (empty($this->smProductGroups)) {
            return [];
        }

        return array_map(
            'intval',
            preg_split('/,/', $this->smProductGroups, -1, PREG_SPLIT_NO_EMPTY)
        );
    }
}
