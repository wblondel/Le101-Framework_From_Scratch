<?php declare(strict_types=1);

namespace Core\Auth;

use Core\Session\Session;

/**
 * Class Auth
 * @package Core\Auth
 */
abstract class Auth
{
    protected $session;

    /**
     * Auth constructor.
     * @param Session $session
     */
    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    /**
     * @return bool
     */
    public function isLogged()
    {
        return !is_null($this->session->read('auth'));
    }


    /**
     * Redirects the user to the index if (s)he is not logged.
     */
    public function restrict()
    {
        if (!$this->isLogged()) {
            $this->session->setFlash('danger', _("You can't access this page."));
            exit(header('Location: /'));
        }
    }

    /**
     * Register a user.
     * @param string $username
     * @param string $password
     * @param string $email
     * @param string $token
     * @return mixed
     */
    abstract protected function register(string $username, string $password, string $email, string $token);

    /**
     * Login the user.
     * @param string $username
     * @param string $password
     * @param bool $remember
     * @return mixed
     */
    abstract protected function login(string $username, string $password, bool $remember = false);

    /**
     * Confirm a user account.
     * @param string $userId
     * @param string $token
     * @return mixed
     */
    abstract protected function confirm(string $userId, string $token);

    /**
     * Logout the user.
     * @return mixed
     */
    abstract protected function logout();


    /**
     * Set the reset password token of a user.
     *
     * @param string $email
     * @param string $token
     * @return mixed
     */
    abstract protected function setResetPasswordToken(string $email, string $token);

    /**
     * Checks the given password reset token.
     *
     * @param string $userId
     * @param string $token
     * @return mixed
     */
    abstract protected function checkPasswordResetToken(string $userId, string $token);

    /**
     * Reset password of a user.
     *
     * @param string $userId
     * @param string $password
     * @return mixed
     */
    abstract protected function resetPassword(string $userId, string $password);

    /**
     * @return bool
     */
    abstract public function connectedUserExists();

    abstract public function connectFromCookie();
}
