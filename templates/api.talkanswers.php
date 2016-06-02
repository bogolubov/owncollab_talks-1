	<?php
	$talk = $_['talk'];
	$startedfrom = $talk['author'] == $_['user'] ? $l->t('You') : $talk['author'];
	?>
	<div class="talk-title"><a href="/index.php/apps/<?=$_['appname'];?>/read/<?=$talk['id'];?>"><?=$talk['title'];?></a></div>
	<div class="talk-author"><?php p($l->t('started from %s on %s', [$startedfrom, date("d.m.Y H:i", strtotime($talk['date']))]));?></div>
	<div class="talk-preview"><?=\OCA\Owncollab_Talks\Helper::firstWords($talk['text'], 10);?></div>
	<div class="talk-answers" id="talk-answers">
	<?php
	foreach ($_['answers'] as $a => $answer) { ?>
		<div class="talk-author"><?php p($l->t('user %s answered on %s', [$answer['author'], date("d.m.Y H:i", strtotime($answer['date']))]));?></div>
		<div class="talk-title"><a href="/index.php/apps/<?=$_['appname'];?>/read/<?=$answer['id'];?>"><?=$answer['title'];?></a></div>
	<?php } ?>
	</div>
	<div class="newanswer" id="newanswer">
		<form id="newanswer">
			<input type="text" name="answertext" placeholder="<?php p($l->t('Answer directly'));?>">
			<input type="hidden" name="messageid" value="<?=$talk['id'];?>">
			<input type="submit">
		</form>
	</div>
