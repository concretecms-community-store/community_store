<?php
namespace Concrete\Package\CommunityStore\Controller\SinglePage;

use Concrete\Core\View\View;
use Concrete\Core\Page\Page;
use Concrete\Core\Routing\Redirect;
use Illuminate\Filesystem\Filesystem;
use Concrete\Core\Support\Facade\Config;
use Concrete\Core\Support\Facade\Session;
use Concrete\Core\Page\Controller\PageController;
use Concrete\Core\Multilingual\Page\Section\Section;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product;
use Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Calculator;
use Concrete\Package\CommunityStore\Src\CommunityStore\Discount\DiscountRule;
use Concrete\Package\CommunityStore\Src\CommunityStore\Discount\DiscountCode;
use Concrete\Package\CommunityStore\Src\CommunityStore\Cart\Cart as StoreCart;
use Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Price as Price;

class Cart extends PageController
{
    public function view($action = '')
    {
        $c = Page::getCurrentPage();
        $al = Section::getBySectionOfSite($c);
        $langpath = '';
        if (null !== $al) {
            $langpath = $al->getCollectionHandle();
        }

        if ('all' == Config::get('community_store.shoppingDisabled')) {
            return Redirect::to("/");
        }

        $codeerror = false;
        $codesuccess = false;

        $returndata = [];

        $token = $this->app->make('token');
        if ($this->request->request->all() && $token->validate('community_store')) {
            if ('code' == $this->request->request->get('action')) {
                $codeerror = false;
                $codesuccess = false;

                if ($this->request->request->get('code')) {
                    $codesuccess = DiscountCode::storeCartCode($this->request->request->get('code'));
                    $codeerror = !$codesuccess;
                } else {
                    DiscountCode::clearCartCode();
                }
            }

            if ('update' == $this->request->request->get('action')) {
                $data = $this->request->request->all();
                if (is_array($data['instance'])) {
                    $result = StoreCart::updateMultiple($data);
                    $quantity = 0;
                    foreach ($data['pQty'] as $q) {
                        $quantity += $q;
                    }

                    $added = 0;
                    foreach ($result as $r) {
                        $added += $r['added'];
                    }
                } else {
                    $result = StoreCart::update($data);
                    $added = $result['added'];
                    $quantity = (int) $data['pQty'];
                }

                $returndata = ['success' => true, 'quantity' => $quantity, 'action' => 'update', 'added' => $added];
            }

            if ('clear' == $this->request->request->get('action')) {
                StoreCart::clear();
                $returndata = ['success' => true, 'action' => 'clear'];
            }

            if ('remove' == $this->request->request->get('action')) {
                $data = $this->request->request->all();
                if (isset($data['instance'])) {
                    StoreCart::remove($data['instance']);
                    $returndata = ['success' => true, 'action' => 'remove'];
                }
            }
        }

        if ($action) {
            $returndata['action'] = $action;
        }

        $this->set('actiondata', $returndata);
        $this->set('codeerror', $codeerror);
        $this->set('codesuccess', $codesuccess);

        $this->set('cart', StoreCart::getCart(true));
        $this->set('discounts', StoreCart::getDiscounts());

        $totals = Calculator::getTotals();

        if (StoreCart::isShippable()) {
            $this->set('shippingEnabled', true);

            if (Session::get('community_store.smID')) {
                $this->set('shippingtotal', $totals['shippingTotal']);
            } else {
                $this->set('shippingtotal', false);
            }
        } else {
            $this->set('shippingEnabled', false);
        }

        $this->set('total', $totals['total']);
        $this->set('subTotal', $totals['subTotal']);
        $this->set('taxes', $totals['taxes']);
        $this->set('taxtotal', $totals['taxTotal']);

        $this->requireAsset('javascript', 'jquery');
        $js = \Concrete\Package\CommunityStore\Controller::returnHeaderJS();
        $this->addFooterItem($js);
        $this->requireAsset('javascript', 'community-store');
        $this->requireAsset('css', 'community-store');

        $discountsWithCodesExist = DiscountRule::discountsWithCodesExist();
        $this->set("discountsWithCodesExist", $discountsWithCodesExist);

        $this->set('token', $this->app->make('token'));
        $this->set('langpath', $langpath);
    }

