<?php declare(strict_types=1);

namespace Core\Auth;

/**
 * Class Auth
 * @package Core\Auth
 */
abstract class Auth
{
    /**
     * @return int
     */
    public function getUserId()
    {
        if ($this->isLogged()) {
            return $_SESSION['auth'];
        }
        return false;
    }

    /**
     * @return bool
     */
    public function isLogged()
    {
        return isset($_SESSION['auth']);
    }
}
