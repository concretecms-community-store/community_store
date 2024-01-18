<?php

namespace Concrete\Package\CommunityStore\Src\CommunityStore\Order;

use Pagerfanta\Adapter\DoctrineDbalAdapter;
use Concrete\Core\Support\Facade\Application;
use Concrete\Core\Search\Pagination\Pagination;
use Concrete\Core\Search\ItemList\Database\AttributedItemList;
use Concrete\Core\Search\Pagination\PaginationProviderInterface;
use Concrete\Package\CommunityStore\Entity\Attribute\Key\StoreOrderKey;
use Doctrine\DBAL\Query\QueryBuilder;

class OrderList extends AttributedItemList implements PaginationProviderInterface
{
    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Search\ItemList\Database\ItemList::$searchRequest
     */
    protected $searchRequest = false;

    /**
     * @var int
     */
    private $limit = 0;

    /**
     * @var string
     */
    private $search = '';

    /**
     * @var string
     */
    private $status = '';

    /**
     * @var int|null
     */
    private $paymentMethod = null;

    /**
     * @var string
     */
    private $paymentStatus = '';

    /**
     * @var bool|null
     */
    private $externalPaymentRequested = null;

    /**
     * @var string
     */
    private $fromDate = '';

    /**
     * @var string
     */
    private $toDate = '';

    /**
     * @var bool|null
     */
    private $paid = null;

    /**
     * @var bool|null
     */
    private $cancelled = null;

    /**
     * @var bool|null
     */
    private $refunded = null;

    /**
     * @var bool|null
     */
    private $shippable = null;

    /**
     * @var int|null
     */
    private $cID = null;

    /**
     * @param int $limit
     */
    public function setLimit($limit = 0)
    {
        $this->limit = (int) $limit;
    }

    /**
     * @param string $search
     */
    public function setSearch($search)
    {
        $this->search = trim((string) $search);
    }

    /**
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->status = (string) $status;
    }

    /**
     * @deprecated use setPaymentMethod
     */
    public function setPaymentMethods($payment)
    {
        $this->setPaymentMethod($payment);
    }

    /**
     * @param int|null $payment
     */
    public function setPaymentMethod($payment)
    {
        $this->paymentMethod = is_numeric($payment) ? (int) $payment : null;
    }

    /**
     * @param string $paymentstatus
     */
    public function setPaymentStatus($paymentstatus)
    {
        $this->paymentStatus = trim((string) $paymentstatus);
    }

    /**
     * @param bool|null $bool
     */
    public function setIncludeExternalPaymentRequested($bool)
    {
        $this->externalPaymentRequested = $bool === null ? null : (bool) $bool;
    }

    /**
     * @param string|null $date
     */
    public function setFromDate($date = null)
    {
        $this->fromDate = $date ? (string) $date : date('Y-m-d', strtotime('-30 days'));
    }

    /**
     * @param string|null $date
     */
    public function setToDate($date = null)
    {
        $this->toDate = $date ? (string) $date : date('Y-m-d');
    }

    /**
     * @param bool|null $bool
     */
    public function setPaid($bool)
    {
        $this->paid = $bool === null ? null : (bool) $bool;
    }

    /**
     * @param bool|null $bool
     */
    public function setCancelled($bool)
    {
        $this->cancelled = $bool === null ? null : (bool) $bool;
    }

    /**
     * @param bool|null $bool
     */
    public function setRefunded($bool)
    {
        $this->refunded = $bool === null ? null : (bool) $bool;
    }

    /**
     * @param bool|null $bool
     */
    public function setIsShippable($bool)
    {
        $this->shippable = $bool === null ? null : (bool) $bool;
    }

