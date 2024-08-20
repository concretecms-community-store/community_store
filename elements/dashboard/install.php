<?php

use Concrete\Core\Form\Service\Form;
use Concrete\Core\Support\Facade\Application;
use Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\PreInstaller;

$application = Application::getFacadeApplication();

/** @var Form $form */
$form = $application->make('helper/form');
$pageSelector = $application->make('helper/form/page_selector');

/** @var PreInstaller $controller */
$controller = $application->make(PreInstaller::class);
?>

<div class="alert alert-info mb-2"><?= t('Community Store will be installed with the following page related settings') ?></div>

<fieldset>
    <legend><?= t('Configuration'); ?></legend>

    <div class="form-group">
        <label for="gtName" class="form-label"><?= t('Which page template can we use for the Product page type?') ?></label>
        <?= $form->select('pageTemplate', $controller->getPageTemplates()) ?>
    </div>

    <div class="form-group">
        <div class="form-check">
            <input type="checkbox" id="createParentProductPage" name="createParentProductPage" class="form-check-input form-check-input" checked="checked" value="1">
            <label for="createParentProductPage" class="form-check-label"><?= t('Create parent product page') ?></label>
        </div>
    </div>

    <div class="form-group" id="toggleCreateProductPage">
        <label for="gtName" class="form-label"><?= t('Select a parent page for the product page') ?></label>
        <?= $pageSelector->selectPage('parentPage', 1); ?>
    </div>
</fieldset>

<script>
    let createParentProductPage = document.getElementById('createParentProductPage');
    let toggleCreateProductPage = document.getElementById('toggleCreateProductPage');

    document.addEventListener('readystatechange', (event) => {
        if (document.readyState === 'complete') {
            createParentProductPage.checked ? toggleCreateProductPage.style.display = 'block' : toggleCreateProductPage.style.display = 'none';
        }
    });

    createParentProductPage.addEventListener('change', function () {
        createParentProductPage.checked ? toggleCreateProductPage.style.display = 'block' : toggleCreateProductPage.style.display = 'none';
    });
</script>
