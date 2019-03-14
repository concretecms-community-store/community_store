<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Order\OrderStatus;

use Concrete\Core\Utility\Service\Text as TextHelper;
use Concrete\Core\Support\Facade\Application;

/**
 * @Entity
 * @Table(name="CommunityStoreOrderStatuses")
 */
class OrderStatus
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

    /** @Column(type="integer",nullable=true) */
    protected $osSortOrder;

    protected static $table = "CommunityStoreOrderStatuses";

    public static function getTableName()
    {
        return self::$table;
    }

    public static function getByID($osID)
    {
        $app = Application::getFacadeApplication();
        $db = $app->make('database')->connection();
        $data = $db->GetRow("SELECT * FROM CommunityStoreOrderStatuses WHERE osID=?", $osID);
        $orderStatus = null;
        if (!empty($data)) {
            $orderStatus = new self();
            $orderStatus->setPropertiesFromArray($data);
        }

        return ($orderStatus instanceof self) ? $orderStatus : false;
    }

    public static function getByHandle($osHandle)
    {
        $app = Application::getFacadeApplication();
        $db = $app->make('database')->connection();
        $data = $db->GetRow("SELECT osID FROM CommunityStoreOrderStatuses WHERE osHandle=?", $osHandle);

        return self::getByID($data['osID']);
    }

    public static function getAll()
    {
        $app = Application::getFacadeApplication();
        $db = $app->make('database')->connection();
        $rows = $db->GetAll("SELECT osID FROM CommunityStoreOrderStatuses ORDER BY osSortOrder ASC, osID ASC");
        $statuses = [];
        if (count($rows) > 0) {
            foreach ($rows as $row) {
                $statuses[] = self::getByID($row['osID']);
            }
        }

        return $statuses;
    }

    public static function getList()
    {
        $statuses = [];
        foreach (self::getAll() as $status) {
            $statuses[$status->getHandle()] = t($status->getName());
        }

        return $statuses;
    }

    public static function add($osHandle, $osName = null, $osInformSite = 1, $osInformCustomer = 1, $osIsStartingStatus = 0)
    {
        if (is_null($osName)) {
            $textHelper = new TextHelper();
            $osName = $textHelper->unhandle($osHandle);
        }
        $app = Application::getFacadeApplication();
        $db = $app->make('database')->connection();
        $sql = "INSERT INTO CommunityStoreOrderStatuses (osHandle, osName, osInformSite, osInformCustomer, osIsStartingStatus) VALUES (?, ?, ?, ?, ?)";
        $values = [
            $osHandle,
            $osName,
            $osInformSite ? 1 : 0,
            $osInformCustomer ? 1 : 0,
            $osIsStartingStatus ? 1 : 0,
        ];
        $db->query($sql, $values);

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

    public static function getStartingStatus()
    {
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
        $app = Application::getFacadeApplication();
        $db = $app->make('database')->connection();
        $sql = "UPDATE CommunityStoreOrderStatuses SET " . $column . "=? WHERE osID=?";
        $db->Execute($sql, [$column, $value]);
    }

    public static function setNewStartingStatus($osHandle = null)
    {
        if ($osHandle) {
            $currentStartingStatus = self::getByHandle($osHandle);
            if ($currentStartingStatus) {
                $app = Application::getFacadeApplication();
                $db = $app->make('database')->connection();
                $db->query("UPDATE CommunityStoreOrderStatuses SET osIsStartingStatus=0 WHERE 1=1");
                $db->query("UPDATE CommunityStoreOrderStatuses SET osIsStartingStatus=1 WHERE osHandle=?", [$osHandle]);
            }
        }
    }

    public function update($data = [], $ignoreFilledColumns = false)
    {
        $orderStatusArray = [
            'osHandle' => $this->osHandle,
            'osName' => $this->osName,
            'osInformSite' => $this->osInformSite,
            'osInformCustomer' => $this->osInformCustomer,
            'osSortOrder' => $this->osSortOrder,
        ];
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
            $app = Application::getFacadeApplication();
            $db = $app->make('database')->connection();
            $db->Execute("UPDATE CommunityStoreOrderStatuses SET " . $columnPhrase . " WHERE osID=?", $values);
            if ($startingStatusHandle) {
                self::setNewStartingStatus($startingStatusHandle);
            }

            return true;
        }

        return false;
    }

    public function setPropertiesFromArray($arr)
    {
        foreach ($arr as $key => $prop) {
            $this->{$key} = $prop;
        }
    }
}
