<?php
/**
 * ownCloud - owncollab_talks
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Werdffelynir <mail@example.com>
 * @copyright Werdffelynir 2016
 */

namespace OCA\Owncollab_Talks\AppInfo;

use OCA\Owncollab_Talks\Helper;
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
	//$l10n = $container->query('OCP\IL10N');
	$l = \OC::$server->getL10N('owncollab_talks');
	return [
		'id' => $appName,
		'order' => 10,
		'href' => $urlGenerator->linkToRoute($appName.'.main.index'),
		'icon' => $urlGenerator->imagePath($appName, 'app.svg'),
		'name' => $l->t('Talks')
	];
});


/**
 * Aliaser class a listen the events "create new users" and "create new group"
 */
$aliaser = new Aliaser($appName);


/**
 * Loading translations
 * The string has to match the app's folder name
 */
Util::addTranslations($appName);


/**
 * Common styles and scripts
 */
if(Helper::isAppPage($appName)) {
	Util::addStyle($appName, 'common');
	Util::addScript($appName, 'libs/ns.application');
	Util::addScript($appName, 'application/init');
}