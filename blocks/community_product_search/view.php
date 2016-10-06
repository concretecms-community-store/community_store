<?php defined('C5_EXECUTE') or die('Access Denied.');

if (isset($error)) {
    ?><?php echo $error?><br/><br/><?php
}

if (!isset($query) || !is_string($query)) {
    $query = '';
}

?><form action="<?php echo $view->url($resultTargetURL)?>" method="get" class="ccm-search-block-form"><?php
    if (isset($title) && ($title !== '')) {
        ?><h3><?php echo h($title)?></h3><?php
    }
    if ($query === '') {
        ?><input name="search_paths[]" type="hidden" value="<?php echo htmlentities($baseSearchPath, ENT_COMPAT, APP_CHARSET) ?>" /><?php
    } elseif (isset($_REQUEST['search_paths']) && is_array($_REQUEST['search_paths'])) {
        foreach ($_REQUEST['search_paths'] as $search_path) {
            ?><input name="search_paths[]" type="hidden" value="<?php echo htmlentities($search_path, ENT_COMPAT, APP_CHARSET) ?>" /><?php
        }
    }
    ?><input name="query" type="text" value="<?php echo htmlentities($query, ENT_COMPAT, APP_CHARSET)?>" class="ccm-search-block-text" /><?php
    if (isset($buttonText) && ($buttonText !== '')) {
        ?> <input name="submit" type="submit" value="<?php echo h($buttonText)?>" class="btn btn-default ccm-search-block-submit" /><?php
    }

    if (isset($do_search) && $do_search) {
        if (count($results) == 0) {
            ?><h4 style="margin-top:32px"><?php echo t('There were no results found. Please try another keyword or phrase.')?></h4><?php
        } else {
            $tt = Core::make('helper/text');
            ?><div id="searchResults"><?php
                $columnClass = 'col-md-12';
                foreach ($results as $product) {

                  $options = $product->getOptions();


                  //this is done so we can get a type of active class if there's a product list on the product page
                  if(Page::getCurrentPage()->getCollectionID()==$product->getPageID()){
                      $activeclass =  'on-product-page';
                  }

              ?>

                  <div class="store-product-list-item <?= $columnClass; ?> <?= $activeclass; ?>">
                      <form   id="store-form-add-to-cart-list-<?= $product->getID()?>">
                          <h2 class="store-product-list-name"><?= $product->getName()?></h2>
                          <?php
                              $imgObj = $product->getImageObj();
                              if(is_object($imgObj)){
                                  $thumb = $ih->getThumbnail($imgObj,400,280,true);?>
                                  <p class="store-product-list-thumbnail">
                                          <a href="<?= \URL::to(Page::getByID($product->getPageID()))?>">
                                              <img src="<?= $thumb->src?>" class="img-responsive">
                                          </a>
                                  </p>
                          <?php
                              }// if is_obj
                          ?>

                          <p class="store-product-list-price">
                              <?php
                                  $salePrice = $product->getSalePrice();
                                  if(isset($salePrice) && $salePrice != ""){
                                      echo '<span class="sale-price">'.$product->getFormattedSalePrice().'</span>';
                                      echo ' ' . t('was') . ' ' . '<span class="original-price">'.$product->getFormattedOriginalPrice().'</span>';
                                  } else {
                                      echo $product->getFormattedPrice();
                                  }
                              ?>
                          </p>
                          <div class="store-product-list-description"><?= $product->getDesc()?></div>

                          <?php if(is_array($product->getAttributes())) :
                                  foreach($product->getAttributes() as $aName => $value){ ?>
                                    <div class="store-product-attributes">
                                      <strong><?= t($aName) ?>:</strong>
                                      <?= $value ?>
                                    </div>
                          <?php   }
                                endif;
                          ?>
                          <p class="store-btn-more-details-container"><a href="<?= \URL::to(Page::getByID($product->getPageID()))?>" class="store-btn-more-details btn btn-default"><?= ($pageLinkText ? $pageLinkText : t("More Details"))?></a></p>
                        

                      </form><!-- .product-list-item-inner -->
                  </div><!-- .product-list-item -->

                  <?php
                      if($i%1==0){
                          echo "</div>";
                          echo '<div class="store-product-list row store-product-list-per-row-'. $productsPerRow .'">';
                      }

                  $i++;
                }
            ?></div><?php
            $pages = $pagination->getCurrentPageResults();
            if ($pagination->getTotalPages() > 1 && $pagination->haveToPaginate()) {
                $showPagination = true;
                echo $pagination->renderDefaultView();
            }
        }
    }
?></form><?php
