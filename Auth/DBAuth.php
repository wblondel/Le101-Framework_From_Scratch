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
     * @param Database $database
     * @param Session $session
     */
    public function __construct(Database $database, Session $session)
    {
        parent::__construct($session);
        $this->db = $database;
    }

    /**
     * @param $password
     * @return bool|string
     */
    public function hashPassword($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
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
        $password = $this->hashPassword($password);
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
     * @param $userId
     */
    public function remember($userId)
    {
        $rememberToken = Str::random(250);
        $this->db->prepare(
            'UPDATE users SET remember_token = ? WHERE id = ?',
            [$rememberToken, $userId],
            null,
            true
        );
        setcookie(
            'remember',
            $userId . '==' . $rememberToken . sha1($userId . 'ratonlaveurs'),
            time() + 60 * 60 * 24 * 7
        );
    }

    /**
     * Confirm a user account.
     * @param string $userId
     * @param string $token
     * @return bool
     */
    public function confirm(string $userId, string $token)
    {
        $user = $this->db->prepare('SELECT * FROM users where id = ?', [$userId], null, true);
        if ($user && $user->confirmation_token == $token) {
            $this->db->prepare(
                'UPDATE users SET confirmation_token = NULL, confirmed_at = NOW() WHERE id = ?',
                [$userId],
                null,
                true
            );
            return true;
        }
        return false;
    }


    /**
     * Set the reset password token of a user.
     *
     * @param string $email
     * @param string $token
     * @return mixed
     */
    public function setResetPasswordToken(string $email, string $token)
    {
        $user = $this->db->prepare('SELECT * FROM users WHERE email = ? AND confirmed_at IS NOT NULL', [$email], null, true);

        if ($user) {
            $this->db->prepare(
                'UPDATE users SET reset_token = ?, reset_at = NOW() WHERE id = ?',
                [$token, $user->id],
                null,
                true
            );
            return $user;
        }
        return false;
    }


    /**
     * Checks the given password reset token.
     *
     * @param string $userId
     * @param string $token
     * @return mixed
     */
    public function checkPasswordResetToken(string $userId, string $token)
    {
        return $this->db->prepare(
            'SELECT * FROM users WHERE id = ? AND reset_token IS NOT NULL AND reset_token = ? AND reset_at > DATE_SUB(NOW(), INTERVAL 30 MINUTE)',
            [$userId, $token],
            null,
            true
        );
    }


    /**
     * Reset password of a user.
     *
     * @param string $userId
     * @param string $password
     * @return mixed
     */
    public function resetPassword(string $userId, string $password)
    {
        $this->db->prepare(
            'UPDATE users SET password = ?, reset_at = NULL, reset_token = NULL WHERE id = ?',
            [$password, $userId],
            null,
            true
        );
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
     * @param $userId
     */
    public function connect($userId)
    {
        $this->session->write('auth', $userId);
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
                $expected = $user_id . '==' . $user->remember_token . sha1($user_id . 'ratonlaveurs');
                if ($expected == $remember_token) {
                    $this->connect($user_id);
                    setcookie('remember', $remember_token, time() + 60 * 60 * 24 * 7, '/');
                } else {
                    setcookie('remember', null, -1, '/');
                }
            } else {
                setcookie('remember', null, -1, '/');
            }
        }
    }
}
