<?php declare(strict_types=1);

namespace Core\Auth;

use Core\Database\Database;
use Core\Session\Session;

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
     * @param Session $session
     */
    public function __construct(Database $db, Session $session)
    {
        parent::__construct($session);
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
                $this->session->write('auth', $user->id);
                return true;
            }
        }
        return false;
    }
}
