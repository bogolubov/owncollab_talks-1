<?php
style('files', 'files');
style('owncollab_talks', 'trumbowyg/trumbowyg');
script('owncollab_talks', 'trumbowyg/trumbowyg');

?>

<div id="app">
	<div id="app-navigation">
		<?php print_unescaped($this->inc('part.navigation')); ?>
	</div>

	<div id="app-content">
		<div id="app-content-wrapper">
			<?php
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
					print_unescaped($this->inc('part.begin'));
					break;
				case 'subscribers':
					print_unescaped($this->inc('part.subscribers'));
					break;
				case 'attachments':
					print_unescaped($this->inc('api.userfiles'));
					break;
				case 'save':
					var_dump($_['saved']);
					break;
				default:
					print_unescaped($this->inc('part.message-list'));
					break;
			}

			?>
		</div>
	</div>
</div>
