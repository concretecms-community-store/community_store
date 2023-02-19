<?php

namespace Concrete\Package\CommunityStore\Controller\SinglePage\Dashboard\Store;

use Concrete\Core\Http\Request;
use Concrete\Core\Http\ResponseFactory;
use Concrete\Core\Page\Controller\DashboardPageController;
use Concrete\Core\Search\Pagination\PaginationFactory;
use Concrete\Core\Support\Facade\Url;
use Concrete\Package\CommunityStore\Src\CommunityStore\Manufacturer\Manufacturer;
use Concrete\Package\CommunityStore\Src\CommunityStore\Manufacturer\ManufacturerList;

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
    }

    public function edit($id = 0)
    {
        $this->set('pageTitle', t('Edit Manufacturer'));
        $manufacturer = Manufacturer::getByID($id);
        $this->set('manufacturer', $manufacturer);
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
