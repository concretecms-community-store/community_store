<?php

namespace Concrete\Package\CommunityStore\Src\CommunityStore\Manufacturer;

use Concrete\Core\Multilingual\Page\Section\Section;
use Concrete\Core\Page\Page;
use Concrete\Core\Support\Facade\DatabaseORM as dbORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

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

    /**
     * @ORM\Column(type="string",nullable=true)
     */
    protected $mName;

    /**
     * @ORM\Column(type="integer",nullable=true)
     */
    protected $cID;

    /**
     * @ORM\Column(type="text",nullable=true)
     */
    protected $mDesc;

    /**
     * @ORM\OneToMany(targetEntity="Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product", mappedBy="manufacturer",cascade={"persist"}))
     */
    protected $products;

    public function __construct()
    {
        $this->products = new ArrayCollection();
    }

    public function getProducts()
    {
        return $this->products;
    }

    public function getID()
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

    public function setPageID($cID)
    {
        $this->setCollectionID($cID);
    }

    public function setCollectionID($cID)
    {
        $this->cID = $cID;
    }

    public function getPageID()
    {
        return $this->cID;
    }

    public function getManufacturerPage()
    {
        if ($this->getPageID()) {
            $pageID = $this->getPageID();
            $manufacturerPage = Page::getByID($pageID);
            if ($manufacturerPage && !$manufacturerPage->isError() && !$manufacturerPage->isInTrash()) {
                $c = Page::getCurrentPage();
                $lang = Section::getBySectionOfSite($c);

                if (is_object($lang)) {
                    $relatedID = $lang->getTranslatedPageID($manufacturerPage);

                    if ($relatedID && $relatedID != $pageID) {
                        $translatedPage = Page::getByID($relatedID);

                        if ($translatedPage && !$translatedPage->isError() && !$translatedPage->isInTrash()) {
                            $manufacturerPage = $translatedPage;
                        }
                    }
                }

                return $manufacturerPage;
            }
        }

        return false;
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

        return $em->find(__CLASS__, $mID);
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
