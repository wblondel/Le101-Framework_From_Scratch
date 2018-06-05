<?php declare(strict_types=1);

namespace Core\String;

class Str
{
    /**
     * @param $length
     * @return bool|string
     */
    public static function random($length)
    {
        $alphabet = "0123456789azertyuiopqsdfghjklmwxcvbnAZERTYUIOPQSDFGHJKLMWXCVBN";
        return substr(str_shuffle(str_repeat($alphabet, $length)), 0, $length);
    }
}