    /**
     * @param int|null $cID
     */
    public function setCustomerID($cID)
    {
        $this->cID = is_numeric($cID) ? (int) $cID : null;
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Search\ItemList\Database\AttributedItemList::getAttributeKeyClassName()
     */
    protected function getAttributeKeyClassName()
    {
        return StoreOrderKey::class;
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Search\ItemList\Database\ItemList::createQuery()
     */
    public function createQuery()
    {
        $this->query
            ->select('o.oID')
            ->from('CommunityStoreOrders', 'o');
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Search\ItemList\Database\ItemList::finalizeQuery()
     */
    public function finalizeQuery(QueryBuilder $query)
    {
        $paramcount = 0;

        if ($this->search !== '') {
            $this->query->where('o.oID = ?')->setParameter($paramcount++, is_numeric($this->search) ? (int) $this->search : 0);
            $this->query->orWhere('transactionReference = ?')->setParameter($paramcount++, $this->search);

            $app = Application::getFacadeApplication();
            $db = $app->make('database')->connection();
            $matchingOrders = $db->query("SELECT DISTINCT(oID) FROM CommunityStoreOrderAttributeValues csoav INNER JOIN atDefault av ON csoav.avID = av.avID WHERE av.value LIKE ?", ['%' . $this->search . '%']);

            $orderIDs = [];
            while ($value = $matchingOrders->fetch()) {
                $orderIDs[] = $value['oID'];
            }

            if (!empty($orderIDs)) {
                $this->query->orWhere('o.oID in (' . implode(',', $orderIDs) . ')');
            }
        }

        if ($this->status !== '') {
            $app = Application::getFacadeApplication();
            $db = $app->make('database')->connection();
            $matchingOrders = $db->query("SELECT oID FROM CommunityStoreOrderStatusHistories t1
                                            WHERE oshStatus = ? and
                                                t1.oshID = (SELECT MAX(t2.oshID)
                                                             FROM CommunityStoreOrderStatusHistories t2
                                                             WHERE t2.oID = t1.oID)", [$this->status]);
            $orderIDs = [];

            while ($value = $matchingOrders->fetch()) {
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


        if ($this->paymentStatus !== '') {
            $app = Application::getFacadeApplication();
            $db = $app->make('database')->connection();
            switch ($this->paymentStatus) {
                case 'paid':
                    $this->query->andWhere('oPaid is not null and oRefunded is null');
                    break;
                case 'unpaid':
                    $this->query->andWhere('oPaid is null and oRefunded is null and externalPaymentRequested is null');
                    break;
                case 'cancelled':
                    $this->query->andWhere('oCancelled is not null');
                    break;
                case 'refunded':
                    $this->query->andWhere('oRefunded is not null');
                    break;
                case 'incomplete':
                    $this->query->andWhere('externalPaymentRequested is not null and oPaid is null');
                    break;
                default:
                    $this->query->andWhere('0 = 1');
                    break;
            }
        }

        if ($this->fromDate !== '') {
            $this->query->andWhere('DATE(oDate) >= DATE(?)')->setParameter($paramcount++, $this->fromDate);
        }
        if ($this->toDate !== '') {
            $this->query->andWhere('DATE(oDate) <= DATE(?)')->setParameter($paramcount++, $this->toDate);
        }
        if ($this->paid === true) {
            $this->query->andWhere('o.oPaid IS NOT NULL AND o.oRefunded IS NULL');
        } elseif ($this->paid === false) {
            $this->query->andWhere('o.oPaid IS NULL OR o.oRefunded IS NOT NULL');
        }

        if ($this->cancelled === true) {
            $this->query->andWhere('o.oCancelled is not null');
        } elseif ($this->cancelled === false) {
            $this->query->andWhere('o.oCancelled is null');
        }

        if ($this->shippable === true) {
            $this->query->andWhere("o.smName IS NOT NULL AND o.smName <> ''");
        } elseif ($this->shippable === false) {
            $this->query->andWhere("o.smName IS NULL OR o.smName = ''");
        }

        if ($this->refunded === true) {
            $this->query->andWhere('o.oRefunded is not null');
        } elseif ($this->refunded === false) {
            $this->query->andWhere('o.oRefunded is null');
        }

        if ($this->limit > 0) {
            $this->query->setMaxResults($this->limit);
        }

        if ($this->externalPaymentRequested === true) {
            $this->query->andWhere('o.externalPaymentRequested is not null');
        } elseif ($this->externalPaymentRequested === false) {
            $this->query->andWhere('o.externalPaymentRequested is null');
        }

        if ($this->cID !== null) {
            $this->query->andWhere('o.cID = ?')->setParameter($paramcount++, $this->cID);
        }

        if ($this->paymentMethod !== null) {
            $this->query->andWhere('o.pmID = ?')->setParameter($paramcount++, $this->paymentMethod);
        }

        $this->query->andWhere('o.temporaryRecordCreated is null');

        $this->query->leftJoin('o', 'CommunityStoreOrderSearchIndexAttributes', 'csi', 'o.oID = csi.oID');
        $this->query->orderBy('oID', 'DESC');

        throw new \Concrete\Core\Error\UserMessageException($this->query->getSQL());
        return $this->query;
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Search\ItemList\ItemList::getResult()
     */
    public function getResult($queryRow)
    {
        return Order::getByID($queryRow['oID']);
    }

    /**
     * @return \Concrete\Core\Search\Pagination\Pagination
     */
    protected function createPaginationObject()
    {
        $adapter = new DoctrineDbalAdapter($this->deliverQueryObject(), function ($query) {
            $query->resetQueryParts(['groupBy', 'orderBy'])->select('count(distinct o.oID)')->setMaxResults(1);
        });
        $pagination = new Pagination($this, $adapter);

        return $pagination;
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Search\Pagination\PaginationProviderInterface::getPaginationAdapter()
     */
    public function getPaginationAdapter()
    {
        $adapter = new DoctrineDbalAdapter($this->deliverQueryObject(), function ($query) {
            $query->resetQueryParts(['groupBy', 'orderBy'])->select('count(distinct o.oID)')->setMaxResults(1);
        });

        return $adapter;
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Search\ItemList\ItemList::getTotalResults()
     */
    public function getTotalResults()
    {
        $query = $this->deliverQueryObject();

        return $query->resetQueryParts(['groupBy', 'orderBy'])->select('count(distinct o.oID)')->setMaxResults(1)->execute()->fetchColumn();
    }

    /**
     * @return string|false
     */
    public static function getDateOfFirstOrder()
    {
        $app = Application::getFacadeApplication();
        $db = $app->make('database')->connection();
        $date = $db->GetRow("SELECT * FROM CommunityStoreOrders ORDER BY oDate ASC LIMIT 1");

        if (isset($date['oDate'])) {
            return $date['oDate'];
        }
        return false;
    }

    /**
     * @return \Concrete\Package\CommunityStore\Src\CommunityStore\Order\OrderItem[]
     */
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
                $oi = OrderItem::getByID($oi['oiID']);
                $orderItems[] = $oi;
            }
        }

        return $orderItems;
    }
}
