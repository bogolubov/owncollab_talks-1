<?php

$content = isset($_['content']) ? $_['content'] : false;

?>

<div id="app">

	<div id="app-navigation">
		<?php print_unescaped($this->inc('part.navigation')); ?>
	</div>

	<div id="app-content">
		<div id="app-content-error"></div>
		<div id="app-content-wrapper">
			<div id="app-content-inline-error"></div>

			<?php if ($content)
				print_unescaped($this->inc("content.$content")); ?>

		</div>
	</div>

</div>
