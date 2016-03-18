<? if (!empty($_['subscribers'])) {
    $selectedsubscribers = explode(',', $_['subscribers']);
}
?>

<div class="begin-talk">
    <form action="/index.php/apps/owncollab_talks/send" method="post" id="begin-talk">

        <div class="talk-subscribers">
        <? foreach ($_['subscribers'] as $group => $users) { ?>
        <fieldset class="usergroup">
            <div class="group-name">
                <input type="checkbox" value="<?=$group;?>" class="groupname"><label><?=$group;?></label>
            </div>
            <div class="group-users" id="<?=$group;?>_users">
            <? foreach ($users as $u => $user) { ?>
                <div class="group-user">
                    <input type="checkbox" name="users[]" value="<?=$user['uid'];?>" id="<?=$user['uid'];?>"><label><?=$user['displayname'];?></label>
                </div>
            <? } ?>
            </div>
        </fieldset>
        <? } ?>
        <input type="hidden" name="replyid" value="<?=$messageid;?>">
        </div>

    </form>
</div>
