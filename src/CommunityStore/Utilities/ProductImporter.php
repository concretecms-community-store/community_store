<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Utilities;
use Core;
use User;
use Page;
use CollectionAttributeKey;
use SplFileObject;
use CollectionVersion;
use Queue;
use Response;
use stdClass;
use Job;
use FilePermissions;
use FileImporter;
use Permissions;

use \Symfony\Component\HttpFoundation\Session\Session as SymfonySession;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product as StoreProduct;
use Concrete\Package\CommunityStore\Src\CommunityStore\Group\Group as StoreGroup;
use Concrete\Package\CommunityStore\Src\Attribute\Key\StoreProductKey;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductGroup as StoreProductGroup;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductImage as StoreProductImage;


class ProductImporter
{
  protected $product_columns = '';

  public function importCsv() {
      $error = '';
      if(\Request::isPost()) {
          $post = \Request::post();
          if (Job::authenticateRequest($post['auth'])) {

            if (isset($_FILES['csv']) && is_uploaded_file($_FILES['csv']['tmp_name'])) {
                  $file     = $_FILES['csv']['tmp_name'];
                  $filename = $_FILES['csv']['name'];
                  $ext      = pathinfo($filename, PATHINFO_EXTENSION);

                  if($ext == 'csv') {
                      $importer = new \Concrete\Core\File\Importer();
                      $result = $importer->import($file, $filename);
                      if ($result instanceof \Concrete\Core\File\Version) {

                          $path = $result->getURL();
                          $csv  = self::readCSV($path);

                          $result->delete();

                          self::sendToQueue($csv, $post['column']);

                      } else {
                          $error = $result;
                      }
                  }else {
                     $error = t('Please upload CSV file type only!');
                  }


              } else if (isset($_FILES['csv'])) {
                  $error = t('Please upload a CSV file!');
              }else {
                  $error = t('Please upload a CSV file!');
              }

          }else {
              $error = t('Access Denied');
          }
      }
      $this->set('errorMessage', $error);
  }
  protected function sendToQueue($arr = '',$columns) {

      if(!empty($arr)) {
          $product_columns['attributes'] = $arr[0];
          $queue = Queue::get('update_products');
          while($queue->count()>0){
            //delete queuemessages that were not processed
            $messages = $queue->receive();
            if($messages){
              foreach($messages as $message){
                $queue->deleteMessage($message);
              }
            }

          }
          $failed = Queue::get('failed_products');
          while($failed->count()>0){
            //delete failed products queue
            $messages = $failed->receive();
            if($messages){
              foreach($messages as $message){
                $failed->deleteMessage($message);
              }
            }

          }
          foreach($arr as $index => $pages) {
              if($index < 1) {//ignore first row
                  continue;
              }
              //send to queue
              $queue->send(serialize($pages));
          }

          $session = new SymfonySession();
          $session->set('csv_headers', $product_columns['attributes']);
          echo serialize($product_columns);
      }
      exit;

  }
  protected function readCSV($csvFile){
      $file_handle = fopen($csvFile, 'r');
      $delimiter   = self::getFileDelimiter($csvFile);
      $ctr  = 0;
      while(!feof($file_handle))
      {
          $rows = fgetcsv($file_handle, '', '\r\n','"');
          $data = preg_split("/\r/is", $rows[0]);
          if(count($data) > 1){ //excel
              foreach($data as $d){
                  $row = str_getcsv($d, ',', '"');
                  $line_of_text[] = self::unfilterNewLines($row);
              }
          }else { //openoffice
              if($delimiter == ';') {
                  $rs = preg_split("/".$delimiter."/is", $data[0]);
                  foreach($rs as $r){
                       $line_of_text[$ctr][] = self::unfilterNewLines($r);
                  }
              }else {
                  $rs = str_getcsv($data[0], ',', '"');
                  foreach($rs as $r){
                       $line_of_text[$ctr][] = self::unfilterNewLines($r);
                  }
              }

              $ctr++;
          }


      }
      fclose($file_handle);
      return $line_of_text;
  }

