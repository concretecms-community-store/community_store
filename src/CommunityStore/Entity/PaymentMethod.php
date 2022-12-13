<?php

namespace Concrete\Package\CommunityStore\Src\CommunityStore\Entity;

use Concrete\Core\Entity\Package;
use Doctrine\ORM\Mapping as ORM;
use Concrete\Core\Controller\Controller;
use Concrete\Package\CommunityStore\Src\CommunityStore\Repository\PaymentMethodRepository;

/**
 * @ORM\Entity(repositoryClass=PaymentMethodRepository::class)
 * @ORM\Table(name="CommunityStorePaymentMethods1")
 */
class PaymentMethod extends Controller
{
    /**
     * @ORM\Id @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected int $id;

    /** @ORM\Column(type="text") */
    protected string $handle;

    /** @ORM\Column(type="text") */
    protected string $name;

    /** @ORM\Column(type="text", nullable=true) */
    protected ?string $displayName = null;

    /** @ORM\Column(type="text", nullable=true) */
    protected ?string $buttonLabel = null;

    /** @ORM\Column(type="string", nullable=true) */
    protected ?string $userGroups = null;

    /** @ORM\Column(type="string", nullable=true) */
    protected ?string $excludedUserGroups = null;

    /** @ORM\Column(type="boolean") */
    protected bool $enabled;

    /** @ORM\Column(type="integer", nullable=true) */
    protected ?int $sortOrder = null;

    /**
     * @ORM\ManyToOne(targetEntity="Concrete\Core\Entity\Package", inversedBy="Packages", cascade={"persist"})
     * @ORM\JoinColumn(referencedColumnName="pkgID", onDelete="CASCADE")
     */
    protected Package $package;

    public function getId(): int
    {
        return $this->id;
    }

    public function getHandle(): string
    {
        return $this->handle;
    }

