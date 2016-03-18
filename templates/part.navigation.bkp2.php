<ul>
	<li id="MainMenu-begin"<? if ($_['menu']=='begin') { echo ' class="active"'; } ?>><a href="/index.php/apps/owncollab_talks">Start Talk</a></li>
	<li id="MainMenu-subscribers"<? if ($_['menu']=='subscribers') { echo ' class="active"'; } ?>><a href="/index.php/apps/owncollab_talks">Select subscribers</a></li>
	<li id="MainMenu-mytalks"<? if ($_['menu']=='mytalks') { echo ' class="active"'; } ?>><a href="/index.php/apps/owncollab_talks">My started talks</a></li>
	<li id="MainMenu-attachments"<? if ($_['menu']=='attachments') { echo ' class="active"'; } ?>><a href="/index.php/apps/owncollab_talks">Attachments</a></li>
	<li id="MainMenu-all"<? if ($_['menu']=='all') { echo ' class="active"'; } ?>><a href="/index.php/apps/owncollab_talks">All talks</a></li>
</ul>