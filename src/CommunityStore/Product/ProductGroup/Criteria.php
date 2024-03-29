<?php

namespace Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductGroup;

use Concrete\Package\CommunityStore\Src\CommunityStore\Group\Group;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product;
use Doctrine\ORM\EntityManagerInterface;

class Criteria
{
    /**
     * Not applicable if any product is in any of the specified groups.
     *
     * @var int
     */
    const EXCLUDE_ANY_PRODUCT_ANY_GROUP = 1;

    /**
     * Not applicable if all the products are in any of the specified groups.
     *
     * @var int
     */
    const EXCLUDE_ALL_PRODUCTS_ANY_GROUP = 2;

    /**
     * Applicale only if any product is in any of the specified groups.
     *
     * @var int
     */
    const ONLYIF_ANY_PRODUCT_ANY_GROUP = 3;

    /**
     * Applicale only if all the products are in any of the specified groups.
     *
     * @var int
     */
    const ONLYIF_ALL_PRODUCTS_ANY_GROUP = 4;

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @param int $criteria
     *
     * @param int[]|\Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product $products
     * @param int[]|\Concrete\Package\CommunityStore\Src\CommunityStore\Group\Group[] $groups
     *
     * @return bool
     */
    public function check($criteria, array $products, array $groups)
    {
        $criteria = (int) $criteria;
        if ($criteria === 0) {
            return true;
        }
        $products = $this->resolveProducts($products);
        $groupIDs = $this->resolveGroupIDs($groups);
        $numProductsInGroups = 0;
        $numProductsNotInGroups = 0;
        foreach ($products as $product) {
            foreach ($product->getGroups() as $productGroup) {
                /** @var \Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductGroup $productGroup */
                if (in_array((int) $productGroup->getGroup()->getID(), $groupIDs, true)) {
                    $numProductsInGroups++;
                    continue 2;
                }
            }
            $numProductsNotInGroups++;
        }
        switch ($criteria) {
            case static::EXCLUDE_ANY_PRODUCT_ANY_GROUP:
                return $numProductsInGroups === 0;
            case static::EXCLUDE_ALL_PRODUCTS_ANY_GROUP:
                return $numProductsInGroups !== count($products);
            case static::ONLYIF_ANY_PRODUCT_ANY_GROUP:
                return $numProductsInGroups !== 0;
            case static::ONLYIF_ALL_PRODUCTS_ANY_GROUP:
                return $products !== [] && $numProductsInGroups === count($products);
            default:
                return true;
        }
    }

    /**
     * @param int[]|\Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product $products
     *
     * @return \Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product[]
     */
    protected function resolveProducts(array $products)
    {
        $result = [];
        foreach ($products as $item) {
            $product = null;
            if ($item instanceof Product) {
                $product = $item;
            } elseif (is_numeric($item)) {
                $product = $this->em->find(Product::class, (int) $item);
            }
            if ($product !== null) {
                $result[$product->getID()] = $product;
            }
        }

        return array_values($result);
    }

    /**
     * @param int[]|\Concrete\Package\CommunityStore\Src\CommunityStore\Group\Group[] $groups
     *
     * @return int[]
     */
    protected function resolveGroupIDs(array $groups)
    {
        $result = [];
        foreach ($groups as $item) {
            $id = null;
            if ($item instanceof Group) {
                $id = (int) $item->getID();
            } elseif (is_numeric($item)) {
                $group = $this->em->find(Group::class, (int) $item);
                if ($group !== null) {
                    $id = (int) $group->getID();
                }
            }
            if ($id !== null && !in_array($id, $result, true)) {
                $result[] = $id;
            }
        }

        return $result;
    }
}
