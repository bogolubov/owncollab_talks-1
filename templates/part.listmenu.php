<?php

$messages = (!empty($_['messages']) && is_array($_['messages'])) ? $_['messages'] : [];


?>

<ul class="listmenu">
	<?php foreach($messages as $message): ?>

        <li data-id="<?=$message['id']?>"><?=$message['title']?></li>

	<?php endforeach; ?>
</ul>
