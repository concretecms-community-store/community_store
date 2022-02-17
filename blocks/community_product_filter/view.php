<?php
defined('C5_EXECUTE') or die("Access Denied.");
use Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Price;
$csm = $app->make('cs/helper/multilingual');

?>

<div class="store-product-filter-block" >

    <form class="<?= ($updateType == 'auto' ? 'store-product-filter-block-auto' : ''); ?>" <?= ($jumpAnchor ? 'id="filter-'. $bID .'"' : ''); ?>>

        <?php
        if (!empty($filterData)) {
            foreach ($filterData as $akhandle => $data) {
                $ak = $attributes[$akhandle];
                ?>
                <div class="form-group mb-3">

                    <h3 class="store-product-filter-block-option-title">
                    <?php if ($data['type'] == 'attr') { ?>
                        <?= h($csm->t($data['label'] ? $data['label'] : $ak->getAttributeKeyName(), 'productAttributeName', null, $ak->getAttributeKeyID())); ?>
                    <?php } elseif ($data['type'] == 'price') { ?>
                      <?= t($data['label'] ? $data['label']  : t('Price')); ?>
                    <?php } ?>
                    </h3>

                    <div class="store-product-filter-block-options">
                    <?php if ($data['type'] == 'attr') { ?>
                        <?php
                        $optiondata = $data['data'];


                        $matchingType = $attrFilterTypes[$akhandle]['matchingType'];
                        $invalidHiding = $attrFilterTypes[$akhandle]['invalidHiding'];

                    foreach ($optiondata as $option => $count) {
                        $checked = false;
                        $disabled = false;
                        $show = true;

                        if (isset($selectedAttributes[$akhandle]) && in_array($option, $selectedAttributes[$akhandle])) {
                            $checked = true;
                        } else {
                            if ($count == 0 && $matchingType == 'and') {
                                $disabled = true;
                                if ($invalidHiding == 'hide') {
                                    $show = false;
                                }
                            }
                        }
                        ?>

                        <?php if ($show) { ?>
                        <div class="store-product-filter-block-option <?= ($disabled ? 'disabled' : ''); ?>">
                            <label>
                                <input type="checkbox" data-matching="<?= $matchingType; ?>"
                                    <?= ($disabled ? 'disabled="disabled"' : ''); ?>
                                    <?= ($checked ? 'checked="checked"' : ''); ?>
                                       value="<?= h($option); ?>" name="<?= $akhandle; ?>[]"/>
                                <span class="store-product-filter-block-option">
                                    <?php
                                    if ('boolean' == $ak->getAttributeType()->getAttributeTypeHandle()) {
                                        // if dealing with a boolean use the label if exists
                                        $checkboxSettings = $ak->getAttributeKeySettings();
                                        $checkboxLabel = false;

                                        // I believe that method might not be availablein earlier versions of C5
                                        if (method_exists($checkboxSettings, 'getCheckboxLabel')) {
                                            $checkboxLabel = $checkboxSettings->getCheckboxLabel();
                                        }

                                        $checkboxLabel = empty($checkboxLabel) ? $option : $checkboxLabel;

                                        echo h($csm->t($checkboxLabel, 'productAttributeLabel'));
                                    } else {
                                        echo h($csm->t($option, 'productAttributeValue'));
                                    }

                                    ?>
                                    <?php if ($showTotals && ($matchingType == 'and' || ($matchingType == 'or' && !key_exists($akhandle, $selectedAttributes)))) { ?>
                                    <span class="store-product-filter-block-count">(<?= $count; ?>)</span>
                                    <?php } ?>

                                </span></label>
                        </div>
                    <?php } ?>
                    <?php }
                    } elseif ($data['type'] == 'price') { ?>

                        <?php if ($minPrice != $maxPrice) { ?>

                        <div data-role="rangeslider">

                            <input type="hidden" class="js-range-slider" name="price" value=""
                                   data-type="double"
                                   data-min="<?= $minPrice; ?>"
                                   data-max="<?= $maxPrice; ?>"
                                   data-from="<?= $minPriceSelected; ?>"
                                   data-to="<?= $maxPriceSelected; ?>"
                                   data-input-values-separator="-"
                                   data-skin="round"
                                   data-prefix="<?= \Concrete\Core\Support\Facade\Config::get('community_store.symbol'); ?>"
                                   data-force-edges="true"
                            />

                        </div>

                        <script>
                            $(document).ready(function () {
                                $(".js-range-slider").ionRangeSlider({
                                    <?php if ($updateType == 'auto') { ?>
                                    onFinish: function() {
                                        communityStore.submitProductFilter($('.js-range-slider'));
                                    }
                                    <?php } ?>
                                });
                            });

                        </script>
                    <?php } else { ?>
                        <?php echo Price::format($minPrice); ?>
                    <?php } ?>

                    <?php } ?>
                    </div>

                </div>


            <?php } ?>

        <?php } ?>

        <?php if ($updateType == 'button') { ?>
            <p><button type="submit"
                    class="store-btn-filter btn btn-block btn-primary"><?= ($filterButtonText ? t($filterButtonText) : t('Filter')); ?></button></p>
        <?php } ?>

        <?php if ($displayClear && (!empty($selectedAttributes) || $priceFiltering)) { ?>
            <p><button type="submit"
                    class="store-btn-filter-clear btn btn-default btn-block btn-secondary"><?= ($clearButtonText ? t($clearButtonText) : t('Clear')); ?></button></p>
        <?php } ?>
    </form>
</div>
