<?php

$content = isset($_['content']) ? $_['content'] : false;

?>

<div id="app">

	<div id="app-navigation">
		<?php print_unescaped($this->inc('part.navigation')); ?>
		<?php print_unescaped($this->inc('part.settings')); ?>
	</div>

	<div id="app-content">
		<div id="app-content-error"></div>
		<div id="app-content-wrapper">

			<div id="app-content-inline-error" style="display: none">
				<div class="tbl">
                    <div class="tbl_cell inline_error_content">Some error display</div>
                    <div class="tbl_cell inline_error_close"><i class="icon-close"></i></div>
                </div>
			</div>

			<?php if ($content)
				print_unescaped($this->inc("content.$content")); ?>

		</div>
	</div>

</div>