  public function processQueue() {
      if (!ini_get('safe_mode')) {
          @set_time_limit(0);
      }

      $response = new Response();
      $response->headers->set('Content-Type', 'application/json');

      $q = Queue::get('update_products');

      if(\Request::isPost()) {

          $post = \Request::post();

          $obj  = new stdClass;
          $obj->error = false;
          $obj->importedItems = 0;
          if (Job::authenticateRequest($post['auth'])) {

                  if($post['process']) {

                      try {
                          $product_columns = unserialize($post['attr']);
                          $messages = $q->receive(1);


                          foreach ($messages as $message) {  //loop through the queue

                              $products  = unserialize($message->body);
                              $newProduct = array();
                              $pid = null;




                              foreach ($product_columns['column'] as $tableColumnName => $csvIndex){
                                $sku = $products[$product_columns['column']['pSKU']];
                                if($tableColumnName=="pSKU"){
                                  $pid = StoreProduct::getBySKU($sku);
                                  if($pid){
                                    $newProduct['pID'] = $pid->getID();
                                  }
                                }

                                if(!empty($csvIndex) && $csvIndex!=null){
                                  $newProduct[$tableColumnName] = $products[$csvIndex];

                                } else {
                                  $newProduct[$tableColumnName] = self::getDefaultValue($tableColumnName,$sku);
                                }

                              }

                              $producterrors = $this->validate($newProduct);
                              if (!$producterrors->has()) {

                                  //upload images
                                  $images = array();

                                  if($newProduct['pfID']){
                                    if(!is_numeric($newProduct['pfID'])){
                                      $images = self::uploadProductImages($newProduct);
                                      if(empty($images['errors'])){
                                        $newProduct['pfID'] = $images['pfID'];
                                      }else{

                                        if($pid){
                                          $newProduct['pfID'] = $pid->getImageID();
                                        }else{
                                          $newProduct['pfID'] = 0;
                                        }

                                        $newProduct['error'] = $images['error'];
                                        $failed = Queue::get('failed_products');
                                        $failed->send(serialize($newProduct));
                                      }
                                    }
                                  }
                                  if(empty($images['errors'])){
                                    //save the product
                                    $product = StoreProduct::saveProduct($newProduct);

                                    //save product groups
                                    self::saveProductGroups($newProduct,$product);

                                    //save product attributes
                                    self::saveProductAttributes($newProduct, $product);


                                    //save additional images
                                    if(!empty($images['pifID'])){
                                      StoreProductImage::addImagesForProduct($images,$product);
                                    }

                                    $obj->importedItems++;
                                  }


                                  //save product user groups
                                  // StoreProductUserGroup::addUserGroupsForProduct($data,$product);

                                  //save product options
                                  // StoreProductOption::addProductOptions($data,$product);

                                  //save files
                                  // StoreProductFile::addFilesForProduct($data,$product);

                                  //save category locations
                                  // StoreProductLocation::addLocationsForProduct($data,$product);

                                  // save variations
                                  // StoreProductVariation::addVariations($data, $product);

                                  $q->deleteMessage($message);
                              }else{
                                foreach($producterrors->getList() as $error){
                                  echo $error.". ";
                                }
                                echo " Please review your table mapping.";
                                $obj->error = true;
                              }


                          }
                          $totalItems = $q->count();
                          $obj->totalItems = $totalItems;


                          if ($q->count() == 0) {
                              $obj->error = false;
                              $obj->totalItems = $totalItems;
                              $result = $result."\nWoohooh! Import successful!";

                              $obj->result = $result;
                              $failedproducts = Queue::get('failed_products');
                              if( $failedproducts->count() > 0){
                                //shows sku of failed products
                                $result = $result."\nFailed Products: ".$failedproducts->count()."\nCodes/SKUs: ";
                                while($failedproducts->count() > 0){
                                  $messages = $failedproducts->receive(1);
                                  foreach ($messages as $message) {  //loop through the queue
                                      $products  = unserialize($message->body);
                                      $result = $result." ".$products['pSKU'].",";
                                      $failedproducts->deleteMessage($message);
                                  }
                                }
                                $result = rtrim($result, ",");
                                $obj->result = $result;
                              }

                          }



                      } catch (\Exception $e) {
                          $obj->error = true;
                          $obj->message = $obj->result; // needed for progressive library.
                      }
                      $response->setStatusCode(Response::HTTP_OK);
                      $response->setContent(json_encode($obj));
                      $response->send();
                      \Core::shutdown();

                  }else {
                      $totalItems = $q->count();
                      \View::element('progress_bar', array(
                          'totalItems' => $totalItems,
                          'totalItemsSummary' => t2("%d item", "%d items", $totalItems)
                      ));
                       \Core::shutdown();

                  }
          }else {
              $obj->error = t('Access Denied');
              $response->setStatusCode(Response::HTTP_FORBIDDEN);
              $response->setContent(json_encode($obj));
              $response->send();
              \Core::shutdown();
          }

      }

  }

