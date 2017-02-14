<?php
/**
 * ownCloud - owncollab
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author ownCollab Team <info@owncollab.com>
 * @copyright ownCollab Team 2015
 */

/**
 * Create your routes in here. The name is the lowercase name of the controller
 * without the controller part, the stuff after the hash is the method.
 * e.g. page#index -> OCA\OwnCollab\Controller\PageController->index()
 *
 * The controller class has to be registered in the application.php file since
 * it's instantiated in there
 */

$application = new \OCA\Owncollab_Talks\AppInfo\Application();

$application->registerRoutes($this, ['routes' => [

    ['name' => 'main#index',    'url' => '/',                   'verb' => 'GET'],
    ['name' => 'main#my',       'url' => '/my',                 'verb' => 'GET'],
    ['name' => 'main#all',      'url' => '/all',                'verb' => 'GET'],
    ['name' => 'main#started',  'url' => '/started',            'verb' => 'GET'],
    ['name' => 'main#begin',    'url' => '/begin',              'verb' => 'GET'],
    ['name' => 'main#read',     'url' => '/read/{id}',          'verb' => 'GET'],
    ['name' => 'api#index',     'url' => '/api',                'verb' => ['GET','POST']],
    ['name' => 'api#insert',    'url' => '/save_talk',          'verb' => 'POST'],
    ['name' => 'api#parser',    'url' => '/parse_manager',      'verb' => 'POST'],
    ['name' => 'api#parserlog', 'url' => '/parserlog',          'verb' => 'POST'],
    ['name' => 'api#test',      'url' => '/test',               'verb' => 'GET'],

]]);