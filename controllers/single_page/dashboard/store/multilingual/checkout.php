<?php
namespace Concrete\Package\CommunityStore\Controller\SinglePage\Dashboard\Store\Multilingual;

use Concrete\Core\Page\Controller\DashboardSitePageController;
use Concrete\Package\CommunityStore\Src\CommunityStore\Multilingual\Translation;
use Concrete\Package\CommunityStore\Src\CommunityStore\Group\GroupList as StoreGroupList;
use Concrete\Core\Search\Pagination\PaginationFactory;
use Concrete\Package\CommunityStore\Src\CommunityStore\Payment\Method as StorePaymentMethod;
use Concrete\Package\CommunityStore\Src\CommunityStore\Shipping\Method\ShippingMethod as StoreShippingMethod;


class Checkout extends DashboardSitePageController
{
    public function view()
    {
        $this->set("paymentMethods", StorePaymentMethod::getMethods());
        $this->set("shippingMethods", StoreShippingMethod::getMethods());

        $this->set('defaultLocale', $this->getLocales()['default']);
        $this->set('locales', $this->getLocales()['additional']);

    }


    private function getLocales() {
        $site = $this->getSite();
        $pages = \Concrete\Core\Multilingual\Page\Section\Section::getList($site);
        $localePages = array('additional'=>array());
        $defaultSourceLocale = $site->getConfigRepository()->get('multilingual.default_source_locale');

        foreach($pages as $p) {
            if ($defaultSourceLocale == $p->getLocale()) {
                $localePages['default'] = $p;
            } else {
                $localePages['additional'][] = $p;
            }
        }

        return $localePages;
    }

    public function save() {
        if ($this->post() && $this->token->validate('community_store')) {

            $translations = $this->post('translation');

            foreach($translations as $locale => $value) {

                foreach($value as $type => $entries) {
                    foreach ($entries as $key => $text) {


                        $qb = $this->entityManager->createQueryBuilder();

                        $query = $qb->select('t')
                            ->from('Concrete\Package\CommunityStore\Src\CommunityStore\Multilingual\Translation', 't')
                            ->where('t.entityType = :type')
                            ->setParameter('type', $key);
                        $query->andWhere('t.entityID = :id')->setParameter('id', $this->post('pID'));

                        $query->setMaxResults(1);

                        $t = $query->getQuery()->getResult();


                        if (!empty($t)) {
                            $t = $t[0];
                        } else {
                            $t = new Translation();
                        }

                        $t->setEntityID($this->post('pID'));
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

            $this->flash('success', t('Checkout Translations Updated'));
            return \Redirect::to('/dashboard/store/multilingual/checkout');

        }
    }
}
