<?php
namespace App\Config;

use Dotenv\DotEnv;

class Security
{

    final public static function secretKey()
    {
        $dotenv = Dotenv::createImmutable(dirname(__DIR__,2));
        $dotenv->load();
        return $_ENV['SECRET_KEY'];
    }
}