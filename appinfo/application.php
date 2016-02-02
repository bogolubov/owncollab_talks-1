<?php

namespace OCA\Owncollab_Talks\AppInfo;


use \OCA\Owncollab\Helper;
use OCA\Owncollab_Talks\Controller\ApiController;
use OCA\Owncollab_Talks\Controller\MainController;
use OCA\Owncollab_Talks\Db\Connect;
use \OCP\AppFramework\App;
use \OCP\AppFramework\IAppContainer;
use \OCP\IContainer;
use \OC\AppFramework\DependencyInjection\DIContainer;

class Application extends App {

    public function __construct ( array $urlParams = [] ) {
        $appName = Application::appName('owncollab_talks');
        parent::__construct($appName, $urlParams);
        $container = $this->getContainer();

        /**
         * Core for application registers service
         */
        $container->registerService('UserId', function(IContainer $c) {
            /** @var \OC\Server $server */
            /** @var \OCP\IUser  $user */
            $server = $c->query('ServerContainer');
            $user = $server->getUserSession()->getUser();
            return ($user) ? $user->getUID() : '';
        });

        $container->registerService('isAdmin', function(DIContainer $c) {
            /** @var \OC\Server $server */
            /** @var \OCP\IUser  $user */
            $server = $c->query('ServerContainer');
            $user = $server->getUserSession()->getUser();
            if($user)
                return $c->getServer()->getGroupManager()->isAdmin($user->getUID());
            else
                return false;
        });

        $container->registerService('L10N', function (IAppContainer $c) use ($appName) {
            return $c->getServer()->getL10N($appName);
        });


        /**
         * Database Layer
         */
        $container->registerService('Connect', function(DIContainer $c) {
            return new Connect(
                \OC::$server->getDatabaseConnection()
            );
        });


        /**
         * Controllers
         */
        $container->registerService('ApiController', function(DIContainer $c) {
            return new ApiController(
                $c->query('AppName'),
                $c->query('Request'),
                $c->query('UserId'),
                $c->query('isAdmin'),
                $c->query('L10N'),
                $c->query('Connect')
            );
        });


        $container->registerService('MainController', function(DIContainer $c) {
            return new MainController(
                $c->query('AppName'),
                $c->query('Request'),
                $c->query('UserId'),
                $c->query('isAdmin'),
                $c->query('L10N'),
                $c->query('Connect')
            );
        });


    }

    public static function appName ($name = null){
        static $_appName;
        if (!empty($name)) $_appName = $name;
        return $_appName;
    }
}
