<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Shipping\Method;

use Database;
use View;
use Illuminate\Filesystem\Filesystem;
use Concrete\Package\CommunityStore\Src\CommunityStore\Shipping\Method\ShippingMethodTypeMethod as StoreShippingMethodTypeMethod;
use Concrete\Package\CommunityStore\Src\CommunityStore\Shipping\Method\ShippingMethodType as StoreShippingMethodType;

/**
 * @Entity
 * @Table(name="CommunityStoreShippingMethods")
 */
class ShippingMethod
{
    /** @Id @Column(type="integer") @GeneratedValue **/
    protected $smID;

    /**
     * @Column(type="integer")
     */
    protected $smtID;

    /**
     * @Column(type="integer")
     */
    protected $smtmID;

    /**
     * @Column(type="string")
     */
    protected $smName;

    /**
     * @Column(type="text",nullable=true)
     */
    protected $smDetails;

    /**
     * @Column(type="integer")
     */
    protected $smEnabled;

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

    public function getID()
    {
        return $this->smID;
    }
    public function getShippingMethodType()
    {
        return StoreShippingMethodType::getByID($this->smtID);
    }
    public function getShippingMethodTypeMethod()
    {
        $methodTypeController = $this->getShippingMethodType()->getMethodTypeController();
        $methodTypeMethod = $methodTypeController->getByID($this->smtmID);

        return $methodTypeMethod;
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

    public static function getByID($smID)
    {
        $db = Database::connection();
        $em = $db->getEntityManager();

        return $em->find(get_called_class(), $smID);
    }

    public static function getAvailableMethods($methodTypeID = null)
    {
        $em = Database::connection()->getEntityManager();
        if ($methodTypeID) {
            $methods = $em->getRepository(get_called_class())->findBy(array('smtID' => $methodTypeID, 'smEnabled'=>'1'));
        } else {
            $methods = $em->createQuery('select sm from \Concrete\Package\CommunityStore\Src\CommunityStore\Shipping\Method\ShippingMethod sm where sm.smEnabled = 1')->getResult();
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
    public static function add($smtm, $smt, $smName, $smEnabled, $smDetails)
    {
        $sm = new self();
        $sm->setShippingMethodTypeMethodID($smtm);
        $sm->setShippingMethodTypeID($smt);
        $sm->setName($smName);
        $sm->setEnabled($smEnabled);
        $sm->setDetails($smDetails);
        $sm->save();
        $smtm->setShippingMethodID($sm->getID());
        $smtm->save();

        return $sm;
    }
    public function update($smName, $smEnabled, $smDetails)
    {
        $this->setName($smName);
        $this->setEnabled($smEnabled);
        $this->setDetails($smDetails);
        $this->save();

        return $this;
    }
    public function save()
    {
        $em = Database::connection()->getEntityManager();
        $em->persist($this);
        $em->flush();
    }
    public function delete()
    {
        $this->getShippingMethodTypeMethod()->delete();
        $em = Database::connection()->getEntityManager();
        $em->remove($this);
        $em->flush();
    }
    public static function getEligibleMethods()
    {
        $allMethods = self::getAvailableMethods();
        $eligibleMethods = array();
        foreach ($allMethods as $method) {
            if ($method->getShippingMethodTypeMethod()->isEligible()) {
                $eligibleMethods[] = $method;
            }
        }

        return $eligibleMethods;
    }

    public function getShippingMethodSelector()
    {
        if (Filesystem::exists(DIR_BASE."/application/elements/checkout/shipping_methods.php")) {
            View::element("checkout/shipping_methods");
        } else {
            View::element("checkout/shipping_methods", "community_store");
        }
    }

    public static function getActiveShippingMethod()
    {
        $smID = \Session::get('smID');
        if ($smID) {
            $sm = self::getByID($smID);

            return $sm;
        }
    }

    public static function getActiveShippingMethodName()
    {
        $sm = self::getActiveShippingMethod();
        if ($sm instanceof self) {
            $shippingMethodTypeName = $sm->getShippingMethodType()->getShippingMethodTypeName();
            $shippingMethodName = $sm->getName();
            $smName = $shippingMethodTypeName.": ".$shippingMethodName;
        } else {
            $smName = '';
        }

        return $smName;
    }
}
