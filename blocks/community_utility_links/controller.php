<?php
namespace Concrete\Package\CommunityStore\Block\CommunityUtilityLinks;

use Concrete\Core\Page\Page;
use Concrete\Core\Block\BlockController;
use Concrete\Core\Multilingual\Page\Section\Section;
use Concrete\Core\Support\Facade\Config;
use Concrete\Package\CommunityStore\Src\CommunityStore\Cart\Cart;
use Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Price;
use Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Calculator;
use Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\SalesSuspension;

class Controller extends BlockController
{
    protected $btTable = 'btCommunityUtilityLinks';
    protected $btInterfaceWidth = "450";
    protected $btWrapperClass = 'ccm-ui';
    protected $btInterfaceHeight = "460";
    protected $btDefaultSet = 'community_store';

    public function getBlockTypeDescription()
    {
        return t("Add your cart links for Community Store");
    }

    public function getBlockTypeName()
    {
        return t("Cart Links");
    }

    public function view()
    {
        $c = Page::getCurrentPage();
        $al = Section::getBySectionOfSite($c);
        $langpath = '';
        if (null !== $al) {
            $langpath = $al->getCollectionHandle();
        }

        $itemcount = Cart::getTotalItemsInCart();
        $this->set("itemCount", $itemcount);

        if ($itemcount > 0) {
            $total = Calculator::getSubTotal();

            if ($total> 0) {
                $this->set('total', Price::format($total));
            } else {
                $this->set('total', '');
            }
        } else {
            $this->set('total', '');
        }

        $collectionHandle =  $c->getCollectionHandle();

        $inCheckout = false;
        $inCart = false;

        if ('checkout' == $collectionHandle) {
            $inCheckout = true;
        }

        if ('cart' == $collectionHandle) {
            $inCart = true;
        }

        $this->set('inCheckout', $inCheckout);
        $this->set('inCart', $inCart);
        $this->set('app', $this->app);
        $this->set('langpath', $langpath);
        $this->set('shoppingDisabled', Config::get('community_store.shoppingDisabled'));
        $this->set('salesSuspended', $this->app->make(SalesSuspension::class)->salesCurrentlySuspended());
    }

    public function registerViewAssets($outputContent = '')
    {
        $this->requireAsset('javascript', 'jquery');
        $js = \Concrete\Package\CommunityStore\Controller::returnHeaderJS();
        $this->addFooterItem($js);
        $this->requireAsset('javascript', 'community-store');
        $this->requireAsset('javascript', 'sysend');

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
        $e = $this->app->make("helper/validation/error");
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
