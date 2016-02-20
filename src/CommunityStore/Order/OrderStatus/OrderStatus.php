<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Order\OrderStatus;

use \Concrete\Core\Foundation\Object as Object;
use \Concrete\Core\Utility\Service\Text as TextHelper;
use Database;

/**
 * @Entity
 * @Table(name="CommunityStoreOrderStatuses")
 */
class OrderStatus extends Object
{

    /**
     * @Id @Column(type="integer")
     * @GeneratedValue
     */
    protected $osID;

    /** @Column(type="text") */
    protected $osHandle;

    /** @Column(type="text") */
    protected $osName;

    /** @Column(type="boolean") */
    protected $osInformSite;

    /** @Column(type="boolean") */
    protected $osInformCustomer;

    /** @Column(type="boolean") */
    protected $osIsStartingStatus;

    /** @Column(type="integer") */
    protected $osSortOrder;

    static protected $table = "CommunityStoreOrderStatuses";

    static public function getTableName()
    {
        return self::$table;
    }

    public static function getByID($osID)
    {
        $db = Database::connection();
        $data = $db->GetRow("SELECT * FROM " . self::getTableName() . " WHERE osID=?", $osID);
        $orderStatus = null;
        if (!empty($data)) {
            $orderStatus = new OrderStatus();
            $orderStatus->setPropertiesFromArray($data);
        }
        return ($orderStatus instanceof OrderStatus) ? $orderStatus : false;
    }

    static public function getByHandle($osHandle)
    {
        $db = Database::connection();
        $data = $db->GetRow("SELECT osID FROM " . self::getTableName() . " WHERE osHandle=?", $osHandle);
        return OrderStatus::getByID($data['osID']);

    }

    static public function getAll() {
        $db = Database::connection();
        $rows = $db->GetAll("SELECT osID FROM " . self::getTableName() . " ORDER BY osSortOrder ASC, osID ASC");
        $statuses = array();
        if (count($rows)>0) {
            foreach ($rows as $row) {
                $statuses[] = self::getByID($row['osID']);
            }
        }
        return $statuses;
    }

    static public function getList()
    {
        $statuses = array();
        foreach (self::getAll() as $status) {
            $statuses[$status->getHandle()] = $status->getName();
        }
        return $statuses;
    }

    static public function add($osHandle, $osName = null, $osInformSite = 1, $osInformCustomer = 1, $osIsStartingStatus=0)
    {
        if (is_null($osName)) {
            $textHelper = new TextHelper();
            $osName = $textHelper->unhandle($osHandle);
        }
        $db = Database::connection();
        $sql = "INSERT INTO " . self::getTableName() . " (osHandle, osName, osInformSite, osInformCustomer) VALUES (?, ?, ?, ?)";
        $values = array(
            $osHandle,
            $osName,
            $osInformSite ? 1 : 0,
            $osInformCustomer ? 1 : 0
        );
        $db->Execute($sql, $values);

        if ($osIsStartingStatus) {
            self::setNewStartingStatus($osHandle);
        }
    }

    public function getID()
    {
        return $this->osID;
    }

    public function getHandle()
    {
        return $this->osHandle;
    }

    public function getReadableHandle()
    {
        $textHelper = new TextHelper();
        return $textHelper->unhandle($this->osHandle);
    }
    public function getName()
    {
        return $this->osName;
    }

    public function setName($value = null)
    {
        if ($value) {
            $this->setColumn('osName', $value);
            return $value;
        }
        return null;
    }

    public function getInformSite()
    {
        return $this->osInformSite ? true : false;
    }

    public function setInformSite($value = true)
    {
        $this->setColumn('osInformSite', $value ? 1 : 0);
        return $value ? true : false;
    }

    public function getInformCustomer()
    {
        return $this->osInformCustomer ? true : false;
    }

    public function setInformCustomer($value = true)
    {
        $this->setColumn('osInformCustomer', $value ? 1 : 0);
        return $value ? true : false;
    }

    public function isStartingStatus()
    {
        return $this->osIsStartingStatus ? true : false;
    }

    public static function getStartingStatus() {
        $statuses = self::getAll();
        $startingStatus = $statuses[0];
        foreach ($statuses as $status) {
            if ($status->isStartingStatus()) {
                $startingStatus = $status;
                break;
            }
        }
        return $startingStatus;
    }

    private function setColumn($column, $value)
    {
        $sql = "UPDATE " . self::getTableName() . " SET " . $column . "=? WHERE osID=?";
        Database::connection()->Execute($sql, array($column, $value));
    }

    public static function setNewStartingStatus($osHandle=null) {
        if ($osHandle) {
            $currentStartingStatus = self::getByHandle($osHandle);
            if ($currentStartingStatus) {
                $db = Database::connection();
                $db->Execute("UPDATE " . self::getTableName() . " SET osIsStartingStatus=0 WHERE 1=1");
                $db->Execute("UPDATE " . self::getTableName() . " SET osIsStartingStatus=1 WHERE osHandle=?", array($osHandle));
            }
        }
    }
    public function update($data = array(), $ignoreFilledColumns = false)
    {
        $orderStatusArray = array(
            'osHandle'=>$this->osHandle,
            'osName'=>$this->osName,
            'osInformSite'=>$this->osInformSite,
            'osInformCustomer'=>$this->osInformCustomer,
            'osSortOrder'=>$this->osSortOrder
        );
        $startingStatusHandle = null;
        if (isset($data['osIsStartingStatus'])) {
            $startingStatusHandle = $this->osHandle;
        }
        $orderStatusUpdateColumns = $ignoreFilledColumns ? array_diff($orderStatusArray, $data) : array_merge($orderStatusArray, $data);
        unset($orderStatusUpdateColumns['osID']);
        if (count($orderStatusUpdateColumns) > 0) {
            $columnPhrase = implode('=?, ', array_keys($orderStatusUpdateColumns)) . "=?";
            $values = array_values($orderStatusUpdateColumns);
            $values[] = $this->osID;
            Database::connection()->Execute("UPDATE " . self::getTableName() . " SET " . $columnPhrase . " WHERE osID=?", $values);
            if ($startingStatusHandle) {
                OrderStatus::setNewStartingStatus($startingStatusHandle);
            }
            return true;
        }
        return false;
    }
}
