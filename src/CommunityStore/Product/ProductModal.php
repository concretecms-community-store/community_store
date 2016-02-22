<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Product;

use Concrete\Core\Controller\Controller;
use View;
use Illuminate\Filesystem\Filesystem;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product as StoreProduct;

class ProductModal extends Controller
{
    public function getProductModal()
    {
        $pID = $this->post('pID');
        $product = StoreProduct::getByID($pID);
        if (Filesystem::exists(DIR_BASE."/application/elements/product_modal.php")) {
            View::element("product_modal", array("product" => $product));
        } else {
            View::element("product_modal", array("product" => $product), "community_store");
        }
    }
}
