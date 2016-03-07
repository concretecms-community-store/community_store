<?php 
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Utilities;

use Controller;
use User;
use Database;

defined('C5_EXECUTE') or die(_("Access Denied."));

class ProductFinder extends Controller
{
    public function getProductMatch()
    {
        $u = new User();
        if (!$u->isLoggedIn()) {
            echo "Access Denied";
            exit;
        }
        if (!$_POST['query']) {
            echo "Access Denied";
            exit;
        } else {
            $query = $_POST['query'];
            $db = \Database::connection();
            $results = $db->query('SELECT * FROM CommunityStoreProducts WHERE pName LIKE "%'.$query.'%"');

            if ($results) {
                foreach ($results as $result) {
                    ?>
        
                <li data-product-id="<?= $result['pID']?>"><?= $result['pName']?></li>
        
            <?php 
                } //for each
            } else { //if no results ?>
                <li><?= t("I can't find a product by that name")?></li>
            <?php 
            }
        }
    }
}
