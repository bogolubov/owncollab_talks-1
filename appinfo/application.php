<?php
    namespace OCA\Owncollab_Talks\AppInfo;

    //ini_set('display_errors', 1);

use \OCA\Owncollab_Talks\Helper;
use OCA\Owncollab_Talks\Controller\ApiController;
use OCA\Owncollab_Talks\Controller\MainController;
use OCA\Owncollab_Talks\Db\Connect;
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


        /**
         * App Config Control
         */
        $container->registerService('MTAConfig', function (IAppContainer $c) use ($appName)
        {
            $mtaConfigFile  = \OC_App::getAppPath($appName) . '/appinfo/config.php';
            $config         = Helper::includePHP($mtaConfigFile);
            $mailDomain     = Aliaser::getMailDomain();

            $hasEmptyParams = empty($config['mail_domain']) || empty($config['mail_domain']) || empty($config['mail_domain']);

            if($hasEmptyParams || !$config['installed']) {

                $updateResult = $this->updateAppConfig([
                    'file_path'   => $mtaConfigFile,
                    'mail_domain' => $mailDomain,
                    'server_host' => Helper::val('serverHost'),
                    'site_url'    => Helper::val('urlFull'),
                ]);

                if($updateResult)
                    $config = Helper::includePHP($mtaConfigFile);
            }

            Helper::val([
                'mtaConfig' => $config,
                'mtaConfigFile' => $mtaConfigFile,
                'mailDomain' => $mailDomain
            ]);

            return $config;
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

            //$c->query('MTAConfig');

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

            $c->query('MTAConfig');

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


    /**
     * @param array $params ['file_path'=>null,'mail_domain'=>null,'server_host'=>null,'site_url'=>null]
     * @return mixed
     */
    public function updateAppConfig(array $params)
    {
        $overwrite   = $putResult = false;

        $file_path   = $params['file_path'];
        $mail_domain = $params['mail_domain'];
        $server_host = $params['server_host'];
        $site_url    = $params['site_url'];

        $fileLines   = file($file_path);
        $len = count($fileLines);

        for ($i = 0; $i < $len; $i ++) {
            if(strpos($fileLines[$i], 'mail_domain') !== false) {
                $overwrite = true;
                $fileLines[$i] = "    'mail_domain' => '{$mail_domain}',\n";
            }
            else if(strpos($fileLines[$i], 'server_host') !== false) {
                $overwrite = true;
                $fileLines[$i] = "    'server_host' => '{$server_host}',\n";
            }
            else if(strpos($fileLines[$i], 'site_url') !== false) {
                $overwrite = true;
                $fileLines[$i] = "    'site_url' => '{$site_url}',\n";
            }
            else if(strpos($fileLines[$i], 'installed') !== false) {
                $overwrite = true;
                $fileLines[$i] = "    'installed' => true,\n";
            }
        }
        if($overwrite) {
            if(!is_writable($file_path)) {
                chmod($file_path, 0777);
            }
            $putResult = file_put_contents($file_path, join("", $fileLines));
            return $putResult;
        }
        return $overwrite;
    }

}
