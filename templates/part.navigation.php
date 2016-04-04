<ul>
	<li<?php if ($_['menu']=='begin') { echo ' class="active"'; } ?>><a href="/index.php/apps/owncollab_talks/begin"><?php p($l->t('Start Talk'));?></a></li>
	<li<?php if ($_['menu']=='startedtalks') { echo ' class="active"'; } ?>><a href="/index.php/apps/owncollab_talks/startedtalks"><?php p($l->t('Your started Talks'));?></a></li>
	<li<?php if ($_['menu']=='mytalks') { echo ' class="active"'; } ?>><a href="/index.php/apps/owncollab_talks/mytalks"><?php p($l->t('Talks you are participating'));?></a></li>
	<li<?php if ($_['menu']=='all') { echo ' class="active"'; } ?>><a href="/index.php/apps/owncollab_talks/all"><?php p($l->t('All Talks'));?></a></li>
</ul>