<?php
namespace Concrete\Package\CommunityStore\Src\Attribute\Key;

use AttributeSet;
use Concrete\Core\Attribute\Value\ValueList as AttributeValueList;
use Concrete\Package\CommunityStore\Src\Attribute\Value\StoreOrderValue as StoreOrderValue;
use Concrete\Core\Attribute\Key\Key as Key;
use Concrete\Core\Support\Facade\Application;

class StoreOrderKey extends Key
{
    public function getAttributes($oID, $method = 'getValue')
    {
        $app = Application::getFacadeApplication();
        $db = $app->make('database')->connection();
        $values = $db->GetAll("select akID, avID from CommunityStoreOrderAttributeValues where oID = ?", [$oID]);
        $avl = new AttributeValueList();
        foreach ($values as $val) {
            $ak = self::getByID($val['akID']);
            if (is_object($ak)) {
                $value = $ak->getAttributeValue($val['avID'], $method);
                $avl->addAttributeValue($ak, $value);
            }
        }

        return $avl;
    }

    public function load($akID, $loadBy = 'akID')
    {
        parent::load($akID);
        $app = Application::getFacadeApplication();
        $db = $app->make('database')->connection();
        $row = $db->GetRow("select * from CommunityStoreOrderAttributeKeys where akID = ?", [$akID]);
        $this->setPropertiesFromArray($row);
    }

    public function getAttributeValue($avID, $method = 'getValue')
    {
        $av = StoreOrderValue::getByID($avID);
        $av->setAttributeKey($this);

        return $av->{$method}();
    }

    public function getAttributeGroups()
    {
        $app = Application::getFacadeApplication();
        $db = $app->make('database')->connection();
        $groups = [];
        $allGroups = $db->GetAll("select gID from CommunityStoreOrderAttributeKeyUserGroups where akID = ?", [$this->akID]);
        foreach ($allGroups as $group) {
            $groups[] = $group['gID'];
        }

        return $groups;
    }

    public static function getByID($akID)
    {
        $ak = new self();
        $ak->load($akID);
        if ($ak->getAttributeKeyID() > 0) {
            return $ak;
        }
    }

    public static function getByHandle($akHandle)
    {
        $app = Application::getFacadeApplication();
        $db = $app->make('database')->connection();
        $q = "SELECT ak.akID
            FROM AttributeKeys ak
            INNER JOIN AttributeKeyCategories akc ON ak.akCategoryID = akc.akCategoryID
            WHERE ak.akHandle = ?
            AND akc.akCategoryHandle = 'store_order'";
        $akID = $db->GetOne($q, [$akHandle]);
        if ($akID > 0) {
            $ak = self::getByID($akID);
        }
        if (-1 === $ak) {
            return false;
        }

        return $ak;
    }

    public static function getList()
    {
        return parent::getList('store_order');
    }

    public static function getAttributeListBySet($set, $user = null)
    {
        if (!$set instanceof AttributeSet) {
            $set = AttributeSet::getByHandle($set);
        }

        if ($user) {
            $uGroupIDs = array_keys($user->getUserGroups());
        }

        $akList = [];
        foreach (parent::getList('store_order') as $ak) {
            if (in_array($set, $ak->getAttributeSets())) {
                $attributeGroups = $ak->getAttributeGroups();

                if (is_null($user) || (empty($attributeGroups) || array_intersect($ak->getAttributeGroups(), $uGroupIDs))) {
                    $akList[] = $ak;
                }
            }
        }

        return $akList;
    }

    protected function saveAttribute($order, $value = false)
    {
        $av = $order->getAttributeValueObject($this, true);
        parent::saveAttribute($av, $value);
        $app = Application::getFacadeApplication();
        $db = $app->make('database')->connection();
        $v = [$order->getOrderID(), $this->getAttributeKeyID(), $av->getAttributeValueID()];
        $db->Replace('CommunityStoreOrderAttributeValues', [
            'oID' => $order->getOrderID(),
            'akID' => $this->getAttributeKeyID(),
            'avID' => $av->getAttributeValueID(),
        ], ['oID', 'akID']);
        unset($av);
    }

    public static function add($handle, $type, $args, $pkg = false)
    {
        $ak = parent::add($handle, $type, $args, $pkg);

        extract($args);

        $akID = $ak->getAttributeKeyID();
        $app = Application::getFacadeApplication();
        $db = $app->make('database')->connection();
        $db->query('REPLACE INTO CommunityStoreOrderAttributeKeys (akID, required) VALUES (?, ?)', [$akID, $required]);

        if (is_array($groups) && !empty($groups)) {
            foreach ($groups as $gID) {
                $db->query('REPLACE INTO CommunityStoreOrderAttributeKeyUserGroups (akID, gID) VALUES (?, ?)', [$akID, $gID]);
            }
        }

        $nak = new self();
        $nak->load($ak->getAttributeKeyID());

        return $ak;
    }

    public function update($args)
    {
        $ak = parent::update($args);
        extract($args);

        $akID = $ak->getAttributeKeyID();
        $app = Application::getFacadeApplication();
        $db = $app->make('database')->connection();
        $db->query('REPLACE INTO CommunityStoreOrderAttributeKeys (akID, required) VALUES (?, ?)', [$akID, $required]);

        $db->query('DELETE FROM CommunityStoreOrderAttributeKeyUserGroups where akID = ?', [$akID]);
        if (is_array($groups) && !empty($groups)) {
            foreach ($groups as $gID) {
                $db->query('REPLACE INTO CommunityStoreOrderAttributeKeyUserGroups (akID, gID) VALUES (?, ?)', [$akID, $gID]);
            }
        }
    }

    public function delete()
    {
        parent::delete();
        $app = Application::getFacadeApplication();
        $db = $app->make('database')->connection();
        $r = $db->query('select avID from CommunityStoreOrderAttributeValues where akID = ?', [$this->getAttributeKeyID()]);
        while ($row = $r->FetchRow()) {
            $db->query('delete from AttributeValues where avID = ?', [$row['avID']]);
        }
        $db->query('delete from CommunityStoreOrderAttributeValues where akID = ?', [$this->getAttributeKeyID()]);
    }

    public function isRequired()
    {
        return (bool) $this->required;
    }
}
