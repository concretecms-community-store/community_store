<?php

namespace Concrete\Package\CommunityStore\Controller\SinglePage\Dashboard\Store\Multilingual;

use Concrete\Core\Multilingual\Page\Section\Section;
use Concrete\Core\Page\Controller\DashboardSitePageController;
use Concrete\Core\Routing\Redirect;
use Concrete\Core\Support\Facade\Config;
use Concrete\Package\CommunityStore\Entity\Attribute\Key\StoreOrderKey;
use Concrete\Package\CommunityStore\Src\CommunityStore\Discount\DiscountRule;
use Concrete\Package\CommunityStore\Src\CommunityStore\Multilingual\Translation;
use Concrete\Package\CommunityStore\Src\CommunityStore\Payment\Method as PaymentMethod;
use Concrete\Package\CommunityStore\Src\CommunityStore\Shipping\Method\ShippingMethod as ShippingMethod;
use Concrete\Package\CommunityStore\Src\CommunityStore\Tax\Tax;

class Checkout extends DashboardSitePageController
{
    public function view()
    {
        $this->set('paymentMethods', PaymentMethod::getMethods());
        $this->set('shippingMethods', ShippingMethod::getMethods());
        $this->set('taxRates', Tax::getTaxRates());
        $this->set('discountRules', DiscountRule::getRules());

        $orderAttributes = StoreOrderKey::getAttributeListBySet('order_choices');
        $this->set('orderAttributes', $orderAttributes);

        $this->set('receiptHeader', trim(Config::get('community_store.receiptHeader')));
        $this->set('receiptFooter', trim(Config::get('community_store.receiptFooter')));
        $this->set('receiptBCC', trim(Config::get('community_store.receiptBCC')));

        $this->set('defaultLocale', $this->getLocales()['default']);
        $this->set('locales', $this->getLocales()['additional']);
        $this->set('pageTitle', t('Checkout Related Translations'));
    }

    public function save()
    {
        if ($this->request->request->all() && $this->token->validate('community_store')) {
            $translations = $this->request->request->get('translation');

            foreach ($translations as $entityType => $translationData) {
                foreach ($translationData as $entityID => $langs) {
                    foreach ($langs as $locale => $types) {
                        foreach ($types as $type => $items) {
                            foreach ($items as $key => $text) {
                                $qb = $this->entityManager->createQueryBuilder();
                                $t = $qb->select('t')
                                    ->from('Concrete\Package\CommunityStore\Src\CommunityStore\Multilingual\Translation', 't')
                                    ->where('t.entityType = :type')->setParameter('type', $key)
                                    ->andWhere('t.locale = :locale')->setParameter('locale', $locale)
                                    ->andWhere('t.entityID = :entid')->setParameter('entid', $entityID)
                                    ->setMaxResults(1)->getQuery()->getResult();

                                if (!empty($t)) {
                                    $t = $t[0];
                                    if (!$text)  {
                                        $t->delete();
                                    }
                                } else {
                                    $t = new Translation();
                                }

                                if ($text) {
                                    $t->setEntityID($entityID);
                                    $t->setEntityType($key);

                                    if ($type == 'text') {
                                        $t->setTranslatedText($text);
                                    } else {
                                        $t->setExtendedText($text);
                                    }
                                    $t->setLocale($locale);
                                    $t->save();
                                }
                            }
                        }
                    }
                }
            }

            $translations = $this->post('configtranslation');
            foreach ($translations as $locale => $types) {
                foreach ($types as $type => $items) {
                    foreach ($items as $key => $text) {
                        $qb = $this->entityManager->createQueryBuilder();
                        $t = $qb->select('t')
                            ->from('Concrete\Package\CommunityStore\Src\CommunityStore\Multilingual\Translation', 't')
                            ->where('t.entityType = :type')->setParameter('type', $key)
                            ->andWhere('t.locale = :locale')->setParameter('locale', $locale)
                            ->setMaxResults(1)->getQuery()->getResult();

                        if (!empty($t)) {
                            $t = $t[0];
                            if (!$text)  {
                                $t->delete();
                            }
                        } else {
                            $t = new Translation();
                        }

                        if ($text) {
                            $t->setEntityType($key);

                            if ($type == 'text') {
                                $t->setTranslatedText($text);
                            } else {
                                $t->setExtendedText($text);
                            }
                            $t->setLocale($locale);
                            $t->save();
                        }
                    }
                }
            }
        }

        $this->flash('success', t('Checkout Translations Updated'));

        return Redirect::to('/dashboard/store/multilingual/checkout');
    }

    private function getLocales()
    {
        $site = $this->getSite();
        $pages = Section::getList($site);
        $locales = $site->getLocales();

        $localePages = ['additional' => []];

        foreach($locales as $locale) {
            if ($locale->getIsDefault()) {
                $localePages['default'] = $locale;
            } else {
                $localePages['additional'][] = $locale;
            }
        }

        return $localePages;
    }
}
