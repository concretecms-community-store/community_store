<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Utilities;

use Controller;
use User;

class ProductFinder extends Controller
{
    public function getProductMatch()
    {
        $u = new User();
        if (!$u->isLoggedIn()) {
            echo "Access Denied";
            exit;
        }
        if (!$_GET['q']) {
            echo "Access Denied";
            exit;
        } else {
            $query = $_GET['q'];
            $db = $this->app->make('database')->connection();
            $results = $db->query('SELECT * FROM CommunityStoreProducts WHERE pName LIKE ? OR pSKU LIKE ? ', ['%' . $query . '%', '%' . $query . '%']);
            $resultsArray = [];

            if ($results) {
                foreach ($results as $result) {
                    $resultsArray[] = ['pID' => $result['pID'], 'name' => $result['pName'], 'SKU' => $result['pSKU']];
                }
            }
            echo json_encode($resultsArray);
        }
    }
}
