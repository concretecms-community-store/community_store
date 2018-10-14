<?php
namespace Concrete\Package\CommunityStore\Controller\SinglePage;

use PageController;
use Config;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product as StoreProduct;
use Concrete\Package\CommunityStore\Src\CommunityStore\Cart\Cart as StoreCart;
use Concrete\Package\CommunityStore\Src\CommunityStore\Discount\DiscountRule as StoreDiscountRule;
use Concrete\Package\CommunityStore\Src\CommunityStore\Discount\DiscountCode as StoreDiscountCode;
use Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Calculator as StoreCalculator;

defined('C5_EXECUTE') or die("Access Denied.");

class Cart extends PageController
{
    public function view($action = '')
    {
        if ('all' == Config::get('community_store.shoppingDisabled')) {
            $this->redirect("/");
        }

        $codeerror = false;
        $codesuccess = false;

        $returndata = [];

        if ($this->post()) {
            if ('code' == $this->post('action')) {
                $codeerror = false;
                $codesuccess = false;

                if ($this->post('code')) {
                    $codesuccess = StoreDiscountCode::storeCartCode($this->post('code'));
                    $codeerror = !$codesuccess;
                } else {
                    StoreDiscountCode::clearCartCode();
                }
            }

            if ('update' == $this->post('action')) {
                $data = $this->post();

                if (is_array($data['instance'])) {
                    $result = StoreCart::updateMutiple($data);
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

            if ('clear' == $this->post('action')) {
                StoreCart::clear();
                $returndata = ['success' => true, 'action' => 'clear'];
            }

            if ('remove' == $this->post('action')) {
                $data = $this->post();
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

        $this->set('cart', StoreCart::getCart());
        $this->set('discounts', StoreCart::getDiscounts());

        $totals = StoreCalculator::getTotals();

        if (StoreCart::isShippable()) {
            $this->set('shippingEnabled', true);

            if (\Session::get('community_store.smID')) {
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

        $discountsWithCodesExist = StoreDiscountRule::discountsWithCodesExist();
        $this->set("discountsWithCodesExist", $discountsWithCodesExist);
    }

    public function add()
    {
        if ($this->post()) {
            $data = $this->post();
            $result = StoreCart::add($data);

            $added = $result['added'];

            $error = 0;

            if ($result['error']) {
                $error = 1;
            }

            $product = StoreProduct::getByID($data['pID']);
            $productdata['pAutoCheckout'] = $product->autoCheckout();
            $productdata['pName'] = $product->getName();

            $returndata = ['quantity' => $data['quantity'], 'added' => $added, 'product' => $productdata, 'action' => 'add', 'error' => $error];
            echo json_encode($returndata);
        }
        exit();
    }

    public function code()
    {
        StoreDiscountCode::storeCartCode($this->post('code'));
        exit();
    }

    public function update()
    {
        if ($this->post()) {
            $data = $this->post();

            if (is_array($data['instance'])) {
                $result = StoreCart::updateMutiple($data);
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
        if ($this->post()) {
            $instanceID = $_POST['instance'];
            StoreCart::remove($instanceID);
            $returndata = ['success' => true, 'action' => 'remove'];
            echo json_encode($returndata);
        }
        exit();
    }

    public function clear()
    {
        if ($this->post()) {
            StoreCart::clear();
            $returndata = ['success' => true, 'action' => 'clear'];
            echo json_encode($returndata);
        }
        exit();
    }
}
