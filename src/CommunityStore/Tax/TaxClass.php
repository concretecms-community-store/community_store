<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Tax;

use Core;
use Database;

/**
 * @Entity
 * @Table(name="CommunityStoreTaxClasses")
 */
class TaxClass
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="AUTO")
     */
    protected $tcID;

    /**
     * @Column(type="string", unique=true)
     */
    protected $taxClassHandle;

    /**
     * @Column(type="string")
     */
    protected $taxClassName;

    /**
     * @Column(type="string",nullable=true)
     */
    protected $taxClassRates;

    /**
     * @Column(type="boolean")
     */
    protected $locked;

    public function setTaxClassHandle($handle)
    {
        $this->taxClassHandle = $handle;
    }
    public function setTaxClassName($name)
    {
        $this->taxClassName = $name;
    }
    public function setTaxClassRates(array $rates = null)
    {
        if ($rates) {
            $rates = implode(',', $rates);
            $this->taxClassRates = $rates;
        }
    }
    public function setTaxClassLock($locked)
    {
        $this->locked = $locked;
    }

    public function getTaxClassID()
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
    public function isLocked()
    {
        return $this->locked;
    }
    public function getTaxClassRates()
    {
        $taxRates = explode(',', $this->taxClassRates);
        $taxes = array();
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
        $db = Database::connection();
        $em = $db->getEntityManager();

        return $em->find('Concrete\Package\CommunityStore\Src\CommunityStore\Tax\TaxClass', $tcID);
    }

    public static function getByHandle($taxClassHandle)
    {
        $db = Database::connection();
        $em = $db->getEntityManager();

        return $em->getRepository('Concrete\Package\CommunityStore\Src\CommunityStore\Tax\TaxClass')->findOneBy(array('taxClassHandle' => $taxClassHandle));
    }

    public static function getTaxClasses()
    {
        return Database::connection()->getEntityManager()->createQuery('select u from \Concrete\Package\CommunityStore\Src\CommunityStore\Tax\TaxClass u')->getResult();
    }

    public static function add($data)
    {
        $locked = 0;
        if ($data['taxClassLocked']) {
            $locked = $data['taxClassLocked'];
        }
        $tc = new self();
        $th = Core::make("helper/text");
        $tc->setTaxClassHandle($th->handle($data['taxClassName']));
        $tc->setTaxClassName($data['taxClassName']);
        $tc->setTaxClassRates($data['taxClassRates']);
        $tc->setTaxClassLock($locked);
        $tc->save();

        return $tc;
    }

    public function update($data)
    {
        $locked = 0;
        if ($data['taxClassLocked']) {
            $locked = $data['taxClassLocked'];
        }
        $this->setTaxClassName($data['taxClassName']);
        $this->setTaxClassRates($data['taxClassRates']);
        $this->save();
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
