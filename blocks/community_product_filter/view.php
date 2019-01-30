<?php
defined('C5_EXECUTE') or die("Access Denied."); ?>

<div class="store-product-filter-block">

    <form class="<?= ($updateType == 'auto' ? 'store-product-filter-block-auto' : ''); ?>">

        <?php
        if (!empty($filterData)) {
            foreach ($filterData as $akhandle => $data) { ?>
                <div class="form-group">
                    <?php if ($data['type'] == 'attr') { ?>
                        <?php
                        $data = $data['data'];

                        $ak = $attributes[$akhandle];
                        $matchingType = $attrFilterTypes[$akhandle]['matchingType'];
                        $invalidHiding = $attrFilterTypes[$akhandle]['invalidHiding'];

                        ?>
                        <h3><?= $ak->getAttributeKeyName(); ?></h3>

                        <?php

                        // Use to fetch type of attribute for different display
                        // $type = $ak->getAttributeType()->getAttributeTypeHandle();

                    foreach ($data as $option => $count) {
                        $checked = false;
                        $disabled = false;
                        $show = true;

                        if (isset($selectedAttributes[$akhandle]) && in_array($option, $selectedAttributes[$akhandle])) {
                            $checked = true;
                        } else {
                            if ($count == 0) {
                                $disabled = true;
                                if ($invalidHiding == 'hide') {
                                    $show = false;
                                }
                            }
                        }
                        ?>

                        <?php if ($show) { ?>
                        <div class="<?= ($count == 0 ? 'disabled' : ''); ?>">
                            <label>
                                <input type="checkbox" data-matching="<?= $matchingType; ?>"
                                    <?= ($disabled ? 'disabled="disabled"' : ''); ?>
                                    <?= ($checked ? 'checked="checked"' : ''); ?>

                                       value="<?php echo h($option); ?>" name="<?php echo $akhandle; ?>[]"/>
                                <span class="store-product-filter-block-option"><?php echo $option; ?> <span
                                            class="store-product-filter-block-count">(<?php echo $count; ?>)</span></span></label>
                        </div>
                    <?php } ?>
                    <?php }
                    } elseif ($data['type'] == 'price') { ?>

                        <?php if ($minPrice != $maxPrice) { ?>
                        <h3><?= t('Price'); ?></h3>
                        <div data-role="rangeslider">

                            <input type="hidden" class="js-range-slider" name="price" value=""
                                   data-type="double"
                                   data-min="<?= $minPrice; ?>"
                                   data-max="<?= $maxPrice; ?>"
                                   data-from="<?= $minPriceSelected; ?>"
                                   data-to="<?= $maxPriceSelected; ?>"
                                   data-input-values-separator="-"
                                   data-skin="round"
                                   data-prefix="<?= \Config::get('community_store.symbol'); ?>"
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
                    <?php } ?>

                    <?php } ?>

                </div>


            <?php } ?>

        <?php } ?>
        <?php if ($updateType == 'button') { ?>
            <button type="submit"
                    class="store-btn-filter btn btn-default"><?= ($filterButtonText ? t($filterButtonText) : t('Filter')); ?></button>
        <?php } ?>

        <?php if ($displayClear) { ?>
            <button type="submit"
                    class="store-btn-filter-clear btn btn-default"><?= ($clearButtonText ? t($clearButtonText) : t('Clear')); ?></button>
        <?php } ?>
    </form>
</div>
