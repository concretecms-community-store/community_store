<?php
namespace Concrete\Package\CommunityStore\Controller\SinglePage\Dashboard\Store\Multilingual;

use Concrete\Core\Routing\Redirect;
use Concrete\Core\Support\Facade\Database;
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
            ->groupBy('o.poName')->orderBy('o.poName')->getQuery()->getResult();

        $optionNames = [];

        foreach($query as $optionName) {
            $optionNames[$optionName->getName()] = $optionName;
        }

        $this->set("optionNames", array_values($optionNames));

        $query = $qb->select('oi')
            ->from('Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductOption\ProductOptionItem', 'oi')
            ->groupBy('oi.poiName')->orderBy('oi.poiName')->getQuery()->getResult();

        $optionItems = [];

        foreach($query as $optionItem) {
            $optionItems[$optionItem->getName()] = $optionItem;
        }

        $this->set("optionItems", array_values($optionItems));

        $this->set('defaultLocale', $this->getLocales()['default']);
        $this->set('locales', $this->getLocales()['additional']);

        $productCategory = $this->app->make('Concrete\Package\CommunityStore\Attribute\Category\ProductCategory');

        $attrList = $productCategory->getList();
        $this->set('attrList', $attrList);

        $attInputTypes = ['text'];
        $attSelectTypes = ['select'];
        $attrHandles = [];

        $attrOptions = [];
        $typeLookup = [];

        foreach ($attrList as $ak) {
            $typeHandle = $ak->getAttributeType()->getAttributeTypeHandle();

            if (in_array($typeHandle, $attInputTypes)) {
                $availableAtts[] = $ak;
                $handle = $ak->getAttributeKeyHandle();

                $typeLookup['ak_' . $handle] = $typeHandle;
                $attrHandles[] = 'ak_' . $handle;
            }

            if (in_array($typeHandle, $attSelectTypes)) {
                $options = $ak->getController()->getOptions();

                foreach ($options as $option) {
                    $attrOptions['text'][$option->getSelectAttributeOptionDisplayValue()] = true;
                }
            }
        }

        $db = Database::connection();

        if ($attrHandles) {
            $attributedata = $db->fetchAll('SELECT ' . implode(',', $attrHandles) . ' FROM CommunityStoreProductSearchIndexAttributes');

            if (!empty($attributedata)) {
                foreach ($attributedata as $row) {
                    foreach ($row as $field => $data) {
                        $lines = explode("\n", trim($data));

                        foreach ($lines as $l) {
                            if ($l && !is_numeric($l)) {
                                $attrOptions[$typeLookup[$field]][trim($l)] = true;
                            }
                        }
                    }
                }
            }
        }

        ksort($attrOptions);

        foreach ($attrOptions as $type => $options) {
            ksort($options);
            $attrOptions[$type] = $options;
        }

        $this->set('attrOptions',$attrOptions);

        $quantityLabels = [];
        $quantityLabelsData =  $db->fetchAll('SELECT distinct pQtyLabel FROM CommunityStoreProducts where pQtyLabel <> ""');

        foreach($quantityLabelsData as $label) {
            $quantityLabels[] = $label['pQtyLabel'];
        }

        $this->set('quantityLabels',$quantityLabels);
    }

    private function getLocales() {
        $site = $this->getSite();
        $pages = \Concrete\Core\Multilingual\Page\Section\Section::getList($site);
        $locales = $site->getLocales();

        $localePages = array('additional'=>array());

        foreach($locales as $locale) {
            if ($locale->getIsDefault()) {
                $localePages['default'] = $locale;
            } else {
                $localePages['additional'][] = $locale;
            }
        }

        return $localePages;
    }

    public function save()
    {
        if ($this->request->request->all() && $this->token->validate('community_store')) {
            $translations = $this->request->request->get('translation');

            foreach ($translations['options'] as $locale => $types) {
                foreach ($types as $type => $items) {
                    foreach ($items as $key => $texts) {
                        foreach ($texts as $original => $text) {
                            $qb = $this->entityManager->createQueryBuilder();
                            $t = $qb->select('t')
                                ->from('Concrete\Package\CommunityStore\Src\CommunityStore\Multilingual\Translation', 't')
                                ->where('t.entityType = :type')->setParameter('type', $key)
                                ->andWhere('t.locale = :locale')->setParameter('locale', $locale)
                                ->andWhere('t.pID is null');

                            if ($key == 'productAttributeName') {
                                $t->andWhere('t.entityID = :entityID')->setParameter('entityID', $original);
                            } else {
                                $t->andWhere('t.originalText = :originalText')->setParameter('originalText', $original);
                            }

                            $t = $t->setMaxResults(1)->getQuery()->getResult();

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

                                if ($key == 'productAttributeName') {
                                    $t->setEntityID($original);
                                } else {
                                    $t->setOriginalText($original);
                                }

                                $t->setLocale($locale);
                                $t->save();
                            }
                        }
                    }
                }
            }
        }

        $this->flash('success', t('Common Translations Updated'));

        return Redirect::to('/dashboard/store/multilingual/common');
    }
}
