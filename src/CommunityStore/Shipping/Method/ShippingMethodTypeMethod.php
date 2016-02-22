<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Shipping\Method;

use Database;
use Controller;

abstract class ShippingMethodTypeMethod extends Controller
{
    /**
     * @Id
     * @Column(name="smtmID",type="integer",nullable=false)
     * @GeneratedValue(strategy="AUTO")
     */
    protected $smtmID;

    /**
     * @Column(type="string",nullable=true)
     */
    protected $smID;

    /**
     * @Column(type="boolean",nullable=true)
     * enables the option for it to be disabled instead of deleted
     */
    protected $disableEnabled;

    public function setShippingMethodID($smID)
    {
        $this->smID = $smID;
    }
    public function enableDisableButton($bool = false)
    {
        $this->disableEnabled = $bool;
    }

    public function disableEnabled()
    {
        return $this->disableEnabled;
    }

    abstract public static function getByID($smtmID);

    public function getShippingMethodTypeMethodID()
    {
        return $this->smtmID;
    }
    public function getShippingMethodID()
    {
        return $this->smID;
    }

    abstract public function dashboardForm();
    abstract public function addMethodTypeMethod($data);
    abstract public function update($data);
    abstract public function isEligible();
    abstract public function getRate();

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
