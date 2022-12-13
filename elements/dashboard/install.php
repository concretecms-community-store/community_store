<?php

use Concrete\Core\Form\Service\Form;
use Concrete\Core\Support\Facade\Application;
use Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\PreInstaller;

$application = Application::getFacadeApplication();

/** @var Form $form */
$form = $application->make('helper/form');

/** @var PreInstaller $controller */
$controller = $application->make(PreInstaller::class);
?>

<div class="alert alert-info mb-2"><?= t('Welcome to the Community Store installer, please let us know what to install besides the base package') ?></div>

<fieldset>
    <legend>General</legend>

    <div class="form-group">
        <label for="gtName" class="form-label"><?= t('') ?></label>
        <select type="text" id="gtName" name="gtName" value="Group" class="form-control ccm-input-select">
            <option value="">sdfsd</option>
            <option value="">sdfsd</option>
        </select>
    </div>

    <div class="form-group">
        <label for="gtName" class="form-label"><?= t('Which page template can we use for the Product page type?') ?></label>
        <?= $form->select('pageTemplate', $controller->getPageTemplates()) ?>
    </div>

    <div class="form-group">
        <div class="form-check">
            <input type="checkbox" id="createParentProductPage" name="createParentProductPage" class="form-check-input form-check-input" checked value="1">
            <label for="createParentProductPage" class="form-check-label"><?= t('Create parent product page') ?></label>
        </div>
    </div>
</fieldset>
