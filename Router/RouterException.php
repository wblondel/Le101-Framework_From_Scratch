<?php declare(strict_types=1);

namespace Core\Router;

use Exception;

/**
 * Class RouterException
 * @package Core\Router
 */
class RouterException extends Exception
{
    /**
     * RouterException constructor.
     *
     * @param string $message
     * @param int $code
     */
    public function __construct(string $message = null, int $code = 0)
    {
        parent::__construct($message, $code);
    }
}
