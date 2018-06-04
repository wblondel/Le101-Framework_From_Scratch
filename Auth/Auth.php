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
}
