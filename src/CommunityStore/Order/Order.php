<?php

namespace Concrete\Package\CommunityStore\Src\CommunityStore\Order;

use Concrete\Core\Multilingual\Page\Section\Section;
use Concrete\Core\Page\Page;
use Concrete\Core\Routing\Redirect;
use Concrete\Core\Support\Facade\Database;
use Concrete\Core\User\User;
use Concrete\Core\Http\Request;
use Concrete\Core\User\UserInfo;
use Doctrine\ORM\Mapping as ORM;
use Concrete\Core\User\Group\Group;
use Concrete\Core\Support\Facade\Log;
use Concrete\Core\Attribute\ObjectTrait;
use Concrete\Core\Support\Facade\Config;
use Concrete\Core\Support\Facade\Events;
use Concrete\Core\Support\Facade\Session;
use Concrete\Core\Localization\Localization;
use Concrete\Core\Support\Facade\Application;
use Doctrine\Common\Collections\ArrayCollection;
use Concrete\Core\Support\Facade\DatabaseORM as dbORM;
use Concrete\Package\CommunityStore\Src\CommunityStore\Tax\Tax as Tax;
use Concrete\Package\CommunityStore\Src\CommunityStore\Cart\Cart as Cart;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product;
use Concrete\Package\CommunityStore\Entity\Attribute\Key\StoreOrderKey;
use Concrete\Package\CommunityStore\Src\CommunityStore\Order\OrderItem;
use Concrete\Package\CommunityStore\Src\CommunityStore\Customer\Customer;
use Concrete\Package\CommunityStore\Src\CommunityStore\Order\OrderEvent;
use Concrete\Package\CommunityStore\Entity\Attribute\Value\StoreOrderValue;
use Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Calculator;
use Concrete\Package\CommunityStore\Src\CommunityStore\Order\OrderDiscount;
use Concrete\Package\CommunityStore\Src\CommunityStore\Discount\DiscountCode;
use Concrete\Package\CommunityStore\Src\CommunityStore\Order\OrderStatus\OrderStatus;
use Concrete\Package\CommunityStore\Src\CommunityStore\Shipping\Method\ShippingMethod;
use Concrete\Package\CommunityStore\Src\CommunityStore\Payment\Method as PaymentMethod;
use Concrete\Package\CommunityStore\Src\CommunityStore\Order\OrderStatus\OrderStatusHistory;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductVariation\ProductVariation;

/**
 * @ORM\Entity
 * @ORM\Table(name="CommunityStoreOrders")
 */
class Order
{
    use ObjectTrait;
    /**
     * @ORM\Id @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $oID;

    /** @ORM\Column(type="integer",nullable=true) */
    protected $cID;

    /** @ORM\Column(type="boolean", nullable=true) */
    protected $memberCreated;

    /** @ORM\Column(type="datetime") */
    protected $oDate;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $pmID;

    /** @ORM\Column(type="string", length=120, nullable=true) */
    protected $pmName;

    /** @ORM\Column(type="string", length=120, nullable=true) */
    protected $smName;

    /** @ORM\Column(type="text",nullable=true) */
    protected $sInstructions;

    /** @ORM\Column(type="string", length=100, nullable=true) */
    protected $sShipmentID;

    /** @ORM\Column(type="string", length=100, nullable=true) */
    protected $sRateID;

    /** @ORM\Column(type="string", length=100, nullable=true) */
    protected $sCarrier;

    /** @ORM\Column(type="string", length=100, nullable=true) */
    protected $sTrackingID;

    /** @ORM\Column(type="text",nullable=true) */
    protected $sTrackingCode;

    /** @ORM\Column(type="text",nullable=true) */
    protected $sTrackingURL;

    /** @ORM\Column(type="text",nullable=true) */
    protected $sTrackingEstimatedDate;

    /** @ORM\Column(type="decimal", precision=10, scale=2, nullable=true) */
    protected $oShippingTotal;

    /** @ORM\Column(type="string", length=100, nullable=true) */
    protected $oTax;

    /** @ORM\Column(type="string", length=100, nullable=true) */
    protected $oTaxIncluded;

    /** @ORM\Column(type="string", length=100, nullable=true) */
    protected $oTaxName;

    /** @ORM\Column(type="decimal", precision=10, scale=2) */
    protected $oTotal;

    /** @ORM\Column(type="string", length=200, nullable=true) */
    protected $transactionReference;

