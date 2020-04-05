<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Utilities;

use Concrete\Core\Controller\Controller;
use Concrete\Core\User\User;

class ProductFinder extends Controller
{
    public function getProductMatch()
    {
        $u = new User();
        if (!$u->isRegistered()) {
            echo "Access Denied";
            exit;
        }
        if (!$this->request->query->get('q')) {
            echo "Access Denied";
            exit;
        } else {
            $query = $this->request->query->get('q');
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
