<?php
$files = $_['files'];
?>

<table id="filestable" data-allow-public-upload="yes" data-preview-x="32" data-preview-y="32" style="top:10px;">
    <thead style="width: 1123px;">
    <tr>
        <th id="headerName" class="column-name">
            <div id="headerName-container" style="padding: 15px 0 0 40px;">
                <span><?php p($l->t('FileName'));?></span>
            </div>
        </th>
        <th id="headerSize" class="column-size" style="padding: 15px 0 0 15px;">
            <span><?php p($l->t('Size'));?></span>
        </th>
        <th id="headerDate" class="column-mtime" style="padding: 15px 0 0 15px;">
            <span><?php p($l->t('Modified'));?></span>
        </th>
    </tr>
    </thead>
    <tbody id="fileList">

    <?php
    $countfolders = 0;
    $countfiles = 0;
    $totalsize = 0;
    foreach ($files as $f => $file) {
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
    <tfoot>
    <tr class="summary" style="height: 50px;">
        <td>
            <span class="info">
                <span class="dirinfo"><?php p($l->t('%s folders', $countfolders));?></span>
                <span class="connector"> <?php p($l->t('and'));?> </span>
                <span class="fileinfo"><?php p($l->t('%s files', $countfiles));?></span>
                <span class="filter hidden"></span>
            </span>
        </td>
        <td class="filesize" style="text-align:right;"><?=\OCA\Owncollab_Talks\Helper::sizeRoundedString($totalsize);?></td>
        <td class="date"></td>
    </tr>
    </tfoot>
</table>