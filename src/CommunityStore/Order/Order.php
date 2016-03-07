<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Order;

use Database;
use User;
use Core;
use Concrete\Core\Mail\Service as MailService;
use Group;
use Events;
use Config;
use Page;
use UserInfo;
use Session;
use Concrete\Package\CommunityStore\Src\Attribute\Key\StoreOrderKey;
use Concrete\Package\CommunityStore\Src\CommunityStore\Cart\Cart as StoreCart;
use Concrete\Package\CommunityStore\Src\CommunityStore\Tax\Tax as StoreTax;
use Concrete\Package\CommunityStore\Src\CommunityStore\Order\OrderItem as StoreOrderItem;
use Concrete\Package\CommunityStore\Src\Attribute\Value\StoreOrderValue as StoreOrderValue;
use Concrete\Package\CommunityStore\Src\CommunityStore\Shipping\Method\ShippingMethod as StoreShippingMethod;
use Concrete\Package\CommunityStore\Src\CommunityStore\Order\OrderEvent as StoreOrderEvent;
use Concrete\Package\CommunityStore\Src\CommunityStore\Order\OrderStatus\OrderStatusHistory as StoreOrderStatusHistory;
use Concrete\Package\CommunityStore\Src\CommunityStore\Order\OrderStatus\OrderStatus as StoreOrderStatus;
use Concrete\Package\CommunityStore\Src\CommunityStore\Order\OrderDiscount as StoreOrderDiscount;
use Concrete\Package\CommunityStore\Src\CommunityStore\Payment\Method as StorePaymentMethod;
use Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Calculator as StoreCalculator;
use Concrete\Package\CommunityStore\Src\CommunityStore\Customer\Customer as StoreCustomer;
use Concrete\Package\CommunityStore\Src\CommunityStore\Discount\DiscountCode as StoreDiscountCode;

/**
 * @Entity
 * @Table(name="CommunityStoreOrders")
 */
class Order
{
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue
     */
    protected $oID;

    /** @Column(type="integer",nullable=true) */
    protected $cID;

    /** @Column(type="datetime") */
    protected $oDate;

    /** @Column(type="text") */
    protected $pmName;

    /** @Column(type="text") */
    protected $smName;

    /** @Column(type="decimal", precision=10, scale=2) * */
    protected $oShippingTotal;

    /** @Column(type="text", nullable=true) * */
    protected $oTax;

    /** @Column(type="text", nullable=true) * */
    protected $oTaxIncluded;

    /** @Column(type="text", nullable=true) * */
    protected $oTaxName;

    /** @Column(type="decimal", precision=10, scale=2) * */
    protected $oTotal;

    /** @Column(type="text", nullable=true) */
    protected $transactionReference;

    /** @Column(type="datetime", nullable=true) */
    protected $externalPaymentRequested;

    public function setCustomerID($cID)
    {
        $this->cID = $cID;
    }

    public function setDate($oDate)
    {
        $this->oDate = $oDate;
    }

    public function setPaymentMethodName($pmName)
    {
        $this->pmName = $pmName;
    }

    public function setShippingMethodName($smName)
    {
        $this->smName = $smName;
    }

    public function setShippingTotal($shippingTotal)
    {
        $this->oShippingTotal = $shippingTotal;
    }

    public function setTaxTotal($taxTotal)
    {
        $this->oTax = $taxTotal;
    }

    public function setTaxIncluded($taxIncluded)
    {
        $this->oTaxIncluded = $taxIncluded;
    }

    public function setTaxLabels($taxLabels)
    {
        $this->oTaxName = $taxLabels;
    }

    public function setTotal($total)
    {
        $this->oTotal = $total;
    }

    public function setTransactionReference($transactionReference)
    {
        $this->transactionReference = $transactionReference;
    }

    public function saveTransactionReference($transactionReference)
    {
        $this->setTransactionReference($transactionReference);
        $this->save();
    }

    public function getOrderID()
    {
        return $this->oID;
    }

    public function getCustomerID()
    {
        return $this->cID;
    }

    public function getOrderDate()
    {
        return $this->oDate;
    }

    public function getPaymentMethodName()
    {
        return $this->pmName;
    }

    public function getShippingMethodName()
    {
        return $this->smName;
    }

    public function getShippingTotal()
    {
        return $this->oShippingTotal;
    }

