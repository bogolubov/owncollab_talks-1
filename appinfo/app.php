<?php
/**
 * ownCloud chart application
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Your Name <mail@example.com>
 * @copyright Your Name 2016
 */

namespace OCA\Owncollab_Talks\AppInfo;

use OCA\Owncollab\Helper;
use OCP\AppFramework\App;
use OCP\Util;

$appName = 'owncollab_talks';
$app = new App($appName);
$container = $app->getContainer();

/**
 * Navigation menu settings
 */
$container->query('OCP\INavigationManager')->add(function () use ($container, $appName) {
	$urlGenerator = $container->query('OCP\IURLGenerator');
	$l10n = $container->query('OCP\IL10N');
	return [
		'id' => $appName,
		'order' => 10,
		'href' => $urlGenerator->linkToRoute($appName.'.main.index'),
		'icon' => $urlGenerator->imagePath($appName, 'app.svg'),
		'name' => $l10n->t('Talks OwnCollab ')
	];
});


/**
 * Loading translations
 * The string has to match the app's folder name
 */
Util::addTranslations($appName);


/**
 * Common styles and scripts
 */
if(Helper::isAppPage($appName)){
	Util::addStyle($appName, 'common');
	Util::addScript($appName, 'inc');
	Util::addScript($appName, 'application');
}


/**
 * Detect and appoints styles and scripts for particular app page
 */
$currentUri = Helper::getCurrentUri($appName);
if($currentUri == '/') {


}