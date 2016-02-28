<?php
defined('C5_EXECUTE') or die("Access Denied.");
use \Concrete\Package\CommunityStore\Src\CommunityStore\Shipping\Method\ShippingMethod as StoreShippingMethod;
use \Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Price as StorePrice;

$eligibleMethods = StoreShippingMethod::getEligibleMethods();
$i=1;
if (!empty($eligibleMethods)) {
foreach($eligibleMethods as $method){
    $sessionShippingMethodID = Session::get('smID');
    if($sessionShippingMethodID == $method->getID()){
        $checked = true;
    } else {
        if($i==1){
            $checked = true;
        } else {
            $checked = false;
        }
    } 
?>
    <div class="radio">
        <label>
            <input type="radio" name="shippingMethod" value="<?= $method->getID()?>"<?php if($checked){echo " checked";}?>>
            <div class="store-shipping-details">
            <?php $rate = $method->getShippingMethodTypeMethod()->getRate(); ?>
            <p><?= $method->getName()?> - <?= $rate > 0 ? StorePrice::format($rate) : t('No Charge');?></p>
            <?= $method->getDetails(); ?>
            </div>
        </label>
    </div>
<?php $i++; } ?>
<?php } else { ?>
<p class="store-no-shipping-warning alert alert-warning"><?= t('There are no shipping options to process your order.'); ?></p>
<?php } ?>