    public function getTaxes()
    {
        $taxes = array();
        if ($this->oTax || $this->oTaxIncluded) {
            $taxAmounts = explode(",", $this->oTax);
            $taxAmountsIncluded = explode(",", $this->oTaxIncluded);
            $taxLabels = explode(",", $this->oTaxName);
            $taxes = array();
            for ($i = 0; $i < count($taxLabels); ++$i) {
                $taxes[] = array(
                    'label' => $taxLabels[$i],
                    'amount' => $taxAmounts[$i],
                    'amountIncluded' => $taxAmountsIncluded[$i],
                );
            }
        }

        return $taxes;
    }

    public function getTaxTotal()
    {
        $taxes = $this->getTaxes();
        $taxTotal = 0;
        foreach ($taxes as $tax) {
            $taxTotal = $taxTotal + $tax['amount'];
        }

        return $taxTotal;
    }

    public function getIncludedTaxTotal()
    {
        $taxes = $this->getTaxes();
        $taxTotal = 0;
        foreach ($taxes as $tax) {
            $taxTotal = $taxTotal + $tax['amountIncluded'];
        }

        return $taxTotal;
    }

    public function getTotal()
    {
        return $this->oTotal;
    }

    public function getSubTotal()
    {
        $items = $this->getOrderItems();
        $subtotal = 0;
        if ($items) {
            foreach ($items as $item) {
                $subtotal = $subtotal + ($item->getPricePaid() * $item->getQty());
            }
        }

        return $subtotal;
    }

    public function getTransactionReference()
    {
        return $this->transactionReference;
    }

    public function getExternalPaymentRequested()
    {
        return $this->externalPaymentRequested;
    }

    public function setExternalPaymentRequested($bool)
    {
        if ($bool) {
            $this->externalPaymentRequested = new \DateTime();
        } else {
            $this->externalPaymentRequested = null;
        }
    }

    public static function getByID($oID)
    {
        $db = \Database::connection();
        $em = $db->getEntityManager();

        return $em->find(get_class(), $oID);
    }

    public function getCustomersMostRecentOrderByCID($cID)
    {
        $db = \Database::connection();
        $em = $db->getEntityManager();

        return $em->getRepository(get_class())->findOneBy(array('cID' => $cID));
    }

    /**
     * @param array $data
     * @param StorePaymentMethod $pm
     * @param string $transactionReference
     * @param bool $status
     *
     * @return Order
     */
    public function add($data, $pm, $transactionReference = '', $status = null)
    {
        $customer = new StoreCustomer();
        $now = new \DateTime();
        $smName = StoreShippingMethod::getActiveShippingLabel();
        $shippingTotal = StoreCalculator::getShippingTotal();
        $taxes = StoreTax::getConcatenatedTaxStrings();
        $totals = StoreCalculator::getTotals();
        $total = $totals['total'];
        $pmName = $pm->getName();

        $order = new self();
        $order->setCustomerID($customer->getUserID());
        $order->setDate($now);
        $order->setPaymentMethodName($pmName);
        $order->setShippingMethodName($smName);
        $order->setShippingTotal($shippingTotal);
        $order->setTaxTotal($taxes['taxTotals']);
        $order->setTaxIncluded($taxes['taxIncludedTotals']);
        $order->setTaxLabels($taxes['taxLabels']);
        $order->setTotal($total);
        if ($pm->getMethodController()->isExternal()) {
            $order->setExternalPaymentRequested(true);
        }

        $order->save();

        $discounts = StoreCart::getDiscounts();
        foreach ($discounts as $discount) {
            $orderDiscount = new StoreOrderDiscount();
            $orderDiscount->setOrder($order);
            if ($discount->getTrigger() == 'code') {
                $orderDiscount->setCode(Session::get('communitystore.code'));
            }
            $orderDiscount->setDisplay($discount->getDisplay());
            $orderDiscount->setName($discount->getName());
            $orderDiscount->setDeductFrom($discount->getDeductFrom());
            $orderDiscount->setPercentage($discount->getPercentage());
            $orderDiscount->setValue($discount->getValue());
            $orderDiscount->save();
        }

        $customer->setLastOrderID($order->getOrderID());
        $order->updateStatus($status);
        $order->addCustomerAddress($customer, $order->isShippable());
        $order->addOrderItems(StoreCart::getCart());

        if (!$pm->getMethodController()->isExternal()) {
            $order->completeOrder($transactionReference);
        }

        return $order;
    }

