<?php

namespace OCA\Owncollab_Talks;


class Configurator
{
    private $filePath = '';
    private $error = null;
    private $config = [
        'mta_connection' => null,
        'group_prefix' => null,
        'user_prefix' => null,
        'installed' => null,
        'mail_domain' => null,
        'server_host' => null,
        'site_url' => null,
    ];

    public function __construct()
    {
        $this->filePath = \OC_App::getAppPath('owncollab_talks') . '/config/config.php';

        if(is_file($this->filePath)) {
            $this->read();
        } else {
            $this->error = 'File "owncollab_talks/config/config.php" not exist';
        }

    }

    /**
     * Read config file
     */
    public function read()
    {
        $configArray = Helper::includePHP($this->filePath);
        $this->config = array_merge($this->config, $configArray);
    }

    /**
     * Get config value by name
     * @param $name
     * @return bool
     */
    public function get($name)
    {
        if(isset($this->config[$name]))
            return $this->config[$name];
        else return false;
    }

    /**
     * Get error string
     * @return bool|null|string
     */
    public function getError()
    {
        if($this->error)
            return $this->error;
        else return false;
    }

    /**
     * Update owncollab_talks/config/config.php file
     * @param array $params
     */
    public function update(array $params)
    {
        $overwrite = false;
        $fileLines = file($this->filePath);
        $arrLength = count($fileLines);

        for ($i = 0; $i < $arrLength; $i ++) {

            foreach ($params as $key => $value) {
                if (array_key_exists($key, $this->config) && strpos($fileLines[$i], $key) !== false) {

                    $overwrite = true;
                    $this->config[$key] = $value;

                    $value = is_bool($value)
                        ? ($value?'true':'false')
                        : "'$value'";

                    $fileLines[$i] = "    '$key' => $value,\n";
                }
            }

        }

        if($overwrite) {

            if(!is_writable($this->filePath)) {
                chmod($this->filePath, 0777);
            }

            if(!file_put_contents($this->filePath, join("", $fileLines)))
                $this->error = 'File config can not overwrite';

        }
    }

}
