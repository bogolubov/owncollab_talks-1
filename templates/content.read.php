<?php

$message = (!empty($_['message'][0]) && is_array($_['message'][0])) ? $_['message'][0] : false;

if($message):

    $subscribers = json_decode($message['subscribers'],true);
    $htmlGroups = (is_array($subscribers['groups'])) ? join(', ',$subscribers['groups']) : false;
    $htmlUsers = (is_array($subscribers['users'])) ? join(', ',$subscribers['users']) : '';

?>

    <ul class="read_head">
        <li><strong>Date:</strong> <?=$message['date']?></li>
        <li><strong>From:</strong> <?=$message['author']?></li>
        <li><strong>To:</strong><?=( $htmlGroups ? '<strong>' . $htmlGroups. '</strong>, ' : '') . $htmlUsers ?></li>
        <li><strong>Subject:</strong> <strong><?=$message['title'];?></strong></li>
    </ul>

    <div class="read_body">
        <?=$message['text'];?>
    </div>

    <div class="read_reply" style="display: none">
        <form id="quick-reply">
            <input type="text" hidden="hidden" name="hash" value="<?=$message['hash']?>">
            <div class="tbl">
                <div class="tbl_cell valign_top width70"><textarea name="message" class="width100" placeholder="Answer directly"></textarea></div>
                <div class="tbl_cell valign_top">&nbsp;&nbsp;<input type="submit" value="Reply now"></div>
            </div>
        </form>
    </div>

    <div class="read_btns">
        <button class="linker" data-id="msg_reply">Reply</button>
        <button class="linker" data-id="msg_back">Back</button>
    </div>

<?php else:?>

    <div class="content_info">
        <div class="font_bold">No message</div>
    </div>

<?php endif;?>
