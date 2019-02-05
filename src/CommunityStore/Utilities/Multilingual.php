<?php

namespace Concrete\Package\CommunityStore\Src\CommunityStore\Utilities;
use Doctrine\ORM\EntityManagerInterface;
use Concrete\Package\CommunityStore\Src\CommunityStore\Multilingual\Translation;
use Concrete\Core\Localization\Localization;
use Concrete\Core\Application\Application;

class Multilingual
{
    /**
     * @var \Concrete\Core\Application\Application
     */
    protected $app;
    protected $entityManager;

    public function __construct(Localization $localization, Application $application, EntityManagerInterface $entityManager)
    {
        $this->localization = $localization;
        $this->app = $application;
        $this->entityManager = $entityManager;
    }

    /**
     * Translate text using Community Store's translation system
     *
     * @param string $text The text to be translated.
     * @param string $type The type of text being translated.
     * @param string $id The ID of the entity being translated, for example a Product's ID.
     * @param string $forcedLocale Force the translation to a specified locale, instead of determining it automatically.
     * @param string $nativeTranslate Fall back to native translate function
     *
     * @return string Returns the translated text.
     *
     */
    function t($text, $type = false, $id = false, $forcedLocale = false, $nativeTranslate = false)
    {
        $locale = $this->localization->getLocale();

        $siteConfig = $this->app->make('site')->getActiveSiteForEditing()->getConfigRepository();
        $defaultSourceLocale = $siteConfig->get('multilingual.default_source_locale');

        if ($locale != $defaultSourceLocale || $forcedLocale) {

            $qb = $this->entityManager->createQueryBuilder();

            $query = $qb->select('t')
                ->from('Concrete\Package\CommunityStore\Src\CommunityStore\Multilingual\Translation', 't')
                ->where('t.entityType = :type')
                ->setParameter('type', $type);

            if ($id) {
                 $query->andWhere('t.entityID = :id')->setParameter('id', $id);
            }

            $query->setMaxResults(1);

            $result = $query->getQuery()->getResult();

            if ($result && $result[0]) {
                $translation = $result[0]->getTranslatedText();

                if ($translation) {
                    return $translation;
                }
            }
        }

        return $text;
    }

}
