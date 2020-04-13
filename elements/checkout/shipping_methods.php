<?php defined('C5_EXECUTE') or die("Access Denied."); ?>
<?php
use Illuminate\Filesystem\Filesystem;
use Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Price;
use Concrete\Package\CommunityStore\Src\CommunityStore\Shipping\Method\ShippingMethod;

$app = \Concrete\Core\Support\Facade\Application::getFacadeApplication();
$eligibleMethods = ShippingMethod::getEligibleMethods();
$currentShippingID = Session::get('community_store.smID');
$count=0;
$foundOffer = false;

$csm = $app->make('cs/helper/multilingual');
?>

<?php if (!empty($eligibleMethods)) { ?>

    <?php foreach ($eligibleMethods as $method) { ?>
        <?php if ($method->getPackageHandle() != 'community_store' && Filesystem::exists(DIR_BASE . "/packages/" . $method->getPackageHandle() . "/elements/checkout/shipping_methods.php")) { ?>
            <?php View::element("checkout/shipping_methods", array('method' => $method), $method->getPackageHandle());
                $foundOffer = true;
            ?>
        <?php } else { ?>
            <?php foreach($method->getOffers() as $offer) {
                $foundOffer = true;
                ?>
                <div class="store-shipping-method">
                    <div class="store-shipping-method-option radio">
                        <label>
                            <input type="radio" name="shippingMethod" value="<?= $offer->getKey()?>"<?php if($offer->getKey() == $currentShippingID|| !$currentShippingID && $count++ == 0 ){echo " checked";}?>>
                            <div class="store-shipping-details">
                                <?php $rate = $offer->getDiscountedRate(); ?>
                                <p class="store-shipping-details-label"><?= $csm->t($offer->getLabel(), 'shippingName', false, $method->getID()); ?> - <?= $rate > 0 ? Price::format($rate) : t('No Charge');?></p>
                                <?php $details = $offer->getOfferDetails();
                                if ($details) { ?>
                                <p class="store-shipping-details-details"><?= $details; ?></p>
                                <?php } ?>
                            </div>
                        </label>
                    </div>
                </div>
            <?php } ?>
            <?= $csm->t($method->getDetails(), 'shippingDetails', false, $method->getID()); ?>

        <?php } ?>
    <?php } ?>
<?php } ?>


<?php if (empty($eligibleMethods) || !$foundOffer ) { ?>
    <p class="store-no-shipping-warning alert alert-warning"><?= t('There are no shipping options to process your order.'); ?></p>
<?php } ?>
