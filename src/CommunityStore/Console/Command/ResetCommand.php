<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Exception;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Concrete\Package\CommunityStore\Src\CommunityStore\Order\OrderList as StoreOrderList;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductList as StoreProductList;

class ResetCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('cstore:reset')
            ->setDescription('Reset the community store package')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force the reset')
            ->setHelp(<<<EOT
Returns codes:
  0 operation completed successfully
  1 errors occurred
EOT
            )
        ;
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $rc = 0;
        try {
            if (!$input->getOption('force')) {
                if (!$input->isInteractive()) {
                    throw new Exception("You have to specify the --force option in order to run this command");
                }
                $confirmQuestion = new ConfirmationQuestion(
                    'Are you sure you want to reset community store? ' .
                    'This will remove all orders and products! (y/n)',
                    false
                );
                if (!$this->getHelper('question')->ask($input, $output, $confirmQuestion)) {
                    throw new Exception("Operation aborted.");
                }

                $orderList = new StoreOrderList();
                $orders = $orderList->getResults();
                $orderCount = count($orders);

                foreach($orders as $order) {
                    $order->delete();
                }
                $output->writeln('<info>' . t2('%d order deleted', '%d orders deleted', $orderCount) .'</info>');

                $productList = new StoreProductList();
                $productList->setActiveOnly(false);
                $productList->setShowOutOfStock(true);
                $products = $productList->getResults();
                $productCount = count($products);

                foreach($products as $product) {
                    $product->remove();

                }
                $output->writeln('<info>' . t2('%d product deleted', '%d products deleted', $productCount) .'</info>');


            }

        } catch (Exception $x) {
            $output->writeln('<error>'.$x->getMessage().'</error>');
            $rc = 1;
        }

        return $rc;
    }
}
