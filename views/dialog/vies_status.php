<?php

defined('C5_EXECUTE') or die('Access Denied.');
/**
 * @var Concrete\Package\CommunityStore\Controller\Dialog\ViesStatus $controller
 * @var Concrete\Core\View\DialogView $view
 * @var bool|null $vowAvailable
 * @var array[] $countryStatuses
 * @var string $viesError
 */

if ($viesError !== '') {
    ?>
    <div class="alert alert-danger">
        <?= nl2bt(h($viesError)) ?>
    </div>
    <?php
}

if ($vowAvailable === true) {
    ?>
    <div class="alert alert-success">
        <?= t('The VIES service is currently available.') ?>
    </div>
    <?php
} elseif ($vowAvailable === false) {
    ?>
    <div class="alert alert-danger">
        <?= t('The VIES service is currently NOT available.') ?>
    </div>
    <?php
}
if ($countryStatuses !== []) {
    ?>
    <table class="table table-sm caption-top">
        <colgroup>
            <col width="1" />
        </colgroup>
        <caption>
            <?= t('Country-specific states')?>
        </caption>
        <tbody>
            <?php
            foreach ($countryStatuses as $countryStatus) {
                ?>
                <tr>
                    <th>
                        <code><?= h($countryStatus['countryCode'])?></code>
                    </th>
                    <th>
                        <?= h($countryStatus['countryName']) ?>
                    </th>
                    <td>
                        <span class="<?= $countryStatus['available'] ? 'text-success' : 'text-danger' ?>">
                            <?= h($countryStatus['status']) ?>
                        </span>
                    </td>
                </tr>
                <?php
            }
            ?>
        </tbody>
    </table>
    <?php
}
