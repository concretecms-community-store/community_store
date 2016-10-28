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
        $db = \Database::connection();
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

    public function load($akID, $loadBy = 'akID')
    {
        parent::load($akID);
        $db = \Database::connection();
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
        $db = \Database::connection();
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

    public function saveAttribute($product, $value = false, $newAvID = false)
    {
        $av = $product->getAttributeValueObject($this, true, $newAvID);
        //saves new attribute value
        if(!is_numeric($newAvID)){
          parent::saveAttribute($av, $newAvID);
        }
        $db = \Database::connection();
        $db->Replace('CommunityStoreProductAttributeValues', array(
            'pID' => $product->getID(),
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
        $db = \Database::connection();
        $db->query('REPLACE INTO CommunityStoreProductAttributeKeys (akID) VALUES (?)', $v);

        $nak = new self();
        $nak->load($ak->getAttributeKeyID());

        return $ak;
    }

    public function update($args)
    {
        $ak = parent::update($args);
        extract($args);
        $v = array($ak->getAttributeKeyID());
        $db = \Database::connection();
        $db->query('REPLACE INTO CommunityStoreProductAttributeKeys (akID) VALUES (?)', $v);
    }

    public function delete()
    {
        parent::delete();
        $db = \Database::connection();
        $r = $db->query('select avID from CommunityStoreProductAttributeValues where akID = ?', array($this->getAttributeKeyID()));
        while ($row = $r->FetchRow()) {
            $db->query('delete from AttributeValues where avID = ?', array($row['avID']));
        }
        $db->query('delete from CommunityStoreProductAttributeValues where akID = ?', array($this->getAttributeKeyID()));
    }

    //gets the product pIDs where product has an attribute value = $keyword
    public function filterAttributeValuesByKeyword($keyword){
      $nak = new self();
      $keys = $nak->getList();
      $validPIDs = Array();
      foreach ($keys as $ak) {
        if($ak->isAttributeKeySearchable()){
          $akID = $ak->getAttributeKeyID();
          $db = \Database::connection();
          $avIDs = $db->GetCol("select distinct(avID) from CommunityStoreProductAttributeValues where akID = ?", array($akID));
          foreach($avIDs as $avID){
            $av = $ak->getAttributeValue($avID);
            if(  stripos($av, $keyword) !== false){
              $db = \Database::connection();
              $r = $db->fetchAll('select p.pID as pID from CommunityStoreProductAttributeValues av right join CommunityStoreProducts p on av.pID = p.pID where avID = ? and p.pActive = 1', array($avID) );
              foreach($r as $val){
                $validPIDs[] = $val['pID'];
              }

            }
          }
        }
      }
      return $validPIDs;
    }

    public function getAttributeKeyValueList($akIDs = array()){

      $list = Array();
      if(!empty($akIDs)){
        //prepares attribute list for filter
        foreach($akIDs as $id){
          $nak = new self();
          $ak = $nak->getByID($id);
          if($ak->isAttributeKeySearchable()){
            $akID = $ak->getAttributeKeyID();
            $list[$akID]['name'] = $ak->getAttributeKeyName();
            $db = \Database::connection();
            $values = $db->GetAll("select akID, avID from CommunityStoreProductAttributeValues where akID = ?", array($akID));
            foreach ($values as $val) {
              $value = $ak->getAttributeValue($val['avID']);
              if(!empty($value)){
                $list[$akID]['values'][$val['avID']] = $value;
              }
            }
          }
        }
      }else{
        //prepares attribute list for selectable attribute
        $nak = new self();
        $keys = $nak->getList();
        foreach ($keys as $ak) {
          if($ak->isAttributeKeySearchable()){
            $akID = $ak->getAttributeKeyID();
            $list[$akID]['name'] = $ak->getAttributeKeyName();
            $db = \Database::connection();
            $values = $db->GetAll("select akID, avID from CommunityStoreProductAttributeValues where akID = ?", array($akID));
            foreach ($values as $val) {
              $value = $ak->getAttributeValue($val['avID']);
              if(!empty($value)){
                $list[$akID]['values'][$val['avID']] = $value;
              }
            }
          }
        }
      }
      return $list;
    }

    public function getAvIDbyValue($value, $akID){
      $nak = new self();
      $ak = $nak->getByID($akID);
      $db = \Database::connection();
      $avIDs = $db->GetCol("select distinct(avID) from CommunityStoreProductAttributeValues where akID = ?", array($akID));
      foreach($avIDs as $avID){
        $avalue = $ak->getAttributeValue($avID);
        if(!empty($avalue) && $avalue==$value){
          return $avID;
        }
      }

    }

}
