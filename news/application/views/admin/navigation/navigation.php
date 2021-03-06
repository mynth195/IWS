<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script src="<?php echo base_url(); ?>assets/admin/plugins/sortable/Sortable.min.js"></script>



<div class="row">
    <div class="col-lg-6 col-md-6 col-sm-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><?php echo trans("navigation"); ?></h3><br>
                <small><?php echo trans("navigation_exp"); ?></small>
            </div>
            <!-- /.box-header -->

            <!-- form start -->
            <?php echo form_open('category_controller/add_category_post'); ?>

            <input type="hidden" name="parent_id" value="0">
            <div class="box-body">
                <?php $this->load->view('admin/includes/_messages'); ?>
                <div id="accordion" data-parent-id="0" data-item-type="none" class="panel-group nested-sortable navigation-editable main-nav-item-container">
                    <div class="panel panel-default nav-item" style="pointer-events: none">
                        <?php if ($this->general_settings->show_home_link == 1): ?>
                            <a href="javascript:void(0)" class="btn btn-sm btn-danger btn-nav-edit btn-show-hide-home"><?php echo trans("hide"); ?></a>
                        <?php else: ?>
                            <a href="javascript:void(0)" class="btn btn-sm btn-success btn-nav-edit btn-show-hide-home"><?php echo trans("show"); ?></a>
                        <?php endif; ?>
                        <div class="panel-heading"><h4 class="panel-title"><span><?php echo trans("home"); ?></span></h4></div>
                    </div>
                    <?php foreach ($menu_links as $menu_item):
                        if ($menu_item->item_location == "main" && $menu_item->item_parent_id == 0):
                            $sub_links = get_sub_menu_links($menu_links, $menu_item->item_id, $menu_item->item_type); ?>
                            <div id="nav_item_<?php echo $menu_item->item_type . '_' . $menu_item->item_id; ?>" class="panel panel-default nav-item" data-item-id="<?php echo $menu_item->item_id; ?>" data-item-type="<?php echo $menu_item->item_type; ?>" data-have-subs-items="<?php echo (!empty($sub_links)) ? '1' : '0'; ?>">
                                <a href="<?php echo get_navigation_item_edit_link($menu_item); ?>" class="btn btn-sm btn-secondary btn-nav-edit"><i class="fa fa-edit"></i></a>
                                <a href="javascript:void(0)" onclick="<?php echo get_navigation_item_delete_function($menu_item); ?>" class="btn btn-sm btn-danger btn-nav-edit btn-nav-delete"><i class="fa fa-trash"></i></a>
                                <div class="panel-heading" data-toggle="collapse" href="#collapse_<?php echo $menu_item->item_type; ?>_<?php echo $menu_item->item_id; ?>">
                                    <h4 class="panel-title">
                                        <i class="fa fa-plus"></i>
                                        <span><?php echo $menu_item->item_name; ?><em>(<?php echo get_navigation_item_type($menu_item); ?>)</em></span>
                                    </h4>
                                </div>
                                <div id="collapse_<?php echo $menu_item->item_type; ?>_<?php echo $menu_item->item_id; ?>" class="panel-collapse collapse">
                                    <div class="panel-body nested-sortable panel-body-sublinks" data-parent-id="<?php echo $menu_item->item_id; ?>" data-item-type="<?php echo $menu_item->item_type; ?>">
                                        <?php foreach ($sub_links as $sub_link): ?>
                                            <div id="nav_item_<?php echo $sub_link->item_type . '_' . $sub_link->item_id; ?>" class="list-group-item nav-item" data-item-id="<?php echo $sub_link->item_id; ?>" data-item-type="<?php echo $sub_link->item_type; ?>">
                                                <?php echo $sub_link->item_name; ?>
                                                <a href="<?php echo get_navigation_item_edit_link($sub_link); ?>" class="btn btn-sm btn-secondary btn-nav-edit"><i class="fa fa-edit"></i></a>
                                                <a href="javascript:void(0)" onclick="<?php echo get_navigation_item_delete_function($sub_link); ?>" class="btn btn-sm btn-danger btn-nav-edit btn-nav-delete"><i class="fa fa-trash"></i></a>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif;
                    endforeach; ?>
                </div>
            </div>
            <?php echo form_close(); ?><!-- form end -->
        </div>
        <div class="alert alert-danger alert-large">
            <strong><?php echo trans("warning"); ?>!</strong>&nbsp;&nbsp;<?php echo trans("nav_drag_warning"); ?>
        </div>
    </div>

