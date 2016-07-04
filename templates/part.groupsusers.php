
<?php if (isset($_['groupsusers']) && is_array($_['groupsusers']) ) :  ?>

    <?php foreach ($_['groupsusers'] as $g_name => $users) : ?>

        <div class="gu_line">
            <span class="gu_item">
                <input id="group_<?=$g_name?>" type="checkbox" name="groups[]" value="<?=$g_name?>" class="groupname">
                <label for="group_<?=$g_name?>" class="font_bold"> <span></span> <?=$g_name?></label>
            </span>
        </div>

        <div class="gu_line">
        <?php foreach ($users as $user) : ?>
            <span class="gu_item">
                <input id="user_<?=$user['uid']?>" type="checkbox" name="users[]" value="<?=$user['uid']?>" data-group="<?=$g_name?>">
                <label for="user_<?=$user['uid']?>"> <span></span> <?=$user['displayname']?> </label>
            </span>
        <?php endforeach;?>
        </div>

    <?php endforeach;?>

<?php endif;?>
