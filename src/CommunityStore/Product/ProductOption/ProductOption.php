<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductOption;

use Database;

use \Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductOption\ProductOptionGroup as StoreProductOptionGroup;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Product\ProductOption\ProductOptionItem as StoreProductOptionItem;

class ProductOption
{
    public static function addProductOptions($data,$product)
    {
        StoreProductOptionGroup::removeOptionGroupsForProduct($product, $data['pogID']);
        StoreProductOptionItem::removeOptionItemsForProduct($product, $data['poiID']);

        $count = count($data['pogSort']);
        $ii=0;//set counter for items
        if($count>0){
            for($i=0;$i<count($data['pogSort']);$i++){

                if (isset($data['pogID'][$i])) {
                    $optionGroup = StoreProductOptionGroup::getByID($data['pogID'][$i]);

                    if ($optionGroup) {
                        $optionGroup->update($product,$data['pogName'][$i],$data['pogSort'][$i]);
                    }
                }

                if (!$optionGroup) {
                    if ($data['pogName'][$i]) {
                        $optionGroup = StoreProductOptionGroup::add($product,$data['pogName'][$i],$data['pogSort'][$i]);
                    }
                }

                if ($optionGroup) {
                    $pogID = $optionGroup->getID();
                    //add option items
                    $itemsInGroup = count($data['optGroup'.$i]);
                    if($itemsInGroup>0){
                        for($gi=0;$gi<$itemsInGroup;$gi++,$ii++){

                            if ($data['poiID'][$ii] > 0) {
                                $option = StoreProductOptionItem::getByID($data['poiID'][$ii]);
                                if ($option) {
                                    $option->update($product,$data['poiName'][$ii],$data['poiSort'][$ii],$data['poiHidden'][$ii] );
                                }
                            } else {
                                if ($data['poiName'][$ii]) {
                                    StoreProductOptionItem::add($product,$pogID,$data['poiName'][$ii],$data['poiSort'][$ii], $data['poiHidden'][$ii]);
                                }
                            }
                        }
                    }
                }

            }
        }
    }
}
