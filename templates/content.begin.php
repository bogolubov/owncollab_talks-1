<?php

style('files', 'files');
style('owncollab_talks', 'trumbowyg/trumbowyg');
script('owncollab_talks', 'libs/trumbowyg/trumbowyg');

$title = '';
$text = '';

?>
<div class="begin-talk">

    <form action="/index.php/apps/owncollab_talks/save_talk" method="post" id="begin-talk" enctype="multipart/form-data">

        <input name="id" hidden="hidden" type="text" value="">
        <input name="uid" hidden="hidden" type="text" value="<?php p($_['user_id']); ?>">
        <div id="share_list_elements"></div>

        <div class="tbl">
            <div class="tbl_cell talk_title_inline"><?php p($l->t('Start a Talk')); ?></div>
            <div class="tbl_cell text_right"><input type="submit" value="Submit"></div>
        </div>

        <div class="talk-title">
            <input type="text" name="title" autocomplete="off" required
                   value="<?= $title?>" placeholder="<?php p($l->t('Title of the Talk'))?>" >
        </div>

        <div class="talk-message">
            <textarea name="message" style="display: none" required placeholder="<?php p($l->t('Enter your text here')); ?>"></textarea>
        </div>


        <div class="tbl">
            <div class="tbl_cell valign_top width50">
                <div class="talk_title">Upload file</div>
                <div id="upload_box">
                    <div id="uploadfile_plugin"></div>
                </div>
            </div>
            <div class="tbl_cell valign_top">
<!--                <div class="talk_title">Attachements file</div>
                <div id="attach_files_btn" class="btn_default">Choice file</div>
                <div id="attach_files"></div>-->
            </div>
        </div>

        <div class="talk-subscribers">
            <div class="talk_title">Subscribers</div>
            <?php print_unescaped($this->inc("part.groupsusers")); ?>
        </div>

        <div class="right clear"><input type="submit" value="Submit"></div>

    </form>

</div>



