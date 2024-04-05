<?php
declare(strict_types=1);

namespace Concrete\Package\CommunityStore\Src\CommunityStore\Utilities;

use Concrete\Core\Config\Repository\Repository;
use Concrete\Core\Entity\File\File;
use Concrete\Core\Entity\File\Version;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product;
use Generator;

defined('C5_EXECUTE') or die('Access Denied.');

class ProductImageInfoUpdater
{
    const OPERATION_KEEP = 'keep';
    const OPERATION_CLEANUP = 'cleanup';
    const OPERATION_PRODNAME_ONCE = 'prodname-once';
    const OPERATION_PRODNAME_ALWAYS = 'prodname-always';
    const FLAG_PRIMARYIMAGE = 0b1;
    const FLAG_SECONDARYIMAGES = 0b10;
    const FLAG_VARIATIONIMAGES = 0b100;

    /**
     * @protected
     */
    const KEY_PREFIX = 'community_store::products.images.autoUpdate';

    private Repository $config;

    public function __construct(Repository $config)
    {
        $this->config = $config;
    }

    public function getTitleOperationDictionary(): array
    {
        return [
            static::OPERATION_KEEP => t('use the file titles'),
            static::OPERATION_CLEANUP => t('use the file titles, removing any file extensions, replacing underscores with spaces'),
            static::OPERATION_PRODNAME_ONCE => t('match the product name if the file title has not been altered since upload'),
            static::OPERATION_PRODNAME_ALWAYS => t('always match the product name'),
        ];
    }

    /**
     * @return string the value of one of the OPERATION_... constants
     */
    public function getTitleOperation(): string
    {
        $result = (string) $this->config->get(static::KEY_PREFIX . '.title');

        return array_key_exists($result, $this->getTitleOperationDictionary()) ? $result : static::OPERATION_KEEP;
    }

    public function getFlagsDictionary(): array
    {
        return [
            static::FLAG_PRIMARYIMAGE => t('apply to primary product images'),
            static::FLAG_SECONDARYIMAGES => t('apply to secondary product images'),
            static::FLAG_VARIATIONIMAGES => t('apply to variation images'),
        ];
    }
    
    public function setTitleOperation(string $value): void
    {
        $this->config->set(static::KEY_PREFIX . '.title', $value);
        $this->config->save(static::KEY_PREFIX . '.title', $value);
    }

    /**
     * @param string $overrideTitleOperation if empty we'll use the configured operation
     *
     * @return int the number of files that have been updated
     */
    public function applyToProduct(Product $product, string $overrideTitleOperation = '', ?int $flags = null): int
    {
        $titleOperation = $overrideTitleOperation === '' ? $this->getTitleOperation() : $overrideTitleOperation;
        if ($flags === null) {
            $flags = static::FLAG_PRIMARYIMAGE | static::FLAG_SECONDARYIMAGES | static::FLAG_VARIATIONIMAGES;
        }

        if ($titleOperation === static::OPERATION_KEEP || $flags === 0) {
            return 0;
        }
        $count = 0;
        foreach ($this->listUniqueProductImages($product, $flags) as $prettyName => $file) {
            $updated = false;
            if ($this->patchTitle($file, $prettyName, $titleOperation)) {
                $updated = true;
            }
            if ($updated) {
                $count++;
            }
        }

        return $count;
    }

    private function listUniqueProductImages(Product $product, int $flags): Generator
    {
        $alreadyDoneImages = [];
        foreach ($this->listProductImages($product, $flags) as $prettyName => $file) {
            if (!in_array($file, $alreadyDoneImages, true)) {
                $alreadyDoneImages[] = $file;
                yield $prettyName => $file;
            }
        }
    }

    private function listProductImages(Product $product, int $flags): Generator
    {
        $productName = $product->getName();
        if (($flags & static::FLAG_PRIMARYIMAGE) === static::FLAG_PRIMARYIMAGE) {
            $image = $product->getImageObj();
            if ($image) {
                yield $productName => $image;
            }
        }
        if (($flags & static::FLAG_SECONDARYIMAGES) === static::FLAG_SECONDARYIMAGES) {
            $index = 1;
            foreach ($product->getimagesobjects() as $image) {
                if ($image) {
                    $index++;
                    yield "{$productName} ({$index})" => $image;
                }
            }
        }
        if (($flags & static::FLAG_VARIATIONIMAGES) === static::FLAG_VARIATIONIMAGES && $product->hasVariations()) {
            foreach ($product->getVariations() as $variation) {
                /** @var \Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductVariation\ProductVariation $variation */
                $image = $variation->getVariationImageObj();
                if (!$image) {
                    continue;
                }
                $optionDescriptions = [];
                foreach ($variation->getOptions() as $variationOptionItem) {
                    /** @var \Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductVariation\ProductVariationOptionItem $variationOptionItem */
                    $optionItem = $variationOptionItem->getOptionItem();
                    /** @var \Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductOption\ProductOptionItem $optionItem */
                    $option = $optionItem->getOption();
                    /** @var \Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductOption\ProductOption $option */
                    $optionDescriptions[] = $option->getName() . ': ' . $optionItem->getName();
                }
                $suffix = implode(', ', $optionDescriptions);
                yield "{$productName} ({$suffix})" => $image;
            }
        }
    }

    private function patchTitle(File $file, string $prettyName, string $operation): bool
    {
        $fileVersion = $file->getApprovedVersion();
        if (!$fileVersion) {
            return false;
        }
        $title = (string) $fileVersion->getTitle();
        switch ($operation) {
            case static::OPERATION_CLEANUP:
                $newTitle = $this->cleanupFilename($fileVersion);
                break;
            case static::OPERATION_PRODNAME_ONCE:
                if ($title !== (string) $fileVersion->getFileName()) {
                    return false;
                }
                $newTitle = $prettyName;
                break;
            case static::OPERATION_PRODNAME_ALWAYS:
                $newTitle = $prettyName;
                break;
            default:
                return false;
        }
        if ($newTitle === '' || $newTitle === $title) {
            return false;
        }
        $fileVersion->updateTitle($newTitle);

        return true;
    }

    private function cleanupFilename(Version $fileVersion): string
    {
        $title = (string) $fileVersion->getTitle();
        $suffix = '.' . $fileVersion->getExtension();
        if (substr($title, -strlen($suffix)) === $suffix) {
            $title = substr($title, 0, -strlen($suffix));
        }
        $title = strtr($title, [
            '_' => ' '
        ]);

        return trim($title);
    }
}
