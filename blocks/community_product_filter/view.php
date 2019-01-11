<?php
defined('C5_EXECUTE') or die("Access Denied."); ?>

<div class="store-product-filter-block">

    <p>Filter</p>

    <form>
    <?php

    if (count($attribs) > 0) {
        foreach($attribs as $ak) { ?>

            <div class="form-group">
                <h4><?= $ak->render('label');?></h4>

                <?php

                $type = $ak->getAttributeType()->getAttributeTypeHandle();

                if ($type == 'select') {

                    $params = $_GET[$ak->getAttributeKeyHandle()];

                    if (!is_array($params)) {
                        $params = str_replace('|', ',', $params);
                        $params = explode(',', $params);
                    }

                    $options = $ak->getController()->getOptions();

                    foreach($options as $option) { ?>
                        <label>
                            <input type="checkbox"
                                   <?php if (in_array($option->getSelectAttributeOptionDisplayValue(), $params)) { ?>
                            checked="checked"
                                    <?php } ?>

                                   value="<?php  echo h($option->getSelectAttributeOptionDisplayValue()); ?>" name="<?php echo $ak->getAttributeKeyHandle(); ?>[]" />
                       <?php  echo $option->getSelectAttributeOptionDisplayValue(); ?></label>
                        <br />

                    <?php }

                } ?>



            </div>
        <?php  } ?>

    <?php }?>
<button type="submit" class="store-btn-filter btn btn-default">Filter Products</button>
    </form>


</div>