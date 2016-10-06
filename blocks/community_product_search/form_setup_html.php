<?php
defined('C5_EXECUTE') or die("Access Denied.");
$form = Loader::helper('form');
$searchWithinOther=($searchObj->baseSearchPath!=Page::getCurrentPage()->getCollectionPath() && $searchObj->baseSearchPath!='' && strlen($searchObj->baseSearchPath)>0)?true:false;

/**
 * Post to another page, get page object.
 */
$basePostPage = Null;
if (isset($searchObj->postTo_cID) && intval($searchObj->postTo_cID) > 0) {
    $basePostPage = Page::getById($searchObj->postTo_cID);
} else if ($searchObj->pagePath != Page::getCurrentPage()->getCollectionPath() && strlen($searchObj->pagePath)) {
    $basePostPage = Page::getByPath($searchObj->pagePath);
}
/**
 * Verify object.
 */
if (is_object($basePostPage) && $basePostPage->isError()) {
    $basePostPage = NULL;
}
$select_page = Loader::helper('form/page_selector');
?>

<?php if (!$controller->indexExists()) { ?>
    <div class="ccm-error"><?php echo t('The search index does not appear to exist. This block will not function until the reindex job has been run at least once in the dashboard.')?></div>
<?php } ?>

<fieldset>

    <div class='form-group'>
        <label for='title'><?php echo t('Title')?>:</label>
        <?php echo $form->text('title',$searchObj->title);?>
    </div>

    <div class='form-group'>
        <label for='buttonText'><?php echo t('Button Text')?>:</label>
        <?php echo $form->text('buttonText',$searchObj->buttonText);?>
    </div>
    <div class='form-group'>
        <label for='title' style="margin-bottom: 0px;"><?php echo t('Results Page')?>:</label>
        <div class="checkbox">
            <label for="ccm-searchBlock-externalTarget">
                <input id="ccm-searchBlock-externalTarget" name="externalTarget" type="checkbox" value="1" <?php echo (strlen($searchObj->resultsURL) || $basePostPage !== NULL)?'checked':''?> />
                <?php echo t('Post Results to a Different Page')?>
            </label>
        </div>
        <div id="ccm-searchBlock-resultsURL-wrap" class="input" style=" <?php echo (strlen($searchObj->resultsURL) || $basePostPage !== NULL)?'':'display:none'?>" >
            <?php
            if ($basePostPage !== NULL) {
                print $select_page->selectPage('postTo_cID', $basePostPage->getCollectionID());
            } else {
                print $select_page->selectPage('postTo_cID');
            }
            ?>
            <?php echo t('OR Path')?>:
            <?php echo $form->text('resultsURL',$searchObj->resultsURL);?>
        </div>
    </div>

</fieldset>
