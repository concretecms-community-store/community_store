<?php

namespace Concrete\Package\CommunityStore\Src\CommunityStore\Order;

use Pagerfanta\Adapter\DoctrineDbalAdapter;
use Concrete\Core\Support\Facade\Application;
use Concrete\Core\Search\Pagination\Pagination;
use Concrete\Core\Search\ItemList\Database\AttributedItemList;
use Concrete\Core\Search\Pagination\PaginationProviderInterface;
use Concrete\Package\CommunityStore\Src\CommunityStore\Order\Order as StoreOrder;
use Concrete\Package\CommunityStore\Src\CommunityStore\Order\OrderItem as StoreOrderItem;

class OrderList extends AttributedItemList implements PaginationProviderInterface
{
    protected function getAttributeKeyClassName()
    {
        return StoreOrderKey::class;
    }

    public function createQuery()
    {
        $this->query
            ->select('o.oID')
            ->from('CommunityStoreOrders', 'o');
    }

    public function finalizeQuery(\Doctrine\DBAL\Query\QueryBuilder $query)
    {
        $paramcount = 0;

        if (isset($this->search)) {
            $this->query->where('o.oID = ?')->setParameter($paramcount++, $this->search);
            $this->query->orWhere('transactionReference = ?')->setParameter($paramcount++, $this->search);

            $app = Application::getFacadeApplication();
            $db = $app->make('database')->connection();
            $matchingOrders = $db->query("SELECT DISTINCT(oID) FROM CommunityStoreOrderAttributeValues csoav INNER JOIN atDefault av ON csoav.avID = av.avID WHERE av.value LIKE ?", ['%' . $this->search . '%']);

            $orderIDs = [];
            while ($value = $matchingOrders->fetchRow()) {
                $orderIDs[] = $value['oID'];
            }

            if (!empty($orderIDs)) {
                $this->query->orWhere('o.oID in (' . implode(',', $orderIDs) . ')');
            }
        }

        if (isset($this->status)) {
            $app = Application::getFacadeApplication();
            $db = $app->make('database')->connection();
            $matchingOrders = $db->query("SELECT oID FROM CommunityStoreOrderStatusHistories t1
                                            WHERE oshStatus = ? and
                                                t1.oshDate = (SELECT MAX(t2.oshDate)
                                                             FROM CommunityStoreOrderStatusHistories t2
                                                             WHERE t2.oID = t1.oID)", [$this->status]);
            $orderIDs = [];

            while ($value = $matchingOrders->fetchRow()) {
                $orderIDs[] = $value['oID'];
            }

            if (!empty($orderIDs)) {
                if ($paramcount > 0) {
                    $this->query->andWhere('o.oID in (' . implode(',', $orderIDs) . ')');
                } else {
                    $this->query->andWhere('o.oID in (' . implode(',', $orderIDs) . ')');
                }
            } else {
                $this->query->where('1 = 0');
            }
        }


        if (isset($this->paymentStatus)) {
            $app = Application::getFacadeApplication();
            $db = $app->make('database')->connection();

            if ($this->paymentStatus === 'paid') {
                $this->query->andWhere('oPaid is not null and oRefunded is null');
            }

            if ($this->paymentStatus === 'unpaid') {
                $this->query->andWhere('oPaid is null and oRefunded is null and externalPaymentRequested is null');
            }

            if ($this->paymentStatus === 'cancelled') {
                $this->query->andWhere('oCancelled is not null');
            }

            if ($this->paymentStatus === 'refunded') {
                $this->query->andWhere('oRefunded is not null');
            }

            if ($this->paymentStatus === 'incomplete') {
                $this->query->andWhere('externalPaymentRequested is not null and oPaid is null');
            }
        }

        if (isset($this->fromDate)) {
            $this->query->andWhere('DATE(oDate) >= DATE(?)')->setParameter($paramcount++, $this->fromDate);
        }
        if (isset($this->toDate)) {
            $this->query->andWhere('DATE(oDate) <= DATE(?)')->setParameter($paramcount++, $this->toDate);
        }
        if (isset($this->paid)) {
            $this->query->andWhere('o.oPaid is not null');
            $this->query->andWhere('o.oRefunded is null');
        }

        if (isset($this->cancelled)) {
            if ($this->cancelled) {
                $this->query->andWhere('o.oCancelled is not null');
            } else {
                $this->query->andWhere('o.oCancelled is null');
            }
        }

        if (isset($this->shippable)) {
            if ($this->shippable) {
                $this->query->andWhere('o.smName <> ""');
            } else {
                $this->query->andWhere('o.smName = ""');
            }
        }

        if (isset($this->refunded)) {
            if ($this->refunded) {
                $this->query->andWhere('o.oRefunded is not null');
            } else {
                $this->query->andWhere('o.oRefunded is null');
            }
        }

        if ($this->limit > 0) {
            $this->query->setMaxResults($this->limit);
        }

        if (isset($this->externalPaymentRequested) && $this->externalPaymentRequested) {
            
        } else {
            $this->query->andWhere('o.externalPaymentRequested is null');
        }

        if (isset($this->cID)) {
            $this->query->andWhere('o.cID = ?')->setParameter($paramcount++, $this->cID);
        }

        if (isset($this->paymentMethod)) {
            $this->query->andWhere('o.pmID = ?')->setParameter($paramcount++, $this->paymentMethod);
        }

        $this->query->leftJoin('o', 'CommunityStoreOrderSearchIndexAttributes', 'csi', 'o.oID = csi.oID');
        $this->query->orderBy('oID', 'DESC');

        return $this->query;
    }

    public function setSearch($search)
    {
        $this->search = trim($search);
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }

    public function setPaymentMethods($payment)
    {
        $this->paymentMethod = $payment;
    }

    public function setPaymentStatus($paymentstatus)
    {
        $this->paymentStatus = $paymentstatus;
    }

    public function setIncludeExternalPaymentRequested($bool)
    {
        $this->externalPaymentRequested = $bool;
    }

    public function setFromDate($date = null)
    {
        if (!$date) {
            $date = date('Y-m-d', strtotime('-30 days'));
        }
        $this->fromDate = $date;
    }

    public function setToDate($date = null)
    {
        if (!$date) {
            $date = date('Y-m-d');
        }
        $this->toDate = $date;
    }

    public function setLimit($limit = 0)
    {
        $this->limit = $limit;
    }

    public function setPaid($bool)
    {
        $this->paid = $bool;
    }

    public function setCancelled($bool)
    {
        $this->cancelled = $bool;
    }

    public function setRefunded($bool)
    {
        $this->refunded = $bool;
    }

    public function setIsShippable($bool)
    {
        $this->shippable = $bool;
    }

    public function getResult($queryRow)
    {
        return StoreOrder::getByID($queryRow['oID']);
    }

    public function setCustomerID($cID)
    {
        $this->cID = $cID;
    }

    protected function createPaginationObject()
    {
        $adapter = new DoctrineDbalAdapter($this->deliverQueryObject(), function ($query) {
            $query->resetQueryParts(['groupBy', 'orderBy'])->select('count(distinct o.oID)')->setMaxResults(1);
        });
        $pagination = new Pagination($this, $adapter);

        return $pagination;
    }

    public function getPaginationAdapter()
    {
        $adapter = new DoctrineDbalAdapter($this->deliverQueryObject(), function ($query) {
            $query->resetQueryParts(['groupBy', 'orderBy'])->select('count(distinct o.oID)')->setMaxResults(1);
        });

        return $adapter;
    }

    public function getTotalResults()
    {
        $query = $this->deliverQueryObject();

        return $query->resetQueryParts(['groupBy', 'orderBy'])->select('count(distinct o.oID)')->setMaxResults(1)->execute()->fetchColumn();
    }

    public static function getDateOfFirstOrder()
    {
        $app = Application::getFacadeApplication();
        $db = $app->make('database')->connection();
        $date = $db->GetRow("SELECT * FROM CommunityStoreOrders ORDER BY oDate ASC LIMIT 1");

        return $date['oDate'];
    }

    public function getOrderItems()
    {
        $orders = $this->getResults();
        $orderItems = [];
        $app = Application::getFacadeApplication();
        $db = $app->make('database')->connection();
        foreach ($orders as $order) {
            $oID = $order->getOrderID();
            $OrderOrderItems = $db->GetAll("SELECT * FROM CommunityStoreOrderItems WHERE oID=?", $oID);
            foreach ($OrderOrderItems as $oi) {
                $oi = StoreOrderItem::getByID($oi['oiID']);
                $orderItems[] = $oi;
            }
        }

        return $orderItems;
    }
}
