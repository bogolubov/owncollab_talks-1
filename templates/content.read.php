<?php

$message = (!empty($_['message']) && is_array($_['message'])) ? $_['message'] : false;

$attaches = isset($_['attachements_info']) && is_array($_['attachements_info'])
    ? $_['attachements_info']
    : [];

$messageId = $message['id'];
$messageHash = $message['hash'];

//var_dump($attaches);

if($message):
    $parent = (!empty($_['parent']) && is_array($_['parent'])) ? $_['parent'] : false;
    $subscribers = json_decode($message['subscribers'],true);
    $htmlGroups = (is_array($subscribers['groups'])) ? join(', ',$subscribers['groups']) : false;
    $htmlUsers = (is_array($subscribers['users'])) ? join(', ',$subscribers['users']) : '';

/**/
    if ($parent) {
        $messageId = $parent['id'];
        $messageHash = $parent['hash'];
    }
?>

    <input type="text" hidden="hidden" name="rid" value="<?=$messageId?>">
    <ul class="read_head">
        <li><strong>Date:</strong><?=$message['date']?></li>
        <li><strong>From:</strong><?=$message['author']?></li>
        <li><strong>To:</strong><?=( $htmlGroups ? '<strong>' . $htmlGroups. '</strong>, ' : '') . $htmlUsers ?></li>
        <li><strong>Subject:</strong><?=$message['title'];?></li>
        <?php if (!empty($attaches)): ?>
            <li><strong>Attachements:</strong>
                <ul class="read_attachements">
                    <?php foreach ($attaches as $atc): ?>
                        <li>
                            <div class="tbl">
                                <div class="tbl_cell" style="width: 20px"><img src="<?php echo $atc['preview'];?>" alt=""></div>
                                <div class="tbl_cell">
                                    <a href="<?php echo $atc['link'];?>">
                                        <?php echo $atc['info']['name'];?>
                                    </a>
                                </div>
                                <div class="tbl_cell width10 text_right"><?php echo number_format($atc['info']['size']/1024, 3, '.', ' ');?> Kb</div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </li>
        <?php endif; ?>
    </ul>

    <div class="read_body">
        <?php echo htmlspecialchars_decode($message['text']) ;?>
    </div>

    <div class="read_reply" style="display: none">
        <form id="quick-reply" method="post">
            <input type="text" hidden="hidden" name="hash" value="<?=$messageHash?>">
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
