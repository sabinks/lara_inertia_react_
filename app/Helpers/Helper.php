<?php // Code within app\Helpers\Helper.php

namespace App\Helpers;

class Helper
{
    public static function shout(string $string)
    {
        return strtoupper($string);
    }
    public static function booleanCheck($value)
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }
}
