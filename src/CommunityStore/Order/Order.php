<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Order;

use User;
use Core;
use Concrete\Core\Mail\Service as MailService;
use Group;
use Events;
use Config;
use Page;
use UserInfo;
use Session;
use Doctrine\Common\Collections\ArrayCollection;
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
use Concrete\Core\Support\Facade\Application;

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

    /** @Column(type="integer",nullable=true) */
    protected $pmID;

    /** @Column(type="text") */
    protected $pmName;

    /** @Column(type="text") */
    protected $smName;

    /** @Column(type="text",nullable=true) */
    protected $sInstructions;

    /** @Column(type="text",nullable=true) */
    protected $sShipmentID;

    /** @Column(type="text",nullable=true) */
    protected $sRateID;

    /** @Column(type="text",nullable=true) */
    protected $sCarrier;

    /** @Column(type="text",nullable=true) */
    protected $sTrackingID;

    /** @Column(type="text",nullable=true) */
    protected $sTrackingCode;

    /** @Column(type="text",nullable=true) */
    protected $sTrackingURL;

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
    protected $oPaid;

    /** @Column(type="integer", nullable=true) */
    protected $oPaidByUID;

    /** @Column(type="datetime", nullable=true) */
    protected $oCancelled;

    /** @Column(type="integer", nullable=true) */
    protected $oCancelledByUID;

    /** @Column(type="datetime", nullable=true) */
    protected $oRefunded;

    /** @Column(type="integer", nullable=true) */
    protected $oRefundedByUID;

    /** @Column(type="text",nullable=true) */
    protected $oRefundReason;

    /** @Column(type="datetime", nullable=true) */
    protected $externalPaymentRequested;

    /**
     * @OneToMany(targetEntity="Concrete\Package\CommunityStore\Src\CommunityStore\Order\OrderItem", mappedBy="order",cascade={"persist"}))
     */
    protected $orderItems;

    public function getOrderItems()
    {
        return $this->orderItems;
    }

    public function __construct()
    {
        $this->orderItems = new ArrayCollection();
    }
    
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

    public function setPaymentMethodID($pmID)
    {
        $this->pmID = $pmID;
    }

    public function setShippingMethodName($smName)
    {
        $this->smName = $smName;
    }

    public function setShippingInstructions($sInstructions)
    {
        $this->sInstructions = $sInstructions;
    }

    public function setShipmentID($shipmentID)
    {
        $this->sShipmentID = $shipmentID;
    }

    public function getShipmentID(){
        return $this->sShipmentID;
    }

    public function getRateID()
    {
        return $this->sRateID;
    }

    public function setRateID($sRateID)
    {
        $this->sRateID = $sRateID;
    }

    public function getCarrier()
    {
        return $this->sCarrier;
    }

    public function setCarrier($sCarrier)
    {
        $this->sCarrier = $sCarrier;
    }

    public function getTrackingID()
    {
        return $this->sTrackingID;
    }

    public function setTrackingID($sTrackingID)
    {
        $this->sTrackingID = $sTrackingID;
    }

    public function getTrackingCode()
    {
        return $this->sTrackingCode;
    }

    public function setTrackingCode($sTrackingCode)
    {
        $this->sTrackingCode = $sTrackingCode;
    }

    public function getTrackingURL()
    {
        return $this->sTrackingURL;
    }

    public function setTrackingURL($sTrackingURL)
    {
        $this->sTrackingURL = $sTrackingURL;
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

    public function getPaid()
    {
        return $this->oPaid;
    }

    public function setPaid($oPaid)
    {
        $this->oPaid = $oPaid;
    }

    public function getPaidByUID()
    {
        return $this->oPaidByUID;
    }

    public function setPaidByUID($oPaidByUID)
    {
        $this->oPaidByUID = $oPaidByUID;
    }

    public function getCancelled()
    {
        return $this->oCancelled;
    }

    public function setCancelled($oCancelled)
    {
        $this->oCancelled = $oCancelled;
    }

    public function getCancelledByUID()
    {
        return $this->oCancelledByUID;
    }

    public function setCancelledByUID($oCancelledByUID)
    {
        $this->oCancelledByUID = $oCancelledByUID;
    }

    public function getRefunded()
    {
        return $this->oRefunded;
    }

    public function setRefunded($oRefunded)
    {
        $this->oRefunded = $oRefunded;
    }

    public function getRefundedByUID()
    {
        return $this->oRefundedByUID;
    }

    public function setRefundedByUID($oRefundedByUID)
    {
        $this->oRefundedByUID = $oRefundedByUID;
    }

    public function getRefundReason()
    {
        return $this->oRefundReason;
    }

    public function setRefundReason($oRefundReason)
    {
        $this->oRefundReason = $oRefundReason;
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

    public function getPaymentMethodID()
    {
        return $this->pmID;
    }

    public function getPaymentMethodName()
    {
        return $this->pmName;
    }

    public function getShippingMethodName()
    {
        return $this->smName;
    }

    public function getShippingInstructions()
    {
        return $this->sInstructions;
    }

    public function getShippingQuoteID() {
        return $this->sQuoteID;
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
        $em = \ORM::entityManager();
        return $em->find(get_class(), $oID);
    }

    public function getCustomersMostRecentOrderByCID($cID)
    {
        $em = \ORM::entityManager();
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
    public static function add($pm, $transactionReference = '', $status = null)
    {
        $customer = new StoreCustomer();
        $now = new \DateTime();
        $smName = StoreShippingMethod::getActiveShippingLabel();
        $sShipmentID = StoreShippingMethod::getActiveShipmentID();
        $sRateID = StoreShippingMethod::getActiveRateID();
        $sInstructions = StoreCart::getShippingInstructions();
        $totals = StoreCalculator::getTotals();
        StoreCart::getShippingInstructions('');
        $shippingTotal = $totals['shippingTotal'];
        $taxes = $totals['taxes'];
        $total = $totals['total'];
        $discountRatio = $totals['discountRatio'];

        $pmName = $pm->getName();
        $pmDisplayName = $pm->getDisplayName();

        $taxCalc = Config::get('community_store.calculation');

        $taxTotal = array();
        $taxIncludedTotal = array();
        $taxLabels = array();

        foreach ($taxes as $tax) {
            if ($tax['taxamount'] > 0) {
                if ($taxCalc == 'extract') {
                    $taxIncludedTotal[] = $tax['taxamount'];
                } else {
                    $taxTotal[] = $tax['taxamount'];
                }
                $taxLabels[] = $tax['name'];
            }
        }

        $taxTotal = implode(',', $taxTotal);
        $taxIncludedTotal = implode(',', $taxIncludedTotal);
        $taxLabels = implode(',', $taxLabels);

        $order = new self();
        $order->setCustomerID($customer->getUserID());
        $order->setDate($now);
        $order->setPaymentMethodName($pmDisplayName ? $pmDisplayName : $pmName);
        $order->setPaymentMethodID($pm->getID());
        $order->setShippingMethodName($smName);
        $order->setShipmentID($sShipmentID);
        $order->setRateID($sRateID);
        $order->setShippingInstructions($sInstructions);
        $order->setShippingTotal($shippingTotal);
        $order->setTaxTotal($taxTotal);
        $order->setTaxIncluded($taxIncludedTotal);
        $order->setTaxLabels($taxLabels);
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

                if ($discount->isSingleUse()) {
                    $code = StoreDiscountCode::getByCode(Session::get('communitystore.code'));
                    if ($code) {
                        $code->setOID($order->getOrderID());
                        $code->save();
                    }
                }
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
        $order->saveOrderChoices($order);
        $order->addOrderItems(StoreCart::getCart(), $discountRatio);

        if (!$pm->getMethodController()->isExternal()) {
            $order->completeOrder($transactionReference, true);
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

    // if sameRequest = true, it's indicating that the same request used to place the order
    // is also completing the order (i.e. the customer, not an external callback)
    public function completeOrder($transactionReference = null, $sameRequest = false)
    {
        if ($transactionReference) {
            $this->setTransactionReference($transactionReference);
        }

        $pmID = $this->getPaymentMethodID();

        if ($pmID) {
            $paymentMethodUsed = StorePaymentMethod::getByID($this->getPaymentMethodID());

            if ($paymentMethodUsed) {
                // if the payment method actually is a payment (as opposed to an invoice), mark order as paid
                if ($paymentMethodUsed->getMethodController()->markPaid()) {
                   $this->completePayment($sameRequest);
                }
            }
        }

        $this->setExternalPaymentRequested(null);
        $this->save();

        // create order event and dispatch
        $event = new StoreOrderEvent($this);
        Events::dispatch('on_community_store_order', $event);

        //receipt
        $this->sendOrderReceipt();

        // notifications
        $this->sendNotifications();

        return $this;
    }

    public function completePayment($sameRequest = false) {
        $this->setPaid(new \DateTime());

        // create payment event and dispatch
        $event = new StoreOrderEvent($this);
        Events::dispatch('on_community_store_payment_complete', $event);
        $this->completePostPaymentProcesses($sameRequest);
    }

    public function completePostPaymentProcesses($sameRequest = false) {
        $groupstoadd = array();
        $createlogin = false;
        $orderItems = $this->getOrderItems();

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

        if($sameRequest) {
            $customer = new StoreCustomer();  // fetch current customer
        } else {
            $customer = new StoreCustomer($this->getCustomerID()); // find customer from order as it's a remote call
        }

        $user = $customer->getUserInfo();

        if ($createlogin && !$user) {
            $email = $this->getAttribute('email');
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

                $userRegistrationService = \Core::make('Concrete\Core\User\RegistrationServiceInterface');
                $newuser = $userRegistrationService->create(array('uName' => $newusername, 'uEmail' => trim($email), 'uPassword' => $password));

                if (Config::get('concrete.user.registration.email_registration')) {
                    $mh->addParameter('username', trim($email));
                } else {
                    $mh->addParameter('username', $newusername);
                }

                $mh->addParameter('password', $password);
                $email = trim($email);

                $mh->load('new_user', 'community_store');

                $user = new User();

                // login the newly created user if in same request as customer
                if (!$user->isLoggedIn() && $sameRequest) {
                    User::loginByUserID($newuser->getUserID());
                }

                $user = $newuser;

                $fromName = Config::get('community_store.emailalertsname');
                $fromEmail = Config::get('community_store.emailalerts');
                if (!$fromEmail) {
                    $fromEmail = "store@" . $_SERVER['SERVER_NAME'];
                }

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
        }

        if ($user) {  // $user is going to either be the new one, or the user of the currently logged in customer

            // update the order created with the user from the newly created user
            $this->setCustomerID($user->getUserID());
            $this->save();

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

            if ($this->isShippable()) {
                $customer->setValue('shipping_first_name', $shipping_first_name);
                $customer->setValue('shipping_last_name', $shipping_last_name);
                $customer->setValue('shipping_address', $shipping_address);
            }

            //add user to Store Customers group
            $group = \Group::getByName('Store Customer');
            if (is_object($group) || $group->getGroupID() < 1) {
                $user->getUserObject()->enterGroup($group);
            }

            foreach ($groupstoadd as $id) {
                $g = Group::getByID($id);
                if ($g) {
                    $user->getUserObject()->enterGroup($g);
                }
            }
        }
    }

    public function sendNotifications() {
        $mh = new MailService();

        $notificationEmails = explode(",", Config::get('community_store.notificationemails'));
        $notificationEmails = array_map('trim', $notificationEmails);
        $validNotification = false;

        $fromName = Config::get('community_store.emailalertsname');
        $fromEmail = Config::get('community_store.emailalerts');
        if (!$fromEmail) {
            $fromEmail = "store@" . $_SERVER['SERVER_NAME'];
        }

        //order notification
        if ($fromName) {
            $mh->from($fromEmail, $fromName);
        } else {
            $mh->from($fromEmail);
        }

        $orderChoicesAttList = StoreOrderKey::getAttributeListBySet('order_choices');

        if (!is_array($orderChoicesAttList)) {
            $orderChoicesAttList = array();
        }

        // Create "on_before_community_store_order_notification_emails" event and dispatch
        $event = new StoreOrderEvent($this);
        $event->setNotificationEmails($notificationEmails);
        $event = Events::dispatch('on_before_community_store_order_notification_emails', $event);
        $notificationEmails = $event->getNotificationEmails();


        foreach ($notificationEmails as $notificationEmail) {
            if ($notificationEmail) {
                $mh->to($notificationEmail);
                $validNotification = true;
            }
        }

        if ($validNotification) {
            $mh->addParameter('orderChoicesAttList', $orderChoicesAttList);
            $mh->addParameter("order", $this);
            $mh->load("new_order_notification", "community_store");
            $mh->sendMail();
        }
    }

    public function sendOrderReceipt($email = '') {
        $mh = new MailService();
        $fromName = Config::get('community_store.emailalertsname');

        $fromEmail = Config::get('community_store.emailalerts');
        if (!$fromEmail) {
            $fromEmail = "store@" . $_SERVER['SERVER_NAME'];
        }

        if ($fromName) {
            $mh->from($fromEmail, $fromName);
        } else {
            $mh->from($fromEmail);
        }

        if ($email) {
            $mh->to($email);
        } else {
            $mh->to($this->getAttribute('email'));
        }

        $pmID = $this->getPaymentMethodID();

        if ($pmID) {
            $paymentMethodUsed = StorePaymentMethod::getByID($this->getPaymentMethodID());
        }

        $paymentInstructions = '';
        if ($paymentMethodUsed) {
            $paymentInstructions = $paymentMethodUsed->getMethodController()->getPaymentInstructions();
        }

        $orderChoicesAttList = StoreOrderKey::getAttributeListBySet('order_choices');

        if (!is_array($orderChoicesAttList)) {
            $orderChoicesAttList = array();
        }

        $mh->addParameter('orderChoicesAttList', $orderChoicesAttList);
        $mh->addParameter('paymentInstructions', $paymentInstructions);
        $mh->addParameter("order", $this);
        $mh->load("order_receipt", "community_store");
        $mh->sendMail();
    }

    public function addOrderItems($cart, $discountRatio = 1)
    {
        $taxCalc = Config::get('community_store.calculation');
        foreach ($cart as $cartItem) {
            $taxes = StoreTax::getTaxForProduct($cartItem);
            $taxProductTotal = array();
            $taxProductIncludedTotal = array();
            $taxProductLabels = array();

            foreach ($taxes as $tax) {

                if ( $tax['taxamount'] > 0) {
                    if ($taxCalc == 'extract') {
                        $taxProductIncludedTotal[] = $tax['taxamount'] * $discountRatio;
                    } else {
                        $taxProductTotal[] = $tax['taxamount'] * $discountRatio;
                    }
                    $taxProductLabels[] = $tax['name'];
                }
            }
            $taxProductTotal = implode(',', $taxProductTotal);
            $taxProductIncludedTotal = implode(',', $taxProductIncludedTotal);
            $taxProductLabels = implode(',', $taxProductLabels);

            $orderItem = StoreOrderItem::add($cartItem, $this->getOrderID(), $taxProductTotal, $taxProductIncludedTotal, $taxProductLabels, $discountRatio);
            $this->orderItems->add($orderItem);
        }
    }

    public function save()
    {
        $em = \ORM::entityManager();
        $em->persist($this);
        $em->flush();
    }

    public function delete()
    {
        $em = \ORM::entityManager();
        $em->remove($this);
        $em->flush();
    }

    public function remove()
    {
        $app = Application::getFacadeApplication();
        $db = $app->make('database')->connection();
        $rows = $db->GetAll("SELECT * FROM CommunityStoreOrderItems WHERE oID=?", $this->oID);
        foreach ($rows as $row) {
            $db->query("DELETE FROM CommunityStoreOrderItemOptions WHERE oiID=?", array($row['oiID']));
        }

        $db->query("DELETE FROM CommunityStoreOrderItems WHERE oID=?", array($this->oID));
        $db->query("DELETE FROM CommunityStoreOrders WHERE oID=?", array($this->oID));
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

    public function getStatusHandle()
    {
        $history = StoreOrderStatusHistory::getForOrder($this);

        if (!empty($history)) {
            $laststatus = $history[0];

            return $laststatus->getOrderStatusHandle();
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
        $app = Application::getFacadeApplication();
        $db = $app->make('database')->connection();
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
        $app = Application::getFacadeApplication();
        $db = $app->make('database')->connection();

        //add the discount
        $vals = array($this->oID, $discount->drName, $discount->getDisplay(), $discount->drValue, $discount->drPercentage, $discount->drDeductFrom, $code);
        $db->query("INSERT INTO CommunityStoreOrderDiscounts(oID,odName,odDisplay,odValue,odPercentage,odDeductFrom,odCode) VALUES (?,?,?,?,?,?,?)", $vals);
    }

    public function getAppliedDiscounts()
    {
        $app = Application::getFacadeApplication();
        $db = $app->make('database')->connection();
        $rows = $db->GetAll("SELECT * FROM CommunityStoreOrderDiscounts WHERE oID=?", $this->oID);

        return $rows;
    }

    public function saveOrderChoices($order)
    {
        //save product attributes
        $akList = StoreOrderKey::getAttributeListBySet('order_choices');
        foreach($akList as $ak) {
            $ak->saveAttributeForm($order);
        }
    }

    public function getAddressValue($handle, $valuename) {
        $att = $this->getValue($handle);
        return $this->returnAttributeValue($att,$valuename);
    }

    private function returnAttributeValue($att, $valuename) {
        $valueCamel = camel_case($valuename);

        if (method_exists($att, 'get' .$valueCamel)) {
            $functionname = 'get'.$valueCamel;
            return $att->$functionname();
        } else {
            return $att->$valuename;
        }
    }

}
