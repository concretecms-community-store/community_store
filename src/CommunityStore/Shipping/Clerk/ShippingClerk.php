<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Shipping\Clerk;

use \DVDoug\BoxPacker\Packer as ClerkPacker;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Cart\Cart as StoreCart;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product as StoreProduct;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Shipping\Clerk\ClerkItem as StoreClerkItem;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Shipping\Clerk\ClerkPackage as StoreClerkPackage;

class ShippingClerk 
{
    /**
     * @return \DVDoug\BoxPacker\PackedBoxList
     */
    public function getPackages()
    {
        $packer = new ClerkPacker();
        $boxes = StoreClerkPackage::getPackages();
        foreach($boxes as $box){
            $packer->addBox($box);
        }
        $cartItems = StoreCart::getCart();
        foreach($cartItems as $cartItem){
            $product = StoreProduct::getByID((int)$cartItem['product']['pID']);
            $description = $product->getProductName();
            $width = $product->getDimensions('w');
            $length = $product->getDimensions('l');
            $depth = $product->getDimensions('h');
            //TODO: convert to MM if in inches format
            $weight = $product->getProductWeight();
            //TODO: convert to g if in lbs.
            $packer->addItem(new StoreClerkItem($description, $width, $length, $depth, $weight));
            //TODO: If an item doesn't fit in any box, make it it's own box.
        }
                
        return $packer->pack();
    }
}