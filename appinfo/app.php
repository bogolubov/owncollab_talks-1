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


use OCA\Owncollab_Talks\Db\Connect;
use OCA\Owncollab_Talks\Helper;
use OCA\Owncollab_Talks\MTAServer\Aliaser;
use OCA\Owncollab_Talks\MTAServer\Configurator;
use OCA\Owncollab_Talks\MTAServer\MtaConnector;
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
		'icon' => $urlGenerator->imagePath($appName, 'app.png'),
		'name' => $l->t('Talks')
	];
});


/**
 * Aliaser class a listen the events "create new users" and "create new group"
 * todo: create error logs for Configurator & MtaConnector
 */
if( Helper::isAppSettingsUsers() ) {

    $configurator = new Configurator();
    $mta = new MtaConnector($configurator);

    if($mtaErrors = $mta->getErrors()) {
        Helper::mailParserLogerError($mtaErrors);
    }
    else if ($mta->getConnection()) {
        $aliaser = new Aliaser($appName, $configurator, $mta);

        // Sync MailServer virtual users with OwnCloud users
        $connect = new Connect(\OC::$server->getDatabaseConnection());

        $users = [];
        $usersArr = $connect->users()->getAll();

        foreach ($usersArr as $ua) {
            $users[] = strtolower($ua['uid']);
        }
        $groups = [];
        $groupsArr = $connect->users()->getAllGroups();
        foreach ($groupsArr as $ga) {
            $groups[] = strtolower($ga['gid']);
        }

        $aliaser->syncVirtualAliasesWithUsers($users, $groups);
    }

}


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