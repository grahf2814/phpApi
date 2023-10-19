<?php

use App\Config\ResponseHttp;

require __DIR__.'/vendor/autoload.php';

if(isset($_GET['route']))
{
    
    $url = explode ('/',$_GET['route']);
    $list = [
        'auth',
        'user',
    ];
    $file = __DIR__.'/src/Routes/'.$url[0].'.php';
    if(!in_array($url[0],$list))
    {
        echo json_encode(ResponseHttp::status200("La ruta no existe"));
        exit;
    }
    if(is_readable($file))
    {
        require $file;
        exit;
    }
    else
    {
        echo "el archivo no existe";
        echo '<br>'.$file;
    }
}
else
{
    echo 'no existe la variable';
}