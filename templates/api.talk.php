<?php
//var_dump($_);

switch ($_['mode']) {
	case 'list':
	case 'all':
		print_unescaped($this->inc('part.message-list'));
		break;
	case 'talk':
	case 'read':
		print_unescaped($this->inc('part.read'));
		break;
	case 'begin':
	case 'reply':
		style('talks', 'files');
		print_unescaped($this->inc('part.begin'));
		break;
	case 'subscribers':
		print_unescaped($this->inc('part.subscribers'));
		break;
	case 'attachments':
		style('talks', 'files');
		print_unescaped($this->inc('api.userfiles'));
		break;
	case 'save':
		var_dump($_['saved']);
		break;
	case 'files':
		?>
		<table>
			<tr>
				<th>id</th>
				<th>share_with</th>
				<th>share_type</th>
				<th>item_type</th>
				<th>file_target</th>
				<th>permissions</th>
				<th>stime</th>
			</tr>
			<?
			foreach($_['files'] as $f => $file) {
				?>
				<tr>
					<td><?=$file['id'];?></td>
					<td><?=$file['share_with'];?></td>
					<td><?=$file['share_type'];?></td>
					<td><?=$file['item_type'];?></td>
					<td><?=$file['file_target'];?></td>
					<td><?=$file['permissions'];?></td>
					<td><?=date('d.m.Y H:i:s', $file['stime']);?></td>
				</tr>
			<?php } ?>
		</table>
		<?
		break;
	default:
		print_unescaped($this->inc('part.message-list'));
		break;
}

?>
