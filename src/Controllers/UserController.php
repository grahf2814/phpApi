<?php

namespace App\Controllers;

use App\Config\ResponseHttp;
class UserController
{
    private static $validate_rol ='/^[1,2,3]{1,1}$/';
    private static $validate_number ='/^[0-9]+$/';
    private static $validate_text ='/^[a-zA-Z]+$/';

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
            if(empty($this->data['name']) || empty($this->data['dni']) || empty($this->data['email'])|| empty($this->data['rol']) || empty($this->data['password']) || empty($this->data['confirmPassword']))
            {
                echo json_encode(ResponseHttp::status400('Todos los campos son requeridos.'));

            }
            else if (!preg_match(self::$validate_text,$this->data['name']))
            {
                echo json_encode(ResponseHttp::status400('El campo nombre solo admite texto.'));
            }
            else if (!preg_match(self::$validate_number,$this->data['dni']))
            {
                echo json_encode(ResponseHttp::status400('El campo dni solo adminte números.'));
            }
            else if (!filter_var($this->data['email'],FILTER_VALIDATE_EMAIL))
            {
                echo json_encode(ResponseHttp::status400('Formato de correo incorrecto.'));
            }
            else if (!preg_match(self::$validate_rol,$this->data['rol']))
            {
                echo json_encode(ResponseHttp::status400('Rol Inválido.'));
            }
            else if (strlen($this->data['password'])<8 ||strlen($this->data['confirmPassword'])<8)
            {
                echo json_encode(ResponseHttp::status400('La contraseña debe tener al menos 8 caracteres.'));
            }
            else if ($this->data['password'] !== $this->data['confirmPassword'])
            {
                echo json_encode(ResponseHttp::status400('Las contraseñas no coinciden.'));
            }
            else
            {
                echo json_encode(ResponseHttp::status200('Todos los parámetros correctos'));
            }
            exit;
        }
    }
}
