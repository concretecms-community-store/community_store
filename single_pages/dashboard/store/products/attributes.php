<?php
defined('C5_EXECUTE') or die(_("Access Denied."));
$addViews = array('select_type','add','edit');
if (isset($key)) { ?>

    <form method="post" action="<?= $this->action('edit')?>" id="ccm-attribute-key-form">

        <?php  View::element("attribute/type_form_required", array('category' => $category, 'type' => $type, 'key' => $key)); ?>
        <?php
        $atHandle  = $type->getAttributeTypeHandle();
        if ($atHandle == "number"){ ?>
          <div class="form-group">
            <label class="control-label">Filter Settings</label>
            <div class="checkbox">
              <label>
                <input type="checkbox" name="enableNumericSlider" id="enableNumericSlider" <?php echo isset($enableNumericSlider) && $enableNumericSlider == 1 ? "checked value='1'" : "value='0'"?>> Enable slider
              </label>
            </div>
            <label for="sliderStepValue" class="control-label">Slider step value </label>
            <input type="text" class="form-control" name="sliderStepValue" id="sliderStepValue" value=" <?php echo isset($sliderStepValue) ? $sliderStepValue : "" ?>">
          </div>
        <?php } ?>
    </form>

<?php  } elseif (in_array($controller->getTask(),$addViews)) { ?>


	<?php  if (isset($type)) { ?>
		<form method="post" action="<?= $this->action('add')?>" id="ccm-attribute-key-form">
		    <?php  View::element("attribute/type_form_required", array('category' => $category, 'type' => $type)); ?>
        <?php
        $atHandle  = $type->getAttributeTypeHandle();
         if ($atHandle == "number"){ ?>
        <div class="form-group">
          <label class="control-label">Filter Settings</label>
          <div class="checkbox">
            <label>
              <input type="checkbox" name="enableNumericSlider" id="enableNumericSlider"> Enable slider
            </label>
          </div>
          <label for="sliderStepValue" class="control-label">Slider step value </label>
          <input type="text" class="form-control" name="sliderStepValue" id="sliderStepValue">
        </div>
        <?php } ?>
    </form>
	<?php  } ?>

<?php  } else {

	View::element('dashboard/attributes_table', array('category' => $category, 'attribs'=> $attrList, 'editURL' => '/dashboard/store/products/attributes')); ?>

	<form method="get" class="form-horizontal" action="<?= $this->action('select_type')?>" id="ccm-attribute-type-form">

    	<div class="form-group">
        	<div class="col-xs-12">
        	<?= $form->label('atID', t('Add Attribute'))?>
        	</div>
        	<div class="input">
                <div class="col-xs-7">
            	<?= $form->select('atID', $types)?>
                </div>
                <div class="col-xs-3">
            	<?= $form->submit('submit', t('Add'), array('class'=>'btn-primary'))?>
                </div>
        	</div>
    	</div>

	</form>


<?php  } ?>
