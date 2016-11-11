<?php

use \OCA\Owncollab_Talks\Helper;


/**
 * @type array
 */
$parent = (isset($_['parent']) && is_array($_['parent']))
    ? $_['parent']
    : [];

/**
 * @type array
 */
$children = (isset($_['children']) && is_array($_['children']))
    ? $_['children']
    : [];
//
//var_dump(attachCount($parent));
//var_dump($children[0]['attachements']);

function attachCount(array $message){
    $attachements = 0;
    if(!empty($message['attachements'])){
        try{
            $attachements = count(json_decode($message['attachements'], true));
        }catch(\Exception $e){}
    }
    return $attachements;
};

?>

<div id="message_parent">
    <div class="item_msg linker" data-id="toreadmsg" data-link="<?=$parent['id']?>">
        <div class="msg_title"><a href="<?=Helper::linkToRoute('owncollab_talks.main.read', ['id'=>$parent['id']])?>"><?php p($parent['title'])?></a></div>
        <div class="msg_desc">
            <?php
                $downcount = Helper::dateDowncounter($parent['date']);
                if ($downcount['days'] == 1) echo "One day ago";
                else if ($downcount['days'] > 1) echo $downcount['days']." days ago";
                else if ($downcount['hours'] == 1 ) echo "One hour ago";
                else if ($downcount['hours'] > 1 ) echo $downcount['hours'] . " hours ago";
                else if ($downcount['minutes'] <= 10 ) echo "A few minutes ago";
                else if ($downcount['minutes'] > 10 ) echo $downcount['minutes'] . " minutes ago";
            ?>
            &nbsp;<strong> <?=$parent['author']?> </strong>
        </div>
        <div class="msg_text"><?php p(substr(strip_tags(htmlspecialchars_decode($parent['text'])), 0, 50 ))?>...</div>
        <?php if(attachCount($parent) > 0):?>
            <div class="msg_attach">
                <strong>
                <?php echo attachCount($parent) == 1
                    ? "Attachment file"
                    : "Attachments ".attachCount($parent)." files";
                ?>
                </strong>
            </div>
        <?php endif;?>
    </div>
</div>

<?php if($children): ?>

    <div id="messages_children">
        <?php foreach($children as $child): ?>
            <div class="item_msg linker" data-id="toreadmsg" data-link="<?=$child['id']?>">
                <div class="msg_title"><a href="<?=Helper::linkToRoute('owncollab_talks.main.read', ['id'=>$child['id']])?>"><?php p($child['title'])?></a></div>
                <div class="msg_desc"><?=$child['author']?> <?php p(date("d.m.Y H:i:s", strtotime($child['date'])))?></div>
                <div class="msg_text"><?php p(substr(strip_tags(htmlspecialchars_decode($child['text'])),0,50))?>...</div>
                <?php
                    // test files
                    //var_dump(attachCount($child));
                ?>
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