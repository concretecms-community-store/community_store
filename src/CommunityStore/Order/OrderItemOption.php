<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Order;

use Concrete\Core\Foundation\Object as Object;
use Database;
use User;
use UserInfo;

use \Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product as StoreProduct;

/**
 * @Entity
 * @Table(name="CommunityStoreOrderItemOptions")
 */
class OrderItemOption
{
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue
     */
    protected $oioID;

    /**
     * @ManyToOne(targetEntity="Concrete\Package\CommunityStore\Src\CommunityStore\Order\OrderItem", inversedBy="OrderItemOptions", cascade={"persist"})
     * @JoinColumn(name="oiID", referencedColumnName="oiID", onDelete="CASCADE")
     */
    protected $orderItem;

    /**
     * @Column(type="string")
     */
    protected $oioKey;

    /**
     * @Column(type="string")
     */
    protected $oioValue;

    /**
     * @return mixed
     */
    public function getID()
    {
        return $this->oioID;
    }

    /**
     * @return mixed
     */
    public function getOrderItem()
    {
        return $this->orderItem;
    }

    /**
     * @param mixed $orderItem
     */
    public function setOrderItem(OrderItem $orderItem)
    {
        $this->orderItem = $orderItem;
    }

    /**
     * @return mixed
     */
    public function getOrderItemOptionKey()
    {
        return $this->oioKey;
    }

    /**
     * @param mixed $oioKey
     */
    public function setOrderItemOptionKey($oioKey)
    {
        $this->oioKey = $oioKey;
    }

    /**
     * @return mixed
     */
    public function getOrderItemOptionValue()
    {
        return $this->oioValue;
    }

    /**
     * @param mixed $oioValue
     */
    public function setOrderItemOptionValue($oioValue)
    {
        $this->oioValue = $oioValue;
    }

    public static function getByID($oioID) {
        $db = Database::connection();
        $em = $db->getEntityManager();
        return $em->find('Concrete\Package\CommunityStore\Src\CommunityStore\Order\OrderItemOption', $oioID);
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
