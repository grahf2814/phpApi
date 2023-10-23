<?php

namespace App\Controllers;

class UserController
{
    public function __construct(
        private string $method,
        private string $route,
        private array $params,
        private $data,
        private $headers
    )
    {

    }
    final public function post(string $endPoint)
    {
        
        error_log ('Metodo:'.$this->method.', Ruta:'.$this->route.', Endpoint:'.$endPoint);
        
        if($this->method =='post' && $endPoint==$this->route)
        {
            echo json_encode('post');
            exit;
        }
    }
}
