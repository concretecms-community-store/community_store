<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Order\OrderStatus;

use Concrete\Core\Foundation\Object as Object;
use Database;
use Events;
use User;
use Concrete\Package\CommunityStore\Src\CommunityStore\Order\OrderEvent as StoreOrderEvent;
use Concrete\Package\CommunityStore\Src\CommunityStore\Order\Order as StoreOrder;
use Concrete\Package\CommunityStore\Src\CommunityStore\Order\OrderStatus\OrderStatus as StoreOrderStatus;

/**
 * @Entity
 * @Table(name="CommunityStoreOrderStatusHistories")
 */
class OrderStatusHistory extends Object
{
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue
     */
    protected $oshID;

    /**
     * @ManyToOne(targetEntity="Concrete\Package\CommunityStore\Src\CommunityStore\Order\Order",  cascade={"persist"})
     * @JoinColumn(name="oID", referencedColumnName="oID", onDelete="CASCADE")
     */
    protected $order;

    /** @Column(type="text") */
    protected $oshStatus;

    /** @Column(type="datetime") */
    protected $oshDate;

    /** @Column(type="integer") */
    protected $uID;

    public static $table = 'CommunityStoreOrderStatusHistories';

    public function setOrder($order)
    {
        $this->order = $order;
    }

    public function getOrder()
    {
        return StoreOrder::getByID($this->getOrderID());
    }

    public function getOrderStatusHandle()
    {
        return $this->oshStatus;
    }

    public function setOrderStatusHandle($oshStatus)
    {
        $this->oshStatus = $oshStatus;
    }

    public function getOrderStatus()
    {
        return StoreOrderStatus::getByHandle($this->getOrderStatusHandle());
    }

    public function getOrderStatusName()
    {
        $os = $this->getOrderStatus();

        if ($os) {
            return $os->getName();
        } else {
            return null;
        }
    }

    public function getDate($format = 'm/d/Y H:i:s')
    {
        return date($format, strtotime($this->oshDate));
    }

    public function setDate($date)
    {
        $this->oshDate = $date;
    }

    public function getUserID()
    {
        return $this->uID;
    }

    public function setUserID($uID)
    {
        $this->uID = $uID;
    }

    public function getUser()
    {
        return User::getByUserID($this->getUserID());
    }

    public function getUserName()
    {
        $u = $this->getUser();
        if ($u) {
            return $u->getUserName();
        }
    }

    private static function getTableName()
    {
        return self::$table;
    }

    private static function getByID($oshID)
    {
        $db = Database::connection();
        $data = $db->GetRow("SELECT * FROM " . self::getTableName() . " WHERE oshID=?", $oshID);
        $history = null;
        if (!empty($data)) {
            $history = new self();
            $history->setPropertiesFromArray($data);
        }

        return ($history instanceof self) ? $history : false;
    }

    public static function getForOrder(StoreOrder $order)
    {
        if (!$order->getOrderID()) {
            return false;
        }
        $sql = "SELECT * FROM " . self::$table . " WHERE oID=? ORDER BY oshDate DESC";
        $rows = Database::connection()->getAll($sql, $order->getOrderID());
        $history = array();
        if (count($rows) > 0) {
            foreach ($rows as $row) {
                $history[] = self::getByID($row['oshID']);
            }
        }

        return $history;
    }

    public static function updateOrderStatusHistory(StoreOrder $order, $statusHandle)
    {
        $history = self::getForOrder($order);

        if (empty($history) || $history[0]->getOrderStatusHandle() != $statusHandle) {
            $updatedOrder = clone $order;
            $updatedOrder->updateStatus(self::recordStatusChange($order, $statusHandle));
            $event = new StoreOrderEvent($updatedOrder, $order);
            Events::dispatch('on_communitystore_order_status_update', $event);
        }
    }

    private static function recordStatusChange(StoreOrder $order, $statusHandle)
    {
        $user = new user();
        $orderStatusHistory = new self();
        $orderStatusHistory->setOrderStatusHandle($statusHandle);
        $orderStatusHistory->setUserID($user->getUserID());
        $orderStatusHistory->setDate(new \DateTime());
        $orderStatusHistory->setOrder($order);
        $orderStatusHistory->save();

        return $orderStatusHistory->getOrderStatusHandle();
    }

    public function save()
    {
        $em = Database::connection()->getEntityManager();
        $em->persist($this);
        $em->flush();
    }

    public function delete()
    {
        $em = Database::connection()->getEntityManager();
        $em->remove($this);
        $em->flush();
    }
}
