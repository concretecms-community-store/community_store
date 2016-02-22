<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Order;

use Database;
use Concrete\Core\Search\Pagination\Pagination;
use Concrete\Core\Search\ItemList\Database\AttributedItemList;
use Pagerfanta\Adapter\DoctrineDbalAdapter;
use Concrete\Package\CommunityStore\Src\CommunityStore\Order\Order as StoreOrder;
use Concrete\Package\CommunityStore\Src\CommunityStore\Order\OrderItem as StoreOrderItem;

class OrderList  extends AttributedItemList
{
    protected function getAttributeKeyClassName()
    {
        return '\\Concrete\\Package\\CommunityStore\\Src\\Attribute\\Key\\StoreOrderKey';
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
            $this->query->where('oID like ?')->setParameter($paramcount++, '%'. $this->search. '%');
        }

        if (isset($this->status)) {
            $db = Database::connection();
            $matchingOrders = $db->query("SELECT oID FROM CommunityStoreOrderStatusHistories t1
                                            WHERE oshStatus = ? and
                                                t1.oshDate = (SELECT MAX(t2.oshDate)
                                                             FROM CommunityStoreOrderStatusHistories t2
                                                             WHERE t2.oID = t1.oID)", array($this->status));
            $orderIDs = array();

            while ($value = $matchingOrders->fetchRow()) {
                $orderIDs[] = $value['oID'];
            }

            if (!empty($orderIDs)) {
                if ($paramcount > 0) {
                    $this->query->addWhere('o.oID in ('.implode(',', $orderIDs).')');
                } else {
                    $this->query->where('o.oID in ('.implode(',', $orderIDs).')');
                }
            } else {
                $this->query->where('1 = 0');
            }
        }

        if (isset($this->fromDate)) {
            $this->query->andWhere('DATE(oDate) >= DATE(?)')->setParameter($paramcount++, $this->fromDate);
        }
        if (isset($this->toDate)) {
            $this->query->andWhere('DATE(oDate) <= DATE(?)')->setParameter($paramcount++, $this->toDate);
        }
        if ($this->limit > 0) {
            $this->query->setMaxResults($this->limit);
        }

        $this->query->orderBy('oID', 'DESC');

        return $this->query;
    }

    public function setSearch($search)
    {
        $this->search = $search;
    }

    public function setStatus($status)
    {
        $this->status = $status;
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

    public function getResult($queryRow)
    {
        return StoreOrder::getByID($queryRow['oID']);
    }

    protected function createPaginationObject()
    {
        $adapter = new DoctrineDbalAdapter($this->deliverQueryObject(), function ($query) {
            $query->select('count(distinct o.oID)')->setMaxResults(1);
        });
        $pagination = new Pagination($this, $adapter);

        return $pagination;
    }

    public function getTotalResults()
    {
        $query = $this->deliverQueryObject();

        return $query->select('count(distinct o.oID)')->setMaxResults(1)->execute()->fetchColumn();
    }

    public static function getDateOfFirstOrder()
    {
        $db = Database::connection();
        $date = $db->GetRow("SELECT * FROM CommunityStoreOrders ORDER BY oDate ASC LIMIT 1");

        return $date['oDate'];
    }
    public function getOrderItems()
    {
        $orders = $this->getResults();
        $orderItems = array();
        $db = Database::connection();
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