    /** @ORM\Column(type="datetime", nullable=true) */
    protected $oPaid;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $oPaidByUID;

    /** @ORM\Column(type="datetime", nullable=true) */
    protected $oCancelled;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $oCancelledByUID;

    /** @ORM\Column(type="datetime", nullable=true) */
    protected $oRefunded;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $oRefundedByUID;

    /** @ORM\Column(type="text",nullable=true) */
    protected $oRefundReason;

    /** @ORM\Column(type="text", nullable=true) */
    protected $oNotes;

    /** @ORM\Column(type="datetime", nullable=true) */
    protected $externalPaymentRequested;

    /** @ORM\Column(type="datetime", nullable=true) */
    protected $temporaryRecordCreated;

    /** @ORM\Column(type="string", length=10, nullable=true) */
    protected $locale;

    /** @ORM\Column(type="string", length=255, nullable=true) */
    protected $userAgent;

    /**
     * @ORM\OneToMany(targetEntity="Concrete\Package\CommunityStore\Src\CommunityStore\Order\OrderItem", mappedBy="order",cascade={"persist"}))
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

    public function getMemberCreated()
    {
        return (bool)$this->memberCreated;
    }

    public function setMemberCreated($memberCreated)
    {
        $this->memberCreated = $memberCreated;
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

    public function getShipmentID()
    {
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

    public function getTrackingEstimatedDate()
    {
        return $this->sTrackingEstimatedDate;
    }

    public function setTrackingEstimatedDate($sTrackingEstimatedDate)
    {
        $this->sTrackingEstimatedDate = $sTrackingEstimatedDate;
    }

    public function setShippingTotal($shippingTotal)
    {
        $this->oShippingTotal = (float)$shippingTotal;
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

    public function getShippingTotal()
    {
        return $this->oShippingTotal;
    }

    public function getLocale()
    {
        return $this->locale;
    }

    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    public function getUserAgent()
    {
        return $this->userAgent;
    }

    public function setUserAgent($userAgent)
    {
        $this->userAgent = $userAgent;
    }

    public function setNotes($notes)
    {
        $this->oNotes = $notes;
    }

    public function getNotes()
    {
        return $this->oNotes;
    }

    public function getTaxes()
    {
        $taxes = [];
        if ($this->oTax || $this->oTaxIncluded) {
            $taxAmounts = explode(",", $this->oTax);
            $taxAmountsIncluded = explode(",", $this->oTaxIncluded);
            $taxLabels = explode(",", $this->oTaxName);
            $taxes = [];
            for ($i = 0; $i < count($taxLabels); ++$i) {
                $taxes[] = [
                    'label' => $taxLabels[$i],
                    'amount' => $taxAmounts[$i],
                    'amountIncluded' => $taxAmountsIncluded[$i],
                ];
            }
        }

        return $taxes;
    }

    public function getTaxTotal()
    {
        $taxes = $this->getTaxes();
        $taxTotal = 0;
        foreach ($taxes as $tax) {
            $taxTotal = $taxTotal + (float)$tax['amount'];
        }

        return $taxTotal;
    }

    public function getIncludedTaxTotal()
    {
        $taxes = $this->getTaxes();
        $taxTotal = 0;
        foreach ($taxes as $tax) {
            $taxTotal = $taxTotal + (float)$tax['amountIncluded'];
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
                $subtotal = $subtotal + ($item->getPricePaid() * $item->getQuantity());
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

    public function getTemporaryRecordCreated()
    {
        return $this->temporaryRecordCreated;
    }

    public function setTemporaryRecordCreated($bool)
    {
        if ($bool) {
            $this->temporaryRecordCreated = new \DateTime();
        } else {
            $this->temporaryRecordCreated = null;
        }
    }


    public static function getByID($oID)
    {
        $em = dbORM::entityManager();

        return $em->find(get_class(), $oID);
    }

    public function getCustomersMostRecentOrderByCID($cID)
    {
        $em = dbORM::entityManager();

        return $em->getRepository(get_class())->findOneBy(['cID' => $cID]);
    }

    public function getAttributes()
    {
        return $this->getObjectAttributeCategory()->getAttributeValues($this);
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
        $app = Application::getFacadeApplication();
        $csm = $app->make('cs/helper/multilingual');

        $userAgent = Session::get('CLIENT_HTTP_USER_AGENT');
        $notes = Session::get('notes');

        $customer = new Customer();
        $now = new \DateTime();
        $smName = ShippingMethod::getActiveShippingLabel();
        $sShipmentID = ShippingMethod::getActiveShipmentID();
        $sRateID = ShippingMethod::getActiveRateID();
        $sInstructions = Cart::getShippingInstructions();
        $totals = Calculator::getTotals();
        $shippingTotal = $totals['shippingTotal'];
        $taxes = $totals['taxes'];
        $total = $totals['total'];
        $discountRatio = $totals['discountRatio'];
        $pmName = '';
        $pmDisplayName = '';
        if ($pm) {
            $pmName = $pm->getName();
            $pmDisplayName = $csm->t($pm->getDisplayName(), 'paymentDisplayName', null, $pm->getID());
        }

        $taxCalc = Config::get('community_store.calculation');

        $taxTotal = [];
        $taxIncludedTotal = [];
        $taxLabels = [];

        foreach ($taxes as $tax) {
            if ($tax['taxamount'] > 0) {
                if ('extract' == $taxCalc) {
                    $taxIncludedTotal[] = $tax['taxamount'];
                } else {
                    $taxTotal[] = $tax['taxamount'];
                }

                $taxlabel = $csm->t($tax['name'], 'taxRateName', null, $tax['id']);
                $taxLabels[] = $taxlabel;
            }
        }

        $taxTotal = implode(',', $taxTotal);
        $taxIncludedTotal = implode(',', $taxIncludedTotal);
        $taxLabels = implode(',', $taxLabels);

        $order = new self();

        $orderID = Session::get('community_store.tempOrderID');
        $order = false;
        if ($orderID) {
            $order = Order::getByID($orderID);
        }

        if (!$order) {
            $order = new self();
        }

        $order->setTemporaryRecordCreated(false);
        $order->setCustomerID($customer->getUserID());
        $order->setDate($now);
        $order->setPaymentMethodName($pmDisplayName ? $pmDisplayName : $pmName);
        $order->setPaymentMethodID($pm ? $pm->getID() : '');
        $order->setShippingMethodName($smName);
        $order->setShipmentID($sShipmentID);
        $order->setRateID($sRateID);
        $order->setShippingInstructions($sInstructions);
        $order->setNotes($notes);
        $order->setShippingTotal($shippingTotal);
        $order->setTaxTotal($taxTotal);
        $order->setTaxIncluded($taxIncludedTotal);
        $order->setTaxLabels($taxLabels);
        $order->setTotal($total);

        Config::get('community_store.logUserAgent') ? $order->setUserAgent($userAgent) : '';

        $order->setLocale(Localization::activeLocale());

        if ($pm && $pm->getMethodController()->isExternal()) {
            $order->setExternalPaymentRequested(true);
        }

        $order->save();

        $discounts = Cart::getDiscounts();
        foreach ($discounts as $discount) {
            $orderDiscount = new OrderDiscount();
            $orderDiscount->setOrder($order);
            if ($discount->getTrigger() == 'code') {
                $orderDiscount->setCode(Session::get('communitystore.code'));

                if ($discount->isSingleUse()) {
                    $code = DiscountCode::getByCode(Session::get('communitystore.code'));
                    if ($code) {
                        $code->setOID($order->getOrderID());
                        $code->save();
                    }
                }
            }
            $orderDiscount->setDisplay($discount->getDisplay());
            $orderDiscount->setDeductType($discount->getDeductType());
            $orderDiscount->setName($discount->getName());
            $orderDiscount->setDeductFrom($discount->getDeductFrom());
            $orderDiscount->setPercentage($discount->getPercentage());
            $orderDiscount->setValue($discount->getValue());
            $orderDiscount->save();
        }

        $customer->setLastOrderID($order->getOrderID());
        $order->updateStatus($status);
        $order->addCustomerAddress($customer, $order->isShippable());
        $order->addOrderItems(Cart::getCart(), $discountRatio);

        $event = new OrderEvent($order);
        Events::dispatch(OrderEvent::ORDER_CREATED, $event);

        if ($pm && !$pm->getMethodController()->isExternal()) {
            $order->completeOrder($transactionReference, true);
        }

        Session::set('community_store.tempOrderID', false);

        return $order;
    }

    /**
     * @param StoreCustomer $customer
     * @param bool $includeShipping
     */
    public function addCustomerAddress($customer = null, $includeShipping = true)
    {
        if (!$customer instanceof Customer) {
            $customer = new Customer();
        }
        $email = $customer->getEmail();
        $billing_first_name = Session::get('billing_first_name');
        $billing_last_name = Session::get('billing_last_name');
        $billing_address = Session::get('billing_address');
        $billing_phone = Session::get('billing_phone');
        $billing_company = Session::get('billing_company');
        $shipping_first_name = Session::get('shipping_first_name');
        $shipping_last_name = Session::get('shipping_last_name');
        $shipping_address = Session::get('shipping_address');
        $shipping_company = Session::get('shipping_company');

        $this->setAttribute("email", $email);
        $this->setAttribute("billing_first_name", $billing_first_name);
        $this->setAttribute("billing_last_name", $billing_last_name);
        $this->setAttribute("billing_address", $billing_address);
        $this->setAttribute("billing_phone", $billing_phone);
        $this->setAttribute("billing_company", $billing_company);
        if ($includeShipping) {
            $this->setAttribute("shipping_first_name", $shipping_first_name);
            $this->setAttribute("shipping_last_name", $shipping_last_name);
            $this->setAttribute("shipping_address", $shipping_address);
            $this->setAttribute("shipping_company", $shipping_company);
        }

        if (Config::get('community_store.vat_number')) {
            $vat_number = $customer->getValue("vat_number");
            $this->setAttribute("vat_number", $vat_number);
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

        $sendReceipt = true;
        if ($pmID) {
            $paymentMethodUsed = PaymentMethod::getByID($this->getPaymentMethodID());

            if ($paymentMethodUsed) {
                // if the payment method actually is a payment (as opposed to an invoice), mark order as paid
                if ($paymentMethodUsed->getMethodController()->markPaid()) {
                    $this->completePayment($sameRequest);
                }
                $sendReceipt = $paymentMethodUsed->getMethodController()->sendReceipt();
            }
        }

        foreach ($this->getOrderItems() as $orderItem) {
            $product = Product::getByID($orderItem->getProductID());

            if ($product) {
                $variationID = $orderItem->getVariationID();

                if ($variationID) {
                    $variation = ProductVariation::getByID($variationID);

                    if ($variation) {
                        if (!$variation->isUnlimited()) {
                            $product->setVariation($variation);
                            $inStock = $product->getStockLevel();
                            $newStock = $inStock - $orderItem->getQuantity();
                            $product->setStockLevel($newStock);
                        }
                    }

                } elseif (!$product->isUnlimited()) {
                    $inStock = $product->getStockLevel();
                    $newStock = $inStock - $orderItem->getQuantity();
                    $product->setStockLevel($newStock);
                }

            }
        }


        $this->setExternalPaymentRequested(null);
        $this->save();

        // create order event and dispatch
        $event = new OrderEvent($this);
        Events::dispatch(OrderEvent::ORDER_PLACED, $event);

        // notifications
        $this->sendNotifications();

        //receipt
        if ($sendReceipt) {
            $this->sendOrderReceipt();
        }

        return $this;
    }

    public function completePayment($sameRequest = false)
    {
        $event = new OrderEvent($this);
        Events::dispatch(OrderEvent::ORDER_BEFORE_PAYMENT_COMPLETE, $event);

        $this->setPaid(new \DateTime());
        $this->completePostPaymentProcesses($sameRequest);
        $this->save();

        // create payment event and dispatch
        $event = new OrderEvent($this);
        Events::dispatch(OrderEvent::ORDER_PAYMENT_COMPLETE, $event);
    }

    public function completePostPaymentProcesses($sameRequest = false)
    {
        $app = Application::getFacadeApplication();
        $request = $app->make(Request::class);
        $groupstoadd = [];
        $createlogin = false;
        $usercreated = false;
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

        if ($sameRequest) {
            $customer = new Customer();  // fetch current customer
        } else {
            $customer = new Customer($this->getCustomerID()); // find customer from order as it's a remote call
        }

        $user = $customer->getUserInfo();

        if ($createlogin && !$user) {
            $email = $this->getAttribute('email');
            $user = $app->make('Concrete\Core\User\UserInfoRepository')->getByEmail($email);

            if (!$user) {
                $password = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 10);

                $mh = $app->make('mail');
                $mh->addParameter('siteName', Config::get('concrete.site'));

                $navhelper = $app->make('helper/navigation');
                $target = Page::getByPath($this->getOrderCompleteDestination('/login', $this->getLocale()));

                if ($target) {
                    $link = $navhelper->getLinkToCollection($target, true);

                    if ($link) {
                        $mh->addParameter('link', $link);
                    }
                } else {
                    $mh->addParameter('link', '');
                }

                $valc = $app->make('helper/concrete/validation');
                $min = Config::get('concrete.user.username.minimum');
                $max = Config::get('concrete.user.username.maximum');

                $newusername = preg_replace("/[^A-Za-z0-9_]/", '', strstr($email, '@', true));

                while (!$valc->isUniqueUsername($newusername) || strlen($newusername) < $min) {
                    if (strlen($newusername) >= $max) {
                        $newusername = substr($newusername, 0, $max - 5);
                    }
                    $newusername .= rand(0, 9);
                }

                $event = new OrderEvent($this);
                /* @var $uae \Concrete\Package\CommunityStore\Src\CommunityStore\Order\OrderEvent */
                $uae = Events::dispatch(OrderEvent::ORDER_BEFORE_USER_ADD, $event);

                // Did the event modify the user data?
                if ($uae->userDataUpdated()) {
                    $newUserData = $uae->getUserData();
                    if (array_key_exists('uName', $newUserData) && !empty($newUserData['uName'])) {
                        $newusername = $newUserData['uName'];
                    }
                    if (array_key_exists('uPassword', $newUserData) && !empty($newUserData['uPassword'])) {
                        $password = $newUserData['uPassword'];
                    }
                }

                $userRegistrationService = $app->make('Concrete\Core\User\RegistrationServiceInterface');
                $newuser = $userRegistrationService->create(['uName' => $newusername, 'uEmail' => trim($email), 'uPassword' => $password]);
                $this->setMemberCreated(true);

                $usercreated = true;

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
                if (!$user->isRegistered() && $sameRequest) {
                    User::loginByUserID($newuser->getUserID());
                }

                $user = $newuser;

                $fromName = Config::get('community_store.emailalertsname');
                $fromEmail = Config::get('community_store.emailalerts');
                if (!$fromEmail) {
                    $fromEmail = "store@" . str_replace('www.', '', $request->getHost());
                }

                // new user password email
                if ($fromName) {
                    $mh->from($fromEmail, $fromName);
                } else {
                    $mh->from($fromEmail);
                }

                $mh->to($email);

                try {
                    $mh->sendMail();
                } catch (\Exception $e) {
                    Log::addWarning(t('Community Store: a new user email failed sending to %s, with error %s', $email, $e->getMessage()));
                }
            }
        }

        if ($user) {  // $user is going to either be the new one, or the user of the currently logged in customer
            // update the order created with the newly created user
            $this->setCustomerID($user->getUserID());
            $this->save();

            if ($usercreated) {
                $billing_first_name = $this->getAttribute("billing_first_name");
                $billing_last_name = $this->getAttribute("billing_last_name");
                $billing_address = clone $this->getAttribute("billing_address");
                $billing_phone = $this->getAttribute("billing_phone");
                $billing_company = $this->getAttribute("billing_company");
                $shipping_first_name = $this->getAttribute("shipping_first_name");
                $shipping_last_name = $this->getAttribute("shipping_last_name");
                $shipping_company = $this->getAttribute("shipping_company");
                $shipping_address = $this->getAttribute("shipping_address");

                if ($shipping_address) {
                    $shipping_address = clone $shipping_address;
                }

                $noBillingSaveGroups = Config::get('community_store.noBillingSaveGroups');
                $noBillingSave = Config::get('community_store.noBillingSave');

                $usergroups = $user->getUserGroups();

                if (!is_array($usergroups)) {
                    $usergroups = [];
                }

                $matchingGroups = array_intersect(explode(',', $noBillingSaveGroups), $usergroups);

                if ($noBillingSaveGroups && empty($matchingGroups)) {
                    $noBillingSave = false;
                }

                // update the  user's attributes
                if (!$noBillingSave) {
                    $user->setAttribute('billing_first_name', $billing_first_name);
                    $user->setAttribute('billing_last_name', $billing_last_name);
                    $user->setAttribute('billing_address', $billing_address);
                    $user->setAttribute('billing_phone', $billing_phone);
                    $user->setAttribute('billing_company', $billing_company);
                }

                $noShippingSaveGroups = Config::get('community_store.noShippingSaveGroups');
                $noShippingSave = Config::get('community_store.noShippingSave');

                $matchingGroups = array_intersect(explode(',', $noBillingSaveGroups), $usergroups);

                if ($noShippingSaveGroups && empty($matchingGroups)) {
                    $noShippingSave = false;
                }

                if ($this->isShippable() && !$noShippingSave) {
                    $user->setAttribute('shipping_first_name', $shipping_first_name);
                    $user->setAttribute('shipping_last_name', $shipping_last_name);
                    $user->setAttribute('shipping_address', $shipping_address);
                    $user->setAttribute('shipping_company', $shipping_company);
                }
            }

            //add user to Store Customers group
            $group = Group::getByID(Config::get('community_store.customerGroup', 0));
            if (is_object($group) && $group->getGroupID() >= 1) {
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

    public function sendNotifications($email = '')
    {
        $app = Application::getFacadeApplication();
        $request = $app->make(Request::class);
        $mh = $app->make('mail');

        $notificationEmails = explode(",", Config::get('community_store.notificationemails'));
        $notificationEmails = array_map('trim', $notificationEmails);

        foreach ($this->getOrderItems() as $oi) {
            $product = $oi->getProductObject();

            if ($product) {
                $notificationEmails = array_merge($notificationEmails, $product->getNotificationEmailsArray());
            }
        }

        $notificationEmails = array_unique($notificationEmails);

        $validNotification = false;

        $fromName = Config::get('community_store.emailalertsname');

        $fromEmail = Config::get('community_store.emailalerts');
        if (!$fromEmail) {
            $fromEmail = "store@" . str_replace('www.', '', $request->getHost());
        }

        //order notification
        if ($fromName) {
            $mh->from($fromEmail, $fromName);
        } else {
            $mh->from($fromEmail);
        }

        $orderChoicesAttList = StoreOrderKey::getAttributeListBySet('order_choices');

        if (!is_array($orderChoicesAttList)) {
            $orderChoicesAttList = [];
        }

        // Create "on_before_community_store_order_notification_emails" event and dispatch
        $event = new OrderEvent($this);
        $event->setNotificationEmails($notificationEmails);
        $event = Events::dispatch('on_before_community_store_order_notification_emails', $event);
        $notificationEmails = $event->getNotificationEmails();

        if ($email) {
            $notificationEmails = explode(",", trim($email));
            $notificationEmails = array_map('trim', $notificationEmails);
        }

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
            if(Config::get('community_store.setReplyTo')) {
                $mh->replyto($this->getAttribute('email'));
            }

            try {
                $mh->sendMail();
            } catch (\Exception $e) {
                Log::addWarning(t('Community Store: a notification email failed sending to %s, with error %s', implode(', ', $notificationEmails), $e->getMessage()));
            }
        }
    }

    public function sendOrderReceipt($email = '')
    {
        $app = Application::getFacadeApplication();
        $request = $app->make(Request::class);
        $mh = $app->make('mail');
        $fromName = Config::get('community_store.emailalertsname');

        $fromEmail = Config::get('community_store.emailalerts');
        if (!$fromEmail) {
            $fromEmail = "store@" . str_replace('www.', '', $request->getHost());
        }

        if ($fromName) {
            $mh->from($fromEmail, $fromName);
        } else {
            $mh->from($fromEmail);
        }

        if (!$email) {
            $email = $this->getAttribute('email');
        }
        $mh->to($email);

        $bbcEmails = trim(Config::get('community_store.receiptBCC'));

        if ($bbcEmails) {
            $bbcEmails = explode(",", $bbcEmails);
            $bbcEmails = array_map('trim', $bbcEmails);

            foreach($bbcEmails as $bbcEmail) {
                $mh->bcc($bbcEmail);
            }
        }

        $pmID = $this->getPaymentMethodID();

        if ($pmID) {
            $paymentMethodUsed = PaymentMethod::getByID($this->getPaymentMethodID());
        }

        $paymentInstructions = '';
        if ($paymentMethodUsed) {
            $paymentInstructions = $paymentMethodUsed->getMethodController()->getPaymentInstructions();
        }

        $orderChoicesAttList = StoreOrderKey::getAttributeListBySet('order_choices');

        if (!is_array($orderChoicesAttList)) {
            $orderChoicesAttList = [];
        }

        $orderItems = $this->getOrderItems();
        $createlogin = false;

        foreach ($orderItems as $orderItem) {
            $product = $orderItem->getProductObject();
            if ($product && $product->createsLogin()) {
                $createlogin = true;
            }
        }

        $mh->addParameter('link', '');

        if ($createlogin) {
            $navhelper = $app->make('helper/navigation');
            $target = Page::getByPath($this->getOrderCompleteDestination('/login', $this->getLocale()));

            if ($target) {
                $link = $navhelper->getLinkToCollection($target, true);

                if ($link) {
                    $mh->addParameter('link', $link);
                }
            }
        }

        $mh->addParameter('paymentMethodID', $pmID);
        $mh->addParameter('orderChoicesAttList', $orderChoicesAttList);
        $mh->addParameter('paymentInstructions', $paymentInstructions);
        $mh->addParameter("order", $this);
        $mh->load("order_receipt", "community_store");

        try {
            $mh->sendMail();
        } catch (\Exception $e) {
            Log::addWarning(t('Community Store: a receipt email failed sending to %s, with error %s', $email, $e->getMessage()));
        }
    }

    public function addOrderItems($cart, $discountRatio = 1)
    {
        $taxCalc = Config::get('community_store.calculation');
        foreach ($cart as $cartItem) {
            $taxes = Tax::getTaxForProduct($cartItem);
            $taxProductTotal = [];
            $taxProductIncludedTotal = [];
            $taxProductLabels = [];

            foreach ($taxes as $tax) {
                if ($tax['taxamount'] > 0) {
                    if ('extract' == $taxCalc) {
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

            $orderItem = OrderItem::add($cartItem, $this->getOrderID(), $taxProductTotal, $taxProductIncludedTotal, $taxProductLabels, $discountRatio);
            $this->orderItems->add($orderItem);
        }
    }

    public function save()
    {
        $em = dbORM::entityManager();
        $em->persist($this);
        $em->flush();
    }

    public function delete()
    {
        $em = dbORM::entityManager();
        $em->remove($this);
        $em->flush();
    }

    public function remove()
    {
        $em = dbORM::entityManager();
        $app = Application::getFacadeApplication();
        $db = $app->make('database')->connection();
        $rows = $db->GetAll("SELECT * FROM CommunityStoreOrderItems WHERE oID=?", $this->oID);
        foreach ($rows as $row) {
            $db->query("DELETE FROM CommunityStoreOrderItemOptions WHERE oiID=?", [$row['oiID']]);
        }

        $db->query("DELETE FROM CommunityStoreOrderItems WHERE oID=?", [$this->oID]);

        $attributes = $this->getAttributes();

        foreach ($attributes as $attribute) {
            $em->remove($attribute);
        }

        $em->remove($this);
        $em->flush();
    }

    public function isShippable()
    {
        return "" != $this->getShippingMethodName();
    }

    public function updateStatus($status = null, $comment = null)
    {
        if ($status) {
            OrderStatusHistory::updateOrderStatusHistory($this, $status, $comment);
        } else {
            OrderStatusHistory::updateOrderStatusHistory($this, OrderStatus::getStartingStatus()->getHandle(), $comment);
        }
    }

    public function getStatusHistory()
    {
        return OrderStatusHistory::getForOrder($this);
    }

    public function getStatus()
    {
        $history = OrderStatusHistory::getForOrder($this);

        if (!empty($history)) {
            $laststatus = $history[0];

            return $laststatus->getOrderStatusName();
        } else {
            return '';
        }
    }

    public function getStatusHandle()
    {
        $history = OrderStatusHistory::getForOrder($this);

        if (!empty($history)) {
            $laststatus = $history[0];

            return $laststatus->getOrderStatusHandle();
        } else {
            return '';
        }
    }

    public function getObjectAttributeCategory()
    {
        $app = Application::getFacadeApplication();

        return $app->make('\Concrete\Package\CommunityStore\Attribute\Category\OrderCategory');
    }

    public function getAttributeValueObject($ak, $createIfNotExists = false)
    {
        $category = $this->getObjectAttributeCategory();

        if (!is_object($ak)) {
            $ak = $category->getByHandle($ak);
        }

        $value = false;
        if (is_object($ak)) {
            $value = $category->getAttributeValue($ak, $this);
        }

        if ($value) {
            return $value;
        } elseif ($createIfNotExists) {
            $attributeValue = new StoreOrderValue();
            $attributeValue->setOrder($this);
            $attributeValue->setAttributeKey($ak);

            return $attributeValue;
        }
    }

    public function addDiscount($discount, $code = '')
    {
        $app = Application::getFacadeApplication();
        $db = $app->make('database')->connection();
        $csm = $app->make('cs/helper/multilingual');

        //add the discount
        $displayName = $discount->getDisplay();
        $displayName = $csm->t($displayName, 'discountRuleDisplayName', null, $discount->getID());

        $vals = [$this->oID, $discount->drName, $displayName, $discount->drValue, $discount->drPercentage, $discount->drDeductFrom, $code];
        $db->query("INSERT INTO CommunityStoreOrderDiscounts(oID,odName,odDisplay,odValue,odPercentage,odDeductFrom,odCode) VALUES (?,?,?,?,?,?,?)", $vals);
    }

    public function getAppliedDiscounts()
    {
        $app = Application::getFacadeApplication();
        $db = $app->make('database')->connection();
        $rows = $db->GetAll("SELECT * FROM CommunityStoreOrderDiscounts WHERE oID=?", $this->oID);

        return $rows;
    }

    public function  saveOrderChoices()
    {
        $aks = StoreOrderKey::getAttributeListBySet('order_choices', new User());

        foreach ($aks as $uak) {
            $controller = $uak->getController();

           $type = $uak->getAttributeTypeHandle();

           if ($type == 'date_time') {
               $app = Application::getFacadeApplication();
               $dh = $app->make('helper/date');
               $format = $dh->getPHPDatePattern();

               $value = \DateTime::createFromFormat(
                   $format,
                   $controller->post()['value'],
                   $dh->getTimezone('user')
               );
           } else {
               $value = false;
               $controller = $uak->getController();
               $value = $controller->createAttributeValueFromRequest();
           }

           $this->setAttribute($uak, $value);
        }


    }

    public function getAddressValue($handle, $valuename)
    {
        $att = $this->getAttribute($handle);

        return $this->returnAttributeValue($att, $valuename);
    }

    public function getOrderCompleteDestination($default = '', $locale = '') {
        $c = Page::getCurrentPage();
        $langpath = '';

        if ($c && !$locale) {
            $lang = Section::getBySectionOfSite($c);
            if (null !== $lang) {
                $langpath = $lang->getCollectionHandle();
            }
        } else {
            if ($locale) {
                $lang = Section::getByLocale($locale);
            }
        }

        // default return
        if ($default) {
            $return = $default;
        } else {
            $return = ($langpath ? '/' . $langpath : '') . '/checkout/complete';
        }

        $orderCompleteCID = false;

        foreach($this->getOrderItems() as $orderItem) {
            $product = $orderItem->getProductObject();
            if ($product && $product->getOrderCompleteCID()) {
                $orderCompleteCID = $product->getOrderCompleteCID();
                break;
            }
        }

        if (!$orderCompleteCID) {
            $orderCompleteCID = Config::get('community_store.orderCompleteCID');
        }

        if ($orderCompleteCID) {
            $page = Page::getByID($orderCompleteCID);

            if ($lang) {
                $relatedID = $lang->getTranslatedPageID($page);

                if ($relatedID && $relatedID != $orderCompleteCID) {
                    $translatedPage = Page::getByID($relatedID);

                    if ($translatedPage && !$translatedPage->isError() && !$translatedPage->isInTrash()) {
                        $page = $translatedPage;
                    }
                }
            }

            if ($page) {
                $return = $page->getCollectionPath();
            }
        }

        return $return;
    }

    private function returnAttributeValue($att, $valuename)
    {
        $valueCamel = camel_case($valuename);

        if (method_exists($att, 'get' . $valueCamel)) {
            $functionname = 'get' . $valueCamel;

            return $att->$functionname();
        } else {
            return $att->$valuename;
        }
    }


    public static function clearTemporaryOrders() {
        $db = app()->make('database')->connection();
        $sql = 'SELECT oID from CommunityStoreOrders where temporaryRecordCreated is not null and temporaryRecordCreated < DATE_SUB(NOW(), INTERVAL 30 DAY )';
        $rows = $db->getAll($sql);

        foreach($rows as $row) {
            $order = self::getByID($row['oID']);

            if ($order && !$order->getPaid()) {
                $order->remove();
            }
        }

        $sql = 'SELECT MAX(oID) as max_id from CommunityStoreOrders';
        $result = $db->query($sql);

        foreach($result as $r) {
            $max = $r['max_id'];
        }

        if ($max) {
            $sql = 'ALTER TABLE CommunityStoreOrders AUTO_INCREMENT = ' .(int)$max +1 ;
            $db->query($sql);
        }
    }
}
