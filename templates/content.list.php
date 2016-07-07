<?php


$messages = (!empty($_['messages']) && is_array($_['messages'])) ? $_['messages'] : false;

if($messages):
?>

    <div class="tbl">

        <div class="tbl_cell width25 valign_top">
            <?php print_unescaped($this->inc("part.listmenu")); ?>
        </div>

        <div id="r_messages" class="tbl_cell valign_top" style="display: none"></div>

    </div>

<?php else:?>

    <div class="content_info">
        <div class="font_bold">No messages yet</div>
    </div>

<?php endif;?>