    /**
     * @param StoreCustomer $customer
     * @param bool $includeShipping
     */
    public function addCustomerAddress($customer = null, $includeShipping = true)
    {
        if (!$customer instanceof StoreCustomer) {
            $customer = new StoreCustomer();
        }
        $email = $customer->getEmail();
        $billing_first_name = $customer->getValue("billing_first_name");
        $billing_last_name = $customer->getValue("billing_last_name");
        $billing_address = $customer->getValueArray("billing_address");
        $billing_phone = $customer->getValue("billing_phone");
        $shipping_first_name = $customer->getValue("shipping_first_name");
        $shipping_last_name = $customer->getValue("shipping_last_name");
        $shipping_address = $customer->getValueArray("shipping_address");

        $this->setAttribute("email", $email);
        $this->setAttribute("billing_first_name", $billing_first_name);
        $this->setAttribute("billing_last_name", $billing_last_name);
        $this->setAttribute("billing_address", $billing_address);
        $this->setAttribute("billing_phone", $billing_phone);
        if ($includeShipping) {
            $this->setAttribute("shipping_first_name", $shipping_first_name);
            $this->setAttribute("shipping_last_name", $shipping_last_name);
            $this->setAttribute("shipping_address", $shipping_address);
        }
    }

    public function addOrderItems($cart)
    {
        $taxCalc = Config::get('community_store.calculation');
        foreach ($cart as $cartItem) {
            $taxes = StoreTax::getTaxForProduct($cartItem);
            $taxProductTotal = array();
            $taxProductIncludedTotal = array();
            $taxProductLabels = array();

            foreach ($taxes as $tax) {
                if ($taxCalc == 'extract') {
                    $taxProductIncludedTotal[] = $tax['taxamount'];
                } else {
                    $taxProductTotal[] = $tax['taxamount'];
                }
                $taxProductLabels[] = $tax['name'];
            }
            $taxProductTotal = implode(',', $taxProductTotal);
            $taxProductIncludedTotal = implode(',', $taxProductIncludedTotal);
            $taxProductLabels = implode(',', $taxProductLabels);

            StoreOrderItem::add($cartItem, $this->getOrderID(), $taxProductTotal, $taxProductIncludedTotal, $taxProductLabels);
        }
    }

    public function save()
    {
        $em = \Database::connection()->getEntityManager();
        $em->persist($this);
        $em->flush();
    }

    public function delete()
    {
        $this->getShippingMethodTypeMethod()->delete();
        $em = \Database::connection()->getEntityManager();
        $em->remove($this);
        $em->flush();
    }

