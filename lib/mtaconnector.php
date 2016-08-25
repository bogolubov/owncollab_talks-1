<?php

namespace OCA\Owncollab_Talks;


class MtaConnector {

    private $instance = null;
    private $configurator = null;
    private $error = null;

    /**
     * MtaConnector constructor.
     * Create connect to MTA database
     * @param Configurator $config
     */
    public function __construct(Configurator $config)
    {
        $this->configurator = $config;
        $settings = $this->configurator->get('mta_connection');

        if ($settings) {
            $config = new \Doctrine\DBAL\Configuration();
            $this->instance = \Doctrine\DBAL\DriverManager::getConnection(['url' => $settings], $config);
            if(!$this->instance)
                $this->error = 'Connection to MTA mail server is failed';

        } else
            $this->error = 'Config mta_connection is empty';
    }

    /**
     * return connected to database or null
     *
     * @return \Doctrine\DBAL\Connection|null
     */
    public function getConnection() {
        return $this->instance;
    }

    public function getErrors() {
        return (!$this->instance) ? false : $this->error;
    }

    /**
     * Return all domains names
     *
     * @param bool $filtering
     * @return array|null
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getVirtualDomains($filtering = true) {
        if($this->instance) {

            $stmt = $this->instance->prepare('SELECT * FROM `mailserver`.`virtual_domains`');
            $stmt->execute();

            if ($result = $stmt->fetchAll(\PDO::FETCH_ASSOC)) {
                if($filtering)
                    return array_map(function($item){ return $item['name']; }, $result);
                else
                    return $result;
            }
        }
    }

    /**
     * Return all virtual users
     *
     * @param bool $filtering
     * @return array|null
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getVirtualUsers($filtering = true) {
        if($this->instance) {
            $stmt = $this->instance->prepare('SELECT * FROM `mailserver`.`virtual_users`');
            $stmt->execute();

            if ($result = $stmt->fetchAll(\PDO::FETCH_ASSOC)) {
                if($filtering)
                    return array_map(function($item){ return $item['email']; }, $result);
                else
                    return $result;
            }
        }
    }

    /**
     * Check on the existence of a domain name into table virtual_domains
     *
     * @param $domain
     * @return bool
     * @throws \Doctrine\DBAL\DBALException
     */
    public function virtualDomainExist($domain) {
        if($this->instance) {
            $stmt = $this->instance->prepare('SELECT * FROM `mailserver`.`virtual_domains` WHERE `name` = ?');
            $stmt->execute([$domain]);
            return is_array($stmt->fetch());
        }
        return false;
    }

    /**
     * Check on the existence of a user by the email into table virtual_users
     *
     * @param $email
     * @return bool
     * @throws \Doctrine\DBAL\DBALException
     */
    public function virtualUserExist($email) {
        if($this->instance) {
            $stmt = $this->instance->prepare('SELECT * FROM `mailserver`.`virtual_users` WHERE `email` = ?');
            $stmt->execute([$email]);
            return is_array($stmt->fetch());
        }
        return false;
    }

    /**
     * Add new virtual user into table virtual_users if it not exist
     *
     * @param $email
     * @param $password
     * @return null|bool
     * @throws \Doctrine\DBAL\DBALException
     */
    public function insertVirtualUser($email, $password) {

        if($this->instance && !$this->virtualUserExist($email)) {

            $domainId = null;
            $domain = $this->configurator->get('mail_domain');
            $domains = $this->getVirtualDomains(false);

            for ($i = 0; $i < count($domains); $i ++) {
                if($domain === $domains[$i]['name']) {
                    $domainId = $domains[$i]['id'];
                }
            }

            if(!$domainId) return false;

            $sql = "INSERT INTO `mailserver`.`virtual_users`
                  (`domain_id`, `password` , `email`) VALUES
                  (?, ENCRYPT(?, CONCAT('$6$', SUBSTRING(SHA(RAND()), -16))) , ?);";

            $stmt = $this->instance->prepare($sql);
            $stmt->bindValue(1, $domainId);
            $stmt->bindValue(2, $password);
            $stmt->bindValue(3, $email);

            return $stmt->execute();
        }
    }

}

