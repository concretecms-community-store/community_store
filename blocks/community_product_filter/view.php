<?php
defined('C5_EXECUTE') or die("Access Denied."); ?>

<div class="store-product-filter-block">

    <form class="store-product-filter-block-auto">
        <?php

        if (!empty($filterData)) {
            foreach ($filterData as $akhandle => $data) { ?>

                <div class="form-group">

                    <?php

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

                    <?php } ?>
                </div>
            <?php } ?>

        <?php } ?>
        <button type="submit" class="store-btn-filter btn btn-default">Filter Products</button>
    </form>
</div>
