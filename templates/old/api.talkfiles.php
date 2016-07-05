<?php
$filenames = $_['filenames'];
$files = $_['files'];
if (!empty($filenames)) {
	foreach ($filenames as $f => $file) {
		$icon = $files->getIcon($file['mimetype']);
		$link = $file['mimetype'] == 'httpd/unix-directory' ? "/index.php/apps/files?dir=//".$file['name'] : "/index.php/apps/files/ajax/download.php?dir=%2F&files=".$file['name'];
		?>
		<div class="file">
			<a href="<?=$link;?>">
				<img src="/core/img/filetypes/<?=$icon;?>.svg">
				<div class="file-name"><?=$file['name'];?></div>
			</a>
			<div class="uploaded-time"><?php p($l->t('uploaded'));?> <?=date("d.m.Y H:i", $file['storage_mtime']);?></div>
		</div>
		<?php
	}
	echo "<div class=\"clear\"></div>";
}
?>
