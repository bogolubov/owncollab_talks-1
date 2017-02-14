<?php
    namespace OCA\Owncollab_Talks\AppInfo;

    //ini_set('display_errors', 1);


use OCA\Owncollab_Talks\Helper;
use OCA\Activity\Data;
use OCA\Owncollab_Talks\Controller\ApiController;
use OCA\Owncollab_Talks\Controller\MainController;
use OCA\Owncollab_Talks\Db\Connect;
use OCA\Owncollab_Talks\MTAServer\Configurator;
use \OCP\AppFramework\App;
use \OCP\AppFramework\IAppContainer;
use \OCP\IContainer;
use \OC\AppFramework\DependencyInjection\DIContainer;

class Application extends App {

    public function __construct ( array $urlParams = [] ) {

        // Static saved the application name
        $appName = Helper::setAppName('owncollab_talks');
        parent::__construct($appName, $urlParams);
        $container = $this->getContainer();

        $container->registerService('ActivityData', function(IContainer $c) {
            /** @var \OC\Server $server */
            $server = $c->query('ServerContainer');
            return new Data(
                $server->getActivityManager(),
                $server->getDatabaseConnection(),
                $server->getUserSession()
            );
        });

        /**
         * Checks the configuration file, if it does not match the server parameters,
         * updates the configuration file
         */
        $container->registerService('Configurator', function (IAppContainer $c) use ($appName) {
            $configurator = new Configurator();
            $this->updateConfig($configurator);
            return $configurator;
        });

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
            /** @var \OC\Server $server */
            $server = $c->query('ServerContainer');
            return new ApiController(
                $c->query('AppName'),
                $c->query('Request'),
                $c->query('UserId'),
                $c->query('isAdmin'),
                $c->query('L10N'),
                $c->query('Connect'),
                $c->query('Configurator'),
                $c->query('ActivityData'),
                $server->getActivityManager()
            );
        });


        $container->registerService('MainController', function(DIContainer $c) {

            return new MainController(
                $c->query('AppName'),
                $c->query('Request'),
                $c->query('UserId'),
                $c->query('isAdmin'),
                $c->query('L10N'),
                $c->query('Connect'),
                $c->query('Configurator')
            );
        });

    }


    /**
     * Проверка на обновления и вывод конфигурационного массива
     *
     * @param Configurator $configurator
     */
    public function updateConfig($configurator)
    {
        $request = \OC::$server->getRequest();
        $domain = $request->getServerHost();

        if ($configurator->get('mail_domain') != $domain || !$configurator->get('installed')) {

            $params = [
                'installed'   => true,
                'mail_domain' => $domain,
                'server_host' => $domain,
                'site_url'    => \OC::$server->getURLGenerator()->getAbsoluteURL('/'),
            ];

            $configurator->update($params);
        }
    }

}
