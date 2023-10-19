<?php
namespace App\Config;

class ErrorLog
{
    public static function activateErrorLog()
    {
        error_reporting(E_ALL);
        ini_set('ignore_repeated_errors',TRUE);
        ini_set('display_errors',FALSE);
        ini_set('log_errors',TRUE);
        ini_set('error_log',__DIR__.'/Logs/php-error.log');
    }
}