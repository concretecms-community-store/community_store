<?php

namespace Concrete\Package\CommunityStore\Controller\SinglePage\Dashboard\Store;

use Concrete\Core\Http\Request;
use Concrete\Core\Routing\Redirect;
use Concrete\Core\Http\ResponseFactory;
use Concrete\Core\Navigation\Item\Item;
use Concrete\Core\Support\Facade\Url;
use \Concrete\Core\Page\Controller\PageController;
use Concrete\Core\Support\Facade\Config;
use Concrete\Package\CommunityStore\Src\CommunityStore\Manufacturer\ManufacturerList;
use Concrete\Package\CommunityStore\Src\CommunityStore\Manufacturer\Manufacturer;
use \Concrete\Core\Attribute\Key\CollectionKey as CollectionAttributeKey;
use Concrete\Core\Search\Pagination\PaginationFactory;
use Concrete\Core\Page\Controller\DashboardPageController;
use Concrete\Core\Page\page;
use Concrete\Core;

class Manufacturers extends DashboardPageController
{

    public function view()
    {
        $manufacturerList = new ManufacturerList();

        $manufacturerList->setItemsPerPage(20);

        $factory = new PaginationFactory($this->app->make(Request::class));

        $paginator = $factory->createPaginationObject($manufacturerList);
        $pagination = $paginator->renderDefaultView();
        $this->set('manufacturers', $paginator->getCurrentPageResults());
        $this->set('pagination', $pagination);
        $this->set('paginator', $paginator);


        $this->set('pageTitle', t('Manufacturers'));
    }

    public function add()
    {
        $this->set('pageTitle', t('Add Manufacturer'));
        $manufacturer = new Manufacturer();
        $this->set('manufacturer', $manufacturer);
        if (method_exists($this, 'createBreadcrumb')) {
            $this->setBreadcrumb($breacrumb = $this->getBreadcrumb() ?: $this->createBreadcrumb());
            $breacrumb->add(new Item('#', t('Add Manufacturer')));
        }
    }

    public function edit($id = 0)
    {
        $manufacturer = Manufacturer::getByID($id);
        if ($manufacturer === null) {
            return $this->buildRedirect('/dashboard/store/manufacturers');
        }
        $this->set('pageTitle', t('Edit Manufacturer'));
        $this->set('manufacturer', $manufacturer);
        if (method_exists($this, 'createBreadcrumb')) {
            $this->setBreadcrumb($breacrumb = $this->getBreadcrumb() ?: $this->createBreadcrumb());
            $breacrumb->add(new Item('#', $manufacturer->getName()));
        }
    }

    public function submit()
    {
        $request = $this->request;

        if (!$this->token->validate('submit')) {
            $this->error->add($this->token->getErrorMessage());
        }
        if (!$this->error->has() && $request->isPost()) {
            if ($request->request->get('mID')) {
                $manufacturer = Manufacturer::getByID($request->request->get('mID'));
            } else {
                $manufacturer = new Manufacturer();
            }
            $manufacturer->setName($request->request->get('name'));
            $manufacturer->setDescription($request->request->get('description'));
            $manufacturer->setCollectionID($request->request->get('pageCID'));
            $manufacturer->save();

            if ($request->request->get('mID')) {
                $this->flash('success', t('Manufacturer Updated'));
            } else {
                $this->flash('success', t('Manufacturer Added'));
            }

            $factory = $this->app->make(ResponseFactory::class);
            return $factory->redirect(Url::to('/dashboard/store/manufacturers'));
        }
    }

    public function delete($mid)
    {

        $manufacturer = Manufacturer::getByID($mid);


        if ($manufacturer) {

            foreach($manufacturer->getProducts() as $product) {
                $product->setManufacturer(null);
                $product->save();
            }

            $manufacturer->delete();

            $this->flash('success', t('Manufacturer Deleted'));
            $factory = $this->app->make(ResponseFactory::class);
        }
        return $factory->redirect(Url::to('/dashboard/store/manufacturers'));

    }

}
