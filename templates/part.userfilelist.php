<?php

/**
 * @type array $_
 *
 */

$flist = !empty($_['file_list']) && is_array($_['file_list']) ? $_['file_list'] : [];

//var_dump($flist);
/*3 =>
    array (size=13)
      'id' => string '238' (length=3)
      'parentId' => string '235' (length=3)
      'date' => string 'June 13, 2016 at 11:27:13 AM GMT+3' (length=34)
      'mtime' => float 1465806.433
      'icon' => string '/core/img/filetypes/image.svg' (length=29)
      'isPreviewAvailable' => boolean true
      'name' => string 'Squirrel.jpg' (length=12)
      'permissions' => int 27
      'mimetype' => string 'image/jpeg' (length=10)
      'size' => int 233724
      'type' => string 'file' (length=4)
      'etag' => string 'a64191665ada14e54612c7d0823ef6f0' (length=32)
      'path' => string '/Photos/Squirrel.jpg' (length=20)
*/
?>

<ul>

    <?php for($i=0; $i<count($flist); $i++): $file = $flist[$i] ?>
    <li>

        <div class="tbl">
            <div class="tbl_cell file_list_icon"><img src="<?=$file['icon']?>"></div>
            <div class="tbl_cell file_list_checkboxs">
                <span class="fitem">

                    <input id="fid-<?php p($file['id'])?>"
                           type="checkbox"
                           name="attaches[]"
                           data-name="<?php p($file['name'])?>"
                           data-id="<?php p($file['id'])?>"
                           data-parentid="<?php p($file['parentId'])?>"
                           data-path="<?php p($file['path'])?>">

                    <label for="fid-<?php p($file['id'])?>"> <span></span> <?php p($file['name'])?> </label>
                </span>
            </div>
        </div>

    </li>
    <?php endfor; ?>

</ul>





