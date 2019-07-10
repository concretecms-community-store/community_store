<?php

namespace Concrete\Package\CommunityStore\Controller\SinglePage\Dashboard\Store;

use Concrete\Core\Http\Request;
use Concrete\Core\Routing\Redirect;
use Concrete\Core\Http\ResponseFactory;
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
    }

    public function edit($id = 0)
    {
        $this->set('pageTitle', t('Edit Manufacturer'));
        $manufacturer = Manufacturer::getByID($id);
        $this->set('manufacturer', $manufacturer);
    }

    public function submit()
    {
        $data = $this->post();
        if (!$this->token->validate('submit')) {
            $this->error->add($this->token->getErrorMessage());
        }
        if (!$this->error->has() && $this->isPost()) {
            if ($this->post('mID')) {
                $manufacturer = Manufacturer::getByID($this->post('mID'));
            } else {
                $manufacturer = new Manufacturer();
            }
            $manufacturer->setName($this->post('name'));
            $manufacturer->setDescription($this->post('description'));
            $manufacturer->setCollectionID($this->post('pageCID'));
            $manufacturer->save();

            if ($this->post('mID')) {
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