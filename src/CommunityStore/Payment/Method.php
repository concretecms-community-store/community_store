<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Payment;

use Concrete\Core\Foundation\Object as Object;
use Database;
use Core;
use Package;
use Controller;
use View;

/**
 * @Entity
 * @Table(name="CommunityStorePaymentMethods")
 */
class Method extends Controller
{
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue
     */
    protected $pmID;

    /** @Column(type="text") */
    protected $pmHandle;

    /** @Column(type="text") */
    protected $pmName;

    /** @Column(type="text", nullable=true) */
    protected $pmDisplayName;

    /** @Column(type="boolean") */
    protected $pmEnabled;

    /** @Column(type="integer", nullable=true) */
    protected $pmSortOrder;

    /**
     * @Column(type="integer")
     */
    protected $pkgID;

    private $methodController;

    public function getPaymentMethodID()
    {
        return $this->pmID;
    }

    public function getPaymentMethodHandle()
    {
        return $this->pmHandle;
    }

    public function getPaymentMethodName()
    {
        return $this->pmName;
    }

    public function getPaymentMethodPkgID()
    {
        return $this->pkgID;
    }

    public function getPaymentMethodSortOrder()
    {
        return $this->pmSortOrder;
    }

    public function getPaymentMethodDisplayName()
    {
        if ($this->pmDisplayName == "") {
            return $this->pmName;
        } else {
            return $this->pmDisplayName;
        }
    }

    public function isEnabled()
    {
        return $this->pmEnabled;
    }

    public static function getByID($pmID)
    {
        $db = Database::connection();
        $data = $db->GetRow("SELECT * FROM CommunityStorePaymentMethods WHERE pmID=?", $pmID);
        if (!empty($data)) {
            $method = new self();
            $method->setPropertiesFromArray($data);
            $method->setMethodController();
        }

        return ($method instanceof self) ? $method : false;
    }

    public static function getByHandle($pmHandle)
    {
        $db = Database::connection();
        $pm = $db->GetRow("SELECT pmID FROM CommunityStorePaymentMethods WHERE pmHandle=?", $pmHandle);

        return self::getByID($pm['pmID']);
    }

    public function setPropertiesFromArray($arr)
    {
        foreach ($arr as $key => $prop) {
            $this->{$key} = $prop;
        }
    }

    public function getMethodDirectory()
    {
        if ($this->pkgID > 0) {
            $pkg = Package::getByID($this->pkgID);
            $dir = $pkg->getPackagePath() . "/src/CommunityStore/Payment/Methods/" . $this->pmHandle . "/";
        }

        return $dir;
    }

    protected function setMethodController()
    {
        $th = Core::make("helper/text");
        $namespace = "Concrete\\Package\\" . $th->camelcase(Package::getByID($this->pkgID)->getPackageHandle()) . "\\Src\\CommunityStore\\Payment\\Methods\\" . $th->camelcase($this->pmHandle);

        $className = $th->camelcase($this->pmHandle) . "PaymentMethod";
        $namespace = $namespace . '\\' . $className;
        $this->methodController = new $namespace();
    }

    public function getMethodController()
    {
        return $this->methodController;
    }

    /*
     * @param string $pmHandle
     * @param string $pmName
     * @pkg Package Object
     * @param string $pmDisplayName
     * @param bool $enabled
     */
    public static function add($pmHandle, $pmName, $pkg = null, $pmDisplayName = null, $enabled = false)
    {
        $db = Database::connection();
        $pkgID = 0;
        if ($pkg instanceof Package) {
            $pkgID = $pkg->getPackageID();
        }
        if ($pmDisplayName == null) {
            $pmDisplayName = $pmName;
        }
        //make sure this gateway isn't already installed
        $pm = self::getByHandle($pmHandle);
        if (!($pm instanceof self)) {
            $vals = array($pmHandle, $pmName, $pmDisplayName, $pkgID);
            $db->Execute("INSERT INTO CommunityStorePaymentMethods (pmHandle,pmName,pmDisplayName,pkgID) VALUES (?,?,?,?)", $vals);
            $pm = self::getByHandle($pmHandle);
            if ($enabled) {
                $pm->setEnabled(1);
            }
        }

        return $pm;
    }

    public function setEnabled($status)
    {
        $db = Database::connection();
        $db->Execute("UPDATE CommunityStorePaymentMethods SET pmEnabled=? WHERE pmID=?", array($status, $this->pmID));
    }

    public function setDisplayName($name)
    {
        $db = Database::connection();
        $db->Execute("UPDATE CommunityStorePaymentMethods SET pmDisplayName=? WHERE pmID=?", array($name, $this->pmID));
    }

    public function setSortOrder($order)
    {
        $db = Database::connection();
        $db->Execute("UPDATE CommunityStorePaymentMethods SET pmSortOrder=? WHERE pmID=?", array($order, $this->pmID));
    }

    public function delete()
    {
        $db = Database::connection();
        $db->Execute("DELETE FROM CommunityStorePaymentMethods WHERE pmID=?", $this->pmID);
    }

    public static function getMethods($enabled = false)
    {
        $db = Database::connection();
        if ($enabled == true) {
            $results = $db->GetAll("SELECT * FROM CommunityStorePaymentMethods WHERE pmEnabled=1 ORDER BY pmSortOrder");
        } else {
            $results = $db->GetAll("SELECT * FROM CommunityStorePaymentMethods ORDER BY pmSortOrder");
        }
        $methods = array();
        foreach ($results as $result) {
            $method = self::getByID($result['pmID']);
            $methods[] = $method;
        }

        return $methods;
    }

    public static function getEnabledMethods()
    {
        return self::getMethods(true);
    }

    public function renderCheckoutForm()
    {
        $class = $this->getMethodController();
        $class->checkoutForm();
        $pkg = Package::getByID($this->pkgID);
        View::element($this->pmHandle . '/checkout_form', array('vars' => $class->getSets()), $pkg->getPackageHandle());
    }

    public function renderDashboardForm()
    {
        $controller = $this->getMethodController();
        $controller->dashboardForm();
        $pkg = Package::getByID($this->pkgID);
        View::element($this->pmHandle . '/dashboard_form', array('vars' => $controller->getSets()), $pkg->getPackageHandle());
    }

    public function renderRedirectForm()
    {
        $controller = $this->getMethodController();
        $controller->redirectForm();
        $pkg = Package::getByID($this->pkgID);
        View::element($this->pmHandle . '/redirect_form', array('vars' => $controller->getSets()), $pkg->getPackageHandle());
    }

    public function submitPayment()
    {
        //load controller    
        $class = $this->getMethodController();

        return $class->submitPayment();
    }

    public function getPaymentMinimum()
    {
        return 0;
    }

    public function getPaymentMaximum()
    {
        return 1000000000; // raises pinky
    }
}
