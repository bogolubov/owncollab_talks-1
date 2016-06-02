<?php

?>

<div id="app">
	<div id="app-navigation">
		<?php print_unescaped($this->inc('part.navigation')); ?>
		<?php print_unescaped($this->inc('part.settings')); ?>
	</div>

	<div id="app-content">
		<div id="app-content-error"
		<div id="app-content-wrapper">
			<div id="app-content-inline-error"></div>
			<?php print_unescaped($this->inc('part.content')); ?>
		</div>
	</div>
</div>
