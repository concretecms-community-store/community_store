    <?php
defined('C5_EXECUTE') or die(_("Access Denied."));
?>

<?php

$groupViews = ['view','groupadded'];
$groupEdits = ['add','edit'];

if (in_array($controller->getTask(),$groupViews)){ ?>

    <div class="ccm-dashboard-header-buttons">
        <a href="<?= \URL::to('/dashboard/store/products/groups/', 'add')?>" class="btn btn-primary"><?= t("Add Product Group")?></a>
    </div>

    <?php if($grouplist){ ?>
    <div class="ccm-dashboard-content-full">
        <table class="ccm-search-results-table">
            <thead>
            <tr>
                <th><a><?= t('Group Name')?></a></th>
                <th><a><?= t('Products')?></a></th>
            </tr>
            </thead>
            <tbody>

             <?php foreach($grouplist as $group){?>
               <tr>
                <td><a href="<?= \URL::to('/dashboard/store/products/groups/edit/', $group->getGroupID())?>"><?= $group->getGroupName()?></a></td>
                <td>
                <?php
                    $products = $group->getProducts();
                    if ($products && count($products) > 0) {
                        foreach ($products as $product) {
                            echo '<span class="label label-primary">' . $product->getProduct()->getName() . '</span> ';
                        }
                    } else {
                        echo '<em>'. t('None') . '</em>';
                    }
                    ?>


                </td>
                </tr>


            <?php } ?>
             </tbody>

        </table>

    <?php } else { ?>

        <div class="alert alert-info"><?= t("You have not added a group yet")?></div>

    <?php } ?>

<?php }  ?>

<?php if (in_array($controller->getTask(),$groupEdits)){ ?>
    <form method="post" action="<?= $view->action($controller->getTask())?><?= $group->getGroupID() ? '/' .$group->getGroupID()  : '' ;?>">
        <?= $token->output('community_store'); ?>
        <div class="form-group">
            <?= $form->label('groupName',t("Group Name")); ?>
            <?= $form->text('groupName',$group->getGroupName(), array('required'=>'required')); ?>
        </div>

        <label><?= t('Products within group');?></label>
                <?php $products = $group->getProducts(); ?>
                <ul class="list-group multi-select-list <?= count($products) == 0 ? 'hidden' : ''; ?>" id="group-products">
                    <?php

                    if ($products && count($products) > 0) {
                        foreach ($products as $product) {
                            echo '<li class="list-group-item">' . $product->getProduct()->getName() . '<input type="hidden" name="products[]" value="'.$product->getProduct()->getID().'" /><a><i class="pull-right fa fa-minus-circle"></i></a></li>';
                        }
                    }
                    ?>
                </ul>

                <div class="form-group" id="product-search">
                    <input name="relatedpID" id="product-select"    style="width: 100%" placeholder="<?= t('Search for a Product') ?>" />
                </div>

                <script type="text/javascript">

                    $(function(){
                        $("#product-select").select2({
                            ajax: {
                                url: "<?= \URL::to('/productfinder')?>",
                                dataType: 'json',
                                quietMillis: 250,
                                data: function (term, page) {
                                    return {
                                        q: term // search term
                                    };
                                },
                                results: function (data) {
                                    var results = [];
                                    $.each(data, function(index, item){
                                        results.push({
                                            id: item.pID,
                                            text: item.name + (item.SKU ? ' (' + item.SKU + ')' : '')
                                        });
                                    });
                                    return {
                                        results: results
                                    };
                                },
                                cache: true
                            },
                            minimumInputLength: 2,
                            initSelection: function(element, callback) {
                                callback({});
                            }
                        }).select2('val', []);

                        $('#product-select').on("change", function(e) {
                            var data = $(this).select2('data');
                            $('#group-products').removeClass('hidden').append('<li class="list-group-item">'+ data.text  +'<a><i class="pull-right fa fa-minus-circle"></i> <input type="hidden" name="products[]" value="' + data.id + '" /></a> </li>');
                            $(this).select2("val", []);
                        });

                        $('#group-products').on('click', 'a', function(){
                            $(this).parent().remove();
                        });


                    });

                </script>


        <div class="ccm-dashboard-form-actions-wrapper">
            <div class="ccm-dashboard-form-actions">
                <a href="<?= \URL::to('/dashboard/store/products/groups')?>" class="btn btn-default"><?= t('Cancel')?></a>
                <button class="pull-right btn btn-primary" type="submit"><?= ($group->getGroupID() > 0 ? t('Update') : t('Add'))?></button>
            </div>
        </div>


    </form>


    <?php } ?>