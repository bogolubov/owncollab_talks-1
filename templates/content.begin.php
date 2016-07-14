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

        <div class="tbl">
            <div class="tbl_cell talk_title_inline"><?php p($l->t('Start a Talk')); ?></div>
            <div class="tbl_cell text_right"><input type="submit" value="Submit"></div>
        </div>

        <div class="talk-title">
            <input type="text" name="title" autocomplete="off" required
                   value="<?= $title?>" placeholder="<?php p($l->t('Title of the Talk'))?>" >
        </div>

        <div class="talk-message">
            <textarea name="message" required placeholder="<?php p($l->t('Enter your text here')); ?>"></textarea>
        </div>

        <div class="talk_title">Upload file</div>


        <div class="actions creatable" style=""><a href="#" class="button new" data-original-title="" title=""><img src="/core/img/actions/add.svg" alt="New"></a>
            <div id="uploadprogresswrapper">
                <div id="uploadprogressbar"></div>
                <button class="stop icon-close" style="display:none">
					<span class="hidden-visually">
						Cancel upload</span>
                </button>
            </div>
        </div>


        <div class="talk_title">Attachements file</div>

        <!--
        <div class="choose-file">
            <input type="text" id="uploadFile" placeholder="<?php //p($l->t('Choose File')); ?>" class="left"/>
            <div class="fileUpload btn btn-default right">
                <span id="fileUploadSpan"><?php //p($l->t('Choose')); ?></span>
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
            <div class="font_bold">Attachements</div>
            <a id="ajax-showfiles"><?php /*p($l->t('Choose files from saved')); */?></a>
            <div id="loadimg" class="loadimg">
                <img src="/core/img/loading-small.gif">
            </div>
            <div class="clear"></div>
            <div id="attach-files"></div>
        </div>
-->
        <div class="talk-subscribers">
            <div class="talk_title">Subscribers</div>
            <?php print_unescaped($this->inc("part.groupsusers")); ?>
        </div>

        <div class="right clear"><input type="submit" value="Submit"></div>
    </form>
</div>