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

    /**
     * Return all domains names
     *
     * @return bool
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getVirtualDomains() {
        if($this->instance) {
            $stmt = $this->instance->prepare('SELECT `name` FROM `mailserver`.`virtual_domains`');
            $stmt->execute();
            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            return is_array($result) ? $result['name'] : false;
        }
    }

    /**
     * Return all virtual users
     * @return bool
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getVirtualUsers() {
        if($this->instance) {
            $stmt = $this->instance->prepare('SELECT `name` FROM `mailserver`.`virtual_users`');
            $stmt->execute();
            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            return is_array($result) ? $result['name'] : false;
        }
    }

    /**
     * Check on the existence of a domain name into table virtual_domains
     *
     * @param $domain
     * @return array|bool|mixed
     * @throws \Doctrine\DBAL\DBALException
     */
    public function virtualDomainExist($domain) {
        if($this->instance) {
            $stmt = $this->instance->prepare('SELECT * FROM `mailserver`.`virtual_domains` WHERE `name` = ?');
            $stmt->execute([$domain]);
            $result = $stmt->fetch();
            return is_array($result) ? $result : false;
        }
    }

    /**
     * Check on the existence of a user by the email into table virtual_users
     *
     * @param $email
     * @return array|bool|mixed
     * @throws \Doctrine\DBAL\DBALException
     */
    public function virtualUserExist($email) {
        if($this->instance) {
            $stmt = $this->instance->prepare('SELECT * FROM `mailserver`.`virtual_users` WHERE `email` = ?');
            $stmt->execute([$email]);
            $result = $stmt->fetch();
            return is_array($result) ? $result : false;
        }
    }

    /**
     * Add new virtual user into table virtual_users if it not exist
     *
     * @param $email
     * @param $password
     * @return bool
     * @throws \Doctrine\DBAL\DBALException
     */
    public function insertVirtualUser($email, $password) {
        if($this->instance) {
            $sql = "INSERT INTO `mailserver`.`virtual_users`
                  (`domain_id`, `password` , `email`) VALUES
                  ('1', ENCRYPT(?, CONCAT('$6$', SUBSTRING(SHA(RAND()), -16))) , ?);";

            $stmt = $this->instance->prepare($sql);
            $stmt->bindValue(1, $password);
            $stmt->bindValue(2, $email);
            return $stmt->execute();
        }
    }

}
