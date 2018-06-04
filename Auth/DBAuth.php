<?php declare(strict_types=1);

namespace Core\Auth;

use Core\Database\Database;

/**
 * Class DBAuth
 * @package Core\Auth
 */
class DBAuth extends Auth
{
    private $db;

    /**
     * DBAuth constructor.
     * @param Database $db
     */
    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * @param $username
     * @param $password
     * @return boolean
     */
    public function login($username, $password)
    {
        $user = $this->db->prepare('SELECT * FROM users WHERE username = ?', [$username], null, true);
        if ($user) {
            if (password_verify($password, $user->password)) {
                $_SESSION['auth'] = $user->id;
                return true;
            }
        }
        return false;
    }
}
