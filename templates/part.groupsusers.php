<?php

/**
 * @type array $_
 */

$user_id = $_['user_id'];

$groupsusers = isset($_['groupsusers']) && is_array($_['groupsusers'])
    ? $_['groupsusers']
    : [];

$nogroup = isset($_['nogroup']) && is_array($_['nogroup'])
    ? $_['nogroup']
    : [];

$deprecated_users = ['collab_user', $user_id];


if ( $groupsusers ) :  ?>

    <?php foreach ($groupsusers as $g_name => $users) : ?>

        <div class="gu_line gu_line_group">
            <span class="gu_item">
                <input id="group_<?=$g_name?>" type="checkbox" name="groups[]" value="<?=$g_name?>" class="groupname">
                <label for="group_<?=$g_name?>" class="font_bold"> <span></span> <?=$g_name?></label>
            </span>
        </div>

        <div class="gu_line">
            <?php foreach ($users as $user) : if(in_array($user['uid'], $deprecated_users)) continue; ?>
                <span class="gu_item">
                    <input id="user_<?=$user['uid']?>" type="checkbox" name="users[]" value="<?=$user['uid']?>" data-group="<?=$g_name?>" data-email="<?=$user['email']?>">
                    <label for="user_<?=$user['uid']?>"> <span></span> <?=$user['displayname']?> </label>
                </span>
            <?php endforeach;?>
        </div>

    <?php endforeach;?>

<?php endif;?>

<?php if ( $nogroup ) :  ?>
    <div class="gu_line gu_line_group">
            <span class="gu_item">
                <input id="nogroup" type="checkbox" name="nogroup[]" value="nogroup" class="groupname">
                <label for="nogroup" class="font_bold"> <span></span> Members who do not belong to any group </label>
            </span>
    </div>
    <div class="gu_line">
        <?php foreach ($nogroup as $ngu) : if(in_array($ngu['uid'], $deprecated_users)) continue; ?>
            <span class="gu_item">
                <input id="user_<?=$ngu['uid']?>" type="checkbox" name="nogroup_users[]" value="<?=$ngu['uid']?>" data-group="nogroup" data-email="<?=$ngu['email']?>">
                <label for="user_<?=$ngu['uid']?>"> <span></span> <?=$ngu['displayname']?$ngu['displayname']:$ngu['uid'] ?> </label>
            </span>
        <?php endforeach;?>
    </div>
<?php endif;?>