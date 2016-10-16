<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Exception;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Concrete\Package\CommunityStore\Src\CommunityStore\Order\OrderList as StoreOrderList;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductList as StoreProductList;
use Concrete\Package\CommunityStore\Src\CommunityStore\Discount\DiscountRuleList as StoreDiscountRuleList;

class ResetCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('cstore:reset')
            ->setDescription('Reset the Community Store package')
            ->addArgument('type', InputArgument::OPTIONAL,  'Type of reset')
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

        $operationType = $input->getArgument('type');

        if (!$operationType) {
            throw new Exception("You have to specify the type of reset to run this command");
        }

        if ($operationType == 'all') {
            $typeMessage = t('Are you sure you want to remove all products, orders and discounts from Community Store? (y/n)');
        }

        if ($operationType == 'products') {
            $typeMessage = t('Are you sure you want to remove all products from Community Store? (y/n)');
        }

        if ($operationType == 'orders') {
            $typeMessage = t('Are you sure you want to remove all orders from Community Store? (y/n)');
        }

        if ($operationType == 'discounts') {
            $typeMessage = t('Are you sure you want to remove all discounts from Community Store? (y/n)');
        }

        $confirmQuestion = new ConfirmationQuestion(
            $typeMessage,
            false
        );

        if (!$this->getHelper('question')->ask($input, $output, $confirmQuestion)) {
            throw new Exception(t("Operation aborted."));
        }

        if ($operationType == 'all' || $operationType == 'orders') {
            $orderList = new StoreOrderList();
            $orders = $orderList->getResults();
            $orderCount = count($orders);

            foreach ($orders as $order) {
                $order->delete();
            }
            $output->writeln('<info>' . t2('%d order deleted', '%d orders deleted', $orderCount) . '</info>');
        }

        if ($operationType == 'all' || $operationType == 'products') {
            $productList = new StoreProductList();
            $productList->setActiveOnly(false);
            $productList->setShowOutOfStock(true);
            $products = $productList->getResults();
            $productCount = count($products);

            foreach ($products as $product) {
                $product->remove();

            }
            $output->writeln('<info>' . t2('%d product deleted', '%d products deleted', $productCount) . '</info>');
        }

        if ($operationType == 'all' || $operationType == 'discounts') {
            $discountList = new StoreDiscountRuleList();
            $discounts = $discountList->getResults();

            $discountCount = 0;

            foreach ($discounts as $discount) {
                $discount->delete();
                $discountCount++;
            }

            $output->writeln('<info>' . t2('%d discount deleted', '%d discounts deleted', $discountCount) . '</info>');

        }


        return $rc;
    }
}
