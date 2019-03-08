<?php
namespace Concrete\Package\CommunityStore\Controller\SinglePage\Dashboard\Store\Multilingual;

use Concrete\Core\Http\Request;
use Concrete\Core\Routing\Redirect;
use Concrete\Core\Support\Facade\Session;
use Concrete\Core\Support\Facade\Database;
use Concrete\Core\Search\Pagination\PaginationFactory;
use Concrete\Core\Page\Controller\DashboardSitePageController;
use Concrete\Package\CommunityStore\Src\CommunityStore\Multilingual\Translation;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product as StoreProduct;
use Concrete\Package\CommunityStore\Src\CommunityStore\Group\GroupList as StoreGroupList;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductList as StoreProductList;

class Products extends DashboardSitePageController
{
    public function view($gID = null)
    {
        $productsList = new StoreProductList();
        $productsList->setItemsPerPage(20);
        $productsList->setGroupID($gID);
        $productsList->setActiveOnly(false);
        $productsList->setShowOutOfStock(true);

        if ($this->request->query->get('ccm_order_by')) {
            $productsList->setSortBy($this->request->query->get('ccm_order_by'));
            $productsList->setSortByDirection($this->request->query->get('ccm_order_by_direction'));
        } else {
            $productsList->setSortBy('date');
            $productsList->setSortByDirection('desc');
        }

        $keywords = trim($this->request->query->get('keywords'));

        if ($keywords) {
            $productsList->setSearch($keywords);
            Session::set('communitystore.dashboard.multilingual.keywords', $keywords);
        } else {
            Session::remove('communitystore.dashboard.multilingual.keywords');
        }


        $this->set('productList', $productsList);

        $factory = new PaginationFactory($this->app->make(Request::class));
        $paginator = $factory->createPaginationObject($productsList);

        $pagination = $paginator->renderDefaultView();
        $this->set('products', $paginator->getCurrentPageResults());
        $this->set('pagination', $pagination);
        $this->set('paginator', $paginator);
        $this->set('pageTitle', t('Product Translations'));
        $this->set('localePages', $this->getLocales());

        $this->set('defaultLocale', $this->getLocales()['default']);
        $this->set('locales', $this->getLocales()['additional']);

        $grouplist = StoreGroupList::getGroupList();
        $this->set("grouplist", $grouplist);
        $this->set('gID', $gID);

        if ($gID) {
            Session::set('communitystore.dashboard.multilingual.group', $gID);
        } else {
            Session::remove('communitystore.dashboard.multilingual.group');
        }
    }

    public function translate($pID)
    {
        $product = StoreProduct::getByID($pID);

        if (!$product) {
            return Redirect::to('/dashboard/store/multilingual/');
        }

        $this->set('product', $product);
        $this->set('locales', $this->getLocales()['additional']);
        $this->set('defaultLocale', $this->getLocales()['default']);
        $this->set('pageTitle', t('Translate Product'));

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
            $attributedata = $db->fetchAll('SELECT ' . implode(',', $attrHandles) . ' FROM CommunityStoreProductSearchIndexAttributes where pID = ?',[$pID]);

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

        $this->set('attrOptions', $attrOptions);

        $this->set('keywordsSearch', Session::get('communitystore.dashboard.multilingual.keywords'));
        $this->set('groupSearch', Session::get('communitystore.dashboard.multilingual.group'));
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

            foreach ($translations as $locale => $value) {
                foreach ($value as $type => $entries) {
                    foreach ($entries as $key => $items) {
                        $itemstosave = [];

                        if (is_array($items)) {
                            $itemstosave = $items;
                        } else {
                            $itemstosave[] = $items;
                        }

                        foreach ($itemstosave as $id => $text) {
                            $qb = $this->entityManager->createQueryBuilder();

                            $productID = $this->request->request->get('pID');

                            $query = $qb->select('t')
                                ->from('Concrete\Package\CommunityStore\Src\CommunityStore\Multilingual\Translation', 't')
                                ->where('t.entityType = :type')->setParameter('type', $key);

                            $query->andWhere('t.locale = :locale')->setParameter('locale', $locale);
                            $query->andWhere('t.pID = :pid')->setParameter('pid', $productID);

                            if ($key == 'optionName' || $key == 'optionValue' || $key == 'productAttributeName' ) {
                                $query->andWhere('t.entityID = :entityID')->setParameter('entityID', $id);
                            }

                            $query->setMaxResults(1);

                            $t = $query->getQuery()->getResult();

                            if (!empty($t)) {
                                $t = $t[0];

                                if (!$text) {
                                    $t->delete();
                                }
                            } else {
                                $t = new Translation();
                            }

                            if ($text) {
                                $t->setProductID($productID);
                                $t->setEntityType($key);

                                if ($key == 'optionName' || $key == 'optionValue' || $key == 'productAttributeName') {
                                    $t->setEntityID($id);
                                }

                                if ($key == 'productAttributeValue') {
                                    $t->setOriginalText($id);
                                }

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

            $this->flash('success', t('Product Translations Updated'));

            $keywords = Session::get('communitystore.dashboard.multilingual.keywords');
            $group = Session::get('communitystore.dashboard.multilingual.group');

            return Redirect::to('/dashboard/store/multilingual/products/' . ($group ? $group : '') . ($keywords ? '?keywords='.urlencode($keywords) : ''));
        }
    }
}
