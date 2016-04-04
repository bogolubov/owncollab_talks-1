<div class="all-talks">
<ul class="messagelist">
	<?php
	$files = $_['files'];
	if (is_array($_['messages']) && !empty($_['messages'])) {
		$messages = $_['messages'];
		$idkey = 'messageid';
	}
	else {
		$messages = $_['talks'];
		$idkey = 'id';
	}
	foreach ($messages as $m => $message) {
		$liclass = $m == 0 ? 'activetalk' : '';
		$filenames = !empty($message['attachements']) ? $files->getByIdList(explode(',', $message['attachements']), $_['user']) : array();
	?>
	<li class="<?=$liclass;?> title">
		<div class="id" id="messageid" value="<?=$message[$idkey];?>"></div>
		<?=$message['title'];?>
	</li>
	<?php }
?>
</ul>

<div class="talk-body" id="talk-body">
	<?php
	$firsttalk = $messages[0];
	$startedfrom = $message['author'] == $_['user'] ? $l->t('You') : $message['author'];
	?>
	<div class="talk-title"><a href="/index.php/apps/<?=$_['appname'];?>/read/<?=$firsttalk[$idkey];?>"><?=$firsttalk['title'];?></a></div>
	<div class="talk-author"><?php p($l->t('started from %s on %s', [$startedfrom, date("d.m.Y H:i", strtotime($firsttalk['date']))]));?></div>
	<div class="talk-preview"><?=\OCA\Owncollab_Talks\Helper::firstWords($firsttalk['text'], 10);?>
	</div>
	<div class="talk-answers" id="talk-answers">
	<?php
	foreach ($_['answers'] as $a => $answer) { ?>
		<div class="talk-author"><?php p($l->t('user %s answered on %s', [$answer['author'], date("d.m.Y H:i", strtotime($answer['date']))]));?></div>
		<div class="talk-title"><a href="/index.php/apps/<?=$_['appname'];?>/read/<?=$answer['id'];?>"><?=$answer['title'];?></a></div>
	<?php } ?>
	</div>
	<?php if ($_['cananswer']) { ?>
	<div class="newanswer">
		<form id="newanswer">
			<input type="text" name="answertext" placeholder="<?php p($l->t('Answer directly'));?>">
			<input type="hidden" name="messageid" value="<?=$firsttalk[$idkey];?>">
			<input type="submit">
		</form>
	</div>
	<?php } ?>
</div>
<div class="attachements" id="talk-files">
	<?php
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
</div>
<div class="clear"></div>
</div>
