<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Multilingual;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="CommunityStoreTranslations", indexes={@ORM\Index(name="locale_idx", columns={"locale"}),@ORM\Index(name="text_idx", columns={"originalText"}),@ORM\Index(name="entitytype_idx", columns={"entityType"}),@ORM\Index(name="entityid_idx", columns={"entityID"})})
 */
class Translation
{
    /**
     * @ORM\Id @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $tID;

    /**
     * @ORM\Column(type="string",nullable=true)
     */
    protected $locale;

    /**
     * @ORM\Column(type="string",nullable=true)
     */
    protected $originalText;

    /**
     * @ORM\Column(type="string",nullable=true)
     */
    protected $translatedText;

    /**
     * @ORM\Column(type="text",nullable=true)
     */
    protected $extendedText;

    /**
     * @ORM\Column(type="string",nullable=true)
     */
    protected $entityType;

    /**
     * @ORM\Column(type="integer",nullable=true)
     */
    protected $entityID;

    /**
     * @return mixed
     */
    public function getTranslationID()
    {
        return $this->tID;
    }


    /**
     * @return mixed
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @param mixed $locale
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * @return mixed
     */
    public function getOriginalText()
    {
        return $this->originalText;
    }

    /**
     * @param mixed $originalText
     */
    public function setOriginalText($originalText)
    {
        $this->originalText = $originalText;
    }

    /**
     * @return mixed
     */
    public function getTranslatedText()
    {
        return $this->translatedText;
    }

    /**
     * @param mixed $translatedText
     */
    public function setTranslatedText($translatedText)
    {
        $this->translatedText = $translatedText;
    }

    /**
     * @return mixed
     */
    public function getExtendedText()
    {
        return $this->extendedText;
    }

    /**
     * @param mixed $extendedText
     */
    public function setExtendedText($extendedText)
    {
        $this->extendedText = $extendedText;
    }

    /**
     * @return mixed
     */
    public function getEntityType()
    {
        return $this->entityType;
    }

    /**
     * @param mixed $entityType
     */
    public function setEntityType($entityType)
    {
        $this->entityType = $entityType;
    }

    /**
     * @return mixed
     */
    public function getEntityID()
    {
        return $this->entityID;
    }

    /**
     * @param mixed $entityID
     */
    public function setEntityID($entityID)
    {
        $this->entityID = $entityID;
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