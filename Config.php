<?php declare(strict_types=1);

namespace Core;

/**
 * Class Config
 * @package Core
 */
class Config
{

    private $settings = [];
    private static $instance;

    /**
     * Config constructor.
     * @param $file
     */
    public function __construct($file)
    {
        $this->settings = require($file);
    }

    /**
     * Return a Config singleton.
     * @param $file
     * @return Config
     */
    public static function getInstance($file)
    {
        if (is_null(self::$instance)) {
            self::$instance = new Config($file);
        }
        return self::$instance;
    }

    /**
     * Return the requested setting.
     * @param $key
     * @return mixed|null
     */
    public function getStg($key)
    {
        if (!isset($this->settings[$key])) {
            return null;
        }
        return $this->settings[$key];
    }
}