    public function completeOrder($transactionReference = null)
    {
        if ($transactionReference) {
            $this->setTransactionReference($transactionReference);
        }

        $this->setExternalPaymentRequested(null);
        $this->save();

        $fromEmail = Config::get('community_store.emailalerts');
        if (!$fromEmail) {
            $fromEmail = "store@" . $_SERVER['SERVER_NAME'];
        }

        $fromName = Config::get('community_store.emailalertsname');

        $smID = \Session::get('smID');
        $groupstoadd = array();
        $createlogin = false;
        $orderItems = $this->getOrderItems();
        $customer = new StoreCustomer();
        foreach ($orderItems as $orderItem) {
            $product = $orderItem->getProductObject();
            if ($product && $product->hasUserGroups()) {
                $productusergroups = $product->getUserGroups();

                foreach ($productusergroups as $pug) {
                    $groupstoadd[] = $pug->getUserGroupID();
                }
            }
            if ($product && $product->createsLogin()) {
                $createlogin = true;
            }
        }

        if ($createlogin && $customer->isGuest()) {
            $email = $customer->getEmail();
            $user = UserInfo::getByEmail($email);

            if (!$user) {
                $password = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 10);

                $mh = Core::make('helper/mail');

                $mh->addParameter('siteName', Config::get('concrete.site'));

                $navhelper = Core::make('helper/navigation');
                $target = Page::getByPath('/login');

                if ($target) {
                    $link = $navhelper->getLinkToCollection($target, true);

                    if ($link) {
                        $mh->addParameter('link', $link);
                    }
                } else {
                    $mh->addParameter('link', '');
                }

                $valc = Core::make('helper/concrete/validation');


                $min = Config::get('concrete.user.username.minimum');
                $max = Config::get('concrete.user.username.maximum');

                $newusername = preg_replace("/[^A-Za-z0-9_]/", '', strstr($email, '@', true));

                while (!$valc->isUniqueUsername($newusername) || strlen($newusername) < $min) {
                    if (strlen($newusername) >= $max) {
                        $newusername = substr($newusername, 0, $max - 5);
                    }
                    $newusername .= rand(0, 9);
                }

                $user = UserInfo::add(array('uName' => $newusername, 'uEmail' => trim($email), 'uPassword' => $password));

                if (Config::get('concrete.user.registration.email_registration')) {
                    $mh->addParameter('username', trim($email));
                } else {
                    $mh->addParameter('username', $newusername);
                }

                $mh->addParameter('password', $password);
                $email = trim($email);

                $mh->load('new_user', 'community_store');

                // login the newly created user
                User::loginByUserID($user->getUserID());

                // new user password email
                if ($fromName) {
                    $mh->from($fromEmail, $fromName);
                } else {
                    $mh->from($fromEmail);
                }

                $mh->to($email);
                $mh->sendMail();
            } else {
                // we're attempting to create a new user with an email that has already been used
                // earlier validation must have failed at this point, don't fetch the user
                $user = null;
            }
        } elseif ($createlogin) {  // or if we found a user (because they are logged in) and need to use it to create logins
            $user = $customer->getUserInfo();
        }

        if ($user) {  // $user is going to either be the new one, or the user of the currently logged in customer

            // update the order created with the user from the newly created user
            $this->associateUser($user->getUserID());

            $billing_first_name = $customer->getValue("billing_first_name");
            $billing_last_name = $customer->getValue("billing_last_name");
            $billing_address = $customer->getValueArray("billing_address");
            $billing_phone = $customer->getValue("billing_phone");
            $shipping_first_name = $customer->getValue("shipping_first_name");
            $shipping_last_name = $customer->getValue("shipping_last_name");
            $shipping_address = $customer->getValueArray("shipping_address");

            // update the  user's attributes
            $customer = new StoreCustomer($user->getUserID());
            $customer->setValue('billing_first_name', $billing_first_name);
            $customer->setValue('billing_last_name', $billing_last_name);
            $customer->setValue('billing_address', $billing_address);
            $customer->setValue('billing_phone', $billing_phone);

            if ($smID) {
                $customer->setValue('shipping_first_name', $shipping_first_name);
                $customer->setValue('shipping_last_name', $shipping_last_name);
                $customer->setValue('shipping_address', $shipping_address);
            }

            //add user to Store Customers group
            $group = \Group::getByName('Store Customer');
            if (is_object($group) || $group->getGroupID() < 1) {
                $user->enterGroup($group);
            }

            foreach ($groupstoadd as $id) {
                $g = Group::getByID($id);
                if ($g) {
                    $user->getUserObject()->enterGroup($g);
                }
            }

            $u = new \User();
            $u->refreshUserGroups();
        }

        StoreDiscountCode::clearCartCode();

        // create order event and dispatch
        $event = new StoreOrderEvent($this);
        Events::dispatch('on_communitystore_order', $event);

        //send out the alerts
        $mh = new MailService();

        $alertEmails = explode(",", Config::get('community_store.notificationemails'));
        $alertEmails = array_map('trim', $alertEmails);

        //receipt
        if ($fromName) {
            $mh->from($fromEmail, $fromName);
        } else {
            $mh->from($fromEmail);
        }

        $mh->to($customer->getEmail());

        $mh->addParameter("order", $this);
        $mh->load("order_receipt", "community_store");
        $mh->sendMail();

        $validNotification = false;

        //order notification
        if ($fromName) {
            $mh->from($fromEmail, $fromName);
        } else {
            $mh->from($fromEmail);
        }

        foreach ($alertEmails as $alertEmail) {
            if ($alertEmail) {
                $mh->to($alertEmail);
                $validNotification = true;
            }
        }

        if ($validNotification) {
            $mh->addParameter("order", $this);
            $mh->load("new_order_notification", "community_store");
            $mh->sendMail();
        }

