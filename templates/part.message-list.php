<table class="messagelist">
	<?php
	$files = $_['files'];
	if (is_array($_['messages'])) {
		foreach ($_['messages'] as $m => $message) {
			$trclass = $m == 0 ? 'messagerow activerow' : 'messagerow';
			$filenames = !empty($message['attachements']) ? $files->getByIdList(explode(',', $message['attachements']), $_['user']) : array();
			?>
		<tr class="<?=$trclass;?><?php /* if ($message['status'] == 0) { echo ' unread'; } */ ?>"> <!-- TODO Використовувати індекс активної рохмови -->
			<td class="id" id="messageid" value="<?=$message['messageid'];?>"></td>
			<td class="title"><?=$message['title'];?></td>
			<?php
			if ($m == 0) {
				$startedfrom = $message['author'] == $_['user'] ? 'You' : $message['author'];
			?>
			<td class="talk-body" <?php if ($m == 0) { ?>rowspan="<?=count($_['messages']);?>"<?php } ?> id="talk-body">
				<div class="talk-title"><a href="/index.php/apps/<?=$_['appname'];?>/read/<?=$message['messageid'];?>"><?=$message['title'];?></a></div>
				<div class="talk-author">started from <?=$startedfrom;?> on <?=date("d.m.Y H:i", strtotime($message['date']));?></div>
				<div class="talk-preview"><?=\OCA\Owncollab_Talks\Helper::firstWords($message['text'], 10);?></div>
				<div class="talk-answers">
				<?php
				foreach ($_['answers'] as $a => $answer) { ?>
					<div class="talk-author">user <?=$answer['author'];?> answered on <?=date("d.m.Y H:i", strtotime($answer['date']));?></div>
					<div class="talk-title"><a href="/index.php/apps/<?=$_['appname'];?>/read/<?=$answer['id'];?>"><?=$answer['title'];?></a></div>
				<?php } /* ?>
					<div class="newanswer" id="newanswer">
						<form id="<?=$answer['id'];?>">
							<input type="text">
							<input type="submit">
						</form>
					</div>
					<?php */ ?>
				</div>
			</td>
			<td class="attachements" <?php if ($m == 0) { ?>rowspan="<?=count($_['messages']);?>"<?php } ?> id="talk-files">
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
					<div class="uploaded-time">uploaded <?=date("d.m.Y H:i", $file['storage_mtime']);?></div>
				</div>
			<?php
				}
				echo "<div class=\"clear\"></div>";
			}
			?>
			</td>
				<?php } ?>
		</tr>
		<?php }
	}
	else {
		//TODO: Тут відображаються розмови, а не повідомлення. Подумати шо робити з ними
		foreach ($_['talks'] as $m => $talk) {
			$trclass = $m == 0 ? 'messagerow activerow' : 'messagerow';
			$startedfrom = $talk['author'] == $_['user'] ? 'You' : $talk['author'];
			$filenames = !empty($message['attachements']) ? $files->getByIdList(explode(',', $message['attachements']), $_['user']) : array();
			?>
		<tr class="<?=$trclass;?><?php /* if ($talk['status'] == 0) { echo ' unread'; } */ ?>"> <!-- TODO використовувати індекс активної розмови -->
			<td class="id" id="messageid" value="<?=$talk['id'];?>"><?=$talk['messageid'];?></td>
			<td class="title"><?=$talk['title'];?></td>
			<?php/*
			<td class="date">Started from <?=$startedfrom;?> on <?=date("d.m.Y H:i", strtotime($talk['date']));?></td>
			<td class="author"><?=$talk['author'];?></td>
			<td class="subscribers"><?=$talk['subscribers'];?></td> <!-- TODO: Зробити обробник адресатів -->
			 */ ?>
			<?php
			if ($m == 0) {
			$startedfrom = $talk['author'] == $_['user'] ? 'You' : $talk['author'];
			?>
			<td class="talk-body" <?php if ($m == 0) { ?>rowspan="<?=count($_['talks']);?>"<?php } ?> id="talk-body">
				<div class="talk-title"><a href="/index.php/apps/<?=$_['appname'];?>/read/<?=$talk['id'];?>"><?=$talk['title'];?></a></div>
				<div class="talk-author">started from <?=$startedfrom;?> on <?=date("d.m.Y H:i", strtotime($talk['date']));?></div>
				<div class="talk-preview"><?=\OCA\Owncollab_Talks\Helper::firstWords($talk['text'], 10);?></div>
				<div class="talk-answers">
				<?php
				foreach ($_['answers'] as $a => $answer) { ?>
					<div class="talk-author">user <?=$answer['author'];?> answered on <?=date("d.m.Y H:i", strtotime($answer['date']));?></div>
					<div class="talk-title"><a href="/index.php/apps/<?=$_['appname'];?>/read/<?=$answer['id'];?>"><?=$answer['title'];?></a></div>
				<?php } /* ?>
					<div class="newanswer">
						<form id="<?=$answer['id'];?>">
							<input type="text">
							<input type="submit">
						</form>
					</div>
					<?php */ ?>
				</div>
			</td>
			<td class="attachements" <?php if ($m == 0) { ?>rowspan="<?=count($_['talks']);?>"<?php } ?> id="talk-files">
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
							<div class="uploaded-time">uploaded <?=date("d.m.Y H:i", $file['storage_mtime']);?></div>
						</div>
						<?php
					}
					echo "<div class=\"clear\"></div>";
				}
				?>
			</td>
				<?php } ?>
		</tr>
	<?php }
	} ?>
</table>
