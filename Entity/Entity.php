<?php declare(strict_types=1);

namespace Core\Entity;

/**
 * Class Entity
 * @package Core\Entity
 */
class Entity
{
    /**
     * @param $key
     * @return mixed
     */
    public function __get($key)
    {
        $method = 'get' . ucfirst($key);
        $this->$key = $this->$method();
        return $this->$key;
    }
}
