<?php

namespace Concrete\Package\CommunityStore\Src\CommunityStore\Utilities;

use Concrete\Core\Application\Application;
use Concrete\Core\Localization\Localization;
use Doctrine\ORM\EntityManagerInterface;

class Multilingual
{
    /**
     * @var \Concrete\Core\Application\Application
     */
    protected $app;

    protected $entityManager;

    protected $longTextTypes = [
        'productDescription',
        'productDetails',
        'receiptEmailHeader',
        'receiptEmailFooter',
        'shippingDetails',
        'paymentInstructions',
    ];

    public function __construct(Localization $localization, Application $application, EntityManagerInterface $entityManager)
    {
        $this->localization = $localization;
        $this->app = $application;
        $this->entityManager = $entityManager;
    }

    /**
     * Translate text using Community Store's translation system.
     *
     * @param string $text the text to be translated
     * @param int $productID the ID of the product (if applicable)
     * @param string $context the type of text being translated
     * @param int $id the ID of the entity being translated, for example an attribute's ID
     * @param string $forcedLocale force the translation to a specified locale, instead of determining it automatically
     * @param true $useCommon Return a common translation for a string if available
     *
     * @return string Returns the translated text.
     *
     *  Current context handles are:
     *
     *  productName
     *  productDescription
     *  productDetails
     *  productQuantityLabel
     *  productAddToCartText
     *  productOutOfStockMessage
     *  productNotAvailableMessage
     *  productAttributeName
     *  productAttributeLabel
     *  productAttributeValue
     *  optionName
     *  optionValue
     *  taxRateName
     *  shippingName
     *  shippingDetails
     *  optionValue
     *  paymentDisplayName
     *  paymentButtonLabel
     *  receiptEmailHeader
     *  receiptEmailFooter
     */
    public function t($text, $context = false, $productID = false, $id = false, $forcedLocale = false, $useCommon = true)
    {
        $locale = $this->localization->getLocale();

        $site = $this->app->make('site')->getSite();
        $defaultSourceLocale = $site->getDefaultLocale()->getLocale();

        $commonContexts = [
            'productAttributeValue',
            'optionName',
            'optionDetails',
            'optionSelectorName',
            'optionValue',
        ];

        if ($locale != $defaultSourceLocale || $forcedLocale) {
            $qb = $this->entityManager->createQueryBuilder();

            $query = $qb->select('t')
                ->from('Concrete\Package\CommunityStore\Src\CommunityStore\Multilingual\Translation', 't')
                ->where('t.entityType = :type')
                ->setParameter('type', $context)
            ;

            if ($id) {
                if ($useCommon) {
                    $query->andWhere('(t.entityID = :id or (t.entityID is null and t.originalText = :text))')->setParameter('id', $id)->setParameter('text', $text);
                } else {
                    $query->andWhere('t.entityID = :id')->setParameter('id', $id);
                }
            } elseif (in_array($context, $commonContexts)) {
                $query->andWhere('t.originalText = :text and t.entityID is null')->setParameter('text', $text);
            }

            if ($productID) {
                if ($useCommon) {
                    switch ($context) {
                        case 'productQuantityLabel':
                        case 'productAddToCartText':
                        case 'productOutOfStockMessage':
                        case 'productNotAvailableMessage':
                            $query->andWhere('t.pID = :pid or (t.originalText = :text)')->setParameter('pid', $productID)->setParameter('text', $text);
                            break;
                        default:
                            $query->andWhere('t.pID = :pid or (t.pID is null)')->setParameter('pid', $productID);
                            break;
                    }
                } else {
                    $query->andWhere('t.pID = :pid')->setParameter('pid', $productID);
                }
            } else {
                $query->andWhere('t.pID is null');
            }

            $query->andWhere('t.locale = :locale')->setParameter('locale', $forcedLocale ? $forcedLocale : $locale);

            if ($productID) {
                $query->orderBy('t.pID', 'desc');
            }

            if ($id) {
                $query->orderBy('t.entityID', 'desc');
            }

            $query->setMaxResults(2);

            $result = $query->getQuery()->getResult();

            if ($result && $result[0]) {
                if (in_array($context, $this->longTextTypes)) {
                    $translation = $result[0]->getExtendedText();
                } else {
                    $translation = $result[0]->getTranslatedText();
                }

                if ($translation) {
                    return $translation;
                }
            }
        }

        if (!$forcedLocale) {
            return $text;
        }

        return '';
    }
}
