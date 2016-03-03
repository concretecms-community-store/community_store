<?php
defined('C5_EXECUTE') or die("Access Denied.");
use \Concrete\Package\CommunityStore\Src\CommunityStore\Shipping\Method\ShippingMethod as StoreShippingMethod;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Price as StorePrice;

$eligibleMethods = StoreShippingMethod::getEligibleMethods();
$count=0;
$currentShippingID = Session::get('smID');
if (!empty($eligibleMethods)) {
foreach($eligibleMethods as $method){
?>
    <?php foreach($method->getOffers() as $offer) { ?>
    <div class="store-shipping-method">
        <div class="store-shipping-method-option radio">
            <label>
                <input type="radio" name="shippingMethod" value="<?= $offer->getKey()?>"<?php if($offer->getKey() ==  $currentShippingID|| !$currentShippingID && $count==0 ){echo " checked";}?>>
                <div class="store-shipping-details">
                <?php $rate = $offer->getRate(); ?>
                <p><?= ($offer->getLabel()) ?> - <?= $rate > 0 ? StorePrice::format($rate) : t('No Charge');?></p>
                <?= $offer->getOfferDetails(); ?>
                </div>
            </label>
        </div>
        <?php } ?>
        <?= $method->getDetails(); ?>
    </div>
<?php $count++; } ?>
<?php } else { ?>
<p class="store-no-shipping-warning alert alert-warning"><?= t('There are no shipping options to process your order.'); ?></p>
<?php } ?>