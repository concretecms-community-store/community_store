<?php

namespace Concrete\Package\CommunityStore\Src\CommunityStore\Manufacturer;

use Doctrine\ORM\Mapping as ORM;
use Concrete\Core\Support\Facade\DatabaseORM as dbORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="CommunityStoreManufacturer")
 */
class Manufacturer
{

    /**
     * @ORM\Id @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $mID;


    /** @ORM\Column(type="string",nullable=true) */
    protected $mName;

    /**
     * @ORM\Column(type="text",nullable=true)
     */
    protected $mDesc;


    public function getMID()
    {
        return $this->mID;
    }

    public function getName()
    {
        return $this->mName;
    }

    public function setName($mName)
    {
        $this->mName = $mName;
    }

    public function getDescription()
    {
        return $this->mDesc;
    }

    public function setDescription($mDesc)
    {
        $this->mDesc = $mDesc;
    }



    public static function getByID($mID)
    {
        $em = dbORM::entityManager();

        return $em->find(get_class(), $mID);
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