  protected function getFileDelimiter($file, $checkLines = 2){
      $file = new SplFileObject($file);
      $delimiters = array(
        ',',
        '\t',
        ';',
        '|',
        ':'
      );
      $results = array();
      $i = 0;
       while($file->valid() && $i <= $checkLines){
          $line = $file->fgets();
          foreach ($delimiters as $delimiter){
              $regExp = '/['.$delimiter.']/';
              $fields = preg_split($regExp, $line);
              if(count($fields) > 1){
                  if(!empty($results[$delimiter])){
                      $results[$delimiter]++;
                  } else {
                      $results[$delimiter] = 1;
                  }
              }
          }
         $i++;
      }
      $results = array_keys($results, max($results));
      return $results[0];
  }
  protected function isCore($string) {
      return (bool) preg_match('/[A-Z]/', $string);
  }

  protected function filterNewLines($val='') {
      if(!empty($val) && is_string($val)) {
          $val = nl2br($val);
          $val = str_replace('<br />', '[br]', $val);
          $val = trim(preg_replace('/\s+/', ' ', $val));
      }
      return $val;
  }

  protected function unfilterNewLines($val='') {
      if(!empty($val)) {
          $val = str_replace('[br]', "\n", $val);
      }
      return $val;
  }

  protected function getDefaultValue($tableName,$sku){
    $product = StoreProduct::getBySKU($sku);
    switch ($tableName) {
      case 'selectPageTemplate':
          $val = 5;
          break;
      case 'pWidth':
          $val = $product ? $product->getDimensions('w') : 0;
          break;
      case 'pHeight':
          $val = $product ? $product->getDimensions('h') : 0;
          break;
      case 'pLength':
          $val = $product ? $product->getDimensions('l') : 0;
          break;
      case 'pWeight':
          $val = $product ? $product->getWeight() : 0;
        break;
      case 'pPrice':
          $val = $product ? $product->getPrice() : 0;
          break;
      case 'pFeatured':
          $val = $product ? $product->isFeatured() : 0;
          break;
      case 'pQty':
          $val = $product ? $product->getQty() : 0;
          break;
      case 'pQtyUnlim':
          $val = $product ? $product->isUnlimited() : 1;
          break;
      case 'pNoQty':
          $val = $product ? $product->allowQuantity() : 0;
          break;
      case 'pTaxClass':
          $val = $product ? $product->getTaxClassID() : 1;
          break;
      case 'pTaxable':
          $val = $product ? $product->isTaxable() : 1;
          break;
      case 'pfID':
          $val = $product ? $product->getImageID() : 0;
          break;
      case 'pActive':
          $val = $product ? $product->isActive() : 0;
          break;
      case 'pShippable':
          $val = $product ? $product->isShippable() : 1;
          break;
      case 'pVariations':
          $val = $product ? $product->hasVariations() : 0;
          break;
      default:
          $val = NULL;
    }
    return $val;
  }

  protected function saveProductGroups($data,$product){
    $group = explode(',',$data['pProductGroups']);
    $list = array();
    foreach($group as $gName){
      $gName = trim($gName);
      if(!empty($gName)){
        $storedgroup = StoreGroup::getByName($gName);
        if(empty($storedgroup)){
          $storedgroup = StoreGroup::add($gName);
        }
        $list['pProductGroups'][] = $storedgroup->getGroupID();
      }
      $storedgroup = null;
    }
    if(!empty($list)){
      StoreProductGroup::addGroupsForProduct($list,$product);
    }
  }

  protected function saveProductAttributes($data, $product){
    foreach($data as $tableColumnName => $csvvalue){
      $ak = StoreProductKey::getByHandle($tableColumnName);
      if(!empty($ak)){
        $avValue = trim($csvvalue);
        if(!empty($avValue)){$avID = self::getAvIDbyValue($avValue,$ak->getAttributeKeyID());
          if($avID){
            $avValue = $avID;
          }
          $ak->saveAttribute($product,false,$avValue);
        }

      }
    }
  }
  protected function getAvIDbyValue($value, $akID){
    return StoreProductKey::getAvIDbyValue($value,$akID);
  }

