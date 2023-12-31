<?php

namespace App\Controllers;

use App\Config\ResponseHttp;
use App\Config\Security;
use App\Models\ProductModel;

class ProductController
{
    private static $validate_stock ='/^[0-9]{1,}$/';
    private static $validate_text ='/^[a-zA-Z]+$/';
    private static $validate_description ='/^[a-zA-Z]{1,30}$/';

    public function __construct(
        private string $method,
        private string $route,
        private array $params,
        private $data,
        private $headers
    )
    {

    }

    final public function postSave(string $endPoint)
    {
        
        error_log("Producto Save:".$this->route.',endpoint:'.$endPoint);
        if($this->method =='post' && $endPoint==$this->route)
        {
            Security::validateJWTToken($this->headers,Security::secretKey());
            if(empty($this->data['name']) || empty($this->data['description']) || empty($this->data['stock']) || empty($_FILES['product']) )
            {
                echo json_encode(ResponseHttp::status400("Todos los campos son requeridos"));
            }  
            else if(!preg_match(self::$validate_text,$this->data['name']))
            {
                echo json_encode(ResponseHttp::status400("El campo nombre solo admite texto"));
            }
            else if(!preg_match(self::$validate_description,$this->data['description']))
            {
                echo json_encode(ResponseHttp::status400("El campo descripcion solo admite texto y un máximo de 30 caracteres"));
            }
            else if(!preg_match(self::$validate_stock,$this->data['stock']))
            {
                echo json_encode(ResponseHttp::status400("El campo stock solo adminte números"));
            }
            else
            {
                $product = new ProductModel();
                echo json_encode($product->postSave($this->data,$_FILES));
            }
            exit;

        }
    }
}