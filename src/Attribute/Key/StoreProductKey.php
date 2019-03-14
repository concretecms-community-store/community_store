<?php
namespace Concrete\Package\CommunityStore\Src\Attribute\Key;

use Concrete\Core\Attribute\Value\ValueList as AttributeValueList;
use Concrete\Package\CommunityStore\Src\Attribute\Value\StoreProductValue as StoreProductValue;
use Concrete\Core\Attribute\Key\Key as Key;
use Concrete\Core\Support\Facade\Application;

class StoreProductKey extends Key
{
    protected $searchIndexFieldDefinition = [
        'columns' => [
            ['name' => 'pID', 'type' => 'integer', 'options' => ['unsigned' => true, 'default' => 0, 'notnull' => true]],
        ],
        'primary' => ['pID'],
    ];

    public static function getDefaultIndexedSearchTable()
    {
        return 'CommunityStoreProductSearchIndexAttributes';
    }

    // required, because method does not exist in 5.8.
    public function getIndexedSearchTable()
    {
        return self::getDefaultIndexedSearchTable();
    }

    // required, because method does not exist in 5.8.
    public function createIndexedSearchTable()
    {
        if (false != $this->getIndexedSearchTable()) {
            $db = \Database::get();
            $platform = $db->getDatabasePlatform();
            $array[$this->getIndexedSearchTable()] = $this->searchIndexFieldDefinition;
            $schema = \Concrete\Core\Database\Schema\Schema::loadFromArray($array, $db);
            $queries = $schema->toSql($platform);
            foreach ($queries as $query) {
                $db->query($query);
            }
        }
    }

    public static function getAttributes($pID, $method = 'getValue')
    {
        $app = Application::getFacadeApplication();
        $db = $app->make('database')->connection();
        $values = $db->GetAll("select akID, avID from CommunityStoreProductAttributeValues where pID = ?", [$pID]);
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
        $row = $db->GetRow("select * from CommunityStoreProductAttributeKeys where akID = ?", [$akID]);
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
        $app = Application::getFacadeApplication();
        $db = $app->make('database')->connection();
        $q = "SELECT ak.akID
            FROM AttributeKeys ak
            INNER JOIN AttributeKeyCategories akc ON ak.akCategoryID = akc.akCategoryID
            WHERE ak.akHandle = ?
            AND akc.akCategoryHandle = 'store_product'";
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
        return parent::getList('store_product');
    }

    public function saveAttribute($product, $value = false)
    {
        $av = $product->getAttributeValueObject($this, true);
        parent::saveAttribute($av, $value);
        $app = Application::getFacadeApplication();
        $db = $app->make('database')->connection();
        $v = [$product->getID(), $this->getAttributeKeyID(), $av->getAttributeValueID()];
        $db->Replace('CommunityStoreProductAttributeValues', [
            'pID' => $product->getID(),
            'akID' => $this->getAttributeKeyID(),
            'avID' => $av->getAttributeValueID(),
        ], ['pID', 'akID']);
        unset($av);
    }

    public static function add($handle, $type, $args, $pkg = false)
    {
        $ak = parent::add($handle, $type, $args, $pkg);

        extract($args);

        $v = [$ak->getAttributeKeyID()];
        $app = Application::getFacadeApplication();
        $db = $app->make('database')->connection();
        $db->query('REPLACE INTO CommunityStoreProductAttributeKeys (akID) VALUES (?)', $v);

        $nak = new self();
        $nak->load($ak->getAttributeKeyID());

        return $ak;
    }

    public function update($args)
    {
        $ak = parent::update($args);
        extract($args);
        $v = [$ak->getAttributeKeyID()];
        $app = \Concrete\Core\Support\Facade\Application::getFacadeApplication();
        $db = $app->make('database')->connection();
        $db->query('REPLACE INTO CommunityStoreProductAttributeKeys (akID) VALUES (?)', $v);
    }

    public function delete()
    {
        parent::delete();
        $app = \Concrete\Core\Support\Facade\Application::getFacadeApplication();
        $db = $app->make('database')->connection();
        $r = $db->query('select avID from CommunityStoreProductAttributeValues where akID = ?', [$this->getAttributeKeyID()]);
        while ($row = $r->FetchRow()) {
            $db->query('delete from AttributeValues where avID = ?', [$row['avID']]);
        }
        $db->query('delete from CommunityStoreProductAttributeValues where akID = ?', [$this->getAttributeKeyID()]);
    }
}
