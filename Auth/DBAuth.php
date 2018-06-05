<?php declare(strict_types=1);

namespace Core\Auth;

use Core\Database\Database;
use Core\Session\Session;
use Core\String\Str;

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
    public function __construct(Database $database, Session $session)
    {
        parent::__construct($session);
        $this->db = $database;
    }

    /**
     * Register a user.
     * @param string $username
     * @param string $password
     * @param string $email
     * @param string $token
     * @return string
     */
    public function register(string $username, string $password, string $email, string $token)
    {
        $password = password_hash($password, PASSWORD_DEFAULT);
        $res = $this->db->prepare(
            'INSERT INTO users SET username = ?, password = ?, email = ?, confirmation_token = ?',
            [
            $username,
            $password,
            $email,
            $token
            ]
        );
        if ($res) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    /**
     * Log in the user.
     * @param string $username
     * @param string $password
     * @param bool $remember
     * @return boolean
     */
    public function login(string $username, string $password, bool $remember = false)
    {
        $user = $this->db->prepare(
            'SELECT * FROM users WHERE username = ? AND confirmed_at IS NOT NULL',
            [$username],
            null,
            true
        );
        if ($user) {
            if (password_verify($password, $user->password)) {
                $this->session->write('auth', $user->id);
                if ($remember) {
                    $this->remember($user->id);
                }
                return true;
            }
        }
        return false;
    }

    /**
     * @param $user_id
     */
    public function remember($user_id)
    {
        $remember_token = Str::random(250);
        $this->db->prepare(
            'UPDATE users SET remember_token = ? WHERE id = ?',
            [$remember_token, $user_id],
            null,
            true
        );
        setcookie(
            'remember',
            $user_id . '==' . $remember_token . sha1($user_id . 'ratonlaveurs'),
            time() + 60 * 60 * 24 * 7
        );
    }

    /**
     * Confirm a user account.
     * @param string $user_id
     * @param string $token
     * @return bool
     */
    public function confirm(string $user_id, string $token)
    {
        $user = $this->db->prepare('SELECT * FROM users where id = ?', [$user_id], null, true);
        if ($user && $user->confirmation_token == $token) {
            $this->db->prepare(
                'UPDATE users SET confirmation_token = NULL, confirmed_at = NOW() WHERE id = ?',
                [$user_id],
                null,
                true
            );
            return true;
        }
        return false;
    }

    /**
     * Log out the user.
     */
    public function logout()
    {
        $this->session->destroy();
        $this->session = null;
    }

    /**
     * @param string $user_id
     * @return bool
     */
    public function connectedUserExists()
    {
        $user_id = $this->session->read('auth');
        $user = $this->db->prepare(
            'SELECT * FROM users WHERE id = ? AND confirmed_at IS NOT NULL',
            [$user_id],
            null,
            true
        );
        if ($user) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $user_id
     */
    public function connect($user_id)
    {
        $this->session->write('auth', $user_id);
    }

    /**
     * Connect from Remember me Cookie.
     */
    public function connectFromCookie()
    {
        if (isset($_COOKIE['remember']) && !$this->isLogged()) {
            $remember_token = $_COOKIE['remember'];
            $parts = explode('==', $remember_token);
            $user_id = $parts[0];
            $user = $this->db->prepare('SELECT * FROM users WHERE id = ?', [$user_id], null, true);
            if ($user) {
                $expected = $user_id . '==' . $user->remember_token . sha1($user_id, 'ratonlaveurs');
                if ($expected == $remember_token) {
                    $this->connect($user);
                    setcookie('remember', $remember_token, time() + 60 * 60 * 24 * 7);
                } else {
                    setcookie('remember', null, -1);
                }
            } else {
                setcookie('remember', null, -1);
            }
        }
    }
}
