<?php

namespace Concrete\Package\CommunityStore\Controller\SinglePage\Dashboard\Store\Multilingual;

use Concrete\Core\Page\Controller\DashboardSitePageController;
use Concrete\Package\CommunityStore\Src\CommunityStore\Multilingual\Translation;

class Common extends DashboardSitePageController
{
    public function view()
    {
        $this->set('pageTitle', t('Common Translations'));


        $qb = $this->entityManager->createQueryBuilder();

        $query = $qb->select('o')
            ->from('Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductOption\ProductOption', 'o')
            ->groupBy('o.poName')->orderBy('o.poName');

        $this->set("optionNames", $query->getQuery()->getResult());

        $query = $qb->select('oi')
            ->from('Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductOption\ProductOptionItem', 'oi')
            ->groupBy('oi.poiName')->orderBy('oi.poiName');

        $this->set("optionItems", $query->getQuery()->getResult());

        $this->set('defaultLocale', $this->getLocales()['default']);
        $this->set('locales', $this->getLocales()['additional']);

    }

    private function getLocales()
    {
        $site = $this->getSite();
        $pages = \Concrete\Core\Multilingual\Page\Section\Section::getList($site);
        $localePages = ['additional' => []];
        $defaultSourceLocale = $site->getConfigRepository()->get('multilingual.default_source_locale');

        foreach ($pages as $p) {
            if ($defaultSourceLocale == $p->getLocale()) {
                $localePages['default'] = $p;
            } else {
                $localePages['additional'][] = $p;
            }
        }

        return $localePages;
    }

    public function save()
    {
        if ($this->post() && $this->token->validate('community_store')) {

            $translations = $this->post('translation');

            foreach ($translations['options'] as $locale => $types) {
                foreach ($types as $type => $items) {
                    foreach ($items as $key => $texts) {
                        foreach ($texts as $original => $text) {
                            if ($text) {
                                $qb = $this->entityManager->createQueryBuilder();
                                $t = $qb->select('t')
                                    ->from('Concrete\Package\CommunityStore\Src\CommunityStore\Multilingual\Translation', 't')
                                    ->where('t.entityType = :type')->setParameter('type', $key)
                                    ->andWhere('t.locale = :locale')->setParameter('locale', $locale)
                                    ->andWhere('t.originalText = :originalText')->setParameter('originalText', $original)
                                    ->setMaxResults(1)->getQuery()->getResult();


                                if (!empty($t)) {
                                    $t = $t[0];
                                } else {
                                    $t = new Translation();
                                }

                                $t->setEntityType($key);

                                if ($type == 'text') {
                                    $t->setTranslatedText($text);
                                } else {
                                    $t->setExtendedText($text);
                                }

                                $t->setOriginalText($original);
                                $t->setLocale($locale);
                                $t->save();
                            }
                        }
                    }
                }
            }
        }


        $this->flash('success', t('Common Translations Updated'));
        return \Redirect::to('/dashboard/store/multilingual/common');

    }
}
