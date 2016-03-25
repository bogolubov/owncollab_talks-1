<?
//print_r($_['firsttalk']);
//var_dump($_['answers']);
?>

	<?
	$talk = $_['talk'];
	$startedfrom = $talk['author'] == $_['user'] ? 'You' : $talk['author'];
	?>
	<div class="talk-title"><a href="/index.php/apps/<?=$_['appname'];?>/read/<?=$talk['id'];?>"><?=$talk['title'];?></a></div>
	<div class="talk-author">started from <?=$startedfrom;?> on <?=date("d.m.Y H:i", strtotime($talk['date']));?></div>
	<div class="talk-preview"><?=\OCA\Owncollab_Talks\Helper::firstWords($talk['text'], 5);?></div>
	<div class="talk-answers">
	<?php
	foreach ($_['answers'] as $a => $answer) { ?>
		<div class="talk-author">user <?=$answer['author'];?> answered on <?=date("d.m.Y H:i", strtotime($answer['date']));?></div>
		<div class="talk-title"><a href="/index.php/apps/<?=$_['appname'];?>/read/<?=$answer['id'];?>"><?=$answer['title'];?></a></div>
	<?php } ?>
	</div>
