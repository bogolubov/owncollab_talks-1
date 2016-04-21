<?php
if (!empty($_['talk'])) {
    $id = $_['talk']['id'];
    $messageid = $_['talk']['mid'];
    $title = "Re:".$_['talk']['title'];
    $text = \OCA\Owncollab_Talks\Helper::generateRepliedText($_['talk']['text'], $_['talk']['author'], date("D, d M Y H:i:s", strtotime($_['talk']['date']))); //TODO: Написати функцію обробки вхідного тексту
    $selectedsubscribers = explode(',', $_['talk']['subscribers']);
    if (!($_['talk']['author'] == $_['user'])) {
        $selectedsubscribers[] = $_['talk']['author'];
    }
    $userstatus = $_['userstatus'];
}
$cancheckusers = ($_['user'] == $_['talk']['author'] || in_array($_['user'], array_column($_['subscribers']['Managers'], 'uid'))) ? true : false;
?>

<div class="begin-talk">
    <form action="/index.php/apps/owncollab_talks/send" method="post" id="begin-talk" enctype="multipart/form-data">

    <div class="left"><h2>
        <span data-original-title="Start s Talk" class="has-tooltip" title=""><?php p($l->t('Start a Talk'));?></span>
    </h2></div>
    <div class="right claer"><input type="submit" value="Submit"></div>
    <div class="talk-title">
        <input type="text" name="title" value="<?=$title;?>"<?php if (!$title) { ?> placeholder="<?php p($l->t('Title of the Talk'));?>"<?php } ?>>
    </div>
    <div class="clear"></div>
    <div class="talk-body">
        <textarea name="message-body" id="message-body" placeholder="<?php p($l->t('Enter your text here'));?>"><?php if ($text) { echo "\n\n\n".$text; } ?></textarea>

    </div>

    <div class="choose-file">
        <input type="text" id="uploadFile" placeholder="<?php p($l->t('Choose File'));?>" class="left" />
        <div class="fileUpload btn btn-default right">
            <span id="fileUploadSpan"><?php p($l->t('Choose'));?></span>
            <input class="upload" type="file" name="uploadfile" id="uploadBtn">
        </div>
    </div>

    <div class="clear uploadedfiles">
        <ul></ul>
        <div id="uploadimg" class="loadimg">
            <img src="/core/img/loading-small.gif">
        </div>
    </div>

    <div class="talk-attachements">
        <a id="ajax-showfiles"><?php p($l->t('Choose files from saved'));?></a>
        <div id="loadimg" class="loadimg">
            <img src="/core/img/loading-small.gif">
        </div>
        <div class="clear"></div>
        <div id="attach-files"></div>
    </div>
    <div class="talk-subscribers">
    <?php foreach ($_['subscribers'] as $group => $users) { ?>
    <fieldset class="usergroup">
        <div class="group-name">
            <input type="checkbox" name="groups[]" value="<?=$group;?>" class="groupname" id="<?=$group;?>">
            <label for="<?=$group;?>"> <span></span> <?=$group;?></label>
        </div>
        <div class="group-users" id="<?=$group;?>_users">
        <?php foreach ($users as $u => $user) {
            if (!($user['uid'] == $_['user'])) { ?>
            <div class="group-user">
                <div class="oneline">
                    <input name="users[]" type="checkbox" value="<?=$user['uid'];?>" id="<?=$group.'-'.$user['uid'];?>"
                        <?php if (in_array($user['uid'],$selectedsubscribers) && $userstatus[$user['uid']] < 3 && !($user['uid'] == $_['user'])) { ?> checked<?php } ?>
                        <?php /* if (!$cancheckusers || $userstatus[$user['uid']] == 3) { ?> disabled<?php } */ ?>
                    ><label for="<?=$group.'-'.$user['uid'];?>"> <span></span> <?=$user['displayname'];?></label>
                </div>
            </div>
        <?php
            }
        }
        ?>
	    <div class="clear"></div>

        </div>
    </fieldset>
    <?php } ?>

    <?php /* if (!$cancheckusers) {
        $uarray = array();
        foreach ($users as $u => $user) {
            if (!in_array($user['uid'], $uarray)) {
                $uarray[] = $user['uid']; ?>
                <input type="hidden" name="users[]" value="<?=$user['uid'];?>">
            <?php }
        }
    } */ ?>
    <input type="hidden" name="replyid" value="<?=$_['replyid'];?>">
    <input type="hidden" name="talkhash" value="<?=$_['talkhash'];?>">
    </div>

    <div class="right clear"><input type="submit" value="Submit"></div>
    </form>
</div>
