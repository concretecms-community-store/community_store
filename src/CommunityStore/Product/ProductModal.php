<?php

namespace Concrete\Package\CommunityStore\Src\CommunityStore\Product;

use Concrete\Core\Controller\Controller;
use Concrete\Core\Routing\Redirect;
use Concrete\Core\View\View;

class ProductModal extends Controller
{
    public function getProductModal()
    {
        $pID = $this->request->query->get('pID');

        $locale = $this->request->query->get('locale');
        if ($locale) {
            \Concrete\Core\Localization\Localization::changeLocale($locale);
        }

        if ($pID) {
            $product = Product::getByID($pID);

            if ($product) {
                if (file_exists(DIR_BASE . '/application/elements/product_modal.php')) {
                    View::element('product_modal', ['product' => $product]);

                    return;
                }
                View::element('product_modal', ['product' => $product], 'community_store');

                return;
            }
        }

        return Redirect::to('/');
    }
}
