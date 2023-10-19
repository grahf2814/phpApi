<?php

if(isset($_GET['route']))
{
    
    $url = explode ('/',$_GET['route']);
    $list = [
        'auth',
        'user',
    ];
    $file = dirname(__DIR__).'src/Routes'.$url[0].'.php';
    if(!in_array($url[0],$lista))
    {
        echo 'La Ruta no existe';
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
    }
}
else
{
    echo 'no existe la variable';
}