    public function add()
    {
        $token = $this->app->make('token');

        if ($this->request->request->all() && $token->validate('community_store')) {
            $data = $this->request->request->all();
            $result = StoreCart::add($data);

            $added = $result['added'];

            $error = 0;
            $errorMsg = null;

            if ($result['error']) {
                $error = 1;
				$errorMsg = $result['errorMsg'];
            }

            $product = Product::getByID($data['pID']);
            $productdata['pAutoCheckout'] = $product->autoCheckout();
            $productdata['pName'] = $product->getName();
            $productdata['pID'] = $product->getID();

            $returndata = ['quantity' => $data['quantity'], 'added' => $added, 'product' => $productdata, 'action' => 'add', 'error' => $error, 'errorMsg' => $errorMsg];
            echo json_encode($returndata);
        }
        exit();
    }

    public function code()
    {
        $token = $this->app->make('token');

        if ($token->validate('community_store')) {
            DiscountCode::storeCartCode($this->request->request->get('code'));
        }

        exit();
    }

    public function update()
    {
        $token = $this->app->make('token');

        if ($this->request->request->all() && $token->validate('community_store')) {
            $data = $this->request->request->all();

            if (is_array($data['instance'])) {
                $result = StoreCart::updateMultiple($data);
                $quantity = 0;
                foreach ($data['pQty'] as $q) {
                    $quantity += $q;
                }

                $added = 0;
                foreach ($result as $r) {
                    $added += $r['added'];
                }
            } else {
                $result = StoreCart::update($data);
                $added = $result['added'];
                $quantity = (int) $data['pQty'];
            }

            $returndata = ['success' => true, 'quantity' => $quantity, 'action' => 'update', 'added' => $added];

            echo json_encode($returndata);
        }
        exit();
    }

    public function remove()
    {
        $token = $this->app->make('token');
        if ($this->request->request->all() && $token->validate('community_store')) {
            $instanceID = $this->request->request->get('instance');
            StoreCart::remove($instanceID);
            $returndata = ['success' => true, 'action' => 'remove'];
            echo json_encode($returndata);
        }
        exit();
    }

    public function clear()
    {
        $token = $this->app->make('token');

        if ($this->request->request->all() && $token->validate('community_store')) {
            StoreCart::clear();
            $returndata = ['success' => true, 'action' => 'clear'];
            echo json_encode($returndata);
        }
        exit();
    }

    public function getmodal()
    {
        $c = Page::getCurrentPage();
        $al = Section::getBySectionOfSite($c);
        $langpath = '';
        if (null !== $al) {
            $langpath = $al->getCollectionHandle();
        }

        $cart = StoreCart::getCart();
        $discounts = StoreCart::getDiscounts();
        $totals = Calculator::getTotals();

        $total = $totals['subTotal'];

        $app = \Concrete\Core\Support\Facade\Application::getFacadeApplication();
        $token = $app->make('token');

        $cartMode = Config::get('community_store.cartMode');

        if (Filesystem::exists(DIR_BASE . '/application/elements/cart_modal.php')) {
            View::element('cart_modal', ['cart' => $cart, 'cartMode'=>$cartMode, 'total' => $total, 'discounts' => $discounts, 'actiondata' => $this->request->request->all(), 'token' => $token, 'langpath' => $langpath]);
        } else {
            View::element('cart_modal', ['cart' => $cart, 'cartMode'=>$cartMode, 'total' => $total, 'discounts' => $discounts, 'actiondata' => $this->request->request->all(), 'token' => $token, 'langpath' => $langpath], 'community_store');
        }

        exit();
    }

    public function getCartSummary()
    {
        $totals = Calculator::getTotals();
        $itemCount = StoreCart::getTotalItemsInCart();
        $total = $totals['total'];
        $subTotal = $totals['subTotal'];
        $shippingTotal = $totals['shippingTotal'];
        $csm = $this->app->make('cs/helper/multilingual');

        $taxes = $totals['taxes'];
        $formattedtaxes = [];

        foreach ($taxes as $tax) {
            // translate tax name
            $tax['name'] = $csm->t($tax['name'] , 'taxRateName', null, $tax['id']);
            $tax['taxamount'] = Price::format($tax['taxamount']);
            $formattedtaxes[] = $tax;
        }

        if (!Session::get('community_store.smID')) {
            $shippingTotalRaw = false;
        } else {
            $shippingTotalRaw = $shippingTotal;
        }

        $data = ['subTotal' => Price::format($subTotal), 'total' => Price::format($total), 'itemCount' => $itemCount, 'totalCents' => $total * 100, 'taxes' => $formattedtaxes, 'shippingTotalRaw' => $shippingTotalRaw, 'shippingTotal' => Price::format($shippingTotal)];
        echo json_encode($data);

        exit();
    }
}
