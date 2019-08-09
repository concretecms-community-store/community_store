<?php
defined('C5_EXECUTE') or die("Access Denied.");

use \Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Price;
use \Concrete\Core\Support\Facade\Url;

$app = \Concrete\Core\Support\Facade\Application::getFacadeApplication();

$weightLabel = Config::get('community_store.weightUnit');
?>


<table class="table">
    <tr>
        <th><?= t('Product'); ?></th>
        <th><?= t('SKU'); ?></th>
        <th><?= t('Active'); ?></th>
        <th><?= t('Configuration'); ?></th>
        <th class="text-right"><?= t('Price'); ?></th>
        <th class="text-right"><?= t('Sale Price'); ?></th>
        <th><?= t('Shipping Data'); ?></th>
        <th  class="text-right"><?= t('Stock Level'); ?></th>
    </tr>

    <?php
    $odd = false;


    foreach ($products as $product) {
        $odd = !$odd;
        ?>
        <tr class="<?= $odd ? 'active' : ''; ?>">
            <td>
                <a href="<?= Url::to('/dashboard/store/products/edit/', $product->getID()) ?>"><?= h($product->getName()); ?></a>
            </td>

            <td>
                <?= h($product->getSKU()); ?>
            </td>

            <td>
                <?php
                if ($product->isActive()) {
                    echo "<span class='label label-success'>" . t('Active') . "</span>";
                } else {
                    echo "<span class='label label-default'>" . t('Inactive') . "</span>";
                }
                ?>
            </td>



            <td>
                <?php  if ($product->hasVariations()) {
                    echo '<span class="label label-default">' . t('Base Product') . '</span>';
                  } ?>

            </td>

            <td  class="text-right">
                <?= Price::format($product->getPrice()); ?>
            </td>
            <td  class="text-right">
                <?= $product->getSalePrice() != '' ? Price::format($product->getSalePrice()) : ''; ?>
            </td>

            <td>
                <?php
                if ($product->isShippable()) {

                    $packages = $product->getPackages();

                    $packagestring = '';

                    if (!empty($packages)) {
                        foreach ($packages as $package) {
                            $packagestring .= $package->getWeight() . $weightLabel . ', ' . $package->getWidth() . 'x' . $package->getHeight() . 'x' . $package->getLength() . "<br />";
                        }
                    }

                    echo trim($packagestring);
                }
                ?>

            </td>
            <td  class="text-right">
                <?php
                if ($product->hasVariations()) {
                    echo '<span class="label label-info">' . t('Multiple') . '</span>';
                } else {
                    echo($product->isUnlimited() ? '<span class="label label-default">' . t('Unlimited') . '</span>' : floatval($product->getQty()));
                } ?>


            </td>

        </tr>

        <?php $variations = $product->getVariations(); ?>

        <?php if (count($variations) > 0) { ?>
            <?php foreach ($variations as $variation) {
                $product->setVariation($variation);

                ?>
                <tr class="<?= $odd ? 'active' : ''; ?>">
                    <td></td>

                    <td>
                        <?= h($product->getSKU()); ?>
                    </td>
                    <td></td>
                    <td>
                        <?php
                        $options = $variation->getOptions();

                        foreach ($options as $option) {
                            echo '<span class="label label-primary">' . h($option->getOptionItem()->getOption()->getName()) . ': ';
                            echo  h($option->getOptionItem()->getName()) . '</span> ';
                        }
                        ?>
                    </td>

                    <td class="text-right">
                        <?= Price::format($product->getPrice()); ?>
                    </td>
                    <td class="text-right">
                        <?= $product->getSalePrice() != '' ? Price::format($product->getSalePrice()) : ''; ?>
                    </td>

                    <td>
                        <?php
                        if ($product->isShippable()){

                        $packages = $product->getPackages();

                        $packagestring = '';

                        if (!empty($packages)) {
                            foreach ($packages as $package) {
                                $packagestring .= $package->getWeight() . $weightLabel . ', ' . $package->getWidth() . 'x' . $package->getHeight() . 'x' . $package->getLength() . "<br />";
                            }
                        }

                        echo trim($packagestring);
                        }
                        ?>
                    </td>
                    <td class="text-right">
                        <?php echo($product->isUnlimited() ? '<span class="label label-default">' . t('Unlimited') . '</span>' : floatval($product->getQty())); ?>
                    </td>
                </tr>
            <?php } ?>
        <?php } ?>


    <?php } ?>


</table>