</div>

<script>
    var moved_item_id = null;
    var nestedSortables = [].slice.call(document.querySelectorAll('.nested-sortable'));

    // Loop through each nested sortable element
    for (var i = 0; i < nestedSortables.length; i++) {
        new Sortable(nestedSortables[i], {
            group: 'nested',
            animation: 100,
            fallbackOnBody: true,
            swapThreshold: 0.65,
            onEnd: function (event) {
                var parent_id = event.to.getAttribute('data-parent-id');
                var parent_type = event.to.getAttribute('data-item-type');
                var new_order = event.newIndex;
                var item_id = event.item.getAttribute('data-item-id');
                var item_type = event.item.getAttribute('data-item-type');
                var is_item_has_sub_items = event.item.getAttribute('data-have-subs-items');
                if ((parent_type != 'none' && item_type != parent_type) || (is_item_has_sub_items == 1 && parent_type != 'none')) {
                    swal({
                        text: '<?php echo trans("invalid"); ?>',
                        icon: "warning",
                        dangerMode: true,
                    }).then(function (willDelete) {
                        location.reload();
                    });
                } else {
                    var menu_items = [];
                    var order = 1;
                    $(".main-nav-item-container > .nav-item").each(function () {
                        var item_id = $(this).attr("data-item-id");
                        var menu_item = {
                            "parent_id": 0,
                            "new_order": order,
                            "item_id": item_id,
                            "item_type": $(this).attr("data-item-type")
                        };
                        menu_items.push(menu_item);
                        order++;

                        //sub items
                        var div_id = $(this).attr("id");
                        var order_sub_item = 1;
                        $("#" + div_id + " .nav-item").each(function () {
                            var menu_item = {
                                "parent_id": item_id,
                                "new_order": order_sub_item,
                                "item_id": $(this).attr("data-item-id"),
                                "item_type": $(this).attr("data-item-type")
                            };
                            menu_items.push(menu_item);
                            order_sub_item++;
                        });
                    });
                    var data = {
                        'json_menu_items': JSON.stringify(menu_items)
                    };
                    data[csfr_token_name] = $.cookie(csfr_cookie_name);
                    $.ajax({
                        type: "POST",
                        url: base_url + "admin_controller/sort_menu_items",
                        data: data,
                        success: function (response) {
                        }
                    });

                }
            },
        });
    }
    $(document).on('click', '.navigation-editable .panel-heading', function () {
        if ($(this).find('i').hasClass('fa-plus')) {
            $(this).find('i').removeClass('fa-plus');
            $(this).find('i').addClass('fa-minus');
        } else {
            $(this).find('i').removeClass('fa-minus');
            $(this).find('i').addClass('fa-plus');
        }
    });
    $(document).on('click', '.btn-show-hide-home', function () {
        if ($(this).hasClass('btn-danger')) {
            $(this).removeClass('btn-danger');
            $(this).addClass('btn-success');
            $(this).text("<?php echo trans("show"); ?>");
        } else {
            $(this).removeClass('btn-success');
            $(this).addClass('btn-danger');
            $(this).text("<?php echo trans("hide"); ?>");
        }
        var data = {};
        data[csfr_token_name] = $.cookie(csfr_cookie_name);
        $.ajax({
            type: "POST",
            url: base_url + "admin_controller/hide_show_home_link",
            data: data,
            success: function (response) {
            }
        });
    });
</script>


