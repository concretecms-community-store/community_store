<?php

namespace Concrete\Package\CommunityStore\Src\CommunityStore\Utilities;

use Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductVariation\ProductVariation;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Doctrine\ORM\Events;

class DoctrineORMEventsSubscriber implements EventSubscriber
{
    /**
     * 
     * @var \Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\AutoUpdaterQuantitiesFromVariations
     */
    private $service;

    public function __construct(AutoUpdaterQuantitiesFromVariations $service)
    {
        $this->service = $service;
    }

    /**
     * {@inheritdoc}
     *
     * @see \Doctrine\Common\EventSubscriber::getSubscribedEvents()
     */
    public function getSubscribedEvents()
    {
        return [
            Events::preFlush,
        ];
    }

    public function preFlush(PreFlushEventArgs $e)
    {
        if ($this->service->isEnabled()) {
            $em = $e->getEntityManager();
            $uow = $em->getUnitOfWork();
            $map = $uow->getIdentityMap();
            // Let's retrieve all the Product instances that have already been loaded (and possibily changed)
            $products = isset($map[Product::class]) ? $map[Product::class] : [];
            // Let's retrieve all the ProductVariation instances that have already been loaded (and possibily changed)
            if (isset($map[ProductVariation::class])) {
                foreach ($map[ProductVariation::class] as $productVariation) {
                    $product = $productVariation->getProduct();
                    if (!in_array($product, $products, true)) {
                        $products[] = $product;
                    }
                }
            }
            foreach ($products as $product) {
                if ($product->hasVariations()) {
                    $this->service->update($product);
                }
            }
        }
    }
}
