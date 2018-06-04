<?php declare(strict_types=1);

namespace Core\Session;

/**
 * Class Auth
 * @package Core\Session
 */
class Session
{
    private static $instance;

    public function __construct()
    {
        session_start();
    }

    /**
     * @return Session
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new Session();
        }
        return self::$instance;
    }

    /**
     * @param $key
     * @param $message
     */
    public function setFlash($key, $message)
    {
        $_SESSION['flash'][$key] = $message;
    }

    /**
     * @return mixed
     */
    public function getFlashes()
    {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }

    /**
     * @return bool
     */
    public function hasFlashes()
    {
        return isset($_SESSION['flash']);
    }

    /**
     * @param $key
     * @param $value
     */
    public function write($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    /**
     * @param $key
     * @return mixed|null
     */
    public function read($key)
    {
        return isset($_SESSION[$key]) ? $_SESSION[$key] : null;
    }

    /**
     * @param $key
     */
    public function delete($key)
    {
        unset($_SESSION[$key]);
    }
}
