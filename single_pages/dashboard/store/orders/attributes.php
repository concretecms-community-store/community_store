<?php defined('C5_EXECUTE') or die(_("Access Denied.")); ?>

<?php $addViews = array('select_type','add','edit'); ?>

<?php if (isset($key)) { ?>
    
    <form method="post" action="<?= $this->action('edit')?>" id="ccm-attribute-key-form">
    
        <?php \View::element("attribute/type_form_required", array('category' => $category, 'type' => $type, 'key' => $key)); ?>
        <?php \View::element("attribute/type_form_order_groups", array('key' => $key, 'groups' => $groups, 'groupList' => $groupList),'community_store'); ?>
    
    </form>

<?php  } elseif (in_array($controller->getTask(),$addViews)) { ?>

    <?php  if (isset($type)) { ?>
        <form method="post" action="<?= $this->action('add')?>" id="ccm-attribute-key-form">
            <?php \View::element("attribute/type_form_required", array('category' => $category, 'type' => $type, 'key' => $key)); ?>
            <?php \View::element("attribute/type_form_order_groups", array('key' => $key, 'groups' => $groups, 'groupList' => $groupList),'community_store'); ?>
        </form> 
    <?php  } ?>
    
<?php  } else {

    \View::element('dashboard/attributes_table', array('category' => $category, 'attribs'=> $attrList, 'editURL' => '/dashboard/store/orders/attributes')); ?>

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