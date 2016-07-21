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

    ['name' => 'main#index', 'url' => '/', 'verb' => 'GET'],
    ['name' => 'main#my', 'url' => '/my', 'verb' => 'GET'],
    ['name' => 'main#all', 'url' => '/all', 'verb' => 'GET'],
    ['name' => 'main#started', 'url' => '/started', 'verb' => 'GET'],
    ['name' => 'main#begin', 'url' => '/begin', 'verb' => 'GET'],
    ['name' => 'main#read', 'url' => '/read/{id}', 'verb' => 'GET'],

    ['name' => 'api#test', 'url' => '/test', 'verb' => 'GET'],


    ['name' => 'api#index', 'url' => '/api', 'verb' => 'POST|GET|FILES'],
    ['name' => 'api#save_talk', 'url' => '/save_talk', 'verb' => 'POST'],
    ['name' => 'api#parse_manager', 'url' => '/parse_manager', 'verb' => 'POST'],



    /*
    ['name' => 'main#page', 'url' => '/page', 'verb' => 'GET'],
    ['name' => 'main#do_echo', 'url' => '/echo', 'verb' => 'POST'],

    ['name' => 'main#index', 'url' => '/all', 'verb' => 'GET'],
    ['name' => 'main#mytalks', 'url' => '/mytalks', 'verb' => 'GET'],
    ['name' => 'main#startedtalks', 'url' => '/startedtalks', 'verb' => 'GET'],
    ['name' => 'main#talk', 'url' => '/talk/{id}', 'verb' => 'GET'],
    ['name' => 'main#read', 'url' => '/read/{id}', 'verb' => 'GET'],
    ['name' => 'main#begin', 'url' => '/begin', 'verb' => 'GET'],
    ['name' => 'main#selectSubscribers', 'url' => '/subscribers', 'verb' => 'GET'],
    ['name' => 'main#attachments', 'url' => '/attachments', 'verb' => 'GET'],
    ['name' => 'main#saveTalk', 'url' => '/send', 'verb' => 'POST'],
    //['name' => 'main#save', 'url' => '/send', 'verb' => 'GET'],
    ['name' => 'main#reply', 'url' => '/reply/{id}', 'verb' => 'GET'],

    ['name' => 'main#addUser', 'url' => '/adduser/{id}', 'verb' => 'GET'],
    ['name' => 'main#removeUser', 'url' => '/removeuser/{talk}/{user}', 'verb' => 'GET'],
    ['name' => 'main#markMessage', 'url' => '/mark/{id}/{flag}', 'verb' => 'GET'],

    ['name' => 'main#getUserFiles', 'url' => '/getfiles', 'verb' => 'GET'],
    ['name' => 'main#parseMessages', 'url' => '/parsemessages', 'verb' => 'GET'],

    //['name' => 'main#parse_mail', 'url' => '/parsemail', 'verb' => 'GET|POST'],
    ['name' => 'main#saveemailanswer', 'url' => '/savemail', 'verb' => 'GET|POST'],
    //['name' => 'main#savemail', 'url' => '/savemail', 'verb' => 'GET|POST'],
    ['name' => 'main#saveemailtalk', 'url' => '/savemailtalk', 'verb' => 'GET|POST'],*/
]]);


/*\OCP\API::register(
    'get',
    '/apps/owncollab_talks/url',
    function($urlParameters) {
        return new \OC_OCS_Result($data);
    },
    'owncollab_talks',
    \OC_API::ADMIN_AUTH
);*/