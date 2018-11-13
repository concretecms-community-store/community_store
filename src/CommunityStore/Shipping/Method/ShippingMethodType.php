<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Shipping\Method;

use Doctrine\ORM\Mapping as ORM;
use Core;
use Package;
use View;
use Concrete\Package\CommunityStore\Src\CommunityStore\Shipping\Method\ShippingMethod as StoreShippingMethod;

/**
 * @ORM\Entity
 * @ORM\Table(name="CommunityStoreShippingMethodTypes")
 */
class ShippingMethodType
{
    /**
     * @ORM\Id @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $smtID;

    /**
     * @ORM\Column(type="string")
     */
    protected $smtHandle;

    /**
     * @ORM\Column(type="string")
     */
    protected $smtName;

    /**
     * @ORM\Column(type="integer")
     */
    protected $pkgID;

    /**
     * @ORM\Column(type="integer",nullable=true)
     */
    protected $hideFromAddMenu;

    private $methodTypeController;

    public function setHandle($handle)
    {
        $this->smtHandle = $handle;
    }

    public function setName($name)
    {
        $this->smtName = $name;
    }

    public function setPackageID($pkgID)
    {
        $this->pkgID = $pkgID;
    }

    public function setMethodTypeController()
    {
        $package = Package::getByID($this->pkgID);

        if (!$package) {
            return false;
        }

        $th = Core::make("helper/text");
        $namespace = "Concrete\\Package\\" . $th->camelcase($package->getPackageHandle()) . "\\Src\\CommunityStore\\Shipping\\Method\\Types";

        $className = $th->camelcase($this->smtHandle) . "ShippingMethod";
        $obj = $namespace . '\\' . $className;
        $this->methodTypeController = new $obj();
    }

    public function hideFromAddMenu($bool = false)
    {
        $this->hideFromAddMenu = $bool;
    }

    public function isHiddenFromAddMenu()
    {
        return $this->hideFromAddMenu;
    }

    public function getShippingMethodTypeID()
    {
        return $this->smtID;
    }

    public function getHandle()
    {
        return $this->smtHandle;
    }

    public function getShippingMethodTypeName()
    {
        return $this->smtName;
    }

    public function getPackageID()
    {
        return $this->pkgID;
    }

    public function getMethodTypeController()
    {
        return $this->methodTypeController;
    }

    public static function getByID($smtID)
    {
        $em = \ORM::entityManager();
        $obj = $em->find(get_called_class(), $smtID);
        $obj->setMethodTypeController();

        return $obj;
    }

    public static function getByHandle($smtHandle)
    {
        $em = \ORM::entityManager();
        $obj = $em->getRepository(get_called_class())->findOneBy(['smtHandle' => $smtHandle]);
        if (is_object($obj)) {
            $obj->setMethodTypeController();

            return $obj;
        }
    }

    public static function add($smtHandle, $smtName, $pkg, $hideFromAddMenu = false)
    {
        $smt = new self();
        $smt->setHandle($smtHandle);
        $smt->setName($smtName);
        $pkgID = $pkg->getPackageID();
        $smt->setPackageID($pkgID);
        $smt->hideFromAddMenu($hideFromAddMenu);
        $smt->save();
        $smt->setMethodTypeController();

        return $smt;
    }

    public function save()
    {
        $em = \ORM::entityManager();
        $em->persist($this);
        $em->flush();
    }

    public function delete()
    {
        $methods = StoreShippingMethod::getAvailableMethods($this->getShippingMethodTypeID());
        foreach ($methods as $method) {
            $method->delete();
        }
        $em = \ORM::entityManager();
        $em->remove($this);
        $em->flush();
    }

    public static function getAvailableMethodTypes()
    {
        $em = \ORM::entityManager();
        $methodTypes = $em->createQuery('select smt from \Concrete\Package\CommunityStore\Src\CommunityStore\Shipping\Method\ShippingMethodType smt')->getResult();

        $methodsWithControllers = [];

        foreach ($methodTypes as $mt) {
            $mt->setMethodTypeController();
            $methodsWithControllers[] = $mt;
        }

        return $methodsWithControllers;
    }

    public function renderDashboardForm($sm)
    {
        $controller = $this->getMethodTypeController();
        $controller->dashboardForm($sm);
        $pkg = Package::getByID($this->pkgID);
        View::element('shipping_method_types/' . $this->smtHandle . '/dashboard_form', ['vars' => $controller->getSets()], $pkg->getPackageHandle());
    }

    public function addMethod($data)
    {
        $sm = $this->getMethodTypeController()->addMethodTypeMethod($data);

        return $sm;
    }
}
