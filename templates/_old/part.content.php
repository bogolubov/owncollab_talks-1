<p>Hello World <?php p($_['user']) ?></p>

<p><button id="hello">click me</button></p>

<p><textarea id="echo-content">
	Send this as ajax
</textarea></p>
<p><button id="echo">Send ajax request</button></p>

Ajax response: <div id="echo-result"></div>

<table class="messagelist">
	<?php foreach ($_['messages'] as $m => $message) { ?>
	<tr<?if ($message['status'] == 0) { echo ' class="unread"'; }?>>
		<td class="id"></td>
		<td class="title"><?=$message['title'];?></td>
		<td class="date"><?=date("d.m.Y H:i", strtotime($message['date']));?></td>
		<td class="author"><?=$message['author'];?></td>
		<td class="attachements"><?=$message['attachements'];?></td>
	</tr>
	<?php } ?>
</table>
