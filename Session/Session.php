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

    /**
     * Destroy the Session and Cookies.
     */
    public function destroy()
    {
        $_SESSION = [];

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        setcookie('remember', '', time() - 1);

        session_destroy();
        self::$instance = null;
    }
}
