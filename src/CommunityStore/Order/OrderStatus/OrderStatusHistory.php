<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Order\OrderStatus;

use Concrete\Core\User\User;
use Doctrine\ORM\Mapping as ORM;
use Concrete\Core\Support\Facade\Events;
use Concrete\Core\Support\Facade\Database;
use Concrete\Core\Support\Facade\DatabaseORM as dbORM;
use Concrete\Package\CommunityStore\Src\CommunityStore\Order\Order;
use Concrete\Package\CommunityStore\Src\CommunityStore\Order\OrderEvent;
use Concrete\Package\CommunityStore\Src\CommunityStore\Order\OrderStatus\OrderStatus;

/**
 * @ORM\Entity
 * @ORM\Table(name="CommunityStoreOrderStatusHistories")
 */
class OrderStatusHistory
{
    /**
     * @ORM\Id @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $oshID;

    /**
     * @ORM\ManyToOne(targetEntity="Concrete\Package\CommunityStore\Src\CommunityStore\Order\Order",  cascade={"persist"})
     * @ORM\JoinColumn(name="oID", referencedColumnName="oID", onDelete="CASCADE")
     */
    protected $order;

    /** @ORM\Column(type="text") */
    protected $oshStatus;

    /** @ORM\Column(type="datetime") */
    protected $oshDate;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $uID;

    public static $table = 'CommunityStoreOrderStatusHistories';

    public function setOrder($order)
    {
        $this->order = $order;
    }

    public function getOrder()
    {
        return Order::getByID($this->getOrderID());
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
        return OrderStatus::getByHandle($this->getOrderStatusHandle());
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
        $app = \Concrete\Core\Support\Facade\Application::getFacadeApplication();
        $db = $app->make('database')->connection();
        $data = $db->GetRow("SELECT * FROM " . self::getTableName() . " WHERE oshID=?", $oshID);
        $history = null;
        if (!empty($data)) {
            $history = new self();
            $history->setPropertiesFromArray($data);
        }

        return ($history instanceof self) ? $history : false;
    }

    public static function getForOrder(Order $order)
    {
        if (!$order->getOrderID()) {
            return false;
        }
        $sql = "SELECT * FROM " . self::$table . " WHERE oID=? ORDER BY oshDate DESC";
        $rows = Database::connection()->getAll($sql, $order->getOrderID());
        $history = [];
        if (count($rows) > 0) {
            foreach ($rows as $row) {
                $history[] = self::getByID($row['oshID']);
            }
        }

        return $history;
    }

    public static function updateOrderStatusHistory(Order $order, $statusHandle)
    {
        $history = self::getForOrder($order);

        if (empty($history) || $history[0]->getOrderStatusHandle() != $statusHandle) {
            $previousStatus = $order->getStatusHandle();
            $order->updateStatus(self::recordStatusChange($order, $statusHandle));

            if (!empty($history)) {
                $event = new OrderEvent($order, $previousStatus);
                Events::dispatch(OrderEvent::ORDER_STATUS_UPDATE, $event);
            }
        }
    }

    private static function recordStatusChange(Order $order, $statusHandle)
    {
        $user = new User();
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
        $em = dbORM::entityManager();
        $em->persist($this);
        $em->flush();
    }

    public function delete()
    {
        $em = dbORM::entityManager();
        $em->remove($this);
        $em->flush();
    }

    public function setPropertiesFromArray($arr)
    {
        foreach ($arr as $key => $prop) {
            $this->{$key} = $prop;
        }
    }
}
