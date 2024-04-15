<?php

namespace Concrete\Package\CommunityStore\Src\CommunityStore\Utilities;

use Concrete\Core\Config\Repository\Repository;
use Concrete\Core\Support\Facade\Log;
use Concrete\Package\CommunityStore\Src\CommunityStore\Order\OrderList;
use Doctrine\ORM\EntityManagerInterface;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product;
use Concrete\Core\Error\UserMessageException;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductVariation\ProductVariation;

/**
 * Utilities for handling quantities of products with variations.
 */
final class RemoveIncompleteOrders
{

    /**
     *
     * @return int the number of orders that have been removed
     */
    public function removeIncompleteOrders($days = 7)
    {
        /// do delete here
       $orderList = new OrderList();
       $orderList->setPaymentStatus('incomplete');
       $orderList->setIncludeExternalPaymentRequested(true);

       $date = new \DateTime();
       $date->sub(new \DateInterval('P' . $days . 'D'));

       $orderList->getQueryObject()->andWhere('DATE(externalPaymentRequested) <= :incompletethreshhold')->setParameter('incompletethreshhold', $date->format("Y-m-d"));
       $orderList->setLimit(100);

       $orders = $orderList->getResults();

       $deleteCount = 0;

       foreach($orders as $order) {
           $order->remove();
           $deleteCount++;
       }

       return $deleteCount;
    }

}
