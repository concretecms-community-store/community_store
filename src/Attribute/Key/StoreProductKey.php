<?php
namespace Concrete\Package\CommunityStore\Src\Attribute\Key;

use Database;
use Concrete\Core\Attribute\Value\ValueList as AttributeValueList;
use Concrete\Package\CommunityStore\Src\Attribute\Value\StoreProductValue as StoreProductValue;
use Concrete\Core\Attribute\Key\Key as Key;

class StoreProductKey extends Key
{
    public function getAttributes($pID, $method = 'getValue')
    {
        $db = Database::connection();
        $values = $db->GetAll("select akID, avID from CommunityStoreProductAttributeValues where pID = ?", array($pID));
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

    public function load($akID)
    {
        parent::load($akID);
        $db = Database::connection();
        $row = $db->GetRow("select * from CommunityStoreProductAttributeKeys where akID = ?", array($akID));
        $this->setPropertiesFromArray($row);
    }

    public function getAttributeValue($avID, $method = 'getValue')
    {
        $av = StoreProductValue::getByID($avID);
        $av->setAttributeKey($this);

        return $av->{$method}();
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
        $db = Database::connection();
        $q = "SELECT ak.akID
            FROM AttributeKeys ak
            INNER JOIN AttributeKeyCategories akc ON ak.akCategoryID = akc.akCategoryID
            WHERE ak.akHandle = ?
            AND akc.akCategoryHandle = 'store_product'";
        $akID = $db->GetOne($q, array($akHandle));
        if ($akID > 0) {
            $ak = self::getByID($akID);
        }
        if ($ak === -1) {
            return false;
        }

        return $ak;
    }

    public static function getList()
    {
        return parent::getList('store_product');
    }

    protected function saveAttribute($product, $value = false)
    {
        $av = $product->getAttributeValueObject($this, true);
        parent::saveAttribute($av, $value);
        $db = Database::connection();
        $v = array($product->getProductID(), $this->getAttributeKeyID(), $av->getAttributeValueID());
        $db->Replace('CommunityStoreProductAttributeValues', array(
            'pID' => $product->getProductID(),
            'akID' => $this->getAttributeKeyID(),
            'avID' => $av->getAttributeValueID(),
        ), array('pID', 'akID'));
        unset($av);
    }

    public static function add($type, $args, $pkg = false)
    {
        $ak = parent::add('store_product', $type, $args, $pkg);

        extract($args);

        $v = array($ak->getAttributeKeyID());
        $db = Database::connection();
        $db->Execute('REPLACE INTO CommunityStoreProductAttributeKeys (akID) VALUES (?)', $v);

        $nak = new self();
        $nak->load($ak->getAttributeKeyID());

        return $ak;
    }

    public function update($args)
    {
        $ak = parent::update($args);
        extract($args);
        $v = array($ak->getAttributeKeyID());
        $db = Database::connection();
        $db->Execute('REPLACE INTO CommunityStoreProductAttributeKeys (akID) VALUES (?)', $v);
    }

    public function delete()
    {
        parent::delete();
        $db = Database::connection();
        $r = $db->Execute('select avID from CommunityStoreProductAttributeValues where akID = ?', array($this->getAttributeKeyID()));
        while ($row = $r->FetchRow()) {
            $db->Execute('delete from AttributeValues where avID = ?', array($row['avID']));
        }
        $db->Execute('delete from CommunityStoreProductAttributeValues where akID = ?', array($this->getAttributeKeyID()));
    }
}
