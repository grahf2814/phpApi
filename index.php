<?php

use App\Config\ErrorLog;
use App\Config\ResponseHttp;

require __DIR__.'/vendor/autoload.php';
//----------------------------- PARA LOS HEADERS
require __DIR__.'/headers.inc.php';

ErrorLog::activateErrorLog();

if(isset($_GET['route']))
{
    
    $url = explode ('/',$_GET['route']);
    $list = [
        'auth',
        'user',
        'product',
        'version'
    ];
    error_log("URL:".$url[0].',lista:'.$list);
    $file = __DIR__.'/src/Routes/'.$url[0].'.php';
    if(!in_array($url[0],$list))
    {
        echo json_encode(ResponseHttp::status400());
        error_log("esto es una prueba de error");
        exit;
    }
    if(is_readable($file))
    {
        require $file;
        exit;
    }
    else
    {
        echo json_encode(ResponseHttp::status400());
    }
}
else
{
    echo json_encode(ResponseHttp::status404('Recurso no existe 1'));
}