    public function setHandle(string $handle): self
    {
        $this->handle = $handle;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDisplayName(): ?string
    {
        return $this->displayName;
    }

    public function setDisplayName(?string $displayName): self
    {
        $this->displayName = $displayName;

        return $this;
    }

    public function getButtonLabel(): ?string
    {
        return $this->buttonLabel;
    }

    public function setButtonLabel(?string $buttonLabel): self
    {
        $this->buttonLabel = $buttonLabel;

        return $this;
    }

    public function getUserGroups(): ?string
    {
        return $this->userGroups;
    }

    public function setUserGroups(?string $userGroups): self
    {
        $this->userGroups = $userGroups;

        return $this;
    }

    public function getExcludedUserGroups(): ?string
    {
        return $this->excludedUserGroups;
    }

    public function setExcludedUserGroups(?string $excludedUserGroups): self
    {
        $this->excludedUserGroups = $excludedUserGroups;

        return $this;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function getSortOrder(): ?int
    {
        return $this->sortOrder;
    }

    public function setSortOrder(?int $sortOrder): self
    {
        $this->sortOrder = $sortOrder;

        return $this;
    }

    public function getPackage(): Package
    {
        return $this->package;
    }

    public function setPackage(Package $package): self
    {
        $this->package = $package;

        return $this;
    }

    private $methodController;

//    public function getID()
//    {
//        return $this->pmID;
//    }
//
//    public function getHandle()
//    {
//        return $this->pmHandle;
//    }
//
//    public function setHandle($handle)
//    {
//        $this->pmHandle = $handle;
//    }
//
//    public function getName()
//    {
//        return $this->pmName;
//    }
//
//    public function setName($name)
//    {
//        return $this->pmName = $name;
//    }
//
//    public function getButtonLabel()
//    {
//        return $this->pmButtonLabel;
//    }
//
//    public function setButtonLabel($pmButtonLabel)
//    {
//        $this->pmButtonLabel = $pmButtonLabel;
//    }
//
//    public function getPackageID()
//    {
//        return $this->pkgID;
//    }
//
//    public function setPackageID($pkgID)
//    {
//        $this->pkgID = $pkgID;
//    }
//
//    public function getSortOrder()
//    {
//        return $this->pmSortOrder;
//    }
//
//    public function setSortOrder($order)
//    {
//        $this->pmSortOrder = $order ? $order : 0;
//    }
//
//    public function getDisplayName()
//    {
//        if ("" == $this->pmDisplayName) {
//            return $this->pmName;
//        } else {
//            return $this->pmDisplayName;
//        }
//    }
//
//    public function setDisplayName($name)
//    {
//        $this->pmDisplayName = $name;
//    }
//
//    public function setEnabled($status)
//    {
//        $this->pmEnabled = (bool) $status;
//    }
//
//    public function isEnabled()
//    {
//        return $this->pmEnabled;
//    }
//
//    public function getUserGroups()
//    {
//        return $this->pmUserGroups ? explode(',', $this->pmUserGroups) : [];
//    }
//
//    public function setUserGroups($userGroups)
//    {
//        if (is_array($userGroups)) {
//            $this->pmUserGroups = implode(',', $userGroups);
//        } else {
//            $this->pmUserGroups = '';
//        }
//    }
//
//    public function getExcludedUserGroups()
//    {
//        return $this->pmExcludedUserGroups ? explode(',', $this->pmExcludedUserGroups) : [];
//    }
//
//    public function setExcludedUserGroups($userGroups)
//    {
//        if (is_array($userGroups)) {
//            $this->pmExcludedUserGroups = implode(',', $userGroups);
//        } else {
//            $this->pmExcludedUserGroups = '';
//        }
//    }
//
//    public static function getByID($pmID)
//    {
//        $em = dbORM::entityManager();
//        $method = $em->find(get_class(), $pmID);
//
//        if ($method) {
//            $method->setMethodController();
//        }
//
//        return ($method instanceof self) ? $method : false;
//    }
//
//    public static function getByHandle($pmHandle)
//    {
//        $em = dbORM::entityManager();
//        $method = $em->getRepository(get_class())->findOneBy(['pmHandle' => $pmHandle]);
//
//        if ($method) {
//            $method->setMethodController();
//        }
//
//        return ($method instanceof self) ? $method : false;
//    }
//
//    public function getMethodDirectory()
//    {
//        if ($this->pkgID > 0) {
//            $pkg = Application::getFacadeApplication()->make('Concrete\Core\Package\PackageService')->getByID($this->pkgID);
//            $dir = $pkg->getPackagePath() . "/src/CommunityStore/Payment/Methods/" . $this->pmHandle . "/";
//        }
//
//        return $dir;
//    }
//
//    protected function setMethodController()
//    {
//        $app = Application::getFacadeApplication();
//
//        $th = $app->make("helper/text");
//        $pkg = $app->make('Concrete\Core\Package\PackageService')->getByID($this->pkgID);
//
//        $namespace = "Concrete\\Package\\" . $th->camelcase($pkg->getPackageHandle()) . "\\Src\\CommunityStore\\Payment\\Methods\\" . $th->camelcase($this->pmHandle);
//
//        $className = $th->camelcase($this->pmHandle) . "PaymentMethod";
//        $namespace = $namespace . '\\' . $className;
//        $this->methodController = new $namespace();
//    }
//
//    public function getMethodController()
//    {
//        return $this->methodController;
//    }
//
//    /*
//     * @ORM\param string $pmHandle
//     * @ORM\param string $pmName
//     * @ORM\pkg Package Object
//     * @ORM\param string $pmDisplayName
//     * @ORM\param bool $enabled
//     */
//    public static function add($pmHandle, $pmName, $pkg = null, $pmButtonLabel = '', $enabled = false)
//    {
//        $pm = self::getByHandle($pmHandle);
//        if (!($pm instanceof self)) {
//            $paymentMethod = new self();
//            $paymentMethod->setHandle($pmHandle);
//            $paymentMethod->setName($pmName);
//            $paymentMethod->setPackageID($pkg->getPackageID());
//            $paymentMethod->setDisplayName($pmName);
//            $paymentMethod->setButtonLabel($pmButtonLabel);
//            $paymentMethod->setEnabled($enabled);
//            $paymentMethod->save();
//        }
//    }
//
//    public static function getMethods($enabled = false)
//    {
//        $em = dbORM::entityManager();
//        if ($enabled) {
//            $methods = $em->getRepository(get_class())->findBy(['pmEnabled' => 1], ['pmSortOrder' => 'ASC']);
//        } else {
//            $methods = $em->getRepository(get_class())->findBy([], ['pmSortOrder' => 'ASC']);
//        }
//        foreach ($methods as $method) {
//            $method->setMethodController();
//        }
//
//        return $methods;
//    }
//
//    public static function getEnabledMethods()
//    {
//        return self::getMethods(true);
//    }
//
//    public static function getAvailableMethods($total) {
//        $enabledMethods = self::getMethods(true);
//
//        $availableMethods = [];
//
//        $u = new User();
//        $userGroups = $u->getUserGroups();
//
//        foreach ($enabledMethods as $em) {
//            $includedGroups = $em->getUserGroups();
//            $excludedGroups = $em->getExcludedUserGroups();
//
//            if (count($includedGroups) > 0 && count(array_intersect($includedGroups, $userGroups)) == 0) {
//                continue;
//            }
//
//            if (count($excludedGroups) > 0 && count(array_intersect($excludedGroups, $userGroups)) > 0) {
//                continue;
//            }
//
//            $emmc = $em->getMethodController();
//
//            if ($total >= $emmc->getPaymentMinimum() && $total <= $emmc->getPaymentMaximum()) {
//                $availableMethods[] = $em;
//            }
//        }
//
//        $event = new PaymentEvent('add');
//        $event->setMethods($availableMethods);
//
//        \Events::dispatch(PaymentEvent::PAYMENT_ON_AVAILABLE_METHODS_GET, $event);
//
//        $changed = $event->getChanged();
//        if ($changed){
//            $availableMethods = $event->getMethods();
//        }
//
//        return $availableMethods;
//    }
//
//
//    public function renderCheckoutForm()
//    {
//        $class = $this->getMethodController();
//        $class->checkoutForm();
//        $pkg = Application::getFacadeApplication()->make('Concrete\Core\Package\PackageService')->getByID($this->pkgID);
//        View::element($this->pmHandle . '/checkout_form', ['vars' => $class->getSets()], $pkg->getPackageHandle());
//    }
//
//    public function renderDashboardForm()
//    {
//        $controller = $this->getMethodController();
//        $controller->dashboardForm();
//        $pkg = Application::getFacadeApplication()->make('Concrete\Core\Package\PackageService')->getByID($this->pkgID);
//        View::element($this->pmHandle . '/dashboard_form', ['vars' => $controller->getSets()], $pkg->getPackageHandle());
//    }
//
//    public function renderRedirectForm()
//    {
//        $controller = $this->getMethodController();
//        $controller->redirectForm();
//        $pkg = Application::getFacadeApplication()->make('Concrete\Core\Package\PackageService')->getByID($this->pkgID);
//        View::element($this->pmHandle . '/redirect_form', ['vars' => $controller->getSets()], $pkg->getPackageHandle());
//    }
//
//    public function submitPayment()
//    {
//        //load controller
//        $class = $this->getMethodController();
//
//        return $class->submitPayment();
//    }
//
//    public function getPaymentMinimum()
//    {
//        return 0;
//    }
//
//    public function getPaymentMaximum()
//    {
//        return 1000000000; // raises pinky
//    }
//
//    public function save(array $data = [])
//    {
//        $em = dbORM::entityManager();
//        $em->persist($this);
//        $em->flush();
//    }
//
//    public function delete()
//    {
//        $this->remove();
//    }
//
//    public function remove()
//    {
//        $em = dbORM::entityManager();
//        $em->remove($this);
//        $em->flush();
//    }
//
//    public function isExternal()
//    {
//        return false;
//    }
//
//    public function markPaid()
//    {
//        return true;
//    }
//
//    public function sendReceipt()
//    {
//        return true;
//    }
//
//    // method stub
//    public function redirectForm()
//    {
//    }
//
//    // method stub
//    public function checkoutForm()
//    {
//    }
//
//    // method stub
//    public function getPaymentInstructions()
//    {
//        return '';
//    }
}
