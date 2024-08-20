<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Tax;

use Doctrine\ORM\Mapping as ORM;
use Concrete\Core\Support\Facade\Application;
use Concrete\Core\Support\Facade\DatabaseORM as dbORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="CommunityStoreTaxClasses")
 */
class TaxClass
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $tcID;

    /**
     * @ORM\Column(type="string", unique=true)
     */
    protected $taxClassHandle;

    /**
     * @ORM\Column(type="string")
     */
    protected $taxClassName;

    /**
     * @ORM\Column(type="text",nullable=true)
     */
    protected $taxClassRates;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $locked;

    public function setTaxClassHandle($handle)
    {
        $this->taxClassHandle = $handle;
    }

    public function setHandle($handle)
    {
        $this->taxClassHandle = $handle;
    }

    public function setTaxClassName($name)
    {
        $this->taxClassName = $name;
    }

    public function setName($name)
    {
        $this->taxClassName = $name;
    }

    public function setTaxClassRates(array $rates = null)
    {
        if ($rates) {
            $rates = implode(',', $rates);
            $this->taxClassRates = $rates;
        } else {
            $this->taxClassRates = '';
        }
    }

    public function setTaxClassLock($locked)
    {
        $this->locked = $locked;
    }

    public function setLocked($locked)
    {
        $this->locked = $locked;
    }

    public function getID()
    {
        return $this->tcID;
    }

    public function getTaxClassHandle()
    {
        return $this->taxClassHandle;
    }

    public function getTaxClassName()
    {
        return $this->taxClassName;
    }

    public function getName()
    {
        return $this->getTaxClassName();
    }

    public function isLocked()
    {
        return $this->locked;
    }

    public function getTaxClassRates()
    {
        $taxRates = explode(',', $this->taxClassRates);
        $taxes = [];
        foreach ($taxRates as $tr) {
            if ($tr) {
                $taxrate = TaxRate::getByID($tr);
                if ($taxrate) {
                    $taxes[] = $taxrate;
                }
            }
        }

        return $taxes;
    }

    public function getTaxClassRateIDs()
    {
        return explode(',', $this->taxClassRates);
    }

    public function addTaxClassRate($trID)
    {
        $taxClassRates = $this->taxClassRates;
        $taxClassRates = explode(",", $taxClassRates);
        $taxClassRates[] = $trID;
        $this->setTaxClassRates($taxClassRates);
        $this->save();
    }

    public function taxClassContainsTaxRate(TaxRate $taxRate)
    {
        $trID = $taxRate->getTaxRateID();
        if (in_array($trID, $this->getTaxClassRateIDs())) {
            return true;
        } else {
            return false;
        }
    }

    public static function getByID($tcID)
    {
        $em = dbORM::entityManager();

        return $em->find(__CLASS__, $tcID);
    }

    public static function getByHandle($taxClassHandle)
    {
        $em = dbORM::entityManager();

        return $em->getRepository(__CLASS__)->findOneBy(['taxClassHandle' => $taxClassHandle]);
    }

    public static function getTaxClasses()
    {
        $em = dbORM::entityManager();

        return $em->createQuery('select u from \Concrete\Package\CommunityStore\Src\CommunityStore\Tax\TaxClass u')->getResult();
    }

    public static function add($data)
    {
        $locked = 0;
        if (isset($data['taxClassLocked'])) {
            $locked = $data['taxClassLocked'];
        }
        $tc = new self();
        $th = Application::getFacadeApplication()->make("helper/text");
        $tc->setTaxClassHandle($th->handle($data['taxClassName']));
        $tc->setTaxClassName($data['taxClassName']);

        if (!isset($data['taxClassRates'])) {
            $data['taxClassRates'] = [];
        }

        $tc->setTaxClassRates($data['taxClassRates']);
        $tc->setTaxClassLock($locked);
        $tc->save();

        return $tc;
    }

    public function update($data)
    {
        $this->setTaxClassName($data['taxClassName']);

        if (!isset($data['taxClassRates'])) {
            $data['taxClassRates'] = [];
        }

        $this->setTaxClassRates($data['taxClassRates']);
        $this->save();
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
}
