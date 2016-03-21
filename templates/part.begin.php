<?
if (!empty($_['talk'])) {
    $id = $_['talk']['id'];
    $messageid = $_['talk']['mid'];
    $title = "Re:".$_['talk']['title'];
    $text = \OCA\Owncollab_Talks\Helper::generateRepliedText($_['talk']['text'], $_['talk']['author'], date("D, d M Y H:i:s", strtotime($_['talk']['date']))); //TODO: Написати функцію обробки вхідного тексту
    $selectedsubscribers = explode(',', $_['talk']['subscribers']);
    $userstatus = $_['userstatus'];
}
$cancheckusers = ($_['user'] == $_['talk']['author'] || in_array($_['user'], array_column($_['subscribers']['Managers'], 'uid'))) ? true : false;
?>

<div class="begin-talk">
    <form action="/index.php/apps/owncollab_talks/send" method="post" id="begin-talk">
    <div class="talk-title">
        <div class="rowheader">Subject: </div>
        <input type="text" name="title" value="<?=$title;?>">
    </div>
    <div class="clear"></div>
    <div class="talk-body">
        <textarea name="message-body" id="message-body"><?="\n\n\n".$text;?></textarea>
        <script language="JavaScript">
            $('#message-body').trumbowyg();
        </script>

    </div>
        <div class="talk-subscribers">
        <?php foreach ($_['subscribers'] as $group => $users) { ?>
        <fieldset class="usergroup">
            <div class="group-name">
                <input type="checkbox" name="groups[]" value="<?=$group;?>" class="groupname"><label><?=$group;?></label>
            </div>
            <div class="group-users" id="<?=$group;?>_users">
            <?php foreach ($users as $u => $user) { ?>
                <div class="group-user">
                    <input type="checkbox" name="users[]" value="<?=$user['uid'];?>" id="<?=$user['uid'];?>"
                           <?php if (in_array($user['uid'],$selectedsubscribers) && $userstatus[$user['uid']] < 3) { ?> checked<?php } ?>
                           <?php if (!$cancheckusers || $userstatus[$user['uid']] == 3) { ?> disabled<?php } ?>
                    ><label><?=$user['displayname'];?></label>
                </div>
            <?php } ?>
            </div>
        </fieldset>
        <?php } ?>
        <?php if (!$cancheckusers) {
            $uarray = array();
            foreach ($users as $u => $user) {
                if (!in_array($user['uid'], $uarray)) {
                    $uarray[] = $user['uid']; ?>
                    <input type="hidden" name="users[]" value="<?=$user['uid'];?>">
                <?php }
            }
        } ?>
        <input type="hidden" name="replyid" value="<?=$messageid;?>">
        </div>

        <!-- TODO: Створити аттачменти -->
        <div class="talk-attachements">
            <a id="ajax-showfiles">Attach files</a>
            <div id="loadimg" class="loadimg">
                <img src="/core/img/loading-small.gif">
            </div>
            <div class="clear"></div>
            <div id="attach-files"></div>
        </div>

    <button id="send">Send</button>
    </form>
</div>
