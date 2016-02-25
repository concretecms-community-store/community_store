<?php
namespace Concrete\Package\CommunityStore\Controller\SinglePage;

use PageController;
use View;

use \Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product as StoreProduct;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Cart\Cart as StoreCart;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Discount\DiscountRule as StoreDiscountRule;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Discount\DiscountCode as StoreDiscountCode;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Calculator as StoreCalculator;

defined('C5_EXECUTE') or die(_("Access Denied."));

class Cart extends PageController
{
    public function view()
    {
        $codeerror = false;
        $codesuccess = false;

        if ($this->post()) {
            if ($this->post('action') == 'code' && $this->post('code')) {
                $codesuccess = StoreDiscountCode::storeCode($this->post('code'));
                $codeerror = !$codesuccess;
            }

            if ($this->post('action') == 'update') {
                $data = $this->post();

                if (is_array($data['instance'])) {
                    $result = StoreCart::updateMutiple($data);
                    $quantity = 0;
                    foreach($data['pQty'] as $q) {
                        $quantity +=  $q;
                    }

                    $added = 0;
                    foreach($result as $r) {
                        $added += $r['added'];
                    }

                } else {
                    $result = StoreCart::update($data);
                    $added = $result['added'];
                    $quantity = (int)$data['pQty'];
                }

                $returndata = array('success' => true, 'quantity' => $quantity, 'action' => 'update', 'added' => $added);
            }

            if ($this->post('action') == 'clear') {
                StoreCart::clear();
                $returndata = array('success' => true, 'action' => 'clear');
            }

            if ($this->post('action') == 'remove') {
                $data = $this->post();
                if (isset($data['instance'])) {
                    $result = StoreCart::remove($data['instance']);
                    $returndata = array('success' => true, 'action' => 'remove');
                }
            }
        }

        $this->set('actiondata', $returndata);
        $this->set('codeerror', $codeerror);
        $this->set('codesuccess', $codesuccess);

        $this->set('cart', StoreCart::getCart());
        $this->set('discounts', StoreCart::getDiscounts());

        $totals = StoreCalculator::getTotals();

        $this->set('shippingtotal',$totals['shippingTotal']);
        $this->set('shippingEnabled', StoreCart::isShippable());

        $this->set('total', $totals['total']);
        $this->set('subTotal', $totals['subTotal']);

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
        $data = $this->post();
        $result = StoreCart::add($data);

        $added = $result['added'];

        $product = StoreProduct::getByID($data['pID']);
        $productdata['pAutoCheckout'] = $product->autoCheckout();
        $productdata['pName'] = $product->getName();

        $returndata = array('success' => true, 'quantity' => (int)$data['quantity'], 'added' => $added, 'product' => $productdata, 'action' => 'add');
        echo json_encode($returndata);
        exit();

    }

    public function code()
    {
        StoreDiscountCode::storeCode($this->post('code'));
        exit();
    }

    public function update()
    {
        $data = $this->post();

        if (is_array($data['instance'])) {
            $result = StoreCart::updateMutiple($data);
            $quantity = 0;
            foreach($data['pQty'] as $q) {
                $quantity +=  $q;
            }

            $added = 0;
            foreach($result as $r) {
                $added += $r['added'];
            }

        } else {
            $result = StoreCart::update($data);
            $added = $result['added'];
            $quantity = (int)$data['pQty'];
        }

        $returndata = array('success' => true, 'quantity' => $quantity, 'action' => 'update', 'added' => $added);

        echo json_encode($returndata);
        exit();
    }

    public function remove()
    {
        $instanceID = $_POST['instance'];
        StoreCart::remove($instanceID);
        $returndata = array('success' => true, 'action' => 'remove');
        echo json_encode($returndata);
        exit();
    }

    public function clear()
    {
        StoreCart::clear();
        $returndata = array('success' => true, 'action' => 'clear');
        echo json_encode($returndata);
        exit();
    }
}
