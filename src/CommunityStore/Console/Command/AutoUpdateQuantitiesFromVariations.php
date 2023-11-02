<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Console\Command;

use Concrete\Core\Support\Facade\Application;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product;
use Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\AutoUpdaterQuantitiesFromVariations;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Concrete\Core\Error\UserMessageException;


class AutoUpdateQuantitiesFromVariations extends Command
{
    protected function configure()
    {
        $this
            ->setName('cstore:product:quantity:update')
            ->setDescription('Update the quantities for products with variations')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force the execution even if automatic product quantities is disabled')
            ->addOption('all', 'a', InputOption::VALUE_NONE, 'Update all the products with variations')
            ->addOption('product', 'p', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Limit the execution only for the specific product ID(s)')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $app = Application::getFacadeApplication();
        $service = $app->make(AutoUpdaterQuantitiesFromVariations::class);
        if (!$service->isEnabled() && !$input->getOption('force')) {
            $output->writeln('<error>Automatic quantities for products with variations is disabled (use the --force option to proceed anyway).</error>');

            return 1;
        }
        $productIDs = $input->getOption('product');
        if ($input->getOption('all') === ($productIDs !== [])) {
            $output->writeln('<error>Please specify the products to be updated (--product option), or --all to update all products.</error>');

            return 1;
        }
        if ($input->getOption('all')) {
            $count = $service->updateAll();
            $output->writeln("Number of updated products: <info>{$count}</info>");

            return 0;
        }
        $em = $app->make(EntityManagerInterface::class);
        $rc = 0;
        $saveChanges = false;
        foreach ($productIDs as $productID) {
            $output->write("Updating product with ID {$productID}: ");
            $product = $em->find(Product::class, $productID);
            if ($product === null) {
                $output->writeln('<error>not found.</error>');
                $rc = 1;
            } else {
                try {
                    if ($service->update($product)) {
                        $output->writeln('<info>updated.</info>');
                        $saveChanges = true;
                    } else {
                        $output->writeln('<info>already up-to-date.</info>');
                    }
                } catch (UserMessageException $x) {
                    $output->writeln("<error>{$x->getMessage()}</error>");
                    $rc = 1;
                }
            }
        }
        if ($saveChanges) {
            $output->write('Saving changes... ');
            $em->flush();
            $output->writeln('<info>done.</info>');
        }

        return $rc;
    }
}
