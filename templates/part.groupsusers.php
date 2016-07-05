
<?php

if (isset($_['groupsusers']) && is_array($_['groupsusers']) ) :  ?>

    <?php foreach ($_['groupsusers'] as $g_name => $users) : ?>

        <div class="gu_line gu_line_group">
            <span class="gu_item">
                <input id="group_<?=$g_name?>" type="checkbox" name="groups[]" value="<?=$g_name?>" class="groupname">
                <label for="group_<?=$g_name?>" class="font_bold"> <span></span> <?=$g_name?></label>
            </span>
        </div>

        <div class="gu_line">
            <?php foreach ($users as $user) : ?>
                <span class="gu_item">
                    <input id="user_<?=$user['uid']?>" type="checkbox" name="users[]" value="<?=$user['uid']?>" data-group="<?=$g_name?>" data-email="<?=$user['email']?>">
                    <label for="user_<?=$user['uid']?>"> <span></span> <?=$user['displayname']?> </label>
                </span>
            <?php endforeach;?>
        </div>

    <?php endforeach;?>

    <?php if (isset($_['nogroup']) && is_array($_['nogroup']) ) :  ?>
        <div class="gu_line gu_line_group">
            <span class="gu_item">
                <input id="nogroup" type="checkbox" name="nogroup[]" value="nogroup" class="groupname">
                <label for="nogroup" class="font_bold"> <span></span> Members who do not belong to any group </label>
            </span>
        </div>
        <div class="gu_line">
            <?php foreach ($_['nogroup'] as $ng_user) : ?>
                <span class="gu_item">
                <input id="user_<?=$ng_user['uid']?>" type="checkbox" name="nogroup_users[]" value="<?=$ng_user['uid']?>" data-group="nogroup" data-email="<?=$ng_user['email']?>">
                <label for="user_<?=$ng_user['uid']?>"> <span></span> <?=$ng_user['displayname']?$ng_user['displayname']:$ng_user['uid'] ?> </label>
            </span>
            <?php endforeach;?>
        </div>
    <?php endif;?>

<?php endif;?>