        // unset the shipping type, as next order might be unshippable
        \Session::set('smID', '');

        StoreCart::clear();

        return $this;
    }

    public function remove()
    {
        $db = \Database::connection();
        $rows = $db->GetAll("SELECT * FROM CommunityStoreOrderItems WHERE oID=?", $this->oID);
        foreach ($rows as $row) {
            $db->query("DELETE FROM CommunityStoreOrderItemOptions WHERE oiID=?", $row['oiID']);
        }

        $db->query("DELETE FROM CommunityStoreOrderItems WHERE oID=?", $this->oID);
        $db->query("DELETE FROM CommunityStoreOrders WHERE oID=?", $this->oID);
    }

    public function getOrderItems()
    {
        $db = \Database::connection();
        $rows = $db->GetAll("SELECT * FROM CommunityStoreOrderItems WHERE oID=?", $this->oID);
        $items = array();

        foreach ($rows as $row) {
            $items[] = StoreOrderItem::getByID($row['oiID']);
        }

        return $items;
    }

    public function isShippable()
    {
        return $this->getShippingMethodName() != "";
    }

    public function updateStatus($status = null)
    {
        if ($status) {
            StoreOrderStatusHistory::updateOrderStatusHistory($this, $status);
        } else {
            StoreOrderStatusHistory::updateOrderStatusHistory($this, StoreOrderStatus::getStartingStatus()->getHandle());
        }
    }

    public function getStatusHistory()
    {
        return StoreOrderStatusHistory::getForOrder($this);
    }

    public function getStatus()
    {
        $history = StoreOrderStatusHistory::getForOrder($this);

        if (!empty($history)) {
            $laststatus = $history[0];

            return $laststatus->getOrderStatusName();
        } else {
            return '';
        }
    }

    public function setAttribute($ak, $value)
    {
        if (!is_object($ak)) {
            $ak = StoreOrderKey::getByHandle($ak);
        }
        $ak->setAttribute($this, $value);
    }

    public function getAttribute($ak, $displayMode = false)
    {
        if (!is_object($ak)) {
            $ak = StoreOrderKey::getByHandle($ak);
        }
        if (is_object($ak)) {
            $av = $this->getAttributeValueObject($ak);
            if (is_object($av)) {
                return $av->getValue($displayMode);
            }
        }
    }

    public function getAttributeValueObject($ak, $createIfNotFound = false)
    {
        $db = \Database::connection();
        $av = false;
        $v = array($this->getOrderID(), $ak->getAttributeKeyID());
        $avID = $db->GetOne("SELECT avID FROM CommunityStoreOrderAttributeValues WHERE oID = ? AND akID = ?", $v);
        if ($avID > 0) {
            $av = StoreOrderValue::getByID($avID);
            if (is_object($av)) {
                $av->setOrder($this);
                $av->setAttributeKey($ak);
            }
        }

        if ($createIfNotFound) {
            $cnt = 0;

            // Is this avID in use ?
            if (is_object($av)) {
                $cnt = $db->GetOne("SELECT COUNT(avID) FROM CommunityStoreOrderAttributeValues WHERE avID = ?", $av->getAttributeValueID());
            }

            if ((!is_object($av)) || ($cnt > 1)) {
                $av = $ak->addAttributeValue();
            }
        }

        return $av;
    }

    public function addDiscount($discount, $code = '')
    {
        $db = \Database::connection();

        //add the discount
        $vals = array($this->oID, $discount->drName, $discount->getDisplay(), $discount->drValue, $discount->drPercentage, $discount->drDeductFrom, $code);
        $db->query("INSERT INTO CommunityStoreOrderDiscounts(oID,odName,odDisplay,odValue,odPercentage,odDeductFrom,odCode) VALUES (?,?,?,?,?,?,?)", $vals);
    }

    public function getAppliedDiscounts()
    {
        $db = \Database::connection();
        $rows = $db->GetAll("SELECT * FROM CommunityStoreOrderDiscounts WHERE oID=?", $this->oID);

        return $rows;
    }

    public function associateUser($uID)
    {
        $db = \Database::connection();
        $rows = $db->query("Update CommunityStoreOrders set cID=? where oID = ?", array($uID, $this->oID));

        return $rows;
    }
}
