<?php
use \OCA\Owncollab_Talks\Helper;
/**
 * @type array
 */
$parent = (isset($_['parent']) && is_array($_['parent']))
    ? $_['parent']
    : false;

$children = (isset($_['children']) && is_array($_['children']))
    ? $_['children']
    : false;

//var_dump($parent);
//var_dump($children);
//chi.author + ' ' + chi.date
//Helper::linkToRoute('owncollab_talks.main.read')
?>

<div id="message_parent">
    <div class="item_msg linker" data-id="<?=$parent['id']?>">
        <div class="msg_title"><a href="<?=Helper::linkToRoute('owncollab_talks.main.read', ['id'=>$parent['id']])?>"><?=$parent['title']?></a></div>
        <div class="msg_desc"><?=$parent['author']?> <?=date("d.m.Y H:i:s", strtotime($parent['date']))?></div>
        <div class="msg_text"><?=substr($parent['text'],0,50)?>...</div>
    </div>
</div>

<?php if($children): ?>

    <div id="messages_children">
        <?php foreach($children as $child): ?>
            <div class="item_msg linker" data-id="<?=$child['id']?>">
                <div class="msg_title"><a href="<?=Helper::linkToRoute('owncollab_talks.main.read', ['id'=>$child['id']])?>"><?=$child['title']?></a></div>
                <div class="msg_desc"><?=$child['author']?> <?=date("d.m.Y H:i:s", strtotime($child['date']))?></div>
                <div class="msg_text"><?=substr($child['text'],0,50)?>...</div>
            </div>
        <?php endforeach; ?>
    </div>

<?php else: ?>

    <div class="font_bold font_italic">No answers yet</div>

<?php endif; ?>

<form action="">
    <textarea name="reply" class="width100"></textarea>
    <p><input type="submit" value="Reply now"></p>
</form>