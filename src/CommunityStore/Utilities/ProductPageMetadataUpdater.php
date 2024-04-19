<?php

declare(strict_types=1);

namespace Concrete\Package\CommunityStore\Src\CommunityStore\Utilities;

use Concrete\Core\Config\Repository\Repository;
use Concrete\Core\Entity\File\File;
use Concrete\Core\Error\UserMessageException;
use Concrete\Core\Page\Page;
use Concrete\Core\Multilingual\Page\Section\Section;
use Concrete\Core\Url\Resolver\Manager\ResolverManagerInterface;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product;
use Generator;

class ProductPageMetadataUpdater
{
    public const FLAG_UPDATE_DESCRIPTION = 0b1;
    public const FLAG_UPDATE_OPENGRAPH = 0b10;

    private ResolverManagerInterface $urlResolver;
    private Repository $config;
    private Multilingual $multilingual;

    public function __construct(
        ResolverManagerInterface $urlResolver,
        Repository $config,
        Multilingual $multilingual
    )
    {
        $this->urlResolver = $urlResolver;
        $this->config = $config;
        $this->multilingual = $multilingual;
    }

    public function isUpdateDescription(): bool
    {
        return (bool) $this->config->get('community_store::products.pages.metadata.updateDescription');
    }

    public function setIsUpdateDescription(bool $value): void
    {
        $this->config->set('community_store::products.pages.metadata.updateDescription', $value);
        $this->config->save('community_store::products.pages.metadata.updateDescription', $value);
    }

    public function isUpdateOpenGraph(): bool
    {
        return (bool) $this->config->get('community_store::products.pages.metadata.updateOpenGraph');
    }

    public function setIsUpdateOpenGraph(bool $value): void
    {
        $this->config->set('community_store::products.pages.metadata.updateOpenGraph', $value);
        $this->config->save('community_store::products.pages.metadata.updateOpenGraph', $value);
    }

    public function getFlagsDictionary(): array
    {
        return [
            static::FLAG_UPDATE_DESCRIPTION => t('Update page descriptions'),
            static::FLAG_UPDATE_OPENGRAPH => t('Update page OpenGraph meta tags'),
        ];
    }

    public function getDefaultFlags(): int
    {
        return 0
            | ($this->isUpdateDescription() ? static::FLAG_UPDATE_DESCRIPTION : 0)
            | ($this->isUpdateOpenGraph() ? static::FLAG_UPDATE_OPENGRAPH : 0)
        ;
    }

    /**
     * @param int|null $flags use NULL to use the configured flags
     *
     * @return int the number of pages that have been updated
     */
    public function applyToProduct(Product $product, ?int $flags = null): int
    {
        if ($flags === null) {
            $flags = $this->getDefaultFlags();
        }
        if ($flags === 0) {
            return 0;
        }
        $numPagesUpdated = 0;
        foreach ($this->getProductPages($product) as [$page, $section]) {
            $pageUpdated = false;
            if (($flags & static::FLAG_UPDATE_DESCRIPTION) === static::FLAG_UPDATE_DESCRIPTION) {
                if ($this->updatePageDescription($product, $page, $section)) {
                    $pageUpdated = true;
                }
            }
            if (($flags & static::FLAG_UPDATE_OPENGRAPH) === static::FLAG_UPDATE_OPENGRAPH) {
                if ($this->updatePageOpenGraph($product, $page, $section)) {
                    $pageUpdated = true;
                }
            }
            if ($pageUpdated) {
                $numPagesUpdated++;
            }
        }

        return $numPagesUpdated;
    }

    private function getProductPages(Product $product): Generator
    {
        $page = Page::getByID($product->getPageID());
        if (!$page || $page->isError() || $page->isInTrash()) {
            return;
        }
        yield [$page, null];
        $doneIDs = [(int) $page->getCollectionID()];
        foreach (Section::getList($page->getSite()) as $section) {
            $translatedPageID = $section->getTranslatedPageID($page);
            if (!$translatedPageID) {
                continue;
            }
            $translatedPageID = (int) $translatedPageID;
            if (in_array($translatedPageID, $doneIDs, true)) {
                continue;
            }
            $doneIDs[] = $translatedPageID;
            $translatedPage = Page::getByID($translatedPageID);
            if (!$translatedPage || $translatedPage->isError() || $translatedPage->isInTrash()) {
                continue;
            }
            yield [$translatedPage, $section];
        }
    }

    private function updatePageDescription(Product $product, Page $page, ?Section $section): bool
    {
        $newDescription = implode(' ', $this->getProductDescriptionLines($product, $section));
        if ($newDescription === '') {
            return false;
        }
        $oldDescription = trim((string) $page->getAttribute('meta_description'));
        if ($newDescription === $oldDescription) {
            return false;
        }
        $page->setAttribute('meta_description', $newDescription);

        return true;
    }

