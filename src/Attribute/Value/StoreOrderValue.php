<?php
namespace Concrete\Package\CommunityStore\Src\Attribute\Value;

use Concrete\Core\Attribute\Value\Value as Value;
use Concrete\Core\Support\Facade\Application;

class StoreOrderValue extends Value
{
    public function setOrder($order)
    {
        $this->order = $order;
    }

    public static function getByID($avID)
    {
        $cav = new self();
        $cav->load($avID);
        if ($cav->getAttributeValueID() == $avID) {
            return $cav;
        }
    }

    public function delete()
    {
        $app = Application::getFacadeApplication();
        $db = $app->make('database')->connection();
        $db->query('delete from CommunityStoreOrderAttributeValues where oID = ? and akID = ? and avID = ?', [
            $this->order->getOrderID(),
            $this->attributeKey->getAttributeKeyID(),
            $this->getAttributeValueID(),
        ]);

        // Before we run delete() on the parent object, we make sure that attribute value isn't being referenced in the table anywhere else
        $num = $db->GetOne('select count(avID) from CommunityStoreOrderAttributeValues where avID = ?', [$this->getAttributeValueID()]);
        if ($num < 1) {
            parent::delete();
        }
    }

    public function getGenericValue()
    {
        return $this->getValueObject();
    }
}
