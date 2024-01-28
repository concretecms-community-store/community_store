<?php

namespace Concrete\Package\CommunityStore\Src\CommunityStore\Utilities;

use Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductVariation\ProductVariation;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\OnFlushEventArgs;

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
            Events::onFlush,
        ];
    }

    public function onFlush(OnFlushEventArgs $e)
    {
        if ($this->service->isEnabled()) {
            $em = $e->getEntityManager();
            $uow = $em->getUnitOfWork();
            $products = [];
            $entityDeletions = $uow->getScheduledEntityDeletions();
            foreach ([
                $uow->getScheduledEntityInsertions(),
                $uow->getScheduledEntityUpdates(),
                $entityDeletions,
            ] as $entities) {
                foreach ($entities as $entity) {
                    if ($entity instanceof Product) {
                        if (!in_array($entity, $products, true)) {
                            $products[] = $entity;
                        }
                    } elseif ($entity instanceof ProductVariation) {
                        $product = $entity->getProduct();
                        if (!in_array($product, $products, true)) {
                            $products[] = $product;
                        }
                    }
                }
            }
            if ($products !== []) {
                $class = $em->getClassMetadata(Product::class);
                foreach ($products as $product) {
                    if (!in_array($product, $entityDeletions, true) && $product->hasVariations()) {
                        if ($this->service->update($product)) {
                            $uow->recomputeSingleEntityChangeSet($class, $product);
                        }
                    }
                }
            }
        }
    }
}
