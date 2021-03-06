<?php
$message = $_['message'];
$talk = $_['talk']; 
$isadmin = in_array($_['user'], array_column($_['subscribers']['Managers'], 'uid')) ? true : false;
$isauthor = $_['user'] == $_['message']['author'] ? true : false;
$files = $_['files'];
$filenames = !empty($message['attachements']) ? $files->getByIdList(explode(',', $message['attachements']), $_['user']) : array();
$filetypes = array('file', 'file', 'folder', 'file', 'application-pdf', 'file', 'file', 'image', 'file', 'file', 'text', 'file', 'x-office-document', 'x-office-spreadsheet', 'x-office-presentation', 'file', 'audio', 'file', 'video');
?>

<div class="read-message">
    <div class="talk-info">
        <div><div class="rowheader">Date: </div><?=$message['date'];?></div>
        <div><div class="rowheader">From: </div><?=$message['author'];?></div>
        <div><div class="rowheader">To: </div><?=$message['subscribers'];?></div>
        <div><div class="rowheader">Subject: </div><strong><?=$message['title'];?></strong></div>
    </div>
    <div class="talk-body">
        <?=$message['text'];?>
    </div>
    <?php if ($message['attachements']) { ?>
    <div class="talk-attachements">
    <?php foreach ($filenames as $f => $file) {
        $link = $file['mimetype'] == 2 ? "/index.php/apps/files?dir=/".$file['file'] : "/index.php/apps/files/ajax/download.php?dir=%2F&files=".$file['name'];
        ?>
        <div class="attached-file">
            <div class="thumbnail" style="background-image: url(&quot;/core/img/filetypes/<?=$filetypes[$file['mimetype']];?>.svg&quot;);"></div>
            <a href="<?=$link;?>" class="name">
                <span class="nametext" style="left:inherit;">
                    <span class="innernametext"><?=$file['name'];?></span>
                </span>
                <span currentuploads="0" class="uploadtext"></span>
            </a>
            <div class="filesize"><?=\OCA\Owncollab_Talks\Helper::sizeRoundedString($file['size']);?></div>
            <div class="clear"></div>
        </div>
    <?php } ?>
        <div class="clear"></div>
    </div>
    <?php } ?>
    <div class="message-buttons">
        <button <?php if ($message['status'] < 3 && $talk['status'] < 3) { ?>data-link="reply" <?php } else { ?>data-link="no-reply" class="disabled"<?php } ?>>Reply</button>
        <button data-link="back" id="HistoryBack">Back</button>
        <input type="hidden" value="<?=$message['mid'];?>" id="messageId"/>
        <input type="hidden" value="<?=$_['user'];?>" id="userId"/>
    </div>
</div>
