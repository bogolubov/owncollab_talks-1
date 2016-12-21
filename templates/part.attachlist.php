<?php
/**
 * @type OCP\Template $this
 * @type array $_
 *
 */

$attachfiles = $_['attachfiles'];

?>

<table>
<?php foreach($attachfiles as $attach):?>
    <tr>
        <td><img src="<?php p($attach['icon'])?>" alt="icon"></td>
        <td><?php p($attach['name'])?></td>
    </tr>
<?php endforeach;?>
</table>

