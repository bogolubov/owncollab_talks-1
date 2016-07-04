<?php

style('files', 'files');
style('owncollab_talks', 'trumbowyg/trumbowyg');
script('owncollab_talks', 'libs/trumbowyg/trumbowyg');


$title = '';
$text = '';

?>
<div class="begin-talk">

    <form action="/index.php/apps/owncollab_talks/save_talk" method="post" id="begin-talk" enctype="multipart/form-data">

        <div class="left">
            <span data-original-title="Start s Talk" class="has-tooltip"
                  title=""><?php p($l->t('Start a Talk')); ?></span>
        </div>

        <div class="right claer"><input type="submit" value="Submit"></div>
        <div class="talk-title">
            <input type="text" name="title" autocomplete="off" required
                   value="<?= $title; ?>" <?php if (!$title) { ?> placeholder="<?php p($l->t('Title of the Talk')); ?>"<?php } ?>>
        </div>
        <div class="clear"></div>
        <div class="talk-body">
            <textarea name="message-body" id="message-body" required
                      placeholder="<?php p($l->t('Enter your text here')); ?>"><?php if ($text) {
                    echo "\n\n\n" . $text;
                } ?></textarea>
        </div>

        <div class="choose-file">
            <input type="text" id="uploadFile" placeholder="<?php p($l->t('Choose File')); ?>" class="left"/>
            <div class="fileUpload btn btn-default right">
                <span id="fileUploadSpan"><?php p($l->t('Choose')); ?></span>
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
            <a id="ajax-showfiles"><?php p($l->t('Choose files from saved')); ?></a>
            <div id="loadimg" class="loadimg">
                <img src="/core/img/loading-small.gif">
            </div>
            <div class="clear"></div>
            <div id="attach-files"></div>
        </div>
        <div class="talk-subscribers">
            subscribers
        </div>

        <div class="right clear"><input type="submit" value="Submit"></div>
    </form>
</div>