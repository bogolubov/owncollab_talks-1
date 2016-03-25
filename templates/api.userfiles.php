<?php
$files = $_['files'];
//var_dump($files);
?>

<table id="filestable" data-allow-public-upload="yes" data-preview-x="32" data-preview-y="32" style="top:10px;">
    <thead style="width: 1123px;">
    <tr>
        <th id="headerName" class="column-name">
            <div id="headerName-container" style="padding: 15px 0 0 40px;">
                <span>Name</span>
            </div>
        </th>
        <th id="headerSize" class="column-size" style="padding: 15px 0 0 15px;">
            <span>Size</span>
        </th>
        <th id="headerDate" class="column-mtime" style="padding: 15px 0 0 15px;">
            <span>Modified</span>
        </th>
    </tr>
    </thead>
    <tbody id="fileList">

    <?php
    $countfolders = 0;
    $countfiles = 0;
    $totalsize = 0;
    $filetypes = array('file', 'file', 'folder', 'file', 'application-pdf', 'file', 'file', 'image', 'file', 'file', 'text', 'file', 'x-office-document', 'x-office-spreadsheet', 'x-office-presentation', 'file', 'audio', 'file', 'video');
    $filetypes2 = array('httpd' => 'file', 'httpd/unix-directory' => 'folder', 'application' => 'application', 'application/pdf' => 'application-pdf', 'application/vnd.oasis.opendocument.text' => 'text', 'image' => 'image', 'image/jpeg' => 'image', 'application/octet-stream' => 'text-code', 'text' => 'text', 'text/plain' => 'text', 'image/png' => 'image', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'x-office-document', 'application/vnd.oasis.opendocument.spreadsheet' => 'x-office-spreadsheet', 'application/vnd.oasis.opendocument.presentation' => 'x-office-presentation', 'audio' => 'audio', 'audio/mpeg' => 'audio', 'video' => 'video', 'video/x-msvideo' => 'video', 'application/msword' => 'x-office-document');
    foreach ($files as $f => $file) {
        if ($file['mimetype'] == 2) { // If folder
            //$link = "/index.php/apps/files?dir=/".$file['file'];
            $link = "#";
            $countfolders++;
        }
        else {
            $link = "/index.php/apps/files/ajax/download.php?dir=%2F&files=".$file['name'];
            $countfiles++;
        }
        $totalsize += $file['size'];
        ?>

        <?php if ($file['mimetype'] == 'httpd/unix-directory') { // If folder ?>
    <tr data-icon="/core/img/filetypes/folder-shared.svg" data-share-permissions="31" data-permissions="31" data-id="<?=$file['activity_id'];?>">
         <td class="filename">
            <a title="" data-original-title="" href="#" class="action action-favorite "><img class="svg" alt="Favorite" src="/core/img/actions/star.svg"></a>
            <label for="select-files-<?=$file['activity_id'];?>">
                <div class="thumbnail" style="background-image: url(&quot;/core/img/filetypes/<?=$filetypes2[$file['mimetype']];?>.svg&quot;); background-size: 32px auto;"></div>
                <span class="hidden-visually">Select</span>
            </label>
            <a class="name ajax-openfolder" id="folder-<?=$file['fileid'];?>"">
            <span class="nametext" style="left:inherit;">
                <span class="innernametext"><?=$file['name'];?></span>
            </span>
                <span currentuploads="0" class="uploadtext"></span>
            </a>
            <div id="folder-files"></div>
        </td>
        <td style="color:rgb(160,160,160); text-align:right;" class="filesize"><?=\OCA\Owncollab_Talks\Helper::sizeRoundedString($file['size']);?></td>
        <td class="date">
            <span data-original-title="<?=date('F d, Y h:i A', $file['timestamp']);?>" style="color:rgb(51,51,51)" title="" class="modified"><?=\OCA\Owncollab_Talks\Helper::time_elapsed_string($file['timestamp']);?></span>
        </td>
    </tr>
    <tr style="height: 0px;">
        <td colspan="3" class="folder" id="folder-files-<?=$file['fileid'];?>"></td>
    </tr>
        <?php }
        else { // If file ?>
    <tr data-icon="/core/img/filetypes/folder-shared.svg" data-share-permissions="31" data-permissions="31" data-id="<?=$file['activity_id'];?>">
        <td class="filename">
            <a title="" data-original-title="" href="#" class="action action-favorite "><img class="svg" alt="Favorite" src="/core/img/actions/star.svg"></a>
            <input id="select-files-<?=$file['activity_id'];?>" name="select-files[<?=$file['fileid'];?>]" class="selectCheckBox checkbox" type="checkbox">
            <label for="select-files-<?=$file['activity_id'];?>">
                <div class="thumbnail" style="background-image: url(&quot;/core/img/filetypes/<?=$filetypes2[$file['mimetype']];?>.svg&quot;); background-size: 32px auto;"></div>
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
            <span data-original-title="<?=date('F d, Y h:i A', $file['timestamp']);?>" style="color:rgb(51,51,51)" title="" class="modified"><?=\OCA\Owncollab_Talks\Helper::time_elapsed_string($file['timestamp']);?></span>
        </td>
    </tr>
        <?php } ?>
    <?php } ?>
    <tfoot>
    <tr class="summary" style="height: 50px;">
        <td>
            <span class="info">
                <span class="dirinfo"><?=$countfolders;?> folders</span>
                <span class="connector"> and </span>
                <span class="fileinfo"><?=$countfiles;?> files</span>
                <span class="filter hidden"></span>
            </span>
        </td>
        <td class="filesize" style="text-align:right;"><?=\OCA\Owncollab_Talks\Helper::sizeRoundedString($totalsize);?></td>
        <td class="date"></td>
    </tr>
    </tfoot>
</table>