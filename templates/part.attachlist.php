<?php
/**
 * @type OCP\Template $this
 * @type array $_
 *
 */

$attachfiles = $_['attachfiles'];

?>

<table id="talk-attachfiles">
<?php foreach($attachfiles as $attach):?>
    <tr>
        <td style="width: 36px"><img src="<?php p($attach['icon'])?>" alt="icon"></td>
        <td>
            <a href="/remote.php/webdav<?php p($attach['file'])?>"><?php p($attach['name'])?></a>
        </td>
    </tr>
<?php endforeach;?>
</table>

