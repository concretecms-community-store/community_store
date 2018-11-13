<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Shipping\Method;

use Doctrine\ORM\Mapping as ORM;
use Controller;

abstract class ShippingMethodTypeMethod extends Controller
{
    /**
     * @ORM\Id
     * @ORM\Column(name="smtmID",type="integer",nullable=false)
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $smtmID;

    /**
     * @ORM\Column(type="string",nullable=true)
     */
    protected $smID;

    /**
     * @ORM\Column(type="boolean",nullable=true)
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

    abstract public function getOffers();

    public static function getByID($smtmID)
    {
        $em = \ORM::entityManager();

        return $em->getRepository(get_called_class())->find($smtmID);
    }

    public function validate($args, $e)
    {
        return $e;
    }

    public function save()
    {
        $em = \ORM::entityManager();
        $em->persist($this);
        $em->flush();
    }

    public function delete()
    {
        $em = \ORM::entityManager();
        $em->remove($this);
        $em->flush();
    }
}
