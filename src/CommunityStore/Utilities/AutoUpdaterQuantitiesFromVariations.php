<?php

namespace Concrete\Package\CommunityStore\Src\CommunityStore\Utilities;

use Concrete\Core\Config\Repository\Repository;
use Doctrine\ORM\EntityManagerInterface;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product;
use Concrete\Core\Error\UserMessageException;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductVariation\ProductVariation;

/**
 * Utilities for handling quantities of products with variations.
 */
final class AutoUpdaterQuantitiesFromVariations
{
    /**
     * @var string
     */
    const CONFIGURATION_KEY = 'community_store::products.autoQuantityIfVariations';

    /**
     * @var \Concrete\Core\Config\Repository\Repository
     */
    private $config;

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $em;

    public function __construct(Repository $config, EntityManagerInterface $em)
    {
        $this->config = $config;
        $this->em = $em;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return (bool) $this->config->get(self::CONFIGURATION_KEY);
    }

    /**
     * @param bool $value
     *
     * @return $this
     */
    public function setEnabled($value)
    {
        $value = (bool) $value;
        $this->config->set(self::CONFIGURATION_KEY, $value);
        $this->config->save(self::CONFIGURATION_KEY, $value);

        return $this;
    }

    /**
     * Update all the pQty/pQtyUnlim fields of products with variations.
     * Please remark that this is performed at the database level, so you shouldn't use Doctrine Entities after executing this method.
     *
     * @return int the number of products that have been updated
     */
    public function updateAll()
    {
        /*
         * It seems that Doctrine ORM doesn't support UPDATE queries with JOINs, so we have to use SQL directly.
         */

        return (int) $this->em->getConnection()->executeUpdate(<<<'EOT'
UPDATE
    CommunityStoreProducts AS p
LEFT JOIN (
    SELECT
        pv.pID,
        SUM(pv.pvQty) AS newQty,
        MAX(COALESCE(pv.pvQtyUnlim, 0)) AS newQtyUnlim
    FROM
        CommunityStoreProductVariations AS pv
    WHERE
        pv.pvDisabled IS NULL OR pv.pvDisabled = 0
    GROUP BY
        pv.pID
) AS t ON p.pID = t.pID
SET
    p.pQty = COALESCE(t.newQty, 0),
    p.pQtyUnlim = COALESCE(t.newQtyUnlim, 0)
WHERE
    p.pVariations = 1
EOT
        );
    }

    /**
     * Update the pQty/pQtyUnlim field of a product with variations.
     *
     * @throws \Concrete\Core\Error\UserMessageException if the product doesn't have variations.
     *
     * @return bool returns true if the product needed to be updated, false otherwise
     */
    public function update(Product $product)
    {
        if (!$product->hasVariations()) {
            throw new UserMessageException(t("The product doesn't have variations."));
        }
        $pQty = 0;
        $pQtyUnlim = false;
        foreach ($product->getVariations() as $productVariation) {
            if ($this->shouldConsiderVariation($productVariation)) {
                $pQty += (float) $productVariation->getStockLevel();
                $pQtyUnlim = $pQtyUnlim || $productVariation->getVariationQtyUnlim();
            }
        }
        $result = false;
        if ($pQty !== (float) $product->getStockLevel()) {
            $product->setQty($pQty);
            $result = true;
        }
        if ($pQtyUnlim !== (bool) $product->isUnlimited(true)) {
            $product->setIsUnlimited($pQtyUnlim);
            $result = true;
        }

        return $result;
    }

    private function shouldConsiderVariation(ProductVariation $productVariation)
    {
        if ($productVariation->getVariationDisabled()) {
            return false;
        }

        return true;
    }
}
