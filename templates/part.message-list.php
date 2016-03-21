
<table class="messagelist">
	<?
	$files = $_['files'];
	if (is_array($_['messages'])) {
		foreach ($_['messages'] as $m => $message) {
			$filenames = !empty($message['attachements']) ? $files->getByIdList(explode(',', $message['attachements']), $_['user']) : array();
			?>
		<tr class="messagerow <?php if ($message['status'] == 0) { echo ' unread'; } ?>">
			<td class="id" id="messageid" value="<?=$message['messageid'];?>"></td>
			<td class="title"><?=$message['title'];?></td>
			<td class="date"><?=date("d.m.Y H:i", strtotime($message['date']));?></td>
			<td class="author"><?=$message['author'];?></td>
			<td class="subscribers"><?=$message['subscribers'];?></td> <!-- TODO: Зробити обробник адресатів -->
			<td class="attachements">
			<?
			if (!empty($filenames)) {
				$fnames = array();
				foreach ($filenames as $f => $file) {
					$fnames[] = $file['name'];
				}
				echo implode(', ', $fnames);
			}
			?>
			</td>
		</tr>
		<?php }
	}
	else {
		//TODO: Тут відображаються розмови, а не повідомлення. Подумати шо робити з ними
		foreach ($_['talks'] as $m => $talk) {
			$filenames = !empty($message['attachements']) ? $files->getByIdList(explode(',', $message['attachements']), $_['user']) : array();
			?>
		<tr class="messagerow <?php if ($talk['status'] == 0) { echo ' unread'; } ?>">
			<td class="id" id="messageid" value="<?=$talk['id'];?>"></td>
			<td class="title"><?=$talk['title'];?></td>
			<td class="date"><?=date("d.m.Y H:i", strtotime($talk['date']));?></td>
			<td class="author"><?=$talk['author'];?></td>
			<td class="subscribers"><?=$talk['subscribers'];?></td> <!-- TODO: Зробити обробник адресатів -->
			<td class="attachements">
				<?
				if (!empty($filenames)) {
					$fnames = array();
					foreach ($filenames as $f => $file) {
						$fnames[] = $file['name'];
					}
					echo implode(', ', $fnames);
				}
				?>
			</td>
		</tr>
	<?php }
	} ?>
</table>