    private function updatePageOpenGraph(Product $product, Page $page, ?Section $section): bool
    {
        $oldHeaders = (string) $page->getAttribute('header_extra_content');
        $newHeaders = $oldHeaders;
        foreach ($this->generateOpenGraphTags($page, $product, $section) as [$rxSearch, $newTag]) {
            $newHeaders = preg_replace($rxSearch, '', $newHeaders);
            if ($newTag !== '') {
                $newHeaders = rtrim($newHeaders);
                if ($newHeaders === '') {
                    $newHeaders = $newTag;
                } else {
                    $newHeaders .= "\n" . $newTag;
                }
            }
        }
        $newHeaders = trim($newHeaders);
        if ($newHeaders !== '') {
            $newHeaders .= "\n";
        }
        if ($newHeaders === $oldHeaders) {
            return false;
        }
        $page->setAttribute('header_extra_content', $newHeaders);

        return true;
    }

    private function generateOpenGraphTags(Page $page, Product $product, ?Section $section): array
    {
        $pageUrl = (string) $this->urlResolver->resolve([$page]);
        if (!filter_var($pageUrl, FILTER_VALIDATE_URL)) {
        	$pageUrl = '';
        }
        $name = $this->getProductName($product, $section);
        $description = implode(' ', array_slice($this->getProductDescriptionLines($product, $section), 0, 2));
        $price = $this->getProductPrice($product);
        $imageUrl = $this->getProductImageUrl($product);
        if ($section === null) {
            $pageSection = Section::getBySectionOfSite($page);
            $localeID = $pageSection ? (string) $pageSection->getLocale() : '';
        } else {
            $localeID = (string) $section->getLocale();
        }
        $site = $page->getSite();
        $siteName = $site ? (string) $site->getSiteName() : '';

        $buildItem = static function(string $property, string $content): array {
            return [
                '/<meta\b[^>]*\bproperty\s*=\s*["\']?' . preg_quote($property, '/') . '\b((\s*>)|(["\'\s][^>]*>))(\s*($|[\r\n]+))?/i',
                $content === '' ? '' : "<meta property=\"{$property}\" content=\"{$content}\" />",
            ];
        };

        return [
            $buildItem('og:type', 'product'),
            $buildItem('og:url', h($pageUrl)),
            $buildItem('og:title', h($name)),
            $buildItem('og:description', h($description)),
            $buildItem('og:locale', h($localeID)),
            $buildItem('og:site_name', h($siteName)),
            $buildItem('og:product:amount', h($price)),
            $buildItem('og:product:currency', $price === '' ? '' : h($this->config->get('community_store.currency'))),
            $buildItem('og:image', h($imageUrl)),
            $buildItem('og:image:alt', $imageUrl === '' ? '' : h($name)),
        ];
    }

    private function getProductName(Product $product, ?Section $section): string
    {
        if ($section === null) {
            return (string) $product->getName();
        }

        return (string) $this->multilingual->t(null, 'productName', $product->getID(), false, $section->getLocale());
    }

    /**
     * @return string[]
     */
    private function getProductDescriptionLines(Product $product, ?Section $section): array
    {
        if ($section === null) {
            $text = (string) $product->getDescription();
        } else {
            $text = (string) $this->multilingual->t(null, 'productDescription', $product->getID(), false, $section->getLocale());
        }
        $text = preg_replace('/\s+/', ' ', $text);
        $text = preg_replace('_(<(br|/p|/div|/td)\b.*?>)_i', "\\1\n", $text);
        $lines = [];
        foreach (preg_split('/\s*\n\s*/', $text, -1, PREG_SPLIT_NO_EMPTY) as $line) {
            $line = trim(html_entity_decode(strip_tags($line)));
            if ($line !== '') {
                $lines[] = preg_replace('/  +/', ' ', $line);
            }
        }

        return $lines;
    }

    private function getProductPrice(Product $product): string
    {
        return (string) $product->getActivePrice();
    }

    private function getProductImageUrl(Product $product): string
    {
        if (($url = $this->resolveProductImageUrl($product->getImageObj())) !== '') {
            return $url;
        }
        foreach ($product->getimagesobjects() as $file) {
            if (($url = $this->resolveProductImageUrl($file)) !== '') {
                return $url;
            }
        }
        foreach ($product->getVariations() as $variation) {
            if (($url = $this->resolveProductImageUrl($variation->getVariationImageObj())) !== '') {
                return $url;
            }
        }

        return '';
    }

    private function resolveProductImageUrl(?File $file): string
    {
        $version = $file === null ? null : $file->getApprovedVersion();
        if ($version === null) {
            return '';
        }

        return $version->getURL() ?? '';
    }
}