  protected function uploadProductImages($data){

    $urls['pfID'] = trim($data['pfID']);
    $addtl_urls = array();
    for ($i = 1; $i < 6; $i++) {
      if(!empty(trim($data['url_upload_' .$i]))){
        $urls['pifID_'.$i] = trim($data['url_upload_' .$i]);

      }
    }
    $u = new User();

    $cf = Core::make('helper/file');
    $fp = FilePermissions::getGlobal();
    if (!$fp->canAddFiles()) {
        die(t("Unable to add files."));
    }
    $error = array();
    $fr = false;
    if (isset($data['pSKU'])) {
        // we are replacing a file
        $product = StoreProduct::getBySKU($data['pSKU']);
        if($product){

          $fr = $product->getImageObj();
          if($fr){
            $frp = new Permissions($fr);
            if (!$frp->canEditFileContents()) {
                array_push($error,t('You do not have permission to modify this file.'));
            }
          }
        }
    }

    $valt = Core::make('helper/validation/token');
    $file = Core::make('helper/file');
    Core::make('helper/mime');

    // load all the incoming fields into an array
    $incoming_urls = array();


    if (!function_exists('iconv_get_encoding')) {
        array_push($error,t('Remote URL import requires the iconv extension enabled on your server.'));
    }

    if (empty($error)) {
        foreach($urls as $key => $url){
          try {
              $request = new \Zend\Http\Request();
              $request->setUri($url);
              $client = new \Zend\Http\Client();
              $response = $client->dispatch($request);
              $incoming_urls[$key] = $url;
          } catch (\Exception $e) {
              array_push($error,$e->getMessage());
          }
        }
        if (count($incoming_urls) < 1) {
          array_push($error,t('You must specify at least one valid URL.'));
        }

    }

    $files = array();
    // if we haven't gotten any errors yet then try to process the form
    if (empty($error)) {
        // iterate over each incoming URL adding if relevant
        foreach ($incoming_urls as $key => $this_url) {
            // try to D/L the provided file
            $request = new \Zend\Http\Request();
            $request->setUri($this_url);
            $client = new \Zend\Http\Client();
            $response = $client->dispatch($request);
            if ($response->isSuccess()) {
                $headers = $response->getHeaders();
                $contentType = $headers->get('ContentType')->getFieldValue();

                $fpath = $file->getTemporaryDirectory();

                // figure out a filename based on filename, mimetype, ???
                if (preg_match('/^.+?[\\/]([-\w%]+\.[-\w%]+)$/', $request->getUri(), $matches)) {
                    // got a filename (with extension)... use it
                    $fname = $matches[1];
                } else if ($contentType) {
                    // use mimetype from http response
                    $fextension = Core::make("helper/mime")->mimeToExtension($contentType);
                    if ($fextension === false)
                        array_push($error,t('Unknown mime-type: %s', $contentType));
                    else {
                        // make sure we're coming up with a unique filename
                        do {
                            // make up a filename based on the current date/time, a random int, and the extension from the mime-type
                            $fname = date('Y-m-d_H-i_') . mt_rand(100, 999) . '.' . $fextension;
                        } while (file_exists($fpath.'/'.$fname));
                    }
                }

                if (strlen($fname)) {
                    // write the downloaded file to a temporary location on disk
                    $handle = fopen($fpath.'/'.$fname, "w");
                    fwrite($handle, $response->getBody());
                    fclose($handle);

                    // import the file into concrete
                    if ($fp->canAddFileType($cf->getExtension($fname))) {
                        $fi = new FileImporter();
                        $resp = $fi->import($fpath.'/'.$fname, $fname, $key=="pfID" ? $fr : false);
                    } else {
                        $resp = FileImporter::E_FILE_INVALID_EXTENSION;
                    }
                    if (!($resp instanceof \Concrete\Core\File\Version)) {
                        array_push($error,$fname . ': ' . FileImporter::getErrorMessage($resp));
                    } else {
                        $respf = $resp->getFile();
                        $respf->setOriginalPage($_POST['ocID']);
                        if($key=="pfID"){
                          $files[$key] = $respf->getFileID();
                        }else{

                          $str = explode('_',$key);
                          $files[$str[0]][] = $respf->getFileID();
                        }

                    }
                    // clean up the file
                    unlink($fpath.'/'.$fname);
                } else {
                    // could not figure out a file name
                    array_push($error,t(/*i18n: %s is an URL*/'Could not determine the name of the file at %s', h($this_url)));
                }
            } else {
                // warn that we couldn't download the file
                array_push($error,t(/*i18n: %s is an URL*/'There was an error downloading %s', h($this_url)));
            }
        }
    }
    if(!empty($error)){
      $files['errors'] = $error;
    }
    return $files;
  }
}
