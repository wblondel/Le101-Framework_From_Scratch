<?php declare(strict_types=1);

namespace Core;

/**
 * Class Autoloader
 * @package Core
 */
class Autoloader
{
    /**
     * Register our Autoloader
     */
    public static function register()
    {
        spl_autoload_register([__CLASS__, 'autoload']);
    }

    /**
     * Include the file where the requested Class is
     * @param string $class Name of the class to load
     */
    public static function autoload(string $class)
    {
        if (strpos($class, __NAMESPACE__ . '\\') === 0) {
            $class = str_replace(__NAMESPACE__ . '\\', '', $class);
            $class = str_replace('\\', '/', $class);
            require __DIR__ . DIRECTORY_SEPARATOR . $class . '.php';
        }
    }
}
