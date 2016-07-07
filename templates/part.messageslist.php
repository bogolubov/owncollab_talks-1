<?php

use \OCA\Owncollab_Talks\Helper;


/**
 * @type array
 */
$parent = (isset($_['parent']) && is_array($_['parent']))
    ? $_['parent']
    : false;

/**
 * @type array
 */
$children = (isset($_['children']) && is_array($_['children']))
    ? $_['children']
    : false;

?>

<div id="message_parent">
    <div class="item_msg linker" data-id="toreadmsg" data-link="<?=$parent['id']?>">
        <div class="msg_title"><a href="<?=Helper::linkToRoute('owncollab_talks.main.read', ['id'=>$parent['id']])?>"><?php p($parent['title'])?></a></div>
        <div class="msg_desc"><?=$parent['author']?> <?php p(date("d.m.Y H:i:s", strtotime($parent['date'])))?></div>
        <div class="msg_text"><?php p(substr($parent['text'],0,50))?>...</div>
    </div>
</div>

<?php if($children): ?>

    <div id="messages_children">
        <?php foreach($children as $child): ?>
            <div class="item_msg linker" data-id="toreadmsg" data-link="<?=$child['id']?>">
                <div class="msg_title"><a href="<?=Helper::linkToRoute('owncollab_talks.main.read', ['id'=>$child['id']])?>"><?php p($child['title'])?></a></div>
                <div class="msg_desc"><?=$child['author']?> <?php p(date("d.m.Y H:i:s", strtotime($child['date'])))?></div>
                <div class="msg_text"><?php p(substr($child['text'],0,50))?>...</div>
            </div>
        <?php endforeach; ?>
    </div>

<?php else: ?>

    <div class="as_children font_bold font_italic">No answers yet</div>

<?php endif; ?>

<form id="quick-reply">
    <input type="text" hidden="hidden" name="hash" value="<?=$parent['hash']?>">
    <div class="tbl">
        <div class="tbl_cell valign_top width70"><textarea name="message" class="width100" placeholder="Answer directly"></textarea></div>
        <div class="tbl_cell valign_top">&nbsp;&nbsp;<input type="submit" value="Reply now"></div>
    </div>
</form>