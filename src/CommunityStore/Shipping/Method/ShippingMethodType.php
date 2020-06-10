<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Shipping\Method;

use Concrete\Core\View\View;
use Doctrine\ORM\Mapping as ORM;
use Concrete\Core\Package\Package;
use Concrete\Core\Support\Facade\Application;
use Concrete\Core\Support\Facade\DatabaseORM as dbORM;
use Concrete\Package\CommunityStore\Src\CommunityStore\Shipping\Method\ShippingMethod as ShippingMethod;

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
        $app = Application::getFacadeApplication();
        $pkg = $app->make('Concrete\Core\Package\PackageService')->getByID($this->pkgID);

        if (!$pkg) {
            return false;
        }

        $th = $app->make("helper/text");
        $namespace = "Concrete\\Package\\" . $th->camelcase($pkg->getPackageHandle()) . "\\Src\\CommunityStore\\Shipping\\Method\\Types";

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

	/**
	 * @param $smtID
	 * @return ShippingMethodType
	 */
    public static function getByID($smtID)
    {
        $em = dbORM::entityManager();
        $obj = $em->find(get_called_class(), $smtID);
        $obj->setMethodTypeController();

        return $obj;
    }

	/**
	 * @param $smtHandle
	 * @return ShippingMethodType|null
	 */
    public static function getByHandle($smtHandle)
    {
        $em = dbORM::entityManager();
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
        $em = dbORM::entityManager();
        $em->persist($this);
        $em->flush();
    }

    public function delete()
    {
        $methods = ShippingMethod::getAvailableMethods($this->getShippingMethodTypeID());
        foreach ($methods as $method) {
            $method->delete();
        }
        $em = dbORM::entityManager();
        $em->remove($this);
        $em->flush();
    }

    public static function getAvailableMethodTypes()
    {
        $em = dbORM::entityManager();
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
        $app = Application::getFacadeApplication();
        $pkg = $app->make('Concrete\Core\Package\PackageService')->getByID($this->pkgID);
        View::element('shipping_method_types/' . $this->smtHandle . '/dashboard_form', ['vars' => $controller->getSets()], $pkg->getPackageHandle());
    }

    public function addMethod($data)
    {
        $sm = $this->getMethodTypeController()->addMethodTypeMethod($data);
        return $sm;
    }
}
