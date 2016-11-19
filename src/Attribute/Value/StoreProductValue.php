<?php
namespace Concrete\Package\CommunityStore\Src\Attribute\Value;

use Concrete\Core\Attribute\Value\Value as Value;
use Concrete\Core\Support\Facade\Application;

defined('C5_EXECUTE') or die(_("Access Denied."));
class StoreProductValue extends Value
{
    public function setProduct($product)
    {
        $this->product = $product;
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
        $db->query('delete from CommunityStoreProductAttributeValues where pID = ? and akID = ? and avID = ?', array(
            $this->product->getID(),
            $this->attributeKey->getAttributeKeyID(),
            $this->getAttributeValueID(),
        ));

        // Before we run delete() on the parent object, we make sure that attribute value isn't being referenced in the table anywhere else
        $num = $db->GetOne('select count(avID) from CommunityStoreProductAttributeValues where avID = ?', array($this->getAttributeValueID()));
        if ($num < 1) {
            parent::delete();
        }
    }
}
