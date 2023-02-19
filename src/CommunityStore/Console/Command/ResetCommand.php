<?php

namespace Concrete\Package\CommunityStore\Src\CommunityStore\Console\Command;

use Concrete\Package\CommunityStore\Src\CommunityStore\Discount\DiscountRuleList;
use Concrete\Package\CommunityStore\Src\CommunityStore\Order\OrderList;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductList;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class ResetCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('cstore:reset')
            ->setDescription('Reset the Community Store package')
            ->addArgument('type', InputArgument::OPTIONAL, 'Type of reset')
            ->addArgument('param1', InputArgument::OPTIONAL, 'Argument')
            ->setHelp(
                <<<'EOT'
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
        $param1 = $input->getArgument('param1');

        if (!$operationType) {
            throw new Exception('You have to specify the type of reset to run this command');
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

        if ($operationType == 'order-number') {
            $typeMessage = t('Are you sure you want to manually set the next order number in Community Store? (y/n)');
        }

        if ($operationType == 'discounts') {
            $typeMessage = t('Are you sure you want to remove all discounts from Community Store? (y/n)');
        }

        $confirmQuestion = new ConfirmationQuestion(
            $typeMessage,
            false
        );

        if (!$this->getHelper('question')->ask($input, $output, $confirmQuestion)) {
            throw new Exception(t('Operation aborted.'));
        }

        if ($operationType == 'all' || $operationType == 'orders') {
            $orderList = new OrderList();
            $orderList->setIncludeExternalPaymentRequested(true);
            $orders = $orderList->getResults();
            $orderCount = count($orders);

            foreach ($orders as $order) {
                $order->remove();
            }
            $output->writeln('<info>' . t2('%d order deleted', '%d orders deleted', $orderCount) . '</info>');
        }

        if ($operationType == 'all' || $operationType == 'products') {
            $productList = new ProductList();
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
            $discountList = new DiscountRuleList();
            $discounts = $discountList->getResults();

            $discountCount = 0;

            foreach ($discounts as $discount) {
                $discount->delete();
                $discountCount++;
            }

            $output->writeln('<info>' . t2('%d discount deleted', '%d discounts deleted', $discountCount) . '</info>');
        }

        if ($operationType == 'all' || $operationType == 'order-number') {
            $nextNumber = 1;
            $max = 0;

            if ($param1) {
                $nextNumber = (int) $param1;
            }

            $db = app()->make('database')->connection();
            $sql = 'SELECT MAX(oID) as max_id from CommunityStoreOrders';
            $result = $db->query($sql, [$nextNumber]);

            foreach($result as $r) {
                $max = $r['max_id'];
            }

            if ($nextNumber > $max) {
                $sql = 'ALTER TABLE CommunityStoreOrders AUTO_INCREMENT = ' . (int) $nextNumber;
                $db->query($sql);
                $output->writeln('<info>' . t('Next order number set to %d', $nextNumber) . '</info>');
            } else {
                $output->writeln('<error>' . t('Existing order with order number %d', $max) . '</error>');
            }
        }

        return $rc;
    }
}
