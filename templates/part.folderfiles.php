<?php
$files = $_['files'];
?>

<table id="filestable" data-allow-public-upload="yes" data-preview-x="32" data-preview-y="32" style="top:0px;">
    <tbody id="fileList">

    <?php
    $countfolders = 0;
    $countfiles = 0;
    $totalsize = 0;
    $filetypes = array('file', 'file', 'folder', 'file', 'application-pdf', 'file', 'file', 'image', 'file', 'file', 'text', 'file', 'x-office-document', 'x-office-spreadsheet', 'x-office-presentation', 'file', 'audio', 'file', 'video', 'x-office-document');
    $filetypes2 = array('httpd' => 'file', 'httpd/unix-directory' => 'folder', 'application' => 'application', 'application/pdf' => 'application-pdf', 'application/vnd.oasis.opendocument.text' => 'text', 'image' => 'image', 'image/jpeg' => 'image', 'application/octet-stream' => 'text-code', 'text' => 'text', 'text/plain' => 'text', 'image/png' => 'image', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'x-office-document', 'application/vnd.oasis.opendocument.spreadsheet' => 'x-office-spreadsheet', 'application/vnd.oasis.opendocument.presentation' => 'x-office-presentation', 'audio' => 'audio', 'audio/mpeg' => 'audio', 'video' => 'video', 'video/x-msvideo' => 'video', 'application/msword' => 'x-office-document');
    foreach ($files as $f => $file) {
        if ($file['permissions'] < 16) {
            break;
        }
        if ($filetypes2[$file['mimetype']] == 'folder') { // If folder
            $link = "#";
            $countfolders++;
        }
        else {
            $link = "/index.php/apps/files/ajax/download.php?dir=%2F&files=".$file['name'];
            $countfiles++;
        }
        $totalsize += $file['size'];
        $modified = \OCA\Owncollab_Talks\Helper::time_elapsed_string($file['mtime']);
        ?>

        <?php if ($file['mimetype'] == 'httpd/unix-directory') { // If folder ?>
            <tr data-icon="/core/img/filetypes/folder-shared.svg" data-share-permissions="31" data-permissions="31" data-id="<?=$file['id'];?>">
                <td class="filename">
                    <a title="" data-original-title="" href="#" class="action action-favorite "><img class="svg" alt="Favorite" src="/core/img/actions/star.svg"></a>
                    <label for="select-files-<?=$file['id'];?>">
                        <div class="thumbnail" style="background-image: url(&quot;<?=$file['icon'];?>&quot;); background-size: 32px auto;"></div>
                        <span class="hidden-visually">Select</span>
                    </label>
                    <a class="name ajax-openfolder" id="folder-<?=$file['id'];?>"">
            <span class="nametext" style="left:inherit;">
                <span class="innernametext"><?=$file['name'];?></span>
            </span>
                    <span currentuploads="0" class="uploadtext"></span>
                    </a>
                    <div id="folder-files"></div>
                </td>
                <td style="color:rgb(160,160,160); text-align:right;" class="filesize"><?=\OCA\Owncollab_Talks\Helper::sizeRoundedString($file['size']);?></td>
                <td class="date">
                    <span data-original-title="<?=date('F d, Y h:i A', $file['mtime']);?>" style="color:rgb(51,51,51)" title="" class="modified"><?php p($l->t($modified['key'], $modified['value']));?></span>
                </td>
            </tr>
            <tr style="height: 0px;">
                <td colspan="3" class="folder" id="folder-files-<?=$file['id'];?>"></td>
            </tr>
        <?php }
        else { // If file ?>
            <tr data-icon="/core/img/filetypes/folder-shared.svg" data-share-permissions="31" data-permissions="31" data-id="<?=$file['id'];?>">
                <td class="filename">
                    <a title="" data-original-title="" href="#" class="action action-favorite "><img class="svg" alt="Favorite" src="/core/img/actions/star.svg"></a>
                    <input id="select-files-<?=$file['id'];?>" name="select-files[<?=$file['id'];?>]" class="selectCheckBox checkbox" type="checkbox">
                    <label for="select-files-<?=$file['id'];?>">
                        <div class="thumbnail" style="background-image: url(&quot;<?=$file['icon'];?>&quot;); background-size: 32px auto;"></div>
                        <span class="hidden-visually">Select</span>
                    </label>
                    <a href="<?=$link;?>" class="name">
                <span class="nametext" style="left:inherit;">
                    <span class="innernametext"><?=$file['name'];?></span>
                </span>
                        <span currentuploads="0" class="uploadtext"></span>
                    </a>
                    <?php if ($file['mimetype'] == 2) { // If folder ?>
                        <div id="folder-files"></div>
                    <?php } ?>
                </td>
                <td style="color:rgb(160,160,160); text-align:right;" class="filesize"><?=\OCA\Owncollab_Talks\Helper::sizeRoundedString($file['size']);?></td>
                <td class="date">
                    <span data-original-title="<?=date('F d, Y h:i A', $file['mtime']);?>" style="color:rgb(51,51,51)" title="" class="modified"><?php p($l->t($modified['key'], $modified['value']));?></span>
                </td>
            </tr>
        <?php } ?>
    <?php } ?>
</table>