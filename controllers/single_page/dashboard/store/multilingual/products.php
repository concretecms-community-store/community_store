<?php
namespace Concrete\Package\CommunityStore\Controller\SinglePage\Dashboard\Store\Multilingual;

use Concrete\Core\Page\Controller\DashboardSitePageController;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product as StoreProduct;
use Concrete\Package\CommunityStore\Src\CommunityStore\Multilingual\Translation;

class Products extends DashboardSitePageController
{
    public function view()
    {
        $this->set('pageTitle', t('Product Translations'));
    }

    public function product($pID)
    {
        $product = StoreProduct::getByID($pID);

        if (!$product) {
            return \Redirect::to('/dashboard/store/multilingual/');
        }

        $this->set('product', $product);
        $site = $this->getSite();
        $pages = \Concrete\Core\Multilingual\Page\Section\Section::getList($site);

        $localePages = array();

        $defaultSourceLocale = $site->getConfigRepository()->get('multilingual.default_source_locale');

        foreach($pages as $p) {
            if ($defaultSourceLocale != $p->getLocale()) {
                $localePages[] = $p;
            }
        }

        $this->set('localePages', $localePages);

        $this->set('pageTitle', t('Translate Product'));
    }

    public function save() {
        if ($this->post() && $this->token->validate('community_store')) {

            $translations = $this->post('translation');


            foreach($translations as $locale => $value) {

                foreach($value as $type => $entries) {
                    foreach ($entries as $key => $text) {
                        $t = new Translation();
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

        }
    }
}
