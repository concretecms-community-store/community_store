<?php
namespace Concrete\Package\CommunityStore\Block\CommunityUtilityLinks;

use Concrete\Core\Block\BlockController;
use Core;
use Page;
use Concrete\Package\CommunityStore\Src\CommunityStore\Cart\Cart as StoreCart;
use Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Price as StorePrice;
use Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Calculator as StoreCalculator;

class Controller extends BlockController
{
    protected $btTable = 'btCommunityUtilityLinks';
    protected $btInterfaceWidth = "450";
    protected $btWrapperClass = 'ccm-ui';
    protected $btInterfaceHeight = "400";
    protected $btDefaultSet = 'community_store';

    public function getBlockTypeDescription()
    {
        return t("Add your cart links for Community Store");
    }

    public function getBlockTypeName()
    {
        return t("Utility Links");
    }

    public function view()
    {
        $itemcount = StoreCart::getTotalItemsInCart();
        $this->set("itemCount", $itemcount);

        if ($itemcount > 0) {
            $totals = StoreCalculator::getTotals();

            if ($totals['total'] > 0) {
                $this->set('total', StorePrice::format($totals['total']));
            } else {
                $this->set('total', '');
            }
        } else {
            $this->set('total', '');
        }

        $c = Page::getCurrentPage();
        $path = $c->getCollectionPath();

        $inCheckout = false;
        $inCart = false;

        if ('/checkout' == $path) {
            $inCheckout = true;
        }

        if ('/cart' == $path) {
            $inCart = true;
        }

        $this->set('inCheckout', $inCheckout);
        $this->set('inCart', $inCart);
    }

    public function registerViewAssets($outputContent = '')
    {
        $this->requireAsset('javascript', 'jquery');
        $js = \Concrete\Package\CommunityStore\Controller::returnHeaderJS();
        $this->addFooterItem($js);
        $this->requireAsset('javascript', 'community-store');
        $this->requireAsset('css', 'community-store');
    }

    public function save($args)
    {
        $args['showCartItems'] = isset($args['showCartItems']) ? 1 : 0;
        $args['showCartTotal'] = isset($args['showCartTotal']) ? 1 : 0;
        $args['showSignIn'] = isset($args['showSignIn']) ? 1 : 0;
        $args['showCheckout'] = isset($args['showCheckout']) ? 1 : 0;
        $args['showGreeting'] = isset($args['showGreeting']) ? 1 : 0;
        $args['popUpCart'] = isset($args['popUpCart']) ? 1 : 0;
        parent::save($args);
    }

    public function validate($args)
    {
        $e = Core::make("helper/validation/error");
        if ("" == $args['cartLabel']) {
            $e->add(t('Cart Label must be set'));
        }
        if (strlen($args['cartLabel']) > 255) {
            $e->add(t('Cart Link Label exceeds 255 characters'));
        }
        if (strlen($args['itemsLabel']) > 255) {
            $e->add(t('Cart Items Label exceeds 255 characters'));
        }

        return $e;
    }
}
