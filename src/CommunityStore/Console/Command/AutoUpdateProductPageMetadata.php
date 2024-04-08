<?php
declare(strict_types=1);

namespace Concrete\Package\CommunityStore\Src\CommunityStore\Console\Command;

use Concrete\Core\Error\UserMessageException;
use Concrete\Core\Site\Service as SiteService;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product;
use Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\ProductPageMetadataUpdater;
use Doctrine\ORM\EntityManagerInterface;
use Generator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

defined('C5_EXECUTE') or die('Access Denied.');

class AutoUpdateProductPageMetadata extends Command
{
    const NAME = 'cstore:product:page:metadata:update';

    /**
     * {@inheritdoc}
     *
     * @see \Symfony\Component\Console\Command\Command::configure()
     */
    protected function configure()
    {
        $app = app();
        $updater = $app->make(ProductPageMetadataUpdater::class);
        $flags = $updater->getDefaultFlags();
        $defaultFlags = [];
        foreach (array_keys($updater->getFlagsDictionary()) as $flag) {
            $flag = (int) $flag;
            if (($flags & $flag) === $flag) {
                $defaultFlags[] = $flag;
            }
        }
        $this
            ->setName(static::NAME)
            ->setDescription('Update the metadata of the product pages')
            ->addOption('flags', 'f', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Flags identifying what should be updated', $defaultFlags)
            ->addOption('all', 'a', InputOption::VALUE_NONE, 'Update all the products')
            ->addOption('product', 'p', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Limit the execution only for the specific product ID(s)')
            ->addOption('yes', 'y', InputOption::VALUE_NONE, 'Execute the operation without confirmation')
        ;
        $lines = [];
        $lines[] = sprintf('Allowed values of the %s option are:', '--flags');
        foreach ($updater->getFlagsDictionary() as $key => $name) {
            $lines[] = "- {$key}: {$name}";
        }
        $this->setHelp(implode("\n", $lines));
    }

    /**
     * {@inheritdoc}
     *
     * @see \Symfony\Component\Console\Command\Command::execute()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $app = app();
        $updater = $app->make(ProductPageMetadataUpdater::class);
        $em = $app->make(EntityManagerInterface::class);
        try {
            $flags = $this->resolveFlags($input, $updater);
            $products = $this->resolveProducts($input, $em);
            if (!$this->confirmOperation($input, $output, $updater, $flags, $products)) {
                return 0;
            }
            $totalUpdated = 0;
            foreach ($this->listProducts($products, $em) as $product) {
                $output->write(sprintf('Updating product with ID %s... ', $product->getID()));
                try {
                    $numUpdated = $updater->applyToProduct($product, $flags);
                } catch (UserMessageException $x) {
                    $output->writeln("<error>{$x->getMessage()}</error>");
                    continue;
                }
                $output->writeln(sprintf("<info>done (pages updated: %s).</info>", $numUpdated));
                $totalUpdated += $numUpdated;
            }
            $output->writeln('');
            $output->writeln(sprintf('Total number of pages updated: %s', $totalUpdated));
        } catch (UserMessageException $x) {
            $output->writeln("<error>{$x->getMessage()}</error>");
            return 1;
        }
        return 0;
    }

    /**
     * @throws \Concrete\Core\Error\UserMessageException
     */
    protected function resolveFlags(InputInterface $input, ProductPageMetadataUpdater $updater): int
    {
        $allPrettyUrlsConfigured = true;
        $siteService = app(SiteService::class);
        foreach ($siteService->getList() as $site) {
            if (!$site->getConfigRepository()->get('seo.canonical_url')) {
                $allPrettyUrlsConfigured = false;
            }
        }
        $flags = 0;
        $dictionary = $updater->getFlagsDictionary();
        foreach ($input->getOption('flags') as $flag) {
            if (is_numeric($flag)) {
                $flag = (int) $flag;
                if (array_key_exists($flag, $dictionary)) {
                    switch ($flag) {
                        case $updater::FLAG_UPDATE_OPENGRAPH:
                            if (!$allPrettyUrlsConfigured) {
                                throw new UserMessageException('In order to set the OpenGraph meta tags you need to configure the website canonical URL');
                            }
                    }
                    $flags = $flags | $flag;
                    continue;
                }
            }
            $lines = [];
            $lines[] = sprintf('%1$s is not a valid value for the %2$s option.', $flag, '--flag');
            $lines[] = '';
            $lines[] = sprintf('Allowed values of the %s option are:', '--flag');
            foreach ($dictionary as $key => $name) {
                $lines[] = "- {$key}: {$name}";
            }
            throw new UserMessageException(implode("\n", $lines));
        }
        if ($flags === 0) {
            throw new UserMessageException('Please specify at least one flag');
        }

        return $flags;
    }

    protected function resolveProducts(InputInterface $input, EntityManagerInterface $em): array
    {
        $productIDs = $input->getOption('product');
        if ($input->getOption('all') === ($productIDs !== [])) {
            throw new UserMessageException('Please specify the products pages to be updated (--product option), or --all to update all product pages.');
        }
        if ($input->getOption('all')) {
            return [];
        }
        $products = [];
        foreach ($productIDs as $productID) {
            if (is_numeric($productID)) {
                $productID = (int) $productID;
                if (isset($products[$productID])) {
                    continue;
                }
                $product = $em->find(Product::class, $productID);
                if ($product !== null) {
                    $products[$productID] = $product;
                    continue;
                }
            }
            throw new UserMessageException(sprintf('Unable to find a product with ID %s', $productID));
        }
        return array_values($products);
    }

    protected function confirmOperation(InputInterface $input, OutputInterface $output, ProductPageMetadataUpdater $updater, int $flags, array $products): bool
    {
        if (!$output->isQuiet()) {
            $table = new Table($output);
            foreach ($updater->getFlagsDictionary() as $flag => $name) {
                $flag = (int) $flag;
                $flagSelected = ($flags & $flag) === $flag;
                $table->addRow([$name, $flagSelected ? 'yes' : 'no']);
            }
            $table->addRow(['Products', $products === [] ? 'all' : sprintf('%s product(s) specified', count($products))]);
            $table->render();
        }
        if ($input->getOption('yes')) {
            return true;
        }
        if (!$input->isInteractive()) {
            throw new UserMessageException('In non-interactive mode you have to specify the --yes option');
        }
        $question = new ConfirmationQuestion(
            'Proceed with the automatic update? (y/n) ',
            false
        );
        return $this->getHelper('question')->ask($input, $output, $question);
    }

    protected function listProducts(array $products, EntityManagerInterface $em): Generator
    {
        if ($products !== []) {
            foreach ($products as $product) {
                yield $product;
            }
            return;
        }
        $repo = $em->getRepository(Product::class);
        foreach ($repo->findAll() as $product) {
            yield $product;
        }
    